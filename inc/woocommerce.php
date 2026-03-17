<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Prevod specifičnih WooCommerce stringova koji nisu deo teme.
 *
 * - "No products in the cart." → "Trenutno nema proizvoda u košarici."
 */
add_filter('gettext', function ($translated, $text, $domain) {
	if ($domain === 'woocommerce') {
		if ($text === 'No products in the cart.') {
			return 'Trenutno nema proizvoda u košarici.';
		}
	}

	return $translated;
}, 10, 3);

