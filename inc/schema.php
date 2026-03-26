<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Rank Math integration layer.
 * Ne ispisujemo ručni standalone JSON-LD,
 * nego samo fino podešavamo Rank Math output.
 */

/**
 * Google je ukinuo Sitelinks Search Box,
 * pa SearchAction za ovaj projekat nema realnu vrednost.
 */
add_filter('rank_math/json_ld/disable_search', '__return_true');

/**
 * Po želji: ukloni Blog schema sa naslovne ako se ikad pojavi.
 * Korisno ako homepage nije blog, nego korporativna/shop landing strana.
 */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
	if (is_front_page() && isset($data['Blog'])) {
		unset($data['Blog']);
	}

	return $data;
}, 20, 2);