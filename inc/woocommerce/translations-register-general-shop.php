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
	pll_register_string('tersa_stock_in_stock', 'Na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
	pll_register_string('tersa_stock_out_of_stock', 'Trenutačno nedostupno', 'Tersa – proizvod (stanje)', ['multiline' => false]);

	pll_register_string('tersa_product_weight', 'Težina', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
	pll_register_string('tersa_product_dimensions', 'Dimenzije', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);

	pll_register_string('tersa_countdown_dana', 'Dana', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sati', 'Sati', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_minuta', 'Minuta', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sekunde', 'Sekunde', 'Tersa – promo countdown', ['multiline' => false]);

	pll_register_string('tersa_related_products_title', 'Slični proizvodi', 'Tersa – proizvod (slični)', ['multiline' => false]);
	pll_register_string('tersa_wc_empty_cart', 'Trenutno nema proizvoda u košarici.', 'Tersa – WooCommerce (košarica)', ['multiline' => false]);

	pll_register_string('tersa_tab_description', 'Opis', 'Tersa – proizvod (tabovi)', ['multiline' => false]);
	pll_register_string('tersa_tab_additional_information', 'Dodatne informacije', 'Tersa – proizvod (tabovi)', ['multiline' => false]);

	pll_register_string('tersa_bestsellers_title', 'Bestsellers', 'Tersa – naslovnica (bestsellers)', ['multiline' => false]);

	pll_register_string('tersa_nav_open_submenu', 'Otvori podizbornik za %s', 'Tersa – navigacija', ['multiline' => false]);

	pll_register_string('tersa_pagination_previous', 'Prethodna', 'Tersa – paginacija (arhiva)', ['multiline' => false]);
	pll_register_string('tersa_pagination_next', 'Sljedeća', 'Tersa – paginacija (arhiva)', ['multiline' => false]);

	// Gumbi (shop kartica + related)
	pll_register_string('tersa_btn_vidi_opcije', 'Vidi opcije', 'Tersa – gumbi', ['multiline' => false]);
	pll_register_string('tersa_btn_dodaj_u_kosaricu', 'Dodaj u košaricu', 'Tersa – gumbi', ['multiline' => false]);

	// Badge
	pll_register_string('tersa_badge_na_snizenju', 'Na sniženju', 'Tersa – proizvod (badge)', ['multiline' => false]);

	// Single product — ARIA / accessibility labeli
	pll_register_string('tersa_product_badges_label', 'Označke proizvoda', 'Tersa – proizvod (single)', ['multiline' => false]);
	pll_register_string('tersa_product_open_image', 'Otvori sliku proizvoda', 'Tersa – proizvod (single)', ['multiline' => false]);
	pll_register_string('tersa_product_gallery_thumbs', 'Minijature u galeriji', 'Tersa – proizvod (single)', ['multiline' => false]);
	pll_register_string('tersa_product_show_image', 'Prikaži sliku %d', 'Tersa – proizvod (single)', ['multiline' => false]);
	pll_register_string('tersa_breadcrumb_nav', 'Navigacija', 'Tersa – navigacija (breadcrumb)', ['multiline' => false]);
	pll_register_string('tersa_reviews_tab', 'Recenzije (%d)', 'Tersa – proizvod (tabovi)', ['multiline' => false]);

	// Wishlist (YITH poruke)
	pll_register_string('tersa_wishlist_title', 'Moja lista želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_added', '"%s" je dodano na vašu listu "%s"!', 'Tersa – wishlist', ['multiline' => false]);

	// Shop archive — no results
	pll_register_string('shop_no_products_found', 'Nema pronađenih proizvoda za odabrane filtere.', 'Tersa Shop');
}
add_action('init', 'tersa_pll_register_general_shop_strings', 20);

