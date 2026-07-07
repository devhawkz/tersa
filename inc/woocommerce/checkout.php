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

/**
 * Prevodi label free shipping metode pre nego što Woo/Blocks izgrade cart/checkout prikaz.
 *
 * @param array<string, WC_Shipping_Rate> $rates
 * @param array<string, mixed>            $package
 * @return array<string, WC_Shipping_Rate>
 */
function tersa_translate_checkout_shipping_rate_labels($rates, $package) {
	if (!is_array($rates) || !function_exists('tersa_translate_ui_string')) {
		return $rates;
	}

	foreach ($rates as $rate) {
		if (!is_object($rate) || !method_exists($rate, 'get_label') || !method_exists($rate, 'set_label')) {
			continue;
		}

		$method_id = method_exists($rate, 'get_method_id') ? (string) $rate->get_method_id() : '';
		$label     = trim(wp_strip_all_tags((string) $rate->get_label()));

		if ('free_shipping' === $method_id || in_array($label, ['Free shipping', 'Besplatna dostava', 'Kostenloser Versand'], true)) {
			$rate->set_label(tersa_translate_ui_string('Free shipping'));
		}
	}

	return $rates;
}
add_filter('woocommerce_package_rates', 'tersa_translate_checkout_shipping_rate_labels', 100, 2);

/**
 * Classic cart fallback za full label dostave.
 */
function tersa_translate_checkout_shipping_full_label($label, $method) {
	if (!function_exists('tersa_translate_ui_string')) {
		return $label;
	}

	$method_id = is_object($method) && method_exists($method, 'get_method_id') ? (string) $method->get_method_id() : '';
	$plain     = trim(wp_strip_all_tags((string) $label));

	if ('free_shipping' === $method_id || in_array($plain, ['Free shipping', 'Besplatna dostava', 'Kostenloser Versand'], true)) {
		return esc_html(tersa_translate_ui_string('Free shipping'));
	}

	return $label;
}
add_filter('woocommerce_cart_shipping_method_full_label', 'tersa_translate_checkout_shipping_full_label', 100, 2);

function tersa_translate_corvuspay_gateway_title_value(string $title): string {
	if (!function_exists('tersa_translate_ui_string')) {
		return $title;
	}

	$plain = trim(wp_strip_all_tags($title));
	if (in_array($plain, ['Kartično plaćanje (CorvusPay)', 'Credit Card (CorvusPay)', 'Card payment (CorvusPay)', 'Kartenzahlung (CorvusPay)'], true)) {
		return tersa_translate_ui_string('Kartično plaćanje (CorvusPay)');
	}

	$translated = tersa_translate_ui_string($plain);

	return $translated !== $plain ? $translated : $title;
}

function tersa_translate_corvuspay_gateway_description_value(string $description): string {
	if (!function_exists('tersa_translate_ui_string')) {
		return $description;
	}

	$translated_sentence = tersa_translate_ui_string('Procesiranje transakcija na internetu.');
	$searches            = [
		'Procesiranje transakcija na internetu.',
		'Online transaction processing.',
		'Sichere Zahlungsabwicklung im Internet.',
	];

	foreach ($searches as $search) {
		if (false !== strpos($description, $search)) {
			return str_replace($search, $translated_sentence, $description);
		}
	}

	return $description;
}

/**
 * CorvusPay u Block checkout-u čita direktno settings option, zato ga prevodimo pre inicijalizacije gateway-a.
 *
 * @param mixed $settings
 * @return mixed
 */
function tersa_translate_corvuspay_settings_option($settings) {
	if (!is_array($settings)) {
		return $settings;
	}
	if (is_admin() && !wp_doing_ajax()) {
		return $settings;
	}

	if (isset($settings['title'])) {
		$settings['title'] = tersa_translate_corvuspay_gateway_title_value((string) $settings['title']);
	}
	if (isset($settings['description'])) {
		$settings['description'] = tersa_translate_corvuspay_gateway_description_value((string) $settings['description']);
	}

	return $settings;
}
add_filter('option_woocommerce_corvuspay_settings', 'tersa_translate_corvuspay_settings_option', 20);

function tersa_translate_corvuspay_gateway_title_filter($title, $gateway_id) {
	return 'corvuspay' === $gateway_id ? tersa_translate_corvuspay_gateway_title_value((string) $title) : $title;
}
add_filter('woocommerce_gateway_title', 'tersa_translate_corvuspay_gateway_title_filter', 20, 2);

function tersa_translate_corvuspay_gateway_description_filter($description, $gateway_id) {
	return 'corvuspay' === $gateway_id ? tersa_translate_corvuspay_gateway_description_value((string) $description) : $description;
}
add_filter('woocommerce_gateway_description', 'tersa_translate_corvuspay_gateway_description_filter', 20, 2);

/**
 * Mutira gateway objekat za klasični checkout i za slučajeve kada je objekat već inicijalizovan.
 *
 * @param array<string, WC_Payment_Gateway> $gateways
 * @return array<string, WC_Payment_Gateway>
 */
function tersa_translate_available_corvuspay_gateway($gateways) {
	if (!is_array($gateways)) {
		return $gateways;
	}

	foreach ($gateways as $gateway) {
		if (!is_object($gateway) || !isset($gateway->id) || 'corvuspay' !== $gateway->id) {
			continue;
		}

		if (isset($gateway->title)) {
			$gateway->title = tersa_translate_corvuspay_gateway_title_value((string) $gateway->title);
		}
		if (isset($gateway->description)) {
			$gateway->description = tersa_translate_corvuspay_gateway_description_value((string) $gateway->description);
		}
	}

	return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'tersa_translate_available_corvuspay_gateway', 20);

