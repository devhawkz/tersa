<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Polylang register strings (catalog/orderby + badge/stock + countdown + related + cart empty).
 */
function tersa_pll_register_general_shop_strings(): void {
	if (!function_exists('pll_register_string')) {
		return;
	}

	pll_register_string('tersa_orderby_latest', 'Sortiraj po najnovijim', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_default', 'Zadano sortiranje', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_price_asc', 'Sortiraj po cijeni: nisko na visoko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_price_desc', 'Sortiraj po cijeni: visoko na nisko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_popularity', 'Sortiraj po popularnosti', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_rating', 'Sortiraj po ocjeni', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);

	pll_register_string('tersa_badge_na_akciji', 'Na akciji', 'Tersa – proizvod (badge)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_zalihi', '%s na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_zalihi_simple', 'na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);

	pll_register_string('tersa_product_weight', 'Težina', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
	pll_register_string('tersa_product_dimensions', 'Dimenzije', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);

	pll_register_string('tersa_countdown_dana', 'Dana', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sati', 'Sati', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_minuta', 'Minuta', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sekunde', 'Sekunde', 'Tersa – promo countdown', ['multiline' => false]);

	pll_register_string('tersa_related_products_title', 'Slični proizvodi', 'Tersa – proizvod (slični)', ['multiline' => false]);
	pll_register_string('tersa_wc_empty_cart', 'Trenutno nema proizvoda u košarici.', 'Tersa – WooCommerce (košarica)', ['multiline' => false]);
}
add_action('init', 'tersa_pll_register_general_shop_strings', 20);

