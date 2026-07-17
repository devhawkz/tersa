<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce AJAX endpoints (mini-cart drawer):
 * - update quantity of a cart item
 * - fetch drawer fragments (mini cart HTML/count/total)
 */

function tersa_ajax_maybe_switch_language(): void {
	if (!function_exists('pll_switch_language')) {
		return;
	}

	$lang = '';

	if (isset($_POST['lang'])) {
		$lang = sanitize_key((string) wp_unslash($_POST['lang']));
	} elseif (isset($_GET['lang'])) {
		$lang = sanitize_key((string) wp_unslash($_GET['lang']));
	}

	if ($lang === '') {
		return;
	}

	if (function_exists('pll_languages_list')) {
		$allowed_langs = (array) pll_languages_list(['fields' => 'slug']);
		if (!in_array($lang, $allowed_langs, true)) {
			return;
		}
	}

	pll_switch_language($lang);
}

function tersa_ajax_update_mini_cart_qty() {
	tersa_ajax_maybe_switch_language();
	check_ajax_referer('tersa_cart_nonce', 'nonce');

	if (!function_exists('WC') || !WC()->cart) {
		wp_send_json_error(['message' => __('Košarica nije dostupna.', 'tersa-shop')], 400);
	}

	$cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
	$quantity      = isset($_POST['quantity']) ? (int) wc_stock_amount(wp_unslash($_POST['quantity'])) : 0;
	$cart          = WC()->cart->get_cart();

	if ($cart_item_key === '' || !isset($cart[$cart_item_key])) {
		wp_send_json_error(['message' => __('Nevažeći artikl u košarici.', 'tersa-shop')], 400);
	}

	$cart_item = $cart[$cart_item_key];
	$product   = $cart_item['data'] ?? null;

	if (!$product instanceof WC_Product || !$product->exists()) {
		wp_send_json_error(['message' => __('Proizvod nije dostupan.', 'tersa-shop')], 400);
	}

	if ($quantity <= 0) {
		WC()->cart->remove_cart_item($cart_item_key);
	} else {
		if (!$product->is_purchasable()) {
			wp_send_json_error(['message' => __('Proizvod nije moguće kupiti.', 'tersa-shop')], 400);
		}

		if ($product->is_sold_individually() && $quantity > 1) {
			wp_send_json_error(['message' => __('Ovaj proizvod se može kupiti samo u količini 1.', 'tersa-shop')], 400);
		}

		$max_quantity = (int) $product->get_max_purchase_quantity();
		if ($max_quantity > 0 && $quantity > $max_quantity) {
			wp_send_json_error(
				[
					'message' => sprintf(
						/* translators: %d: max purchase quantity */
						__('Maksimalna dostupna količina je %d.', 'tersa-shop'),
						$max_quantity
					),
				],
				400
			);
		}

		if (!$product->has_enough_stock($quantity)) {
			wp_send_json_error(['message' => __('Tražena količina trenutno nije dostupna.', 'tersa-shop')], 400);
		}

		$passed_validation = apply_filters('woocommerce_update_cart_validation', true, $cart_item_key, $cart_item, $quantity);
		if (!$passed_validation) {
			$message = __('Količina nije validna za ovaj proizvod.', 'tersa-shop');
			$notices = function_exists('wc_get_notices') ? wc_get_notices('error') : [];
			if (!empty($notices)) {
				$first_notice = reset($notices);
				if (is_array($first_notice) && !empty($first_notice['notice'])) {
					$message = wp_strip_all_tags((string) $first_notice['notice']);
				}
			}
			if (function_exists('wc_clear_notices')) {
				wc_clear_notices();
			}
			wp_send_json_error(['message' => $message], 400);
		}

		$updated = WC()->cart->set_quantity($cart_item_key, $quantity, true);
		if (false === $updated) {
			wp_send_json_error(['message' => __('Količina nije mogla biti ažurirana.', 'tersa-shop')], 400);
		}
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
	tersa_ajax_maybe_switch_language();
	// Read-only endpoint: do not hard-fail on stale nonce from cached pages.
	// Quantity-changing endpoints still enforce nonce checks.
	tersa_get_cart_drawer_fragments();
}
add_action('wp_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wp_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wc_ajax_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
add_action('wc_ajax_nopriv_tersa_get_cart_drawer_fragments', 'tersa_ajax_get_cart_drawer_fragments');
