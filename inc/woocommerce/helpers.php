<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shared helpers for WooCommerce/YITH integration.
 *
 * Keeps plugin-specific string mapping isolated from hook files.
 */

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
		'add'                => 'Dodaj na listu želja',
		'browse'             => 'Pregledaj listu želja',
		'added'              => 'Dodano na listu želja',
		'added_notification' => '"%s" je dodano na vašu listu "%s"!',
		'remove'             => 'Ukloni s liste želja',
		'already_in'         => 'Proizvod je već na listi želja!',
		'title'              => 'Lista želja',
		'title_mine'         => 'Moja lista želja',
		'product_name'       => 'Naziv proizvoda',
		'unit_price'         => 'Cijena',
		'price'              => 'Cijena',
		'stock'              => 'Status zaliha',
		'stock_status'       => 'Status zaliha',
		'add_to_cart'        => 'Dodaj u košaricu',
		'remove_product'     => 'Ukloni ovaj proizvod',
		'empty'              => 'Nema proizvoda na listi želja.',
		'in_stock'           => 'Na stanju',
	];

	$string = $map[$key] ?? '';
	return ($string !== '' && function_exists('pll__')) ? pll__($string) : $string;
}

/**
 * Oznaka „Prethodna“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_prev_text(): string {
	$hr = 'Prethodna';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Oznaka „Sljedeća“ za the_posts_pagination — hr + Polylang (Strings translations).
 */
function tersa_pagination_next_text(): string {
	$hr = 'Sljedeća';
	if (function_exists('pll__')) {
		return (string) pll__($hr);
	}
	return (string) __($hr, 'tersa-shop');
}

/**
 * Trenutni Polylang jezik kao bezbedan slug.
 *
 * @return string
 */
function tersa_get_current_language_slug(): string {
	if (!function_exists('pll_current_language')) {
		return '';
	}

	$lang = pll_current_language('slug');

	return is_string($lang) ? sanitize_key($lang) : '';
}

/**
 * Argument za Polylang-aware WP/Woo query-je.
 *
 * @return array{lang?: string}
 */
function tersa_get_current_language_query_arg(): array {
	$lang = tersa_get_current_language_slug();

	return $lang !== '' ? ['lang' => $lang] : [];
}

/**
 * Dohvata term po slug-u u trenutnom Polylang jeziku.
 *
 * @param string $slug
 * @param string $taxonomy
 * @return WP_Term|null
 */
function tersa_get_current_language_term_by_slug(string $slug, string $taxonomy): ?WP_Term {
	$slug     = sanitize_title($slug);
	$taxonomy = sanitize_key($taxonomy);

	if ($slug === '' || $taxonomy === '' || !taxonomy_exists($taxonomy)) {
		return null;
	}

	$args = [
		'taxonomy'   => $taxonomy,
		'slug'       => $slug,
		'hide_empty' => false,
		'number'     => 1,
	];

	$lang = tersa_get_current_language_slug();
	if ($lang !== '') {
		$args['lang'] = $lang;
	}

	$terms = get_terms($args);

	if (!is_wp_error($terms) && !empty($terms) && $terms[0] instanceof WP_Term) {
		return $terms[0];
	}

	$term = get_term_by('slug', $slug, $taxonomy);
	if (!$term instanceof WP_Term) {
		return null;
	}

	if ($lang !== '' && function_exists('pll_get_term_language')) {
		$term_lang = pll_get_term_language($term->term_id, 'slug');

		if ($term_lang === $lang) {
			return $term;
		}

		if (function_exists('pll_get_term')) {
			$translated_id = absint(pll_get_term($term->term_id, $lang));
			if ($translated_id) {
				$translated = get_term($translated_id, $taxonomy);
				return $translated instanceof WP_Term && !is_wp_error($translated) ? $translated : null;
			}
		}

		return null;
	}

	return $term;
}

/**
 * Vraća product ID preveden u trenutni jezik; 0 ako proizvod ne pripada jeziku.
 *
 * @param int $product_id
 * @return int
 */
function tersa_get_current_language_product_id(int $product_id): int {
	static $cache = [];

	$product_id = absint($product_id);
	if (!$product_id) {
		return 0;
	}

	$lang = tersa_get_current_language_slug();
	if ($lang === '' || !function_exists('pll_get_post')) {
		return $product_id;
	}

	$cache_key = $lang . '|' . $product_id;
	if (isset($cache[$cache_key])) {
		return $cache[$cache_key];
	}

	$translated_id = absint(pll_get_post($product_id, $lang));
	if ($translated_id) {
		return $cache[$cache_key] = $translated_id;
	}

	if (function_exists('pll_get_post_language')) {
		$post_lang = pll_get_post_language($product_id, 'slug');
		return $cache[$cache_key] = ($post_lang === $lang ? $product_id : 0);
	}

	return $cache[$cache_key] = $product_id;
}

