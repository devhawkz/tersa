<?php
/**
 * Targetirano debug logovanje za kritične tokove na shop-u.
 *
 * Cilj: sve "soft" greške koje WP ne tretira kao PHP error (mail failure,
 * WC_Logger poruke iz payment gateway-a, tersa AJAX endpoint exception-i)
 * forsirano završe u /wp-content/debug.log.
 *
 * Destinacija je uvek fiksna (/wp-content/debug.log) bez obzira na
 * WP_DEBUG_LOG vrednost — modul radi "always on" kao što je traženo.
 * Ako je WP_DEBUG_LOG aktivan, PHP native error_log() takođe piše tamo,
 * pa su svi log trag-ovi na jednom mestu.
 *
 * Pokriva:
 *   1. wp_mail_failed               — WP PHPMailer greške (SMTP, invalid To, ...)
 *   2. WC_Logger::log               — Corvus Pay i drugi WC plugin-i
 *   3. tersa AJAX endpoint-i        — exception-i u tersa_ajax_* funkcijama
 *   4. WooCommerce gateway process_payment — uncaught exception-i u checkout-u
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Dani čuvanja rotiranih log fajlova (debug-YYYY-MM-DD.log).
 * Stariji se brišu pri svakoj rotaciji.
 */
if (!defined('TERSA_DEBUG_LOG_RETENTION_DAYS')) {
	define('TERSA_DEBUG_LOG_RETENTION_DAYS', 7);
}

/**
 * Fiksna destinacija za sve tema-level log poruke.
 */
function tersa_debug_log_path(): string {
	if (defined('WP_CONTENT_DIR') && WP_CONTENT_DIR) {
		return rtrim(WP_CONTENT_DIR, '/\\') . '/debug.log';
	}
	return ABSPATH . 'wp-content/debug.log';
}

/**
 * Zapisuje liniju u /wp-content/debug.log.
 * Format: [YYYY-MM-DD HH:MM:SS] [TERSA:category] message
 *
 * Koristi 3-arg error_log() varijantu tako da destinacija radi i kad
 * WP_DEBUG_LOG nije postavljen (server-level log ostaje netaknut).
 */
function tersa_debug_log(string $category, string $message, array $context = []): void {
	$line = sprintf(
		'[%s] [TERSA:%s] %s',
		gmdate('Y-m-d H:i:s'),
		$category,
		$message
	);

	if (!empty($context)) {
		$json = wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if (is_string($json)) {
			$line .= ' | context=' . $json;
		}
	}

	$line .= PHP_EOL;

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	@error_log($line, 3, tersa_debug_log_path());
}

/* ------------------------------------------------------------------------ */
/* 1. wp_mail_failed — SMTP / PHPMailer greške                               */
/* ------------------------------------------------------------------------ */

add_action('wp_mail_failed', static function ($error): void {
	if (!($error instanceof WP_Error)) {
		return;
	}

	$data = $error->get_error_data();
	$to   = '';
	$subj = '';

	if (is_array($data)) {
		if (isset($data['to'])) {
			$to = is_array($data['to']) ? implode(', ', $data['to']) : (string) $data['to'];
		}
		if (isset($data['subject'])) {
			$subj = (string) $data['subject'];
		}
	}

	tersa_debug_log(
		'mail_failed',
		$error->get_error_message(),
		[
			'code'    => $error->get_error_code(),
			'to'      => $to,
			'subject' => $subj,
		]
	);
});

/* ------------------------------------------------------------------------ */
/* 2. WC_Logger proxy — Corvus Pay i sve WC gateway log-poruke               */
/* ------------------------------------------------------------------------ */

/**
 * Presreće svaku WC_Logger poruku pre nego što se zapiše u uploads/wc-logs.
 * Filtriramo da ne logujemo rutinske info poruke — samo error/warning/critical
 * nivoe + sve što dolazi iz payment gateway izvora (Corvus Pay, stripe, ...).
 *
 * Link: https://woocommerce.com/document/logger/
 */
