<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('gettext', function ($translated, $text, $domain) {
	if ($domain !== 'woocommerce') {
		return $translated;
	}

	switch ($text) {
		case 'No products in the cart.':
			return 'Trenutno nema proizvoda u košarici.';
		case 'Subtotal':
			return 'Međuzbir';
		case 'Checkout':
			return 'Blagajna';
		case 'Add to cart':
			// Ako Polylang već ima prevod, koristi ga; inače fallback na HR/BS/Ceo prevod koji želiš.
			if ($translated !== $text) {
				return $translated;
			}
			return 'Dodaj u košaricu';
		case 'View cart':
			return 'Pogledaj košaricu';
		default:
			return $translated;
	}
}, 10, 3);

// Sakrij poruke/link “View cart” kod AJAX dodavanja u košaricu (na karticama proizvoda).
add_filter(
	'wc_add_to_cart_message_html',
	function ($message, $products, $show_qty) {
		$is_ajax = false;
		if (function_exists('wp_doing_ajax')) {
			$is_ajax = wp_doing_ajax();
		}

		$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		$is_wc_ajax = isset($_REQUEST['wc-ajax']) && in_array((string) $_REQUEST['wc-ajax'], ['add_to_cart', 'add_to_cart_form'], true);

		if ($is_ajax || $is_xhr || $is_wc_ajax) {
			return '';
		}

		return $message;
	},
	999,
	3
);

function tersa_get_cart_drawer_fragments() {
	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$cart_count = 0;
	if (function_exists('WC') && WC()->cart) {
		$cart_count = WC()->cart->get_cart_contents_count();
	}

	wp_send_json_success([
		'mini_cart_html' => $mini_cart,
		'cart_count'     => $cart_count,
		'cart_total'     => WC()->cart ? WC()->cart->get_cart_subtotal() : '',
	]);
}

function tersa_ajax_update_mini_cart_qty() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	if (!function_exists('WC') || !WC()->cart) {
		wp_send_json_error(['message' => 'Cart unavailable.'], 400);
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

	if (!$cart_item_key || !isset(WC()->cart->get_cart()[$cart_item_key])) {
		wp_send_json_error(['message' => 'Invalid cart item.'], 400);
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

// Dodatni endpoint za osvežavanje badge-a/mini-cart-a posle AJAX “Add to cart”.
function tersa_ajax_get_cart_drawer_fragments() {
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wp_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
