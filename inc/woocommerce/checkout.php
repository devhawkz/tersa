<?php
/**
 * Checkout customisations — payment & security badges, CorvusPay-related hooks.
 *
 * Obavezno po "Standardi logotipa v3.4" (CorvusPay):
 *   - logotipi kartica MORAJU biti na stranici za plaćanje (checkout),
 *   - logotipi sigurnosnih programa — na checkoutu samo u footeru (v3.4: jednom po stranici).
 *     U checkout bloku ispod plaćanja: kartice + CorvusPay (bez duplog security stripa).
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Render payment + security badges blok ispod payment metoda na checkout-u.
 *
 * Hook `woocommerce_review_order_after_payment` se okida unutar `<div id="payment">`
 * odmah ispod liste payment metoda i "Place Order" dugmeta — idealno mjesto da kupac
 * vidi prihvaćene kartice i CorvusPay netom prije plaćanja (sigurnosni logotipi su u footeru).
 *
 * @return void
 */
function tersa_render_checkout_payment_badges(): void {
	if (!function_exists('is_checkout') || !is_checkout()) {
		return;
	}
	// `is_order_received_page()` također vraća true unutar `is_checkout()`; preskoči thank-you stranicu.
	if (function_exists('is_order_received_page') && is_order_received_page()) {
		return;
	}

	get_template_part(
		'template-parts/woocommerce/payment-security-badges',
		null,
		[
			'variant'        => 'light',
			'title'          => __('Sigurno plaćanje karticama', 'tersa-shop'),
			'show_cards'     => true,
			'show_security'  => false,
			'show_corvuspay' => true,
		]
	);
}
add_action('woocommerce_review_order_after_payment', 'tersa_render_checkout_payment_badges', 20);
