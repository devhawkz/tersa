<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Uklanja WordPress verziju iz front-end HTML-a i RSS feedova.
 * Sprečava automatsko skeniranje poznate ranjivosti po verziji.
 */
add_filter('the_generator', '__return_empty_string');

/**
 * Uklanja ?ver= query string iz URL-ova statičnih resursa na frontendu.
 * Smanjuje informacije o instalaciji dostupne automatskim skenerima.
 */
add_filter(
	'style_loader_src',
	function (string $src): string {
		if (is_admin()) {
			return $src;
		}

		return $src ? esc_url(remove_query_arg('ver', $src)) : $src;
	}
);

add_filter(
	'script_loader_src',
	function (string $src): string {
		if (is_admin()) {
			return $src;
		}

		return $src ? esc_url(remove_query_arg('ver', $src)) : $src;
	}
);

/**
 * Onemogućava enumeraciju korisnika putem REST API-ja (/wp-json/wp/v2/users).
 * Izlaganje korisničkih lozinki/user-name-ova olakšava brute-force napade.
 */
add_filter(
	'rest_endpoints',
	function (array $endpoints): array {
		if (!current_user_can('list_users')) {
			unset($endpoints['/wp/v2/users']);
			unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
		}

		return $endpoints;
	}
);

/**
 * Dodaje sigurnosne HTTP zaglavlja za sve frontend odgovore.
 * - X-Content-Type-Options: Sprečava MIME-type sniffing napad.
 * - X-Frame-Options: Sprečava clickjacking embedding u iframe.
 * - Referrer-Policy: Ograničava koje se informacije šalju u Referer zaglavlju.
 */
add_action(
	'send_headers',
	function (): void {
		if (is_admin()) {
			return;
		}

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: strict-origin-when-cross-origin');
	}
);

/**
 * Onemogućava XML-RPC pristup koji se koristi za brute-force i DDoS amplifikaciju.
 * WooCommerce ne koristi XML-RPC — isključivo REST API.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Uklanja X-Pingback zaglavlje koje otkriva da sajt koristi WordPress.
 */
add_filter(
	'wp_headers',
	function (array $headers): array {
		unset($headers['X-Pingback']);

		return $headers;
	}
);
