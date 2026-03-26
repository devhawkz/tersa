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

	// 2) RELATED: transient key ne varira po jeziku/instance-u.
	$related_transient = '_transient_tersa_related_' . $post_id;
	$related_timeout   = '_transient_timeout_tersa_related_' . $post_id;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name = %s OR option_name = %s",
			$related_transient,
			$related_timeout
		)
	);
}

add_action('save_post_product', 'tersa_purge_woocommerce_transients_on_product_save', 10, 3);

