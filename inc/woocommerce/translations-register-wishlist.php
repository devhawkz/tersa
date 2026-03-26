<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Polylang register strings (wishlist).
 */
add_action('init', function () {
	if (!function_exists('pll_register_string')) {
		return;
	}

	pll_register_string('tersa_wishlist_added_notification', '"%s" je dodano na vašu listu "%s"!', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_add_to_wishlist', 'Dodaj na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_browse_wishlist', 'Pregledaj listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_added_to_wishlist', 'Dodano na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_remove_from_wishlist', 'Ukloni s liste želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_already_in_wishlist', 'Proizvod je već na listi želja!', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_title', 'Lista želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_title_mine', 'Moja lista želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_product_name', 'Naziv proizvoda', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_price', 'Cijena', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_stock', 'Zaliha', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_stock_status', 'Status zaliha', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_add_to_cart', 'Dodaj u košaricu', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_remove_product', 'Ukloni ovaj proizvod', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_empty', 'Nema proizvoda na listi želja.', 'Tersa – wishlist', ['multiline' => false]);
}, 20);

