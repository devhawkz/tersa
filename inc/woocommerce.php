<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce Analytics sinkronizira korisničke podatke na svakom page loadu,
 * čak i na frontendu, uzrokujući suvišne usermeta upite prema bazi.
 * Na frontendu ovo nije potrebno — dovoljno je raditi tracking pri narudžbi.
 */
add_action(
	'init',
	function (): void {
		if (is_admin()) {
			return;
		}

		remove_action(
			'woocommerce_new_customer',
			[
				'Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore',
				'update_registered_customer',
			]
		);

		remove_action(
			'woocommerce_update_customer',
			[
				'Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore',
				'update_registered_customer',
			]
		);
	},
	20
);

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
		'add'                   => 'Dodaj na listu želja',
		'browse'                => 'Pregledaj listu želja',
		'added'                 => 'Dodano na listu želja',
		'added_notification'    => '"%s" je dodano na vašu listu "%s"!',
		'remove'                => 'Ukloni s liste želja',
		'already_in'            => 'Proizvod je već na listi želja!',
		'title'                 => 'Lista želja',
		'title_mine'            => 'Moja lista želja',
		'product_name'          => 'Naziv proizvoda',
		'unit_price'            => 'Cijena',
		'price'                 => 'Cijena',
		'stock'                 => 'Status zaliha',
		'stock_status'          => 'Status zaliha',
		'add_to_cart'           => 'Dodaj u košaricu',
		'remove_product'        => 'Ukloni ovaj proizvod',
		'empty'                 => 'Nema proizvoda na listi želja.',
		'in_stock'              => 'Na stanju',
	];

	$string = $map[$key] ?? '';

	return ($string !== '' && function_exists('pll__')) ? pll__($string) : $string;
}

/**
 * WooCommerce i YITH prijevodi.
 */
add_filter(
	'gettext',
	function ($translated, $text, $domain) {
		// Tisuće gettext poziva za druge domene — izlaz odmah (CPU na sharedu).
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
				// Popup poruka kada se proizvod doda u wishlist.
				// YITH koristi %1$s/%2$s u starijim verzijama, %s u novijim.
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
	},
	10,
	3
);

/**
 * YITH Wishlist — prijevodi preko option filtera.
 */
add_filter('option_yith_wcwl_add_to_wishlist_text',      fn() => tersa_pll_wishlist('add'));
add_filter('option_yith_wcwl_browse_wishlist_text',      fn() => tersa_pll_wishlist('browse'));
add_filter('option_yith_wcwl_product_added_text',        fn() => tersa_pll_wishlist('added'));
add_filter('option_yith_wcwl_remove_from_wishlist_text', fn() => tersa_pll_wishlist('remove'));
add_filter('option_yith_wcwl_already_in_wishlist_text',  fn() => tersa_pll_wishlist('already_in'));
add_filter('option_yith_wcwl_wishlist_title',            fn() => tersa_pll_wishlist('title_mine'));
add_filter('option_yith_wcwl_add_to_cart_text',          fn() => tersa_pll_wishlist('add_to_cart'));

// Fallback za YITH template filtere.
add_filter('yith_wcwl_browse_wishlist_label', fn() => tersa_pll_wishlist('browse'));

/**
 * Fallback za YITH AJAX notifikacijsku poruku.
 * Koristi se u slučajevima kada YITH ne prolazi notifikacijski string
 * kroz gettext (npr. poruka je generisana direktno u JavaScriptu).
 * Radi za YITH WooCommerce Wishlist Free i Premium.
 */
add_filter(
	'yith_wcwl_ajax_add_response',
	function (array $response): array {
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
	}
);

/**
 * Hrvatski množina za naslov recenzija.
 */
