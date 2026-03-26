<?php
if (!defined('ABSPATH')) {
	exit;
}

add_action('init', function (): void {
	if (is_admin()) {
		return;
	}

	remove_action(
		'woocommerce_new_customer',
		['Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore', 'update_registered_customer']
	);
	remove_action(
		'woocommerce_update_customer',
		['Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore', 'update_registered_customer']
	);
}, 20);

function tersa_purge_woocommerce_transients_on_product_save(int $post_id, $post = null, $update = null): void {
	global $wpdb;

	// Purge bestsellers transients (ne zavise od konkretnog product ID-a).
	// NOTE: Keširanje koristi više delova key-a (slug/tag/instance/lang), pa ovde čistimo opseg
	// preko options tabele LIKE match-om. Ako kasnije budemo hteli 100% deterministiku,
	// možemo preći na enumeraciju tačnih transient key-ova.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_tersa_bestsellers_%',
			'_transient_timeout_tersa_bestsellers_%'
		)
	);

	// Purge related transients (zavisi od product ID-a).
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_tersa_related_' . $post_id,
			'_transient_timeout_tersa_related_' . $post_id
		)
	);
}

add_action('save_post_product', 'tersa_purge_woocommerce_transients_on_product_save', 10, 3);

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

	return $cache[$key] ?? $output;
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