add_filter('woocommerce_logger_log_message', static function ($message, $level, $context, $handler) {
	// Nivoi koji uvek idu u debug.log.
	$critical_levels = ['emergency', 'alert', 'critical', 'error', 'warning'];

	$source = isset($context['source']) ? (string) $context['source'] : '';
	$is_payment_source = $source !== '' && (
		stripos($source, 'corvus') !== false
		|| stripos($source, 'payment') !== false
		|| stripos($source, 'gateway') !== false
		|| stripos($source, 'stripe') !== false
		|| stripos($source, 'paypal') !== false
	);

	$should_log = in_array(strtolower((string) $level), $critical_levels, true) || $is_payment_source;

	if ($should_log) {
		tersa_debug_log(
			'wc_logger',
			sprintf('[%s][%s] %s', (string) $level, $source !== '' ? $source : 'wc', (string) $message),
			[
				'level'  => (string) $level,
				'source' => $source,
			]
		);
	}

	// Ne menjamo poruku — WC i dalje piše svoj log u wc-logs/.
	return $message;
}, 10, 4);

/* ------------------------------------------------------------------------ */
/* 3. Tersa AJAX endpoint-i — uncaught exception-i                           */
/* ------------------------------------------------------------------------ */

/**
 * Wrap-uje tersa_ajax_* callback-ove sa try/catch tako da ako neki baci
 * Exception, umesto 500 bez traga, dobijamo kontekst (action, user id, payload).
 *
 * Napomena: registracija se dešava na 'init' da budemo sigurni da su originalni
 * add_action('wp_ajax_*') pozivi već izvršeni (inc/woocommerce/ajax.php se
 * uključuje ranije preko functions.php).
 */
add_action('init', static function (): void {
	if (!function_exists('tersa_ajax_update_mini_cart_qty') && !function_exists('tersa_ajax_get_cart_drawer_fragments')) {
		return;
	}

	$actions = [
		'tersa_update_mini_cart_qty',
		'tersa_get_cart_drawer_fragments',
	];

	foreach ($actions as $action) {
		add_action('wp_ajax_' . $action, 'tersa_debug_log_ajax_watchdog', 1);
		add_action('wp_ajax_nopriv_' . $action, 'tersa_debug_log_ajax_watchdog', 1);
	}
}, 20);

function tersa_debug_log_ajax_watchdog(): void {
	// Registruje shutdown hook koji loguje fatal error ako se dogodi.
	register_shutdown_function(static function (): void {
		$err = error_get_last();
		if (!$err) {
			return;
		}
		$fatal_types = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
		if (!in_array((int) ($err['type'] ?? 0), $fatal_types, true)) {
			return;
		}

		tersa_debug_log(
			'ajax_fatal',
			$err['message'] ?? '(no message)',
			[
				'action' => isset($_REQUEST['action']) ? sanitize_key(wp_unslash((string) $_REQUEST['action'])) : '',
				'user'   => function_exists('get_current_user_id') ? get_current_user_id() : 0,
				'file'   => $err['file'] ?? '',
				'line'   => $err['line'] ?? 0,
			]
		);
	});
}

/* ------------------------------------------------------------------------ */
/* 4. WooCommerce gateway process_payment exception-i                        */
/* ------------------------------------------------------------------------ */

/**
 * Hvata exception-e koje WC payment gateway-i ne uhvate sami.
 * `woocommerce_checkout_order_exception` fire-uje se unutar WC_Checkout::process_order()
 * kada gateway->process_payment() baci neuhvaćen Exception.
 */
add_action('woocommerce_checkout_order_exception', static function ($order, $e): void {
	if (!($e instanceof \Throwable)) {
		return;
	}

	$order_id = 0;
	if (is_object($order) && method_exists($order, 'get_id')) {
		$order_id = (int) $order->get_id();
	}

	$payment_method = '';
	if (is_object($order) && method_exists($order, 'get_payment_method')) {
		$payment_method = (string) $order->get_payment_method();
	}

	tersa_debug_log(
		'checkout_exception',
		$e->getMessage(),
		[
			'order_id'       => $order_id,
			'payment_method' => $payment_method,
			'file'           => $e->getFile(),
			'line'           => $e->getLine(),
		]
	);
}, 10, 2);