add_filter(
	'ngettext',
	function ($translation, $single, $plural, $number, $domain) {
		if ($domain !== 'woocommerce') {
			return $translation;
		}

		if ($single !== '%1$s review for %2$s' || $plural !== '%1$s reviews for %2$s') {
			return $translation;
		}

		$n      = (int) $number;
		$mod10  = $n % 10;
		$mod100 = $n % 100;

		if ($mod10 === 1 && $mod100 !== 11) {
			$hr = '%1$s komentara za %2$s';
		} elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
			$hr = '%1$s komentara za %2$s';
		} else {
			$hr = '%1$s recenzija za %2$s';
		}

		return function_exists('pll__') ? pll__($hr) : $hr;
	},
	10,
	5
);

// Sakrij “View cart” poruku kod AJAX dodavanja u košaricu.
add_filter(
	'wc_add_to_cart_message_html',
	function ($message, $products, $show_qty) {
		$is_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : false;
		$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		$is_wc_ajax = isset($_REQUEST['wc-ajax']) && in_array((string) $_REQUEST['wc-ajax'], ['add_to_cart', 'add_to_cart_form'], true);

		if ($is_ajax || $is_xhr || $is_wc_ajax) {
			return '';
		}

		return $message;
	},
	999,
	3
);

/**
 * Mini-cart helper.
 */
function tersa_get_cart_drawer_fragments() {
	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$cart_count = 0;
	$cart_total = '';

	if (function_exists('WC') && WC()->cart) {
		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_subtotal();
	}

	wp_send_json_success([
		'mini_cart_html' => $mini_cart,
		'cart_count'     => $cart_count,
		'cart_total'     => $cart_total,
	]);
}

