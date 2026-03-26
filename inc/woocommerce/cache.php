<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cache-related WooCommerce modules loader.
 *
 * Razdvaja:
 * - transient/cache invalidation
 * - shortcode in-request cache
 */
require_once __DIR__ . '/analytics.php';       // Woo analytics disable (per-request)
require_once __DIR__ . '/cache-transients.php';      // purge transients on product save
require_once __DIR__ . '/cache-shortcodes.php';      // wishlist shortcode in-request cache
