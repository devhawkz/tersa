<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Polylang register strings (reviews + product tabs + aria labels).
 */
function tersa_pll_register_reviews_strings(): void {
	if (!function_exists('pll_register_string')) {
		return;
	}

	pll_register_string('tersa_product_tab_opis', 'Opis', 'Tersa – proizvod (accordion)', ['multiline' => false]);
	pll_register_string('tersa_product_tab_dodatne', 'Dodatne informacije', 'Tersa – proizvod (accordion)', ['multiline' => false]);
	pll_register_string('tersa_product_tab_recenzije', 'Recenzije (%d)', 'Tersa – proizvod (accordion)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_badges', 'Označke proizvoda', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_open_image', 'Otvori sliku proizvoda', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_open_gallery', 'Otvori galeriju slika', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_thumbs', 'Minijature u galeriji', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_show_image', 'Prikaži sliku %d', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);
	pll_register_string('tersa_product_aria_nav', 'Navigacija', 'Tersa – proizvod (pristupačnost)', ['multiline' => false]);

	pll_register_string('tersa_wc_reviews_none', 'Još nema recenzija.', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_add_review', 'Dodaj recenziju', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_first_review', 'Budite prvi koji će recenzirati &ldquo;%s&rdquo;', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_reply_to', 'Odgovor na %s', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_submit', 'Pošalji', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_name', 'Ime', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_email', 'E-pošta', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_must_login_review', 'Morate biti %1$s prijavljeni%2$s kako biste objavili recenziju.', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_your_rating', 'Vaša ocjena', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rate_ellipsis', 'Ocjena&hellip;', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rating_perfect', 'Izvrsno', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rating_good', 'Dobro', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rating_average', 'Prosječno', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rating_not_bad', 'Nije loše', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_rating_poor', 'Vrlo loše', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_your_review', 'Vaša recenzija', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_verified_only', 'Samo prijavljeni kupci koji su kupili ovaj proizvod mogu ostaviti recenziju.', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_review_awaiting', 'Vaša recenzija čeka odobrenje', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_verified_owner', 'potvrđeni kupac', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_reviews_title_1', '%1$s recenzija za %2$s', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_reviews_title_2', '%1$s recenzije za %2$s', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
	pll_register_string('tersa_wc_reviews_heading', 'Recenzije', 'Tersa – WooCommerce (recenzije)', ['multiline' => false]);
}
add_action('init', 'tersa_pll_register_reviews_strings', 20);

