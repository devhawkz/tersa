<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cache invalidation (transients) za WooCommerce.
 *
 * Handles:
 * - `tersa_bestsellers_{tag}_{instance}` + `_{lang}` varijante (legacy slug keys)
 * - `tersa_bestsellers_term_{term_id}_{instance}` + `_{lang}` varijante
 * - `tersa_related_{product_id}` + `_{lang}` varijante
 * - `tersa_cart_bestseller_fields` + `_{lang}` varijante
 * - `tersa_filter_terms_{taxonomy}` + `_{lang}` varijante
 *
 * NAPOMENA O OBJECT CACHE-u:
 * - Bez persistent object cache-a (npr. Redis/Memcached), transient se čuva
 *   u `wp_options` pod `_transient_{key}` / `_transient_timeout_{key}`.
 * - SA persistent object cache-om, transient se NIKAD ne ispisuje u
 *   `wp_options` nego samo u object cache (`wp_cache_set` u grupi
 *   `transient` / `site-transient`).
 *
 * Zbog toga OVAJ modul NE koristi SQL `LIKE` nad `wp_options` — radi isključivo
 * preko `delete_transient()` sa poznatim ključevima. `delete_transient()`
 * interno zna oba storage sloja i radi korektno u oba slučaja.
 */

/**
 * Lista svih Polylang jezika + prazan string (za default/non-Polylang ključeve).
 *
 * @return array<int, string>
 */
function tersa_get_transient_lang_suffixes(): array {
	$suffixes = [''];

	if (function_exists('pll_languages_list')) {
		$langs = (array) pll_languages_list(['fields' => 'slug']);
		foreach ($langs as $slug) {
			$slug = (string) $slug;
			if ($slug !== '') {
				$suffixes[] = '_' . $slug;
			}
		}
	}

	return array_values(array_unique($suffixes));
}

/**
 * Briše transient za bazni ključ i sve jezičke varijante.
 */
function tersa_delete_transient_all_langs(string $base_key): void {
	if ($base_key === '') {
		return;
	}

	foreach (tersa_get_transient_lang_suffixes() as $suffix) {
		delete_transient($base_key . $suffix);
	}
}

/**
 * Taksonomije koje čine sidebar/filter cache na shop arhivi.
 *
 * @return array<int, string>
 */
function tersa_get_filter_term_cache_taxonomies(): array {
	$taxonomies = ['product_cat', 'product_tag', 'pa_color', 'pa_material', 'pa_size', 'pa_patterns_textures', 'pa_pattern'];

	if (function_exists('wc_get_attribute_taxonomies') && function_exists('wc_attribute_taxonomy_name')) {
		foreach ((array) wc_get_attribute_taxonomies() as $attribute) {
			if (!is_object($attribute) || empty($attribute->attribute_name)) {
				continue;
			}

			$taxonomies[] = wc_attribute_taxonomy_name((string) $attribute->attribute_name);
		}
	}

	return array_values(array_unique(array_filter(array_map('sanitize_key', $taxonomies))));
}

/**
 * Briše sve shop filter term cache varijante.
 */
function tersa_purge_all_filter_terms_cache(): void {
	foreach (tersa_get_filter_term_cache_taxonomies() as $taxonomy) {
		tersa_delete_transient_all_langs('tersa_filter_terms_' . $taxonomy);
	}
}

/**
 * Briše cached payload za galerije varijacija proizvoda.
 */
function tersa_purge_variation_gallery_payload_cache_for_product(int $product_id): void {
	$product_id = absint($product_id);
	if (!$product_id) {
		return;
	}

	delete_transient('tersa_variation_gallery_payload_' . $product_id);
}

/**
 * Invalidacija na spremanje proizvoda:
 * - BESTSELLERS transient za svaku (tag × instance × jezik) kombinaciju.
 * - RELATED transient za dati product_id za svaki jezik.
 *
 * @param int          $post_id
 * @param \WP_Post|null $post
 * @param bool|null    $update
 */
function tersa_purge_woocommerce_transients_on_product_save(int $post_id, $post = null, $update = null): void {
	if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}

	// 1) BESTSELLERS legacy slug keys: skup je mali i poznat — razvlačimo eksplicitno.
	$bestseller_tag_slugs = ['najprodavanije', 'najnovije'];
	$bestseller_instances = [1, 2];

	foreach ($bestseller_tag_slugs as $tag_slug) {
		foreach ($bestseller_instances as $instance) {
			$base_key = 'tersa_bestsellers_' . $tag_slug . '_' . $instance;
			tersa_delete_transient_all_langs($base_key);
		}
	}

	// 1b) BESTSELLERS term-ID keys: tags are usually a tiny taxonomy, and this runs only on product save.
	$product_tag_ids = get_terms([
		'taxonomy'   => 'product_tag',
		'hide_empty' => false,
		'fields'     => 'ids',
		'lang'       => '',
	]);

	if (!is_wp_error($product_tag_ids) && is_array($product_tag_ids)) {
		foreach ($product_tag_ids as $tag_id) {
			$tag_id = absint($tag_id);
			if (!$tag_id) {
				continue;
			}

			foreach ($bestseller_instances as $instance) {
				tersa_delete_transient_all_langs('tersa_bestsellers_term_' . $tag_id . '_' . $instance);
			}
		}
	}

	// 2) RELATED: ključ ovisi o product_id-u i jeziku.
	tersa_delete_transient_all_langs('tersa_related_' . $post_id);

	// 3) FILTER TERMS: hide_empty zavisi od statusa/termina proizvoda.
	tersa_purge_all_filter_terms_cache();

	// 4) SALE FILTER: WooCommerce sale state affects archive post__in.
	tersa_delete_transient_all_langs('tersa_sale_product_ids');

	// 5) SINGLE PRODUCT variation gallery payload.
	tersa_purge_variation_gallery_payload_cache_for_product($post_id);
}
add_action('save_post_product', 'tersa_purge_woocommerce_transients_on_product_save', 10, 3);

