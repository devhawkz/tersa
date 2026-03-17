<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_enqueue_assets() {
	$theme_version = wp_get_theme()->get('Version');

	$theme_uri = get_template_directory_uri();

	wp_enqueue_style('tersa-base', $theme_uri . '/assets/css/base.css', [], $theme_version);
	wp_enqueue_style('tersa-layout', $theme_uri . '/assets/css/layout.css', ['tersa-base'], $theme_version);
	wp_enqueue_style('tersa-header', $theme_uri . '/assets/css/header.css', ['tersa-base'], $theme_version);
	
	wp_enqueue_style('tersa-footer', $theme_uri . '/assets/css/footer.css', ['tersa-base'], $theme_version);
/*
	if (is_front_page()) {
		wp_enqueue_style('tersa-home', $theme_uri . '/assets/css/home.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}

	if (function_exists('is_shop') && (is_shop() || is_product_category())) {
		wp_enqueue_style('tersa-shop', $theme_uri . '/assets/css/shop.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}

	if (function_exists('is_product') && is_product()) {
		wp_enqueue_style('tersa-product', $theme_uri . '/assets/css/product.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}

	if (function_exists('is_cart') && is_cart()) {
		wp_enqueue_style('tersa-cart', $theme_uri . '/assets/css/cart.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}

	if (function_exists('is_checkout') && is_checkout()) {
		wp_enqueue_style('tersa-checkout', $theme_uri . '/assets/css/checkout.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}
	*/

	wp_enqueue_script('tersa-header-js', $theme_uri . '/assets/js/header.js', [], $theme_version, true);
}
add_action('wp_enqueue_scripts', 'tersa_enqueue_assets');