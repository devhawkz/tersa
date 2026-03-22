<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_enqueue_assets() {
	$theme_version = wp_get_theme()->get('Version');
	$theme_uri     = get_template_directory_uri();

	wp_enqueue_style('tersa-base', $theme_uri . '/assets/css/base.css', [], $theme_version);
	wp_enqueue_style('tersa-layout', $theme_uri . '/assets/css/layout.css', ['tersa-base'], $theme_version);
	wp_enqueue_style('tersa-header', $theme_uri . '/assets/css/header.css', ['tersa-base'], $theme_version);
	wp_enqueue_style('tersa-footer', $theme_uri . '/assets/css/footer.css', ['tersa-base'], $theme_version);

	//contact page style
	if (is_page_template('page-templates/template-contact.php')) {
		wp_enqueue_style(
			'tersa-contact',
			$theme_uri . '/assets/css/contact.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);
	}

	//about page style
	if (is_page_template('page-templates/template-about.php')) {
		wp_enqueue_style(
			'tersa-about',
			$theme_uri . '/assets/css/about.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);
	}
	
	if (is_front_page()) {
		wp_enqueue_style(
			'tersa-home',
			$theme_uri . '/assets/css/home.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);
	
		wp_enqueue_script(
			'tersa-home',
			$theme_uri . '/assets/js/home.js',
			[],
			$theme_version,
			true
		);
	}

	if (
		function_exists('is_shop')
		&& (
			is_shop()
			|| is_product_category()
			|| is_product_tag()
			|| is_product_taxonomy()
		)
	) {
		wp_enqueue_style(
			'tersa-shop',
			$theme_uri . '/assets/css/shop.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);

		wp_enqueue_script(
			'tersa-shop',
			$theme_uri . '/assets/js/shop.js',
			[],
			$theme_version,
			true
		);
	}

	if (function_exists('is_product') && is_product()) {
		wp_enqueue_style(
			'tersa-home',
			$theme_uri . '/assets/css/home.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);

		wp_enqueue_style(
			'tersa-product',
			$theme_uri . '/assets/css/product.css',
			['tersa-base', 'tersa-layout', 'tersa-home'],
			$theme_version
		);
	
		wp_enqueue_script(
			'tersa-product',
			$theme_uri . '/assets/js/product.js',
			[],
			$theme_version,
			true
		);
	}

	if (function_exists('is_cart') && is_cart()) {
		wp_enqueue_style(
			'tersa-home',
			$theme_uri . '/assets/css/home.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);

		wp_enqueue_script(
			'tersa-home',
			$theme_uri . '/assets/js/home.js',
			[],
			$theme_version,
			true
		);
	}

	if (tersa_is_wishlist_page()) {
		wp_enqueue_style(
			'tersa-wishlist',
			$theme_uri . '/assets/css/wishlist.css',
			['tersa-base', 'tersa-layout'],
			$theme_version
		);
	}

/*
	if (function_exists('is_cart') && is_cart()) {
		wp_enqueue_style('tersa-cart', $theme_uri . '/assets/css/cart.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}

	if (function_exists('is_checkout') && is_checkout()) {
		wp_enqueue_style('tersa-checkout', $theme_uri . '/assets/css/checkout.css', ['tersa-base', 'tersa-layout'], $theme_version);
	}
	*/

	wp_enqueue_script('tersa-header-js', $theme_uri . '/assets/js/header.js', [], $theme_version, true);

	wp_localize_script('tersa-header-js', 'tersaCartDrawer', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('tersa_cart_nonce'),
	]);
}
add_action('wp_enqueue_scripts', 'tersa_enqueue_assets');
