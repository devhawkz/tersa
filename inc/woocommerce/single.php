<?php
if (!defined('ABSPATH')) {
	exit;
}

add_filter('woocommerce_product_tabs', function ($tabs) {
	global $product;

	$hr_titles = [
		'description'            => 'Opis',
		'additional_information' => 'Dodatne informacije',
	];

	foreach ($hr_titles as $key => $title) {
		if (isset($tabs[$key]['title'])) {
			$tabs[$key]['title'] = $title;
		}
	}

	if (isset($tabs['reviews']) && $product instanceof WC_Product) {
		$count = (int) $product->get_review_count();
		$tabs['reviews']['title'] = sprintf(
			function_exists('pll__') ? pll__('Recenzije (%d)') : __('Recenzije (%d)', 'tersa-shop'),
			$count
		);
	}

	return $tabs;
}, 20);

add_filter('ngettext', function ($translation, $single, $plural, $number, $domain) {
	if ($domain !== 'woocommerce') {
		return $translation;
	}

	if ($single !== '%1$s review for %2$s' || $plural !== '%1$s reviews for %2$s') {
		return $translation;
	}

	$n      = (int) $number;
	$mod10  = $n % 10;
	$mod100 = $n % 100;

	if ($mod10 === 1 && $mod100 !== 11) {
		$hr = '%1$s komentara za %2$s';
	} elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
		$hr = '%1$s komentara za %2$s';
	} else {
		$hr = '%1$s recenzija za %2$s';
	}

	return function_exists('pll__') ? pll__($hr) : $hr;
}, 10, 5);
