<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * YITH wishlist hooks:
 * - option string overrides (Polylang)
 * - AJAX wishlist message translation fallback
 */

function tersa_yith_wcwl_option_text_override_from_current_filter($value) {
	$map = [
		'option_yith_wcwl_add_to_wishlist_text'          => 'add',
		'option_yith_wcwl_browse_wishlist_text'          => 'browse',
		'option_yith_wcwl_product_added_text'           => 'added',
		'option_yith_wcwl_remove_from_wishlist_text'     => 'remove',
		'option_yith_wcwl_already_in_wishlist_text'     => 'already_in',
		'option_yith_wcwl_wishlist_title'              => 'title_mine',
		'option_yith_wcwl_add_to_cart_text'            => 'add_to_cart',
		'yith_wcwl_browse_wishlist_label'              => 'browse',
	];

	$filter = function_exists('current_filter') ? current_filter() : '';
	if (!is_string($filter) || $filter === '') {
		return $value;
	}

	$key = $map[$filter] ?? '';
	return $key !== '' ? tersa_pll_wishlist($key) : $value;
}

add_filter('option_yith_wcwl_add_to_wishlist_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_browse_wishlist_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_product_added_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_remove_from_wishlist_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_already_in_wishlist_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_wishlist_title', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('option_yith_wcwl_add_to_cart_text', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);
add_filter('yith_wcwl_browse_wishlist_label', 'tersa_yith_wcwl_option_text_override_from_current_filter', 10, 1);

function tersa_yith_wcwl_ajax_add_response(array $response): array {
	if (empty($response['message'])) {
		return $response;
	}

	$product_name   = $response['product_name'] ?? '';
	$wishlist_title = tersa_pll_wishlist('title_mine');

	if ($product_name !== '') {
		$safe_product_name   = esc_html(wp_strip_all_tags((string) $product_name));
		$safe_wishlist_title = esc_html(wp_strip_all_tags((string) $wishlist_title));
		$response['message'] = sprintf(tersa_pll_wishlist('added_notification'), $safe_product_name, $safe_wishlist_title);
	}

	return $response;
}
add_filter('yith_wcwl_ajax_add_response', 'tersa_yith_wcwl_ajax_add_response', 10, 1);
