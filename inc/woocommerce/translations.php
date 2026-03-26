<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce i YITH prijevodi.
 */
add_filter('gettext', function ($translated, $text, $domain) {
	if ($domain !== 'woocommerce' && $domain !== 'yith-woocommerce-wishlist') {
		return $translated;
	}

	if ($domain === 'woocommerce') {
		switch ($text) {
			case 'No products in the cart.':
				$hr = 'Trenutačno nema proizvoda u košarici.';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Stock':
				$hr = 'Zalihe';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Subtotal':
				return 'Međuzbir';
			case 'Checkout':
				return 'Blagajna';
			case 'Add to cart':
				if ($translated !== $text) {
					return $translated;
				}
				return tersa_pll_wishlist('add_to_cart');
			case 'View cart':
				return 'Pogledaj košaricu';
			case '%s in stock':
				$hr = '%s na zalihima';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'in stock':
				$hr = 'na zalihima';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Weight':
				$hr = 'Težina';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Dimensions':
				$hr = 'Dimenzije';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Additional information':
				$hr = 'Dodatne informacije';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Reviews (%d)':
				$hr = 'Recenzije (%d)';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Reviews':
				$hr = 'Recenzije';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'There are no reviews yet.':
				$hr = 'Još nema recenzija.';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Add a review':
				$hr = 'Dodaj recenziju';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Be the first to review &ldquo;%s&rdquo;':
				$hr = 'Budite prvi koji će recenzirati &ldquo;%s&rdquo;';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Leave a Reply to %s':
				$hr = 'Odgovor na %s';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Submit':
				$hr = 'Pošalji';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Name':
				$hr = 'Ime';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Email':
				$hr = 'E-pošta';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'You must be %1$slogged in%2$s to post a review.':
				$hr = 'Morate biti %1$s prijavljeni%2$s kako biste objavili recenziju.';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Your rating':
				$hr = 'Vaša ocjena';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Rate&hellip;':
				$hr = 'Ocjena&hellip;';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Perfect':
				$hr = 'Izvrsno';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Good':
				$hr = 'Dobro';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Average':
				$hr = 'Prosječno';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Not that bad':
				$hr = 'Nije loše';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Very poor':
				$hr = 'Vrlo loše';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Your review':
				$hr = 'Vaša recenzija';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Only logged in customers who have purchased this product may leave a review.':
				$hr = 'Samo prijavljeni kupci koji su kupili ovaj proizvod mogu ostaviti recenziju.';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Your review is awaiting approval':
				$hr = 'Vaša recenzija čeka odobrenje';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'verified owner':
				$hr = 'potvrđeni kupac';
				return function_exists('pll__') ? pll__($hr) : $hr;
		}
	}

	if ($domain === 'yith-woocommerce-wishlist' || $domain === 'woocommerce') {
		switch ($text) {
			case '"%1$s" has been added to your "%2$s" list!':
				$hr = '"%1$s" je dodano na vašu listu "%2$s"!';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case '"%s" has been added to your "%s" list!':
				$hr = '"%s" je dodano na vašu listu "%s"!';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'Product name':
				return tersa_pll_wishlist('product_name');
			case 'Unit price':
				return tersa_pll_wishlist('unit_price');
			case 'Price':
			case 'Price:':
				return tersa_pll_wishlist('price');
			case 'Stock':
			case 'stock':
			case 'Stock:':
			case 'stock:':
				return tersa_pll_wishlist('stock');
			case 'Stock status':
				return tersa_pll_wishlist('stock_status');
			case 'Add to cart':
				return tersa_pll_wishlist('add_to_cart');
			case 'Remove this product':
				return tersa_pll_wishlist('remove_product');
			case 'Wishlist':
				return tersa_pll_wishlist('title');
			case 'My wishlist':
				return tersa_pll_wishlist('title_mine');
			case 'No products added to the wishlist':
				return tersa_pll_wishlist('empty');
			case 'In Stock':
			case 'in stock':
				return tersa_pll_wishlist('in_stock');
		}
	}

	return $translated;
}, 10, 3);

/**
 * Polylang string registracija.
 */
add_action('init', function () {
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
	pll_register_string('tersa_orderby_latest', 'Sortiraj po najnovijim', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_default', 'Zadano sortiranje', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_price_asc', 'Sortiraj po cijeni: nisko na visoko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_price_desc', 'Sortiraj po cijeni: visoko na nisko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_popularity', 'Sortiraj po popularnosti', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_orderby_rating', 'Sortiraj po ocjeni', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
	pll_register_string('tersa_badge_na_akciji', 'Na akciji', 'Tersa – proizvod (badge)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_zalihi', '%s na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_zalihi_simple', 'na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
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
	pll_register_string('tersa_product_weight', 'Težina', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
	pll_register_string('tersa_product_dimensions', 'Dimenzije', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
	pll_register_string('tersa_countdown_dana', 'Dana', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sati', 'Sati', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_minuta', 'Minuta', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_countdown_sekunde', 'Sekunde', 'Tersa – promo countdown', ['multiline' => false]);
	pll_register_string('tersa_related_products_title', 'Slični proizvodi', 'Tersa – proizvod (slični)', ['multiline' => false]);
	pll_register_string('tersa_wc_empty_cart', 'Trenutno nema proizvoda u košarici.', 'Tersa – WooCommerce (košarica)', ['multiline' => false]);
}, 20);