/**
 * Normalizuje listu product ID-jeva na trenutni jezik i uklanja duplikate.
 *
 * @param array<int, int|string> $product_ids
 * @return array<int, int>
 */
function tersa_filter_product_ids_for_current_language(array $product_ids): array {
	$filtered = [];

	foreach ($product_ids as $product_id) {
		$translated_id = tersa_get_current_language_product_id(absint($product_id));

		if ($translated_id) {
			$filtered[$translated_id] = $translated_id;
		}
	}

	return array_values($filtered);
}

/**
 * Cached wishlist button markup for product cards.
 * Avoids repeated shortcode parsing/rendering inside loops.
 */
function tersa_get_wishlist_button_markup(int $product_id, string $link_class): string {
	static $has_shortcode = null;
	static $cache = [];

	if ($product_id <= 0) {
		return '';
	}

	if ($has_shortcode === null) {
		$has_shortcode = function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist');
	}

	if (!$has_shortcode) {
		return '';
	}

	$cache_key = $product_id . '|' . $link_class;
	if (!isset($cache[$cache_key])) {
		$cache[$cache_key] = (string) do_shortcode(
			sprintf(
				'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="%s"]',
				$product_id,
				esc_attr($link_class)
			)
		);
	}

	return $cache[$cache_key];
}

/**
 * Returns translated product badge rules keyed by current-language term ID.
 *
 * Base slugs are intentionally Croatian because hr is the default language. When
 * Polylang is active, each base term is mapped to the current language with
 * pll_get_term(), so EN/DE badge detection does not depend on translated names.
 *
 * @return array<int, bool> term_id => primary badge flag
 */
function tersa_get_product_badge_term_rules(): array {
	static $cache = [];

	$lang      = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	$cache_key = $lang ?: '_default';

	if (isset($cache[$cache_key])) {
		return $cache[$cache_key];
	}

	$base_rules = [
		'najprodavanije' => true,
		'najnovije'      => false,
		'novo'           => false,
	];
	$base_rules = (array) apply_filters('tersa_product_badge_base_tag_rules', $base_rules);

	$rules = [];

	foreach ($base_rules as $base_slug => $is_primary) {
		$terms = get_terms([
			'taxonomy'   => 'product_tag',
			'slug'       => sanitize_title($base_slug),
			'hide_empty' => false,
			'number'     => 1,
			'fields'     => 'ids',
			'lang'       => '',
		]);

		if (is_wp_error($terms) || empty($terms)) {
			continue;
		}

		$term_id = absint($terms[0]);
		if (!$term_id) {
			continue;
		}

		if (function_exists('pll_get_term')) {
			$translated_id = $lang ? pll_get_term($term_id, $lang) : pll_get_term($term_id);
			if ($translated_id) {
				$term_id = absint($translated_id);
			}
		}

		if ($term_id) {
			$rules[$term_id] = (bool) $is_primary;
		}
	}

	return $cache[$cache_key] = $rules;
}

/**
 * Builds product badges from product_tag terms in the current language.
 *
 * @param int $product_id Product ID.
 * @param int $limit      Maximum number of taxonomy badges, before sale badge is appended.
 * @return array<int, array{label:string,primary:bool}>
 */
function tersa_get_product_tag_badges(int $product_id, int $limit = 2): array {
	if ($product_id <= 0) {
		return [];
	}

	$badge_rules = tersa_get_product_badge_term_rules();
	if (empty($badge_rules)) {
		return [];
	}

	$terms = get_the_terms($product_id, 'product_tag');
	if (is_wp_error($terms) || !is_array($terms) || empty($terms)) {
		return [];
	}

	$badges = [];
	foreach ($terms as $term) {
		if (!$term instanceof WP_Term) {
			continue;
		}

		$term_id = (int) $term->term_id;
		if (!array_key_exists($term_id, $badge_rules)) {
			continue;
		}

		$badges[] = [
			'label'   => (string) $term->name,
			'primary' => (bool) $badge_rules[$term_id],
		];
	}

	if (empty($badges)) {
		return [];
	}

	usort($badges, static function (array $a, array $b): int {
		return (int) $b['primary'] <=> (int) $a['primary'];
	});

	return array_slice($badges, 0, max(1, $limit));
}
