<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Purge WooCommerce transients-a pri promeni proizvoda.
 */
function tersa_purge_woocommerce_transients_on_product_save(int $post_id, $post = null, $update = null): void {
	global $wpdb;

	// Purge bestsellers transients (ne zavise od konkretnog product ID-a).
	// NOTE: Keširanje koristi više delova key-a (slug/tag/instance/lang), pa ovde čistimo opseg
	// preko LIKE match-om. Ako kasnije budemo hteli 100% deterministiku, možemo preći na enumeraciju key-ova.
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