function tersa_cart_checkout_uppercase(string $text): string {
	return function_exists('mb_strtoupper') ? mb_strtoupper($text, 'UTF-8') : strtoupper($text);
}

/**
 * WooCommerce Blocks naknadno renderuje delove korpe/kase u browseru.
 * Ovaj mali normalizer čuva poznate shop stringove u istom jeziku posle svakog re-rendera.
 */
function tersa_enqueue_cart_checkout_blocks_i18n_fix(): void {
	if (!function_exists('is_cart') || !function_exists('is_checkout')) {
		return;
	}
	if (!is_cart() && !is_checkout()) {
		return;
	}
	if (function_exists('is_order_received_page') && is_order_received_page()) {
		return;
	}
	if (!function_exists('tersa_translate_ui_string')) {
		return;
	}

	$cart_totals = tersa_translate_ui_string('Ukupni iznos košarice');
	$replacements = [
		'UKUPNI IZNOS KOŠARICE'              => tersa_cart_checkout_uppercase($cart_totals),
		'WARENKORBSUMME'                     => tersa_cart_checkout_uppercase($cart_totals),
		'Ukupni iznos košarice'              => $cart_totals,
		'Warenkorbsumme'                     => $cart_totals,
		'Cart totals'                        => $cart_totals,
		'Free shipping'                      => tersa_translate_ui_string('Free shipping'),
		'Besplatna dostava'                  => tersa_translate_ui_string('Free shipping'),
		'Kostenloser Versand'                => tersa_translate_ui_string('Free shipping'),
		'Shipping options'                   => tersa_translate_ui_string('Shipping options'),
		'Opcije dostave'                     => tersa_translate_ui_string('Shipping options'),
		'Versandoptionen'                    => tersa_translate_ui_string('Shipping options'),
		'Payment options'                    => tersa_translate_ui_string('Payment options'),
		'Opcije plaćanja'                    => tersa_translate_ui_string('Payment options'),
		'Zahlungsoptionen'                   => tersa_translate_ui_string('Payment options'),
		'Kartično plaćanje (CorvusPay)'      => tersa_translate_ui_string('Kartično plaćanje (CorvusPay)'),
		'Credit Card (CorvusPay)'            => tersa_translate_ui_string('Kartično plaćanje (CorvusPay)'),
		'Card payment (CorvusPay)'           => tersa_translate_ui_string('Kartično plaćanje (CorvusPay)'),
		'Kartenzahlung (CorvusPay)'          => tersa_translate_ui_string('Kartično plaćanje (CorvusPay)'),
		'Procesiranje transakcija na internetu.' => tersa_translate_ui_string('Procesiranje transakcija na internetu.'),
		'Online transaction processing.'          => tersa_translate_ui_string('Procesiranje transakcija na internetu.'),
		'Sichere Zahlungsabwicklung im Internet.' => tersa_translate_ui_string('Procesiranje transakcija na internetu.'),
	];

	wp_register_script('tersa-cart-checkout-i18n', false, [], null, true);
	wp_enqueue_script('tersa-cart-checkout-i18n');
	wp_add_inline_script(
		'tersa-cart-checkout-i18n',
		'window.tersaCartCheckoutI18n=' . wp_json_encode($replacements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';
(function () {
	var replacements = window.tersaCartCheckoutI18n || {};
	var skipTags = { SCRIPT: true, STYLE: true, NOSCRIPT: true, TEXTAREA: true };
	var attrs = ["aria-label", "title", "placeholder", "value"];
	var queued = false;

	function normalize(value) {
		return String(value || "").replace(/\s+/g, " ").trim();
	}

	function translateValue(value) {
		var key = normalize(value);
		return key && replacements[key] ? replacements[key] : null;
	}

	function translateTextNode(node) {
		var translated = translateValue(node.nodeValue);
		if (!translated) {
			return;
		}

		var leading = (node.nodeValue.match(/^\s*/) || [""])[0];
		var trailing = (node.nodeValue.match(/\s*$/) || [""])[0];
		node.nodeValue = leading + translated + trailing;
	}

	function translateElementAttrs(element) {
		attrs.forEach(function (attr) {
			if (!element.hasAttribute(attr)) {
				return;
			}

			var translated = translateValue(element.getAttribute(attr));
			if (translated) {
				element.setAttribute(attr, translated);
			}
		});
	}

	function walk(root) {
		if (!root || skipTags[root.nodeName]) {
			return;
		}
		if (root.nodeType === Node.TEXT_NODE) {
			translateTextNode(root);
			return;
		}
		if (root.nodeType !== Node.ELEMENT_NODE) {
			return;
		}

		translateElementAttrs(root);
		root.querySelectorAll("[aria-label], [title], [placeholder], [value]").forEach(translateElementAttrs);

		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				return node.parentElement && !skipTags[node.parentElement.nodeName]
					? NodeFilter.FILTER_ACCEPT
					: NodeFilter.FILTER_REJECT;
			}
		});
		var node;
		while ((node = walker.nextNode())) {
			translateTextNode(node);
		}
	}

	function run() {
		if (document.body) {
			walk(document.body);
		}
	}

	function observe() {
		if (!document.body || !window.MutationObserver) {
			return;
		}
		new MutationObserver(function (mutations) {
			if (queued) {
				return;
			}
			queued = true;
			window.requestAnimationFrame(function () {
				queued = false;
				mutations.forEach(function (mutation) {
					if (mutation.type === "characterData") {
						walk(mutation.target);
						return;
					}
					mutation.addedNodes.forEach(walk);
				});
			});
		}).observe(document.body, { childList: true, subtree: true, characterData: true });
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", function () {
			run();
			observe();
		});
	} else {
		run();
		observe();
	}
}());'
	);
}
add_action('wp_enqueue_scripts', 'tersa_enqueue_cart_checkout_blocks_i18n_fix', 40);
