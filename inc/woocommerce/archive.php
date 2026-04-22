<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce archive behavior:
 * - shop/category/page cleanup hooks
 * - allowed catalog sorting
 * - taxonomy + meta filtering for archives
 * - custom `orderby` implementation
 */

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

function tersa_woocommerce_cart_fragments_params($params) {
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
add_filter('woocommerce_cart_fragments_params', 'tersa_woocommerce_cart_fragments_params', 10, 1);

function tersa_get_allowed_catalog_orderby(): array {
	$t = function (string $str): string {
		return function_exists('pll__') ? pll__($str) : $str;
	};

	return [
		'date'       => $t('Sortiraj po najnovijim'),
		'menu_order' => $t('Zadano sortiranje'),
		'price'      => $t('Sortiraj po cijeni: nisko na visoko'),
		'price-desc' => $t('Sortiraj po cijeni: visoko na nisko'),
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

	$raw    = wp_unslash($_GET[$key]);
	$values = is_array($raw) ? $raw : explode(',', (string) $raw);
	$values = array_map('sanitize_title', $values);
	$values = array_filter($values);

	return array_values(array_unique($values));
}

function tersa_get_archive_reset_url(): string {
	if (function_exists('wc_get_page_id')) {
		$shop_id = wc_get_page_id('shop');
		if ($shop_id > 0) {
			return get_permalink($shop_id);
		}
	}

	if (function_exists('get_post_type_archive_link')) {
		$archive_link = get_post_type_archive_link('product');
		if (!empty($archive_link)) {
			return $archive_link;
		}
	}

	return home_url('/');
}

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
	$filter_taxonomies = ['product_cat', 'pa_color', 'pa_material', 'pa_size', 'pa_patterns_textures', 'pa_pattern'];
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
			$tax_query[] = ['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => [$current_term->term_id]];
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
		if ($taxonomy === 'product_cat' && $current_term instanceof WP_Term && $current_term->taxonomy === 'product_cat') {
			$values = array_values(array_unique(array_merge([$current_term->slug], $values)));
		}
		$tax_query[] = ['taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $values, 'operator' => 'IN'];
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
 * Prima product_tag term cache za sve proizvode na tekućoj stranici odjednom,
 * prije nego što loop počne. WP_Query core to radi globalno za sve taksonomije,
 * ali ovo garantira da je product_tag cachiran i izbjegava per-product DB upite
 * u content-product.php kad core cache nije grijan (npr. custom query konteksti).
 * update_object_term_cache je no-op za ID-eve koji su već u cacheu.
 */
function tersa_prime_product_tag_term_cache(): void {
	global $wp_query;

	if (empty($wp_query->posts) || !is_array($wp_query->posts)) {
		return;
	}

	$ids = wp_list_pluck($wp_query->posts, 'ID');
	if (empty($ids)) {
		return;
	}

	update_object_term_cache($ids, 'product');
}
add_action('woocommerce_before_shop_loop', 'tersa_prime_product_tag_term_cache', 5);

/**
 * Registruje statične shop UI stringove za Polylang String translations.
 * Pojavljuju se u Polylang → Languages → String translations → Tersa Shop.
 */
function tersa_register_shop_ui_strings(): void {
	if (!function_exists('pll_register_string')) {
		return;
	}

	pll_register_string('shop_no_products_found', 'Nema pronađenih proizvoda za odabrane filtere.', 'Tersa Shop');
}
add_action('init', 'tersa_register_shop_ui_strings');
