<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Variation Swatches — keširanje _transient_global_settings opcije.
 *
 * Woo Variation Swatches plugin poziva get_option('_transient_global_settings')
 * jednom po svakom proizvodu s varijantama na stranici (17+ puta na shop
 * listingu), a opcija nije autoloadovana pa svaki poziv udara u bazu.
 *
 * Koristimo WordPress in-memory object cache (aktivan na svakom requestu,
 * čak i bez Redis/Memcached) kako bismo vrijednost učitali samo jednom.
 *
 * pre_option_{$option}  — short-circuit prije DB upita (vraća cached vrijednost)
 * option_{$option}      — sprema vrijednost u cache nakon prvog DB upita
 */
add_filter('pre_option__transient_global_settings', function ($pre_option) {
	$cached = wp_cache_get('_transient_global_settings', 'tersa_perf');

	if ($cached !== false) {
		return $cached;
	}

	return $pre_option;
});

add_filter('option__transient_global_settings', function ($value) {
	wp_cache_add('_transient_global_settings', $value ?? '', 'tersa_perf');
	return $value;
});

/**
 * WordPress emoji skripta i inline CSS — ne koriste se u storefrontu; uklanjanje štedi
 * request i par HTTP-a (posebno vidljivo na sporijem sharedu).
 */
add_action(
	'init',
	static function (): void {
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	},
	20
);

/**
 * wp-embed skripta — potrebna je samo ako u sadržaju ugrađujete embed druge WP stranice.
 */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if (is_admin()) {
			return;
		}
		wp_deregister_script('wp-embed');
	},
	100
);

/**
 * Heartbeat na frontendu — sporiji interval = manje poziva admin-ajax.php (npr. s otvorenim tabovima).
 */
add_filter(
	'heartbeat_settings',
	static function (array $settings): array {
		if (!is_admin()) {
			$settings['interval'] = max(60, (int) ($settings['interval'] ?? 15));
		}
		return $settings;
	},
	99
);

/**
 * Dashicons na frontendu — gosti ga ne trebaju (admin bar ionako nema).
 * Ako neki plugin prikaže “prazan” ikon font, ukloni ovaj blok.
 */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if (is_admin() || is_user_logged_in()) {
			return;
		}
		wp_dequeue_style('dashicons');
	},
	100
);