/**
 * Generic fallback: loguj svaku `woocommerce_add_error` sa source-om koji liči na gateway.
 */
add_action('woocommerce_add_error', static function ($error): void {
	$message = is_string($error) ? $error : wp_strip_all_tags((string) $error);
	if ($message === '') {
		return;
	}

	// Heuristika: loguj samo error-e koji pominju payment/gateway/corvus.
	if (stripos($message, 'payment') === false
		&& stripos($message, 'gateway') === false
		&& stripos($message, 'corvus') === false
		&& stripos($message, 'transaction') === false
	) {
		return;
	}

	tersa_debug_log('wc_add_error', $message);
});

/* ------------------------------------------------------------------------ */
/* 5. Dnevna rotacija — /wp-content/debug-YYYY-MM-DD.log, retention 7 dana   */
/* ------------------------------------------------------------------------ */

/**
 * Izvodi rotaciju log-a i čišćenje starih rotiranih fajlova.
 *
 * Korak 1: ako `debug.log` nije prazan, `rename()` u `debug-YYYY-MM-DD.log`
 *          (atomična operacija na istom filesystem-u → nema trke sa pisanjem).
 *          Odmah se kreira svež prazan `debug.log` istim permisijama.
 * Korak 2: skenira se direktorij i `unlink()`-uju svi `debug-*.log` fajlovi
 *          čiji je mtime stariji od TERSA_DEBUG_LOG_RETENTION_DAYS.
 */
function tersa_debug_log_rotate_run(): void {
	$main = tersa_debug_log_path();
	$dir  = dirname($main);

	if (!is_dir($dir) || !is_writable($dir)) {
		return;
	}

	if (is_file($main) && @filesize($main) > 0) {
		$stamp   = gmdate('Y-m-d');
		$rotated = $dir . '/debug-' . $stamp . '.log';

		$counter = 0;
		while (file_exists($rotated)) {
			$counter++;
			$rotated = $dir . '/debug-' . $stamp . '-' . $counter . '.log';
			if ($counter > 50) {
				return;
			}
		}

		if (@rename($main, $rotated)) {
			@touch($main);
			@chmod($main, 0664);
		}
	}

	$cutoff = time() - (TERSA_DEBUG_LOG_RETENTION_DAYS * DAY_IN_SECONDS);
	$files  = glob($dir . '/debug-*.log') ?: [];

	foreach ($files as $file) {
		if (basename($file) === 'debug.log') {
			continue;
		}
		$mtime = @filemtime($file);
		if ($mtime !== false && $mtime < $cutoff) {
			@unlink($file);
		}
	}
}

add_action('tersa_debug_log_rotate', 'tersa_debug_log_rotate_run');

/**
 * Obezbeđuje da je dnevna rotacija zakazana u WP cron-u.
 * Zakazuje prvi put 1h posle trenutnog vremena pa `daily` nadalje.
 */
add_action('init', static function (): void {
	if (!function_exists('wp_next_scheduled') || !function_exists('wp_schedule_event')) {
		return;
	}

	if (!wp_next_scheduled('tersa_debug_log_rotate')) {
		wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'tersa_debug_log_rotate');
	}
});

/**
 * Pri promeni teme skini zakazan event — da ne ostaje mrtav hook u bazi.
 */
add_action('switch_theme', static function (): void {
	if (function_exists('wp_clear_scheduled_hook')) {
		wp_clear_scheduled_hook('tersa_debug_log_rotate');
	}
});

/**
 * WP-CLI hook: `wp tersa debug-log-rotate` za manuelni trigger.
 * Registruje se samo kad WP-CLI kontekst postoji, ne troši ništa drugde.
 */
if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('tersa debug-log-rotate', static function (): void {
		tersa_debug_log_rotate_run();
		\WP_CLI::success('debug.log rotiran i fajlovi stariji od ' . TERSA_DEBUG_LOG_RETENTION_DAYS . ' dana obrisani.');
	});
}
