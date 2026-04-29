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

	// Shop archive — gumbi i UI labeli (archive-product.php)
	pll_register_string('tersa_archive_btn_apply', 'Primijeni', 'Tersa – kolekcija (gumbi)', ['multiline' => false]);
	pll_register_string('tersa_archive_btn_apply_filters', 'Primijeni filtre', 'Tersa – kolekcija (gumbi)', ['multiline' => false]);
	pll_register_string('tersa_archive_btn_reset_filters', 'Poništi filtre', 'Tersa – kolekcija (gumbi)', ['multiline' => false]);
	pll_register_string('tersa_archive_btn_show_all', 'Prikaži sve proizvode', 'Tersa – kolekcija (gumbi)', ['multiline' => false]);
	pll_register_string('tersa_archive_btn_read_more', 'Pročitaj više', 'Tersa – kolekcija (opis)', ['multiline' => false]);
	pll_register_string('tersa_archive_btn_read_less', 'Sažmi opis', 'Tersa – kolekcija (opis)', ['multiline' => false]);
	pll_register_string('tersa_archive_eyebrow_active_category', 'Aktivni filter kategorije', 'Tersa – kolekcija (opis)', ['multiline' => false]);
	pll_register_string('tersa_archive_sale_toggle', 'Prikaži samo proizvode na popustu', 'Tersa – kolekcija (filteri)', ['multiline' => false]);

	// Shop archive — filter naslovi
	pll_register_string('tersa_archive_filter_category', 'Kategorija', 'Tersa – kolekcija (filteri)', ['multiline' => false]);
	pll_register_string('tersa_archive_filter_color', 'Boja', 'Tersa – kolekcija (filteri)', ['multiline' => false]);
	pll_register_string('tersa_archive_filter_material', 'Materijal', 'Tersa – kolekcija (filteri)', ['multiline' => false]);
	pll_register_string('tersa_archive_filter_size', 'Dimenzija', 'Tersa – kolekcija (filteri)', ['multiline' => false]);
	pll_register_string('tersa_archive_filter_patterns', 'Patterns & Textures', 'Tersa – kolekcija (filteri)', ['multiline' => false]);

	// Shop archive — pristupačnost (aria)
	pll_register_string('tersa_archive_aria_breadcrumb', 'Breadcrumb', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_home', 'Naslovnica', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_view', 'Product view', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_view_list', 'List view', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_view_grid', 'Grid view', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_sort', 'Sort products', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_filters', 'Product filters', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_products', 'Products', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_archive_aria_pagination', 'Navigacija kroz proizvode', 'Tersa – kolekcija (pristupačnost)', ['multiline' => false]);

	// Cart drawer JS error
	pll_register_string('tersa_cart_load_error', 'Greška pri učitavanju košarice.', 'Tersa – WooCommerce (košarica)', ['multiline' => false]);

	// Reviews ngettext format strings (inc/woocommerce/single.php)
	pll_register_string('tersa_reviews_ngettext_single', '%1$s komentara za %2$s', 'Tersa – recenzije', ['multiline' => false]);
	pll_register_string('tersa_reviews_ngettext_plural', '%1$s recenzija za %2$s', 'Tersa – recenzije', ['multiline' => false]);

	// Footer
	pll_register_string('tersa_footer_services_heading', 'USLUGE', 'Tersa – footer', ['multiline' => false]);

	// Wishlist helper strings (inc/woocommerce/helpers.php – tersa_pll_wishlist)
	pll_register_string('tersa_wishlist_add', 'Dodaj na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_browse', 'Pregledaj listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_added_short', 'Dodano na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_remove', 'Ukloni s liste želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_already_in', 'Proizvod je već na listi želja!', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_list_title', 'Lista želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_product_name', 'Naziv proizvoda', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_price', 'Cijena', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_stock_status', 'Status zaliha', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_remove_product', 'Ukloni ovaj proizvod', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_wishlist_empty', 'Nema proizvoda na listi želja.', 'Tersa – wishlist', ['multiline' => false]);
}
add_action('init', 'tersa_pll_register_general_shop_strings', 20);

