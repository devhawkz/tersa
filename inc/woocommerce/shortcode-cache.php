<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Per-request in-memory cache za yith_wcwl_add_to_wishlist shortcode.
 */
function &tersa_wishlist_shortcode_cache(): array {
	static $cache = [];
	return $cache;
}

function tersa_pre_do_shortcode_tag_yith_wcwl_add_to_wishlist($output, $tag, $atts) {
	if ($tag !== 'yith_wcwl_add_to_wishlist' || is_admin()) {
		return $output;
	}

	$product_id = isset($atts['product_id']) ? (int) $atts['product_id'] : 0;
	if ($product_id <= 0) {
		return $output;
	}

	$cache = &tersa_wishlist_shortcode_cache();
	$key   = get_current_user_id() . '_' . $product_id;

	return $cache[$key] ?? $output;
}
add_filter('pre_do_shortcode_tag', 'tersa_pre_do_shortcode_tag_yith_wcwl_add_to_wishlist', 10, 3);

function tersa_do_shortcode_tag_yith_wcwl_add_to_wishlist($output, $tag, $atts) {
	if ($tag !== 'yith_wcwl_add_to_wishlist' || is_admin()) {
		return $output;
	}

	$product_id = isset($atts['product_id']) ? (int) $atts['product_id'] : 0;
	if ($product_id <= 0) {
		return $output;
	}

	$cache = &tersa_wishlist_shortcode_cache();
	$key   = get_current_user_id() . '_' . $product_id;

	if (!isset($cache[$key])) {
		$cache[$key] = $output;
	}

	return $output;
}
add_filter('do_shortcode_tag', 'tersa_do_shortcode_tag_yith_wcwl_add_to_wishlist', 10, 3);

