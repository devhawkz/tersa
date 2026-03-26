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
			wp_safe_redirect(home_url('/'), 301);
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

/**
 * Sigurnosna HTTP zaglavlja.
 *
 * CSP nije dodat iz teme jer lako lomi WooCommerce/payment/plugin flow.
 * To se uvodi tek posle punog QA-a i report-only faze.
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

		if (is_ssl()) {
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	},
	20
);