function tersa_ajax_update_mini_cart_qty() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	if (!function_exists('WC') || !WC()->cart) {
		wp_send_json_error(['message' => 'Cart unavailable.'], 400);
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
	$cart          = WC()->cart->get_cart();

	if ($cart_item_key === '' || !isset($cart[$cart_item_key])) {
		wp_send_json_error(['message' => 'Invalid cart item.'], 400);
	}

	if ($quantity <= 0) {
		WC()->cart->remove_cart_item($cart_item_key);
	} else {
		WC()->cart->set_quantity($cart_item_key, $quantity, true);
	}

	WC()->cart->calculate_totals();

	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');
add_action('wp_ajax_nopriv_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');

function tersa_ajax_get_cart_drawer_fragments() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');
	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wp_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');

/**
 * Product tabs.
 */
add_filter(
	'woocommerce_product_tabs',
	function ($tabs) {
		global $product;

		$hr_titles = [
			'description'            => 'Opis',
			'additional_information' => 'Dodatne informacije',
		];

		foreach ($hr_titles as $key => $title) {
			if (isset($tabs[$key]['title'])) {
				$tabs[$key]['title'] = $title;
			}
		}

		if (isset($tabs['reviews']) && $product instanceof WC_Product) {
			$count = (int) $product->get_review_count();
			$tabs['reviews']['title'] = sprintf(
				function_exists('pll__') ? pll__('Recenzije (%d)') : __('Recenzije (%d)', 'tersa-shop'),
				$count
			);
		}

		return $tabs;
	},
	20
);

/**
 * Polylang string registracija.
 */
add_action(
	'init',
	function () {
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

		pll_register_string('tersa_orderby_latest',     'Sortiraj po najnovijim',             'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
		pll_register_string('tersa_orderby_default',    'Zadano sortiranje',                  'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
		pll_register_string('tersa_orderby_price_asc',  'Sortiraj po cijeni: nisko na visoko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
		pll_register_string('tersa_orderby_price_desc', 'Sortiraj po cijeni: visoko na nisko', 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
		pll_register_string('tersa_orderby_popularity', 'Sortiraj po popularnosti',            'Tersa – kolekcija (sortiranje)', ['multiline' => false]);
		pll_register_string('tersa_orderby_rating',     'Sortiraj po ocjeni',                 'Tersa – kolekcija (sortiranje)', ['multiline' => false]);

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
	},
	20
);

/**
 * Invalidacija bestsellers transient-a pri promeni proizvoda.
 */
add_action(
	'save_post_product',
	function (): void {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_tersa_bestsellers_%' OR option_name LIKE '_transient_timeout_tersa_bestsellers_%'"
		);
	}
);

add_action(
	'save_post_product',
	function (int $post_id): void {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_tersa_related_' . $post_id,
				'_transient_timeout_tersa_related_' . $post_id
			)
		);
	}
);

/**
 * Shop archive cleanup.
 */
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

/**
 * Na archive stranicama ne trebaju WooCommerce cart fragments params.
 */
add_filter(
	'woocommerce_cart_fragments_params',
	function ($params) {
		if (
			function_exists('is_shop')
			&& (
				is_shop()
				|| is_product_category()
				|| is_product_tag()
				|| is_product_taxonomy()
			)
		) {
			return false;
		}

		return $params;
	}
);

/**
 * Dozvoljene orderby vrednosti.
 */
function tersa_get_allowed_catalog_orderby(): array {
	$t = function (string $str): string {
		return function_exists('pll__') ? pll__($str) : $str;
	};

	return [
		'date'       => $t('Sortiraj po najnovijim'),
		'menu_order' => $t('Zadano sortiranje'),
		'price'      => $t('Sortiraj po cijeni: od niske prema viskoj'),
		'price-desc' => $t('Sortiraj po cijeni: od visoke prema niskoj'),
		'popularity' => $t('Sortiraj po popularnosti'),
		'rating'     => $t('Sortiraj po ocjeni'),
	];
}

function tersa_get_current_catalog_orderby(): string {
	$allowed = array_keys(tersa_get_allowed_catalog_orderby());
	$value   = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'date';

	return in_array($value, $allowed, true) ? $value : 'date';
}

function tersa_is_sale_filter_active(): bool {
	return isset($_GET['on_sale']) && absint(wp_unslash($_GET['on_sale'])) === 1;
}

function tersa_get_active_filter_values(string $taxonomy): array {
	$key = 'filter_' . $taxonomy;

	if (!isset($_GET[$key])) {
		return [];
	}

	$raw = wp_unslash($_GET[$key]);

	$values = is_array($raw) ? $raw : explode(',', (string) $raw);
	$values = array_map('sanitize_title', $values);
	$values = array_filter($values);

	return array_values(array_unique($values));
}

function tersa_get_archive_reset_url(): string {
	if (is_product_category() || is_product_tag() || is_tax()) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			$link = get_term_link($term);
			if (!is_wp_error($link)) {
				return $link;
			}
		}
	}

	if (function_exists('wc_get_page_id')) {
		$shop_id = wc_get_page_id('shop');
		if ($shop_id > 0) {
			return get_permalink($shop_id);
		}
	}

	return home_url('/');
}

/**
 * Glavni archive query.
 */
