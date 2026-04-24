<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cache invalidation (transients) za WooCommerce.
 *
 * Handles:
 * - `tersa_bestsellers_{tag}_{instance}` + `_{lang}` varijante
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
 * Invalidacija na spremanje proizvoda:
 * - BESTSELLERS transient za svaku (tag × instance × jezik) kombinaciju.
 * - RELATED transient za dati product_id za svaki jezik.
 *
 * @param int          $post_id
 * @param \WP_Post|null $post
 * @param bool|null    $update
 */
function tersa_purge_woocommerce_transients_on_product_save(int $post_id, $post = null, $update = null): void {
	// 1) BESTSELLERS: skup je mali i poznat — razvlačimo eksplicitno.
	$bestseller_tag_slugs = ['najprodavanije', 'najnovije'];
	$bestseller_instances = [1, 2];

	foreach ($bestseller_tag_slugs as $tag_slug) {
		foreach ($bestseller_instances as $instance) {
			$base_key = 'tersa_bestsellers_' . $tag_slug . '_' . $instance;
			tersa_delete_transient_all_langs($base_key);
		}
	}

	// 2) RELATED: ključ ovisi o product_id-u i jeziku.
	tersa_delete_transient_all_langs('tersa_related_' . $post_id);
}
add_action('save_post_product', 'tersa_purge_woocommerce_transients_on_product_save', 10, 3);

/**
 * Čisti cart bestseller fields transient kada se snimi naslovnica (front page).
 *
 * @param int $post_id
 */
function tersa_purge_cart_bestseller_fields_cache(int $post_id): void {
	$front_id = (int) get_option('page_on_front');
	if ($front_id !== $post_id) {
		return;
	}

	tersa_delete_transient_all_langs('tersa_cart_bestseller_fields');
}
add_action('save_post_page', 'tersa_purge_cart_bestseller_fields_cache', 10, 1);

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
}
add_action('edited_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('created_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('delete_term', 'tersa_purge_filter_terms_cache', 10, 3);
