<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Tersa security hardening
 *
 * Tema-level hardening sloj. Ne pokušava da zameni server/WAF zaštitu,
 * već pokriva ono što je bezbedno i smisleno držati u temi.
 */

/**
 * Ukloni generator meta tag i generator iz feedova.
 */
add_filter('the_generator', '__return_empty_string');

/**
 * XML-RPC nije potreban za ovaj projekat i ostaje ugašen.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Ukloni X-Pingback header.
 */
add_filter(
	'wp_headers',
	static function (array $headers): array {
		unset($headers['X-Pingback']);
		return $headers;
	}
);

/**
 * Sakrij REST users endpoint za neprivilegovane korisnike.
 */
add_filter(
	'rest_endpoints',
	static function (array $endpoints): array {
		if (!current_user_can('list_users')) {
			unset($endpoints['/wp/v2/users']);
			unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
		}

		return $endpoints;
	}
);

/**
 * Blokiraj klasičnu author enumeration putanju (?author=1).
 */
add_action(
	'template_redirect',
	static function (): void {
		if (is_admin()) {
			return;
		}

		if (!isset($_GET['author'])) {
			return;
		}

		$author = wp_unslash($_GET['author']);

		if (is_scalar($author) && preg_match('/^\d+$/', (string) $author)) {
			wp_safe_redirect(tersa_get_current_language_home_url(), 301);
			exit;
		}
	}
);

/**
 * Generičke login greške: ne otkrivaj da li je problem username ili password.
 */
add_filter(
	'login_errors',
	static function (): string {
		return __('Prijava nije uspela.', 'tersa-shop');
	}
);

function tersa_get_content_security_policy(): string {
	$directives = [
		'default-src'               => ["'self'"],
		'base-uri'                  => ["'self'"],
		'object-src'                => ["'none'"],
		'frame-ancestors'           => ["'self'"],
		'form-action'               => ["'self'", 'https://corvuspay.com', 'https://*.corvuspay.com', 'https://www.paypal.com', 'https://*.paypal.com'],
		'img-src'                   => ["'self'", 'data:', 'blob:', 'https:'],
		'font-src'                  => ["'self'", 'data:', 'https:'],
		'style-src'                 => ["'self'", "'unsafe-inline'", 'https:'],
		'script-src'                => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https:'],
		'connect-src'               => ["'self'", 'https:'],
		'frame-src'                 => ["'self'", 'https://www.google.com', 'https://maps.google.com', 'https://www.youtube.com', 'https://corvuspay.com', 'https://*.corvuspay.com', 'https://www.paypal.com', 'https://*.paypal.com'],
		'upgrade-insecure-requests' => [],
	];

	/**
	 * Tune CSP before switching TERSA_CSP_ENFORCE on.
	 *
	 * @param array<string, array<int, string>> $directives
	 */
	$directives = apply_filters('tersa_content_security_policy_directives', $directives);

	$parts = [];
	foreach ($directives as $directive => $sources) {
		$directive = sanitize_key((string) $directive);
		if ($directive === '') {
			continue;
		}

		$sources = is_array($sources) ? array_filter(array_map('trim', array_map('strval', $sources))) : [];
		$parts[] = trim($directive . ' ' . implode(' ', $sources));
	}

	return implode('; ', $parts);
}

function tersa_should_enforce_content_security_policy(): bool {
	$enforce = defined('TERSA_CSP_ENFORCE') && TERSA_CSP_ENFORCE;

	return (bool) apply_filters('tersa_csp_enforce', $enforce);
}

/**
 * Sigurnosna HTTP zaglavlja.
 */
add_action(
	'send_headers',
	static function (): void {
		if (headers_sent()) {
			return;
		}

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=()');

		$csp = tersa_get_content_security_policy();
		if ($csp !== '') {
			$header_name = tersa_should_enforce_content_security_policy()
				? 'Content-Security-Policy'
				: 'Content-Security-Policy-Report-Only';

			header($header_name . ': ' . $csp);
		}

		if (is_ssl()) {
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	},
	20
);