function tersa_purge_variation_gallery_payload_cache_on_variation_save(int $post_id, $post = null, $update = null): void {
	unset($post, $update);

	if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}

	$parent_id = (int) wp_get_post_parent_id($post_id);
	if ($parent_id > 0) {
		tersa_purge_variation_gallery_payload_cache_for_product($parent_id);
	}
}
add_action('save_post_product_variation', 'tersa_purge_variation_gallery_payload_cache_on_variation_save', 10, 3);

/**
 * Vraća front page ID i sve Polylang prevode koji nasleđuju ista ACF polja.
 *
 * @return array<int, int>
 */
function tersa_get_front_page_translation_ids_for_cache_purge(): array {
	$front_id = (int) get_option('page_on_front');
	if (!$front_id) {
		return [];
	}

	$page_ids = [$front_id];

	if (function_exists('pll_get_post_translations')) {
		$translations = pll_get_post_translations($front_id);
		if (is_array($translations)) {
			$page_ids = array_merge($page_ids, array_map('absint', $translations));
		}
	} elseif (function_exists('pll_languages_list') && function_exists('pll_get_post')) {
		foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
			$translated_id = absint(pll_get_post($front_id, (string) $lang_slug));
			if ($translated_id) {
				$page_ids[] = $translated_id;
			}
		}
	}

	return array_values(array_unique(array_filter(array_map('intval', $page_ids))));
}

/**
 * Čisti cart bestseller fields transient kada se snimi naslovnica ili njen prevod.
 *
 * @param int $post_id
 */
function tersa_purge_cart_bestseller_fields_cache(int $post_id): void {
	if (!in_array($post_id, tersa_get_front_page_translation_ids_for_cache_purge(), true)) {
		return;
	}

	tersa_delete_transient_all_langs('tersa_cart_bestseller_fields');
}
add_action('save_post_page', 'tersa_purge_cart_bestseller_fields_cache', 10, 1);

function tersa_purge_cart_bestseller_fields_cache_after_acf_save($post_id): void {
	if (!is_numeric($post_id)) {
		return;
	}

	tersa_purge_cart_bestseller_fields_cache((int) $post_id);
}
add_action('acf/save_post', 'tersa_purge_cart_bestseller_fields_cache_after_acf_save', 20);

/**
 * Čisti filter terms transient kada se uredi/kreira/obriše termin (kategorija,
 * atribut, tag). Taksonomija se zna iz hook argumenata — brišemo samo nju.
 *
 * @param int    $term_id
 * @param int    $tt_id
 * @param string $taxonomy
 */
function tersa_purge_filter_terms_cache(int $term_id, int $tt_id, string $taxonomy): void {
	if ($taxonomy === '') {
		return;
	}

	tersa_delete_transient_all_langs('tersa_filter_terms_' . $taxonomy);

	if ($taxonomy === 'product_tag') {
		$term_id = absint($term_id);
		if (!$term_id) {
			return;
		}

		foreach ([1, 2] as $instance) {
			tersa_delete_transient_all_langs('tersa_bestsellers_term_' . $term_id . '_' . $instance);
		}
	}
}
add_action('edited_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('created_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('delete_term', 'tersa_purge_filter_terms_cache', 10, 3);

function tersa_purge_filter_terms_cache_on_product_terms($object_id, $terms, $tt_ids, string $taxonomy, bool $append, array $old_tt_ids): void {
	unset($terms, $tt_ids, $append, $old_tt_ids);

	if ('product' !== get_post_type((int) $object_id)) {
		return;
	}

	if (!in_array($taxonomy, tersa_get_filter_term_cache_taxonomies(), true)) {
		return;
	}

	tersa_delete_transient_all_langs('tersa_filter_terms_' . $taxonomy);
}
add_action('set_object_terms', 'tersa_purge_filter_terms_cache_on_product_terms', 10, 6);

function tersa_purge_filter_terms_cache_on_product_status_change(string $new_status, string $old_status, WP_Post $post): void {
	if ($new_status === $old_status || 'product' !== $post->post_type) {
		return;
	}

	tersa_purge_all_filter_terms_cache();
}
add_action('transition_post_status', 'tersa_purge_filter_terms_cache_on_product_status_change', 10, 3);

function tersa_purge_filter_terms_cache_on_product_delete(int $post_id, $post = null): void {
	$post_obj = $post instanceof WP_Post ? $post : get_post($post_id);
	if (!$post_obj instanceof WP_Post) {
		return;
	}

	if ('product' === $post_obj->post_type) {
		tersa_purge_all_filter_terms_cache();
		tersa_purge_variation_gallery_payload_cache_for_product($post_id);
		return;
	}

	if ('product_variation' === $post_obj->post_type && (int) $post_obj->post_parent > 0) {
		tersa_purge_variation_gallery_payload_cache_for_product((int) $post_obj->post_parent);
	}
}
add_action('deleted_post', 'tersa_purge_filter_terms_cache_on_product_delete', 10, 2);

function tersa_purge_filter_terms_cache_on_attribute_change(): void {
	tersa_purge_all_filter_terms_cache();
}
add_action('woocommerce_attribute_added', 'tersa_purge_filter_terms_cache_on_attribute_change', 10, 0);
add_action('woocommerce_attribute_updated', 'tersa_purge_filter_terms_cache_on_attribute_change', 10, 0);
add_action('woocommerce_attribute_deleted', 'tersa_purge_filter_terms_cache_on_attribute_change', 10, 0);
