<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('option_yith_wcwl_add_to_wishlist_text', fn() => tersa_pll_wishlist('add'));
add_filter('option_yith_wcwl_browse_wishlist_text', fn() => tersa_pll_wishlist('browse'));
add_filter('option_yith_wcwl_product_added_text', fn() => tersa_pll_wishlist('added'));
add_filter('option_yith_wcwl_remove_from_wishlist_text', fn() => tersa_pll_wishlist('remove'));
add_filter('option_yith_wcwl_already_in_wishlist_text', fn() => tersa_pll_wishlist('already_in'));
add_filter('option_yith_wcwl_wishlist_title', fn() => tersa_pll_wishlist('title_mine'));
add_filter('option_yith_wcwl_add_to_cart_text', fn() => tersa_pll_wishlist('add_to_cart'));
add_filter('yith_wcwl_browse_wishlist_label', fn() => tersa_pll_wishlist('browse'));

add_filter('yith_wcwl_ajax_add_response', function (array $response): array {
	if (empty($response['message'])) {
		return $response;
	}

	$product_name   = $response['product_name'] ?? '';
	$wishlist_title = function_exists('pll__') ? pll__('Moja lista želja') : 'Moja lista želja';

	if ($product_name !== '') {
		$hr = function_exists('pll__')
			? pll__('"%s" je dodano na vašu listu "%s"!')
			: '"%s" je dodano na vašu listu "%s"!';
		$response['message'] = sprintf($hr, $product_name, $wishlist_title);
	}

	return $response;
});
