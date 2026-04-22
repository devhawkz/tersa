<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shared helpers for WooCommerce/YITH integration.
 *
 * Keeps plugin-specific string mapping isolated from hook files.
 */

/**
 * Detekcija YITH wishlist stranice.
 */
function tersa_is_wishlist_page(): bool {
	if (function_exists('yith_wcwl_is_wishlist_page')) {
		return (bool) yith_wcwl_is_wishlist_page();
	}

	if (function_exists('tersa_get_wishlist_url')) {
		$wishlist_url = tersa_get_wishlist_url();

		if (!empty($wishlist_url)) {
			$current_request = $GLOBALS['wp']->request ?? '';
			$current_url     = home_url(add_query_arg([], $current_request));

			$wishlist_path = wp_parse_url($wishlist_url, PHP_URL_PATH);
			$current_path  = wp_parse_url($current_url, PHP_URL_PATH);

			if ($wishlist_path && $current_path && untrailingslashit($wishlist_path) === untrailingslashit($current_path)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Wishlist helper za Polylang stringove.
 */
function tersa_pll_wishlist(string $key): string {
	$map = [
		'add'                => 'Dodaj na listu želja',
		'browse'             => 'Pregledaj listu želja',
		'added'              => 'Dodano na listu želja',
		'added_notification' => '"%s" je dodano na vašu listu "%s"!',
		'remove'             => 'Ukloni s liste želja',
		'already_in'         => 'Proizvod je već na listi želja!',
		'title'              => 'Lista želja',
		'title_mine'         => 'Moja lista želja',
		'product_name'       => 'Naziv proizvoda',
		'unit_price'         => 'Cijena',
		'price'              => 'Cijena',
		'stock'              => 'Status zaliha',
		'stock_status'       => 'Status zaliha',
		'add_to_cart'        => 'Dodaj u košaricu',
		'remove_product'     => 'Ukloni ovaj proizvod',
		'empty'              => 'Nema proizvoda na listi želja.',
		'in_stock'           => 'Na stanju',
	];

	$string = $map[$key] ?? '';
	return ($string !== '' && function_exists('pll__')) ? pll__($string) : $string;
}

/**
 * Oznaka „Prethodna“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_prev_text(): string {
	$hr = 'Prethodna';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Oznaka „Sljedeća“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_next_text(): string {
	$hr = 'Sljedeća';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Cached wishlist button markup for product cards.
 * Avoids repeated shortcode parsing/rendering inside loops.
 */
function tersa_get_wishlist_button_markup(int $product_id, string $link_class): string {
	static $has_shortcode = null;
	static $cache = [];

	if ($product_id <= 0) {
		return '';
	}

	if ($has_shortcode === null) {
		$has_shortcode = function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist');
	}

	if (!$has_shortcode) {
		return '';
	}

	$cache_key = $product_id . '|' . $link_class;
	if (!isset($cache[$cache_key])) {
		$cache[$cache_key] = (string) do_shortcode(
			sprintf(
				'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="%s"]',
				$product_id,
				esc_attr($link_class)
			)
		);
	}

	return $cache[$cache_key];
}
