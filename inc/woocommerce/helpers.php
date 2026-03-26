<?php
if (!defined('ABSPATH')) {
	exit;
}

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
