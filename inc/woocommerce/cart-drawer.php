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

/**
 * Vraća ACF polja bestsellera za cart prikaz, keširana transientom po jeziku.
 * Koristi pll_get_post() za dohvat prevedene verzije naslovnice.
 */
function tersa_get_cart_bestseller_fields(): array {
	$lang        = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	$cache_key   = 'tersa_cart_bestseller_fields' . ($lang ? '_' . $lang : '');
	$cached      = get_transient($cache_key);

	if (is_array($cached)) {
		return $cached;
	}

	$front_id = (int) get_option('page_on_front');

	// Polylang: dohvati prevedenu verziju naslovnice za trenutni jezik.
	if ($front_id && $lang && function_exists('pll_get_post')) {
		$translated = pll_get_post($front_id, $lang);
		if ($translated) {
			$front_id = (int) $translated;
		}
	}

	if (!$front_id || !function_exists('get_field')) {
		return [];
	}

	// Prime WP meta cache za naslovnicu — izbjegavamo 8 odvojenih DB upita.
	if (function_exists('update_post_meta_cache')) {
		update_post_meta_cache([$front_id]);
	} else {
		update_meta_cache('post', [$front_id]);
	}

	$fields = [
		'show_home_bestsellers_section_1'     => get_field('show_home_bestsellers_section_1', $front_id),
		'show_home_bestsellers_section'       => get_field('show_home_bestsellers_section', $front_id),
		'home_bestsellers_section_title_1'    => get_field('home_bestsellers_section_title_1', $front_id),
		'home_bestsellers_section_title'      => get_field('home_bestsellers_section_title', $front_id),
		'home_bestsellers_badge_color_1'      => get_field('home_bestsellers_badge_color_1', $front_id),
		'home_bestsellers_badge_color'        => get_field('home_bestsellers_badge_color', $front_id),
		'home_bestsellers_product_tag_slug_1' => get_field('home_bestsellers_product_tag_slug_1', $front_id),
		'home_bestsellers_product_tag_slug'   => get_field('home_bestsellers_product_tag_slug', $front_id),
	];

	set_transient($cache_key, $fields, 12 * HOUR_IN_SECONDS);

	return $fields;
}

function tersa_append_bestsellers_to_cart_block(string $block_content, array $block): string {
	if (($block['blockName'] ?? '') !== 'woocommerce/cart') {
		return $block_content;
	}

	if (!function_exists('is_cart') || !is_cart()) {
		return $block_content;
	}

	$front_id               = (int) get_option('page_on_front');
	$lang                   = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	if ($front_id && $lang && function_exists('pll_get_post')) {
		$translated = pll_get_post($front_id, $lang);
		if ($translated) {
			$front_id = (int) $translated;
		}
	}

	$cart_bestseller_fields = tersa_get_cart_bestseller_fields();

	ob_start();
	get_template_part('template-parts/home/bestsellers', null, [
		'page_id'  => $front_id,
		'instance' => 1,
		'fields'   => $cart_bestseller_fields,
	]);
	$bestsellers = (string) ob_get_clean();

	if (trim($bestsellers) === '') {
		return $block_content;
	}

	return $block_content . $bestsellers;
}
add_filter('render_block', 'tersa_append_bestsellers_to_cart_block', 10, 2);

/**
 * Registrira badge sa brojem artikala u WooCommerce fragment sistem.
 * WC's wc-add-to-cart.js uključuje ovaj fragment u odgovor za wc-ajax=add_to_cart
 * i za wc-ajax=get_refreshed_fragments — badge se ažurira odmah, bez našeg custom AJAX-a.
 */
function tersa_wc_cart_badge_fragment(array $fragments): array {
	$count = (function_exists('WC') && WC()->cart)
		? (int) WC()->cart->get_cart_contents_count()
		: 0;

	$fragments['span.site-header__badge[data-cart-badge]'] = sprintf(
		'<span class="site-header__badge" data-cart-badge aria-hidden="true">%s</span>',
		esc_html($count)
	);

	return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'tersa_wc_cart_badge_fragment');

/**
 * Registruje kompletan mini-cart markup kao WC fragment.
 * WC's wc-cart-fragments.js kešira fragmente u sessionStorage po cart hash-u
 * i automatski ih primenjuje na svakom page load-u pre DOMContentLoaded,
 * tako da otvaranje drawer-a bude instant i bez AJAX round-tripa.
 */
function tersa_wc_drawer_fragment(array $fragments): array {
	if (!function_exists('woocommerce_mini_cart')) {
		return $fragments;
	}

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$fragments['#cart-drawer div.widget_shopping_cart_content'] =
		'<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';

	return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'tersa_wc_drawer_fragment');
