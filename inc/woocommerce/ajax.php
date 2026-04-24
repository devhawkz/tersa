<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce AJAX endpoints (mini-cart drawer):
 * - update quantity of a cart item
 * - fetch drawer fragments (mini cart HTML/count/total)
 */

function tersa_ajax_update_mini_cart_qty() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	if (!function_exists('WC') || !WC()->cart) {
		wp_send_json_error(['message' => __('Košarica nije dostupna.', 'tersa-shop')], 400);
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
	$cart          = WC()->cart->get_cart();

	if ($cart_item_key === '' || !isset($cart[$cart_item_key])) {
		wp_send_json_error(['message' => __('Nevažeći artikl u košarici.', 'tersa-shop')], 400);
	}

	if ($quantity <= 0) {
		WC()->cart->remove_cart_item($cart_item_key);
	} else {
		WC()->cart->set_quantity($cart_item_key, $quantity, true);
	}

	WC()->cart->calculate_totals();
	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');
add_action('wp_ajax_nopriv_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');
// wc-ajax ruta (lakša od admin-ajax.php jer zaobilazi deo WP admin bootstrap-a).
add_action('wc_ajax_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');
add_action('wc_ajax_nopriv_tersa_update_mini_cart_qty', 'tersa_ajax_update_mini_cart_qty');

function tersa_ajax_get_cart_drawer_fragments() {
	// Read-only endpoint: do not hard-fail on stale nonce from cached pages.
	// Quantity-changing endpoints still enforce nonce checks.
	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wp_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wc_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wc_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
