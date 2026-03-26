<?php
if (!defined('ABSPATH')) {
	exit;
}
$base = __DIR__ . '/woocommerce/';

require_once $base . 'helpers.php';       // shared helpers (wishlist helpers)
require_once $base . 'translations.php';  // i18n/yith overrides + Polylang string registration
require_once $base . 'wishlist.php';      // YITH wishlist integration + AJAX fallback
require_once $base . 'archive.php';       // shop archive behavior (query + ordering)
require_once $base . 'single.php';        // product single behavior (tabs/reviews)
require_once $base . 'cart-drawer.php';   // mini-cart + cart block append (bestsellers)
require_once $base . 'ajax.php';          // custom AJAX endpoints (drawer fragments)
require_once $base . 'cache.php';         // transient/cache handling + cart fragment cache