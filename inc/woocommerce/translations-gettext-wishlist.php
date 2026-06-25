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
	if (is_admin()) {
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
			return tersa_pll_wishlist('added_notification');
		case '"%s" has been added to your "%s" list!':
		case '"%s" je dodano na vašu listu "%s"!':
			return tersa_pll_wishlist('added_notification');
		case 'Product name':
		case 'Product Name':
		case 'Naziv proizvoda':
			return tersa_pll_wishlist('product_name');
		case 'Unit price':
		case 'Unit Price':
			return tersa_pll_wishlist('unit_price');
		case 'Price':
		case 'Price:':
		case 'Cijena':
		case 'Cijena:':
			return tersa_pll_wishlist('price');
		case 'Stock':
		case 'stock':
		case 'Stock:':
		case 'stock:':
		case 'Zaliha':
		case 'Zaliha:':
			return tersa_pll_wishlist('stock');
		case 'Stock status':
		case 'Stock Status':
		case 'Status zaliha':
			return tersa_pll_wishlist('stock_status');
		case 'Add to cart':
		case 'Dodaj u košaricu':
			return tersa_pll_wishlist('add_to_cart');
		case 'Remove this product':
		case 'Ukloni ovaj proizvod':
			return tersa_pll_wishlist('remove_product');
		case 'Wishlist':
		case 'Lista želja':
			return tersa_pll_wishlist('title');
		case 'My wishlist':
		case 'Moja lista želja':
			return tersa_pll_wishlist('title_mine');
		case 'No products added to the wishlist':
		case 'No products added to the wishlist.':
		case 'Nema proizvoda na listi želja.':
			return tersa_pll_wishlist('empty');
		case 'In Stock':
		case 'in stock':
		case 'Na stanju':
			return tersa_pll_wishlist('in_stock');
	}

	return $translated;
}
add_filter('gettext', 'tersa_yith_wishlist_gettext_override', 10, 3);
