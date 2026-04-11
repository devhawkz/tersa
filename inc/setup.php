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

	// Veličina za product kartice na shop/archive stranicama
	add_image_size('tersa-card', 480, 600, true);

	// Home „Bestsellers“ kartice — aspect 1 / 1.28 (usklađeno sa .home-bestsellers__media u home.css)
	add_image_size('tersa-bestseller', 756, 968, true);

	// Hero: max širina, proporcije kao original (bez hard crop-a)
	add_image_size('tersa-hero', 1600, 0);
	add_image_size('tersa-hero-mobile', 800, 0);

	// Veličina za promo banner slike
	add_image_size('tersa-banner', 900, 700, true);
	add_image_size('tersa-countdown', 900, 900, true);
	
	

	register_nav_menus([
		'primary'         => __('Primary menu', 'tersa-shop'),
		'footer_about'    => __('Footer About menu', 'tersa-shop'),
		'footer_services' => __('Footer Services menu', 'tersa-shop'),
		'footer_legal'    => __('Footer Legal menu', 'tersa-shop'),
	]);
}
add_action('after_setup_theme', 'tersa_theme_setup');


function tersa_register_sidebars() {
	register_sidebar([
		'name'          => esc_html__('Glavna bočna traka', 'tersa-shop'),
		'id'            => 'primary-sidebar',
		'description'   => esc_html__('Widgeti za glavnu bočnu traku.', 'tersa-shop'),
		'before_widget' => '<section id="%1$s" class="widget tersa-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title tersa-widget__title">',
		'after_title'   => '</h2>',
	]);
}
add_action('widgets_init', 'tersa_register_sidebars');