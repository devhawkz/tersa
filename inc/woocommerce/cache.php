<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Cache/transient i shortcode cache logika.
 *
 * Razdvojeno radi duge održivosti (transients vs shortcode cache).
 */
require_once __DIR__ . '/analytics.php';       // Woo analytics disable (per-request)
require_once __DIR__ . '/cache-transients.php';      // purge transients on product save
require_once __DIR__ . '/cache-shortcodes.php';      // wishlist shortcode in-request cache
