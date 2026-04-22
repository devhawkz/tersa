<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_register_eu_projects_cpt() {
	$labels = [
		'name'               => __('EU Projekti', 'tersa-shop'),
		'singular_name'      => __('EU Projekt', 'tersa-shop'),
		'menu_name'          => __('EU Projekti', 'tersa-shop'),
		'add_new'            => __('Dodaj novi', 'tersa-shop'),
		'add_new_item'       => __('Dodaj novi EU projekt', 'tersa-shop'),
		'edit_item'          => __('Uredi EU projekt', 'tersa-shop'),
		'new_item'           => __('Novi EU projekt', 'tersa-shop'),
		'view_item'          => __('Pogledaj EU projekt', 'tersa-shop'),
		'all_items'          => __('Svi EU projekti', 'tersa-shop'),
		'search_items'       => __('Traži EU projekte', 'tersa-shop'),
		'not_found'          => __('Nema EU projekata.', 'tersa-shop'),
		'not_found_in_trash' => __('Nema EU projekata u smeću.', 'tersa-shop'),
	];

	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'has_archive'        => true,
		'rewrite'            => [
			'slug'       => 'eu-projekti',
			'with_front' => false,
		],
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
	];

	register_post_type('eu_project', $args);
}
add_action('init', 'tersa_register_eu_projects_cpt');