<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_theme_setup() {
	load_theme_textdomain('tersa-shop', get_template_directory() . '/languages');

	// WordPress kontroliše <title> tag umesto teme (potrebno za SEO plugine)
	add_theme_support('title-tag');

	// Podrška za istaknute slike na svim vrstama sadržaja
	add_theme_support('post-thumbnails');

	// HTML5 markup za standardne WordPress outpute
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);

	// Podrška za custom logo sa deklarisanim dimenzijama
	add_theme_support('custom-logo', [
		'height'      => 104,
		'width'       => 370,
		'flex-height' => true,
		'flex-width'  => true,
	]);

	// WooCommerce podrška — uklanja "Your theme does not support WooCommerce" poruku
	add_theme_support('woocommerce');
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');

	// Custom image size za logo (2× za Retina prikaz)
	// Nakon dodavanja, treba pokrenuti regeneraciju thumbnailova ako je logo već uploadovan.
	add_image_size('tersa-logo', 370, 104);

	register_nav_menus([
		'primary'         => __('Primary menu', 'tersa-shop'),
		'footer_about'    => __('Footer About menu', 'tersa-shop'),
		'footer_services' => __('Footer Services menu', 'tersa-shop'),
		'footer_legal'    => __('Footer Legal menu', 'tersa-shop'),
	]);
}
add_action('after_setup_theme', 'tersa_theme_setup');
