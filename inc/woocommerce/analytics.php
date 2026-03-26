<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce analytics:
 * - uklanja customer analytics hookove na `frontend` (smanjuje DB upite)
 * - na admin-u ostavlja originalno ponašanje
 */
function tersa_disable_woocommerce_customer_analytics_on_frontend(): void {
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
}
add_action('init', 'tersa_disable_woocommerce_customer_analytics_on_frontend', 20);

