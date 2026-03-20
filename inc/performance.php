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
