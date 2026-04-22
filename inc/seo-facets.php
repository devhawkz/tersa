<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Mark faceted Woo archive URLs as noindex to reduce duplicate content risk.
 */
function tersa_wp_robots_for_faceted_shop(array $robots): array {
	if (!function_exists('is_shop') || !(is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag())) {
		return $robots;
	}

	$facet_keys = [
		'on_sale',
		'orderby',
		'view',
		'min_price',
		'max_price',
		'rating_filter',
	];

	foreach (array_keys($_GET) as $key) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sanitized = sanitize_key($key);
		if (strpos($sanitized, 'filter_') === 0 || in_array($sanitized, $facet_keys, true)) {
			$robots['noindex']  = true;
			$robots['nofollow'] = false;
			break;
		}
	}

	return $robots;
}
add_filter('wp_robots', 'tersa_wp_robots_for_faceted_shop');
