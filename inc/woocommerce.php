<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('gettext', function ($translated, $text, $domain) {
	// WooCommerce
	if ($domain === 'woocommerce') {
		switch ($text) {
			case 'No products in the cart.':
				return 'Trenutno nema proizvoda u košarici.';
			case 'Subtotal':
				return 'Međuzbir';
			case 'Checkout':
				return 'Blagajna';
			case 'Add to cart':
				if ($translated !== $text) {
					return $translated;
				}
				return 'Dodaj u košaricu';
			case 'View cart':
				return 'Pogledaj košaricu';
			case '%s in stock':
				$hr = '%s kom na stanju';
				return function_exists('pll__') ? pll__($hr) : $hr;
			case 'in stock':
				$hr = 'na stanju';
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
			// Recenzije / komentari na proizvodu (single-product-reviews.php)
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
			default:
				return $translated;
		}
	}

	return $translated;
}, 10, 3);

/**
 * YITH Wishlist — prijevodi na hrvatski.
 *
 * YITH labele su pohranjene kao WordPress opcije u bazi podataka
 * (yith_wcwl_add_to_wishlist_text, yith_wcwl_browse_wishlist_text, itd.)
 * i čitaju se direktno s get_option(), bez ikakve veze s __() ili gettext.
 * Jedino pouzdano rješenje je option_{name} filter koji interceptuje
 * čitanje opcije direktno iz baze.
 */
function tersa_pll_wishlist( string $key ): string {
	$map = [
		'add'    => 'Dodaj na listu želja',
		'browse' => 'Pregledaj listu želja',
		'added'  => 'Dodano na listu želja',
		'remove' => 'Ukloni s liste želja',
		'already_in' => 'Proizvod je već na listi želja!',
	];
	$str = $map[ $key ] ?? '';
	return $str !== '' && function_exists('pll__') ? pll__( $str ) : $str;
}

add_filter('option_yith_wcwl_add_to_wishlist_text',    fn() => tersa_pll_wishlist('add'));
add_filter('option_yith_wcwl_browse_wishlist_text',    fn() => tersa_pll_wishlist('browse'));
add_filter('option_yith_wcwl_product_added_text',      fn() => tersa_pll_wishlist('added'));
add_filter('option_yith_wcwl_remove_from_wishlist_text', fn() => tersa_pll_wishlist('remove'));
add_filter('option_yith_wcwl_already_in_wishlist_text',  fn() => tersa_pll_wishlist('already_in'));

// Fallback: YITH template filter za browse label (renderuje se direktno u template-u)
add_filter('yith_wcwl_browse_wishlist_label', fn() => tersa_pll_wishlist('browse'));

/**
 * Hrvatski množina za naslov recenzija: „%d recenzija/e za …“ (WooCommerce _n).
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

// Sakrij poruke/link “View cart” kod AJAX dodavanja u košaricu (na karticama proizvoda).
add_filter(
	'wc_add_to_cart_message_html',
	function ($message, $products, $show_qty) {
		$is_ajax = false;
		if (function_exists('wp_doing_ajax')) {
			$is_ajax = wp_doing_ajax();
		}

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

function tersa_get_cart_drawer_fragments() {
	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$cart_count = 0;
	if (function_exists('WC') && WC()->cart) {
		$cart_count = WC()->cart->get_cart_contents_count();
	}

	wp_send_json_success([
		'mini_cart_html' => $mini_cart,
		'cart_count'     => $cart_count,
		'cart_total'     => WC()->cart ? WC()->cart->get_cart_subtotal() : '',
	]);
}

function tersa_ajax_update_mini_cart_qty() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	if (!function_exists('WC') || !WC()->cart) {
		wp_send_json_error(['message' => 'Cart unavailable.'], 400);
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

	if (!$cart_item_key || !isset(WC()->cart->get_cart()[$cart_item_key])) {
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

// Dodatni endpoint za osvežavanje badge-a/mini-cart-a posle AJAX “Add to cart”.
function tersa_ajax_get_cart_drawer_fragments() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wp_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');

/**
 * Product tabs (accordion) — naslovi na hrvatskom kao izvor za Polylang.
 */
add_filter('woocommerce_product_tabs', function ($tabs) {
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
}, 20);

/**
 * Registracija accordion stringova u Polylang (Languages → String translations).
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
	pll_register_string('tersa_badge_na_akciji', 'Na akciji', 'Tersa – proizvod (badge)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_stanju', '%s na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
	pll_register_string('tersa_stock_na_stanju_simple', 'na stanju', 'Tersa – proizvod (stanje)', ['multiline' => false]);
	pll_register_string('tersa_add_to_wishlist', 'Dodaj na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_browse_wishlist', 'Pregledaj listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_added_to_wishlist', 'Dodano na listu želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_remove_from_wishlist', 'Ukloni s liste želja', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_already_in_wishlist', 'Proizvod je već na listi želja!', 'Tersa – wishlist', ['multiline' => false]);
	pll_register_string('tersa_product_weight', 'Težina', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
	pll_register_string('tersa_product_dimensions', 'Dimenzije', 'Tersa – proizvod (dodatne informacije)', ['multiline' => false]);
}, 20);

/**
 * Shop archive cleanup
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
add_filter('woocommerce_cart_fragments_params', function ($params) {
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
});

/**
 * Dozvoljene orderby vrednosti.
 */
function tersa_get_allowed_catalog_orderby(): array {
	return [
		'date'       => __('Sort by latest', 'tersa-shop'),
		'menu_order' => __('Default sorting', 'tersa-shop'),
		'price'      => __('Sort by price: low to high', 'tersa-shop'),
		'price-desc' => __('Sort by price: high to low', 'tersa-shop'),
		'popularity' => __('Sort by popularity', 'tersa-shop'),
		'rating'     => __('Sort by rating', 'tersa-shop'),
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

	$tax_query = (array) $query->get('tax_query');
	$meta_query = (array) $query->get('meta_query');

	$filter_taxonomies = [
		'product_cat',
		'pa_color',
		'pa_material',
		'pa_size',
		'pa_patterns_textures',
		'pa_pattern',
	];

	// Na stranici kategorije proizvoda uvek uključi trenutnu kategoriju u tax_query
	// (WC ponekad ne postavi tax_query pre našeg hooka).
	$current_term = get_queried_object();
	if (
		$current_term instanceof WP_Term
		&& $current_term->taxonomy === 'product_cat'
	) {
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
 *
 * YITH interno poziva data store query() dva puta po proizvodu tokom jednog
 * shortcode rendera, što stvara duplicate queries u Query Monitoru.
 * Cache sprema HTML output prvog rendera i vraća ga za sve naknadne pozive
 * s istim product_id i user_id unutar istog page loada, bez ijednog dodatnog
 * DB upita.
 *
 * Koristi helper s pass-by-reference static varijablom kako bi oba filtera
 * dijelila isti cache prostor.
 */
function &tersa_wishlist_shortcode_cache(): array {
	static $cache = [];
	return $cache;
}

add_filter('pre_do_shortcode_tag', function ($output, $tag, $atts) {
	if ($tag !== 'yith_wcwl_add_to_wishlist' || is_admin()) {
		return $output;
	}

	$product_id = isset($atts['product_id']) ? (int) $atts['product_id'] : 0;
	if ($product_id <= 0) {
		return $output;
	}

	$cache = &tersa_wishlist_shortcode_cache();
	$key   = get_current_user_id() . '_' . $product_id;

	return isset($cache[$key]) ? $cache[$key] : $output;
}, 10, 3);

add_filter('do_shortcode_tag', function ($output, $tag, $atts) {
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
}, 10, 3);
