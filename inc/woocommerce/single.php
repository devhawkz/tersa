<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Product single behavior:
 * - overwrite product tab titles
 * - pluralization for reviews count (Polylang-aware)
 */

function tersa_woocommerce_product_tabs_override($tabs) {
	global $product;

	$_t = function (string $str): string {
		return function_exists('pll__') ? pll__($str) : __($str, 'tersa-shop');
	};

	$hr_titles = [
		'description'            => $_t('Opis'),
		'additional_information' => $_t('Dodatne informacije'),
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
}
add_filter('woocommerce_product_tabs', 'tersa_woocommerce_product_tabs_override', 20);

function tersa_woocommerce_ngettext_reviews_plural($translation, $single, $plural, $number, $domain) {
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
}
add_filter('ngettext', 'tersa_woocommerce_ngettext_reviews_plural', 10, 5);

/**
 * Uklanja slučajne omotne navodnike oko naziva opcija varijacija (npr. "Ovalni" iz uvoza u atribute).
 */
function tersa_woocommerce_clean_variation_option_name($name, $term = null, $attribute = '', $product = null) {
	if (!is_string($name) || $name === '') {
		return $name;
	}

	$t = trim($name);
	if (function_exists('mb_strlen') ? mb_strlen($t) < 2 : strlen($t) < 2) {
		return $name;
	}

	$pairs = [
		['"', '"'],
		['„', '"'],
		['«', '»'],
		['“', '”'],
	];

	foreach ($pairs as $pair) {
		$open  = $pair[0];
		$close = $pair[1];
		$len   = function_exists('mb_strlen') ? mb_strlen($t) : strlen($t);
		$first = function_exists('mb_substr') ? mb_substr($t, 0, 1) : $t[0];
		$last  = function_exists('mb_substr') ? mb_substr($t, -1) : substr($t, -1);
		if ($len >= 2 && $first === $open && $last === $close) {
			$inner = function_exists('mb_substr')
				? mb_substr($t, 1, $len - 2)
				: substr($t, 1, -1);
			return trim($inner);
		}
	}

	return $name;
}
add_filter('woocommerce_variation_option_name', 'tersa_woocommerce_clean_variation_option_name', 5, 4);

/**
 * Reset varijacija: dodatna klasa za stil u temi (outline dugme).
 */
function tersa_woocommerce_reset_variations_link_markup(string $html): string {
	return str_replace(
		'class="reset_variations"',
		'class="reset_variations product-single__variations-reset"',
		$html
	);
}
add_filter('woocommerce_reset_variations_link', 'tersa_woocommerce_reset_variations_link_markup', 10, 1);

/**
 * Uklanja WooCommerce default callbacks sa woocommerce_single_product_summary hooka.
 *
 * Naša tema renderuje title, cijenu, kratki opis, add-to-cart i meta ručno u
 * content-single-product.php. Default WC callbacks bi izazvali duplikate.
 * Nakon uklanjanja, tema zove do_action('woocommerce_single_product_summary') na kraju
 * summary sekcije — WooCommerce dodaci (Subscriptions, Product Add-ons, Bookings,
 * Composite Products itd.) i dalje mogu hookovati i prikazivati vlastiti sadržaj.
 */
function tersa_remove_default_single_product_summary_hooks(): void {
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
}
add_action('woocommerce_before_single_product', 'tersa_remove_default_single_product_summary_hooks', 1);
