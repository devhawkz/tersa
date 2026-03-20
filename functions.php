<?php
if (!defined('ABSPATH')) {
	exit;
}

require_once get_template_directory() . '/inc/enqueue.php';


// Theme setup
require_once get_template_directory() . '/inc/setup.php';

// Helpers
require_once get_template_directory() . '/inc/header-helpers.php';
require_once get_template_directory() . '/inc/footer-helpers.php';

// WooCommerce
require_once get_template_directory() . '/inc/woocommerce.php';

// SEO / Schema
// require_once get_template_directory() . '/inc/schema.php';

// Performance
require_once get_template_directory() . '/inc/performance.php';

// Security
// require_once get_template_directory() . '/inc/security.php';
