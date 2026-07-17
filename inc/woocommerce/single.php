<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Product single behavior:
 * - overwrite product tab titles
 * - always show accordion tabs (even when empty)
 * - remove duplicate H2 headings inside accordion panels
 * - pluralization for reviews count (Polylang-aware)
 */

function tersa_product_tab_translate(string $string): string {
	if (function_exists('tersa_translate_ui_string')) {
		return tersa_translate_ui_string($string);
	}

	return function_exists('pll__') ? pll__($string) : __($string, 'tersa-shop');
}

function tersa_product_tab_empty_message(string $string): void {
	echo '<p class="product-single__accordion-empty">' . esc_html(tersa_product_tab_translate($string)) . '</p>';
}

function tersa_product_has_description_content(): bool {
	global $post;

	if (!$post instanceof WP_Post) {
		return false;
	}

	return trim(wp_strip_all_tags((string) $post->post_content)) !== '';
}

function tersa_product_has_additional_information(): bool {
	global $product;

	if (!$product instanceof WC_Product) {
		return false;
	}

	return $product->has_attributes()
		|| apply_filters('wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions());
}

function tersa_woocommerce_product_description_tab_panel(): void {
	if (tersa_product_has_description_content()) {
		woocommerce_product_description_tab();
		return;
	}

	tersa_product_tab_empty_message('Još nema opisa.');
}

function tersa_woocommerce_product_additional_information_tab_panel(): void {
	if (tersa_product_has_additional_information()) {
		woocommerce_product_additional_information_tab();
		return;
	}

	tersa_product_tab_empty_message('Nema dodatnih informacija.');
}

function tersa_woocommerce_product_reviews_tab_panel(): void {
	if (!comments_open()) {
		tersa_product_tab_empty_message('Recenzije nisu dostupne.');
		return;
	}

	comments_template();
}

function tersa_woocommerce_product_tabs_override($tabs) {
	global $product, $post;

	if (!$product instanceof WC_Product || !$post instanceof WP_Post) {
		return $tabs;
	}

	$review_count = (int) $product->get_review_count();

	$tabs['description'] = [
		'title'    => tersa_product_tab_translate('Opis'),
		'priority' => 10,
		'callback' => 'tersa_woocommerce_product_description_tab_panel',
	];

	$tabs['additional_information'] = [
		'title'    => tersa_product_tab_translate('Dodatne informacije'),
		'priority' => 20,
		'callback' => 'tersa_woocommerce_product_additional_information_tab_panel',
	];

	$tabs['reviews'] = [
		'title'    => sprintf(
			tersa_product_tab_translate('Recenzije (%d)'),
			$review_count
		),
		'priority' => 30,
		'callback' => 'tersa_woocommerce_product_reviews_tab_panel',
	];

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'tersa_woocommerce_product_tabs_override', 99);

/**
 * Accordion toggle već prikazuje naslov taba — ukloni dupli H2 iz WC tab šablona.
 */
add_filter('woocommerce_product_description_heading', '__return_empty_string');
add_filter('woocommerce_product_additional_information_heading', '__return_empty_string');

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
 * Build image payload in the same shape used by product.js galleries.
 */
function tersa_build_product_gallery_image_payload(int $attachment_id, string $size = '1536x1536', string $thumb_size = 'thumbnail', string $sizes_attr = ''): ?array {
	if ($attachment_id <= 0) {
		return null;
	}

	$src = wp_get_attachment_image_url($attachment_id, $size);
	if (!$src) {
		return null;
	}

	return [
		'id'     => $attachment_id,
		'src'    => $src,
		'full'   => wp_get_attachment_image_url($attachment_id, 'full') ?: $src,
		'thumb'  => wp_get_attachment_image_url($attachment_id, $thumb_size) ?: $src,
		'srcset' => (string) wp_get_attachment_image_srcset($attachment_id, $size),
		'sizes'  => $sizes_attr !== '' ? $sizes_attr : (string) wp_get_attachment_image_sizes($attachment_id, $size),
		'alt'    => (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
	];
}

/**
 * Frontend index of per-variation galleries, so selecting a partial attribute
 * like color can immediately replace thumbnails before WC has a full variation.
 *
 * @return array<int, array{variation_id:int,attributes:array<string,string>,gallery:array<int,array<string,mixed>>}>
 */
function tersa_get_product_variation_gallery_payload(WC_Product $product, string $size = '1536x1536', string $thumb_size = 'thumbnail', string $sizes_attr = ''): array {
	if (!$product->is_type('variable') || !method_exists($product, 'get_children')) {
		return [];
	}

	$product_id = (int) $product->get_id();
	$args_hash  = md5($size . '|' . $thumb_size . '|' . $sizes_attr);
	$cache_key  = 'tersa_variation_gallery_payload_' . $product_id;
	$cached     = get_transient($cache_key);

	if (
		is_array($cached)
		&& isset($cached['args_hash'], $cached['payload'])
		&& $cached['args_hash'] === $args_hash
		&& is_array($cached['payload'])
	) {
		return $cached['payload'];
	}

	$raw_entries         = [];
	$all_attachment_ids = [];

	foreach ((array) $product->get_children() as $variation_id) {
		$variation_id = absint($variation_id);
		if (!$variation_id) {
			continue;
		}

		$variation = wc_get_product($variation_id);
		if (!$variation instanceof WC_Product_Variation || !$variation->exists()) {
			continue;
		}

		if (method_exists($variation, 'variation_is_visible') && !$variation->variation_is_visible()) {
			continue;
		}

		$gallery_ids = function_exists('tvg_get_variation_gallery_ids')
			? tvg_get_variation_gallery_ids($variation_id)
			: [];

		if (empty($gallery_ids)) {
			$variation_image_id = (int) $variation->get_image_id();
			if ($variation_image_id > 0) {
				$gallery_ids = [$variation_image_id];
			}
		}

		$gallery_ids = array_values(array_unique(array_filter(array_map('absint', $gallery_ids))));
		if (empty($gallery_ids)) {
			continue;
		}

		$raw_entries[] = [
			'variation_id' => $variation_id,
			'attributes'   => array_map('strval', $variation->get_variation_attributes()),
			'gallery_ids'  => $gallery_ids,
		];

		$all_attachment_ids = array_merge($all_attachment_ids, $gallery_ids);
	}

	if (empty($raw_entries)) {
		set_transient(
			$cache_key,
			[
				'args_hash' => $args_hash,
				'payload'   => [],
			],
			DAY_IN_SECONDS
		);
		return [];
	}

	$all_attachment_ids = array_values(array_unique(array_filter(array_map('absint', $all_attachment_ids))));
	if (!empty($all_attachment_ids)) {
		_prime_post_caches($all_attachment_ids, true, true);
	}

	$payload = [];
	foreach ($raw_entries as $entry) {
		$gallery = [];

		foreach ($entry['gallery_ids'] as $attachment_id) {
			$item = tersa_build_product_gallery_image_payload((int) $attachment_id, $size, $thumb_size, $sizes_attr);
			if ($item !== null) {
				$gallery[] = $item;
			}
		}

		if (empty($gallery)) {
			continue;
		}

		$payload[] = [
			'variation_id' => (int) $entry['variation_id'],
			'attributes'   => $entry['attributes'],
			'gallery'      => $gallery,
		];
	}

	set_transient(
		$cache_key,
		[
			'args_hash' => $args_hash,
			'payload'   => $payload,
		],
		DAY_IN_SECONDS
	);

	return $payload;
}

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

/**
 * Preload samo glavne slike proizvoda.
 *
 * Strategija: glavna slika je LCP target i dobija visok prioritet. Ostale
 * gallery slike ostaju lazy kako mobilni browser ne bi skidao nekoliko velikih
 * 1536px varijanti pre nego što korisnik uopšte klikne thumbnail.
 */
function tersa_preload_product_gallery(): void {
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	$product = wc_get_product(get_queried_object_id());
	if (!$product instanceof WC_Product) {
		return;
	}

	$main_image_id = (int) apply_filters('tersa_main_product_image_id', (int) $product->get_image_id(), $product);
	if ($main_image_id <= 0) {
		return;
	}

	// Mora biti identično vrednostima u šablonu da browser ne duplira fetch.
	$size  = '1536x1536';
	$sizes = '(min-width: 1201px) 50vw, 100vw';

	// Glavna slika — visok prioritet.
	$main_src    = wp_get_attachment_image_url($main_image_id, $size);
	if (!$main_src) {
		return;
	}
	$main_srcset = (string) wp_get_attachment_image_srcset($main_image_id, $size);

	printf(
		'<link rel="preload" as="image" href="%s" imagesrcset="%s" imagesizes="%s" fetchpriority="high" />' . "\n",
		esc_url($main_src),
		esc_attr($main_srcset),
		esc_attr($sizes)
	);
}
add_action('wp_head', 'tersa_preload_product_gallery', 5);
