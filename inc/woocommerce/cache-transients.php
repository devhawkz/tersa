<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cache invalidation (transients) za WooCommerce.
 *
 * Handles:
 * - `tersa_bestsellers_*` transients (prod tag + instance, više jezika)
 * - `tersa_related_{product_id}` transients
 *
 * NOTE:
 * - Woo transients se skladište kao `wp_options` ključevi sa prefiksom
 *   `_transient_` / `_transient_timeout_`.
 * - Koristimo LIKE sa ESCAPE da bismo izbegli da SQL `_` “pojede” kao wildcard.
 */

function tersa_sql_like_escape(string $value): string {
	// MySQL LIKE: `_` je wildcard za 1 char, `%` wildcard za 0+ chars.
	// Escapujemo i backslash da budemo sigurni sa ESCAPE klauzulom.
	$value = str_replace('\\', '\\\\', $value);
	$value = str_replace('%', '\\%', $value);
	$value = str_replace('_', '\\_', $value);
	return $value;
}

function tersa_purge_woocommerce_transients_on_product_save(int $post_id, $post = null, $update = null): void {
	global $wpdb;

	// 1) BESTSELLERS: čistimo po tag-u + instance-u (ne “sve bestsellers” odjednom).
	$bestseller_tag_slugs = ['najprodavanije', 'najnovije'];
	$bestseller_instances = [1, 2];

	foreach ($bestseller_tag_slugs as $tag_slug) {
		foreach ($bestseller_instances as $instance) {
			$transient_prefix = '_transient_tersa_bestsellers_' . $tag_slug . '_' . $instance;
			$timeout_prefix   = '_transient_timeout_tersa_bestsellers_' . $tag_slug . '_' . $instance;

			// Brišemo sve jezike/timeouts varijante koje krenu od tog prefiksa.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options}
					 WHERE option_name LIKE %s ESCAPE '\\\\'",
					tersa_sql_like_escape($transient_prefix) . '%'
				)
			);
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options}
					 WHERE option_name LIKE %s ESCAPE '\\\\'",
					tersa_sql_like_escape($timeout_prefix) . '%'
				)
			);
		}
	}

	// 2) RELATED: transient key sada varira po jeziku — brišemo sve varijante LIKE-om.
	$related_transient_prefix = '_transient_tersa_related_' . $post_id;
	$related_timeout_prefix   = '_transient_timeout_tersa_related_' . $post_id;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($related_transient_prefix) . '%'
		)
	);
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($related_timeout_prefix) . '%'
		)
	);
}

add_action('save_post_product', 'tersa_purge_woocommerce_transients_on_product_save', 10, 3);

/**
 * Čisti cart bestseller fields transient kada se spasi naslovnica (front page).
 * Briše sve jezičke varijante LIKE-om.
 */
function tersa_purge_cart_bestseller_fields_cache(int $post_id): void {
	$front_id = (int) get_option('page_on_front');
	if ($front_id !== $post_id) {
		return;
	}

	global $wpdb;
	$prefix  = '_transient_tersa_cart_bestseller_fields';
	$timeout = '_transient_timeout_tersa_cart_bestseller_fields';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($prefix) . '%'
		)
	);
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($timeout) . '%'
		)
	);
}
add_action('save_post_page', 'tersa_purge_cart_bestseller_fields_cache', 10, 1);

/**
 * Čisti filter terms transient kada se uredi termin (kategorija, atribut, tag).
 * Briše sve jezičke varijante za datu taksonomiju.
 */
function tersa_purge_filter_terms_cache(int $term_id, int $tt_id, string $taxonomy): void {
	global $wpdb;
	$prefix  = '_transient_tersa_filter_terms_' . $taxonomy;
	$timeout = '_transient_timeout_tersa_filter_terms_' . $taxonomy;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($prefix) . '%'
		)
	);
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s ESCAPE '\\\\'",
			tersa_sql_like_escape($timeout) . '%'
		)
	);
}
add_action('edited_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('created_term', 'tersa_purge_filter_terms_cache', 10, 3);
add_action('delete_term', 'tersa_purge_filter_terms_cache', 10, 3);

