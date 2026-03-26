<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cart drawer:
 * - remove default "added to cart" message for AJAX add-to-cart flows
 * - append bestsellers section below cart block (block-based + classic)
 */

function tersa_wc_suppress_add_to_cart_message_html($message, $products, $show_qty) {
	$is_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : false;
	$is_xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	$is_wc_ajax = isset($_REQUEST['wc-ajax']) && in_array((string) $_REQUEST['wc-ajax'], ['add_to_cart', 'add_to_cart_form'], true);

	if ($is_ajax || $is_xhr || $is_wc_ajax) {
		return '';
	}

	return $message;
}
add_filter('wc_add_to_cart_message_html', 'tersa_wc_suppress_add_to_cart_message_html', 999, 3);

function tersa_get_cart_drawer_fragments() {
	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$cart_count = 0;
	$cart_total = '';

	if (function_exists('WC') && WC()->cart) {
		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_subtotal();
	}

	wp_send_json_success([
		'mini_cart_html' => $mini_cart,
		'cart_count'     => $cart_count,
		'cart_total'     => $cart_total,
	]);
}

function tersa_append_bestsellers_to_cart_block(string $block_content, array $block): string {
	if (($block['blockName'] ?? '') !== 'woocommerce/cart') {
		return $block_content;
	}

	if (!function_exists('is_cart') || !is_cart()) {
		return $block_content;
	}

	$home_page_id = (int) get_option('page_on_front');
	ob_start();
	get_template_part('template-parts/home/bestsellers', null, ['page_id' => $home_page_id, 'instance' => 1]);
	$bestsellers = (string) ob_get_clean();

	if (trim($bestsellers) === '') {
		return $block_content;
	}

	return $block_content . $bestsellers;
}
add_filter('render_block', 'tersa_append_bestsellers_to_cart_block', 10, 2);