function tersa_modify_archive_query(WP_Query $query) {
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	if (
		!function_exists('is_shop')
		|| !(
			is_shop()
			|| is_product_category()
			|| is_product_tag()
			|| is_product_taxonomy()
		)
	) {
		return;
	}

	$tax_query  = (array) $query->get('tax_query');
	$meta_query = (array) $query->get('meta_query');

	$filter_taxonomies = [
		'product_cat',
		'pa_color',
		'pa_material',
		'pa_size',
		'pa_patterns_textures',
		'pa_pattern',
	];

	$current_term = get_queried_object();

	if ($current_term instanceof WP_Term && $current_term->taxonomy === 'product_cat') {
		$has_product_cat_in_query = false;

		foreach ($tax_query as $clause) {
			if (is_array($clause) && isset($clause['taxonomy']) && $clause['taxonomy'] === 'product_cat') {
				$has_product_cat_in_query = true;
				break;
			}
		}

		if (!$has_product_cat_in_query) {
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => [$current_term->term_id],
			];
		}
	}

	foreach ($filter_taxonomies as $taxonomy) {
		if (!taxonomy_exists($taxonomy)) {
			continue;
		}

		$values = tersa_get_active_filter_values($taxonomy);

		if (empty($values)) {
			continue;
		}

		$current_term = get_queried_object();

		if (
			$taxonomy === 'product_cat'
			&& $current_term instanceof WP_Term
			&& $current_term->taxonomy === 'product_cat'
		) {
			$values = array_values(array_unique(array_merge([$current_term->slug], $values)));
		}

		$tax_query[] = [
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $values,
			'operator' => 'IN',
		];
	}

	if (count($tax_query) > 1) {
		$tax_query['relation'] = 'AND';
	}

	if (tersa_is_sale_filter_active() && function_exists('wc_get_product_ids_on_sale')) {
		$sale_ids = wc_get_product_ids_on_sale();
		$query->set('post__in', !empty($sale_ids) ? $sale_ids : [0]);
	}

	$orderby = tersa_get_current_catalog_orderby();

	switch ($orderby) {
		case 'menu_order':
			$query->set('orderby', ['menu_order' => 'ASC', 'title' => 'ASC']);
			break;

		case 'price':
			$query->set('meta_key', '_price');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', 'ASC');
			break;

		case 'price-desc':
			$query->set('meta_key', '_price');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', 'DESC');
			break;

		case 'popularity':
			$query->set('meta_key', 'total_sales');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', 'DESC');
			break;

		case 'rating':
			$query->set('meta_key', '_wc_average_rating');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', 'DESC');
			break;

		case 'date':
		default:
			$query->set('orderby', 'date');
			$query->set('order', 'DESC');
			break;
	}

	$query->set('tax_query', $tax_query);
	$query->set('meta_query', $meta_query);
}
add_action('pre_get_posts', 'tersa_modify_archive_query', 20);

/**
 * Per-request in-memory cache za yith_wcwl_add_to_wishlist shortcode.
 */
function &tersa_wishlist_shortcode_cache(): array {
	static $cache = [];
	return $cache;
}

add_filter(
	'pre_do_shortcode_tag',
	function ($output, $tag, $atts) {
		if ($tag !== 'yith_wcwl_add_to_wishlist' || is_admin()) {
			return $output;
		}

		$product_id = isset($atts['product_id']) ? (int) $atts['product_id'] : 0;
		if ($product_id <= 0) {
			return $output;
		}

		$cache = &tersa_wishlist_shortcode_cache();
		$key   = get_current_user_id() . '_' . $product_id;

		return $cache[$key] ?? $output;
	},
	10,
	3
);

add_filter(
	'do_shortcode_tag',
	function ($output, $tag, $atts) {
		if ($tag !== 'yith_wcwl_add_to_wishlist' || is_admin()) {
			return $output;
		}

		$product_id = isset($atts['product_id']) ? (int) $atts['product_id'] : 0;
		if ($product_id <= 0) {
			return $output;
		}

		$cache = &tersa_wishlist_shortcode_cache();
		$key   = get_current_user_id() . '_' . $product_id;

		if (!isset($cache[$key])) {
			$cache[$key] = $output;
		}

		return $output;
	},
	10,
	3
);

/**
 * Cart page — bestsellers section below the WooCommerce Cart block.
 *
 * Block-based cart ne okida klasične WC hookove (woocommerce_after_cart),
 * pa koristimo render_block filter koji radi i za block i za classic cart.
 */
add_filter(
	'render_block',
	function (string $block_content, array $block): string {
		if ($block['blockName'] !== 'woocommerce/cart') {
			return $block_content;
		}

		if (!function_exists('is_cart') || !is_cart()) {
			return $block_content;
		}

		$home_page_id = (int) get_option('page_on_front');

		ob_start();
		get_template_part(
			'template-parts/home/bestsellers',
			null,
			['page_id' => $home_page_id, 'instance' => 1]
		);
		$bestsellers = (string) ob_get_clean();

		if (trim($bestsellers) === '') {
			return $block_content;
		}

		return $block_content . $bestsellers;
	},
	10,
	2
);