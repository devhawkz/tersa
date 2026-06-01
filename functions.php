<?php
if (!defined('ABSPATH')) {
	exit;
}

defined('TERSA_THEME_DIR') || define('TERSA_THEME_DIR', get_template_directory());

// Theme setup
require_once TERSA_THEME_DIR . '/inc/setup.php';
require_once TERSA_THEME_DIR . '/inc/customizer.php';

require_once TERSA_THEME_DIR . '/inc/enqueue.php';
require_once TERSA_THEME_DIR . '/inc/eu-projects.php';

// Helpers
require_once TERSA_THEME_DIR . '/inc/header-helpers.php';
require_once TERSA_THEME_DIR . '/inc/footer-helpers.php';
require_once TERSA_THEME_DIR . '/inc/shortcodes.php';

// WooCommerce
require_once TERSA_THEME_DIR . '/inc/woocommerce.php';

// SEO / Schema
require_once TERSA_THEME_DIR . '/inc/schema.php';
require_once TERSA_THEME_DIR . '/inc/seo-facets.php';

// Performance
require_once TERSA_THEME_DIR . '/inc/performance.php';

// Security
require_once TERSA_THEME_DIR . '/inc/security.php';

// Debug logging (targeted: mail, WC gateway, tersa AJAX) — always on, /wp-content/debug.log
require_once TERSA_THEME_DIR . '/inc/debug-log.php';
