<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * WooCommerce gettext override.
 *
 * Ovde držimo samo domen `woocommerce`, da bismo izbegli “overlap” override-e sa wishlist logikom.
 */
function tersa_woocommerce_gettext_override($translated, $text, $domain) {
	if ($domain !== 'woocommerce') {
		return $translated;
	}

	switch ($text) {
		case 'No products in the cart.':
			$hr = 'Trenutačno nema proizvoda u košarici.';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Stock':
			$hr = 'Zalihe';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Subtotal':
			return 'Međuzbir';
		case 'Checkout':
			return 'Blagajna';
		case 'Add to cart':
			if ($translated !== $text) {
				return $translated;
			}
			return function_exists('tersa_pll_wishlist') ? tersa_pll_wishlist('add_to_cart') : $translated;
		case 'View cart':
			return 'Pogledaj košaricu';
		case '%s in stock':
			$hr = '%s na zalihima';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'in stock':
			$hr = 'na zalihima';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Weight':
			$hr = 'Težina';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Dimensions':
			$hr = 'Dimenzije';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Additional information':
			$hr = 'Dodatne informacije';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Reviews (%d)':
			$hr = 'Recenzije (%d)';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Reviews':
			$hr = 'Recenzije';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'There are no reviews yet.':
			$hr = 'Još nema recenzija.';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Add a review':
			$hr = 'Dodaj recenziju';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Be the first to review &ldquo;%s&rdquo;':
			$hr = 'Budite prvi koji će recenzirati &ldquo;%s&rdquo;';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Leave a Reply to %s':
			$hr = 'Odgovor na %s';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Submit':
			$hr = 'Pošalji';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Name':
			$hr = 'Ime';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Email':
			$hr = 'E-pošta';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'You must be %1$slogged in%2$s to post a review.':
			$hr = 'Morate biti %1$s prijavljeni%2$s kako biste objavili recenziju.';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Your rating':
			$hr = 'Vaša ocjena';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Rate&hellip;':
			$hr = 'Ocjena&hellip;';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Perfect':
			$hr = 'Izvrsno';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Good':
			$hr = 'Dobro';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Average':
			$hr = 'Prosječno';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Not that bad':
			$hr = 'Nije loše';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Very poor':
			$hr = 'Vrlo loše';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Your review':
			$hr = 'Vaša recenzija';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Only logged in customers who have purchased this product may leave a review.':
			$hr = 'Samo prijavljeni kupci koji su kupili ovaj proizvod mogu ostaviti recenziju.';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'Your review is awaiting approval':
			$hr = 'Vaša recenzija čeka odobrenje';
			return function_exists('pll__') ? pll__($hr) : $hr;
		case 'verified owner':
			$hr = 'potvrđeni kupac';
			return function_exists('pll__') ? pll__($hr) : $hr;
		default:
			return $translated;
	}
}
add_filter('gettext', 'tersa_woocommerce_gettext_override', 10, 3);

