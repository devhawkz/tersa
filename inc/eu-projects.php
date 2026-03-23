<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_register_eu_projects_cpt() {
	$labels = [
		'name'               => __('EU Projects', 'tersa-shop'),
		'singular_name'      => __('EU Project', 'tersa-shop'),
		'menu_name'          => __('EU Projects', 'tersa-shop'),
		'add_new'            => __('Add New', 'tersa-shop'),
		'add_new_item'       => __('Add New EU Project', 'tersa-shop'),
		'edit_item'          => __('Edit EU Project', 'tersa-shop'),
		'new_item'           => __('New EU Project', 'tersa-shop'),
		'view_item'          => __('View EU Project', 'tersa-shop'),
		'all_items'          => __('All EU Projects', 'tersa-shop'),
		'search_items'       => __('Search EU Projects', 'tersa-shop'),
		'not_found'          => __('No EU projects found.', 'tersa-shop'),
		'not_found_in_trash' => __('No EU projects found in Trash.', 'tersa-shop'),
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