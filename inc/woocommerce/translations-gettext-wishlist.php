<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * YITH wishlist + deo WooCommerce gettext-a (wishlist-related stringovi).
 *
 * Važno: kada je $domain === 'woocommerce', preskačemo overlap stringove
 * (`Stock`, `in stock`, `Add to cart`) jer ih već obrađuje
 * `translations-gettext-woocommerce.php` (isto kao u originalnom monolitu).
 */
function tersa_yith_wishlist_gettext_override($translated, $text, $domain) {
	if ($domain !== 'yith-woocommerce-wishlist' && $domain !== 'woocommerce') {
		return $translated;
	}

	if ($domain === 'woocommerce') {
		$skip_overlap = in_array($text, ['Stock', 'in stock', 'Add to cart'], true);
		if ($skip_overlap) {
			return $translated;
		}
	}

	switch ($text) {
		case '"%1$s" has been added to your "%2$s" list!':
			$hr = '"%1$s" je dodano na vašu listu "%2$s"!';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case '"%s" has been added to your "%s" list!':
			$hr = '"%s" je dodano na vašu listu "%s"!';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Product name':
			return tersa_pll_wishlist('product_name');
		case 'Unit price':
			return tersa_pll_wishlist('unit_price');
		case 'Price':
		case 'Price:':
			return tersa_pll_wishlist('price');
		case 'Stock':
		case 'stock':
		case 'Stock:':
		case 'stock:':
			return tersa_pll_wishlist('stock');
		case 'Stock status':
			return tersa_pll_wishlist('stock_status');
		case 'Add to cart':
			return tersa_pll_wishlist('add_to_cart');
		case 'Remove this product':
			return tersa_pll_wishlist('remove_product');
		case 'Wishlist':
			return tersa_pll_wishlist('title');
		case 'My wishlist':
			return tersa_pll_wishlist('title_mine');
		case 'No products added to the wishlist':
			return tersa_pll_wishlist('empty');
		case 'In Stock':
		case 'in stock':
			return tersa_pll_wishlist('in_stock');
	}

	return $translated;
}
add_filter('gettext', 'tersa_yith_wishlist_gettext_override', 10, 3);

