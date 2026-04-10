<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer options for homepage behavior.
 */
function tersa_customize_register(WP_Customize_Manager $wp_customize): void {
	$wp_customize->add_section(
		'tersa_homepage_options',
		[
			'title'       => __('Homepage options', 'tersa-shop'),
			'priority'    => 160,
			'description' => __('Settings for front page layout and behavior.', 'tersa-shop'),
		]
	);

	$wp_customize->add_setting(
		'tersa_show_front_page_sidebar',
		[
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		]
	);

	$wp_customize->add_control(
		'tersa_show_front_page_sidebar',
		[
			'type'        => 'checkbox',
			'section'     => 'tersa_homepage_options',
			'label'       => __('Show sidebar on homepage', 'tersa-shop'),
			'description' => __('If enabled, the primary sidebar is displayed on the front page when it has active widgets.', 'tersa-shop'),
		]
	);
}
add_action('customize_register', 'tersa_customize_register');
