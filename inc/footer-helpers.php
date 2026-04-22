<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Vraća slug stranice koja čuva footer podešavanja za ACF Free.
 * Implementirano kao funkcija (ne konstanta) da bi child tema mogla da je override-uje.
 *
 * @return string
 */
function tersa_get_footer_settings_slug(): string {
	return 'footer-settings';
}

/**
 * Vraća transient key za footer settings.
 * Uključuje Polylang jezični sufiks kako bi svaki jezik imao vlastiti cache.
 *
 * @return string
 */
function tersa_get_footer_settings_cache_key(): string {
	$lang = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	return 'tersa_footer_settings' . ($lang ? '_' . $lang : '');
}

/**
 * Dohvata ACF footer settings sa fallback vrednostima.
 * Rezultat se kešira transientom na jedan dan — uklanjaj pri promeni stranice.
 *
 * @return array<string, mixed>
 */
function tersa_get_footer_settings(): array {
	$cache_key = tersa_get_footer_settings_cache_key();
	$settings  = get_transient($cache_key);

	if (false !== $settings && is_array($settings)) {
		return wp_parse_args(
			$settings,
			[
				'footer_newsletter_heading' => '',
				'footer_newsletter_text'   => '',
			]
		);
	}

	$page    = get_page_by_path(tersa_get_footer_settings_slug());
	$page_id = $page ? (int) $page->ID : 0;

	// Polylang: dohvati prevedenu verziju settings stranice za trenutni jezik.
	if ($page_id && function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page_id);
		if ($translated_id) {
			$page_id = (int) $translated_id;
		}
	}

	$get_field = function_exists('get_field');

	$settings = [
		'footer_newsletter_heading' => ($page_id && $get_field) ? (string) get_field('footer_newsletter_heading', $page_id) : '',
		'footer_newsletter_text'   => ($page_id && $get_field) ? (string) get_field('footer_newsletter_text', $page_id) : '',
	];

	set_transient($cache_key, $settings, DAY_IN_SECONDS);

	return $settings;
}

/**
 * Briše keš kada se sačuva Footer Settings stranica.
 *
 * @param int          $post_id
 * @param WP_Post|null $post
 * @return void
 */
function tersa_maybe_clear_footer_settings_cache(int $post_id, $post = null): void {
	if (wp_is_post_revision($post_id) || 'page' !== get_post_type($post_id)) {
		return;
	}

	$post_obj = $post instanceof WP_Post ? $post : get_post($post_id);
	if (!$post_obj instanceof WP_Post) {
		return;
	}

	if (tersa_get_footer_settings_slug() !== $post_obj->post_name) {
		return;
	}

	// Briše base key + sve jezičke varijante.
	$base = 'tersa_footer_settings';
	delete_transient($base);
	if (function_exists('pll_languages_list')) {
		foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
			delete_transient($base . '_' . $lang_slug);
		}
	}
}
add_action('save_post_page', 'tersa_maybe_clear_footer_settings_cache', 10, 2);

/**
 * Vraća URL stranice za trenutni Polylang jezik.
 * Koristi pll_get_post() da bi pronašao prevedenu verziju stranice po slug-u.
 * Fallback na get_permalink() originalne stranice ako prevod ne postoji.
 *
 * @param string $slug Post slug na default jeziku (npr. 'kontakt', 'o-nama').
 * @return string URL stranice na trenutnom jeziku.
 */
function tersa_pll_page_url(string $slug): string {
	static $cache = [];

	if (isset($cache[$slug])) {
		return $cache[$slug];
	}

	$page = get_page_by_path($slug);

	if (!$page instanceof WP_Post) {
		return $cache[$slug] = home_url('/' . $slug . '/');
	}

	if (function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page->ID);
		if ($translated_id) {
			$url = get_permalink($translated_id);
			return $cache[$slug] = ($url ?: get_permalink($page->ID) ?: home_url('/' . $slug . '/'));
		}
	}

	return $cache[$slug] = (get_permalink($page->ID) ?: home_url('/' . $slug . '/'));
}

/**
 * Slug stranice koja cuva globalna podešavanja za ACF Free.
 *
 * @return string
 */
function tersa_get_global_settings_slug(): string {
	return 'global-settings';
}

/**
 * Vraća transient key za company settings.
 * Uključuje Polylang jezični sufiks kako bi svaki jezik imao vlastiti cache.
 *
 * @return string
 */
function tersa_get_company_settings_cache_key(): string {
	$lang = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	return 'tersa_company_settings' . ($lang ? '_' . $lang : '');
}

/**
 * Dohvata ACF company settings sa fallback vrednostima.
 * Rezultat se kešira transientom na jedan dan — uklanjaj pri promeni global-settings stranice.
 *
 * @return array<string, string>
 */
function tersa_get_company_settings(): array {
	$cache_key = tersa_get_company_settings_cache_key();
	$settings  = get_transient($cache_key);

	if (false !== $settings && is_array($settings)) {
		return $settings;
	}

	$page_id   = tersa_get_global_settings_page_id();
	$get_field = function_exists('get_field');

	$settings = [
		'company_name'                    => ($page_id && $get_field) ? (string) get_field('company_name', $page_id) : '',
		'company_activity'                => ($page_id && $get_field) ? (string) get_field('company_activity', $page_id) : '',
		'company_address'                 => ($page_id && $get_field) ? (string) get_field('company_address', $page_id) : '',
		'company_email'                   => ($page_id && $get_field) ? (string) get_field('company_email', $page_id) : '',
		'footer_newsletter_cf7_shortcode' => ($page_id && $get_field) ? (string) get_field('footer_newsletter_cf7_shortcode', $page_id) : '',
	];

	set_transient($cache_key, $settings, DAY_IN_SECONDS);

	return $settings;
}

/**
 * Briše keš kada se sačuva Global Settings stranica.
 *
 * @param int          $post_id
 * @param WP_Post|null $post
 * @return void
 */
function tersa_maybe_clear_company_settings_cache(int $post_id, $post = null): void {
	if (wp_is_post_revision($post_id) || 'page' !== get_post_type($post_id)) {
		return;
	}

	$post_obj = $post instanceof WP_Post ? $post : get_post($post_id);
	if (!$post_obj instanceof WP_Post) {
		return;
	}

	if (tersa_get_global_settings_slug() !== $post_obj->post_name) {
		return;
	}

	// Briše base key + sve jezičke varijante.
	$base = 'tersa_company_settings';
	delete_transient($base);
	if (function_exists('pll_languages_list')) {
		foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
			delete_transient($base . '_' . $lang_slug);
		}
	}
}
add_action('save_post_page', 'tersa_maybe_clear_company_settings_cache', 10, 2);

/**
 * ID global settings stranice za trenutni Polylang jezik (ACF Free pristup).
 * Static cache je per-language — isti request nema više od jednog jezičnog konteksta.
 *
 * @return int
 */
function tersa_get_global_settings_page_id(): int {
	static $cached_ids = [];

	$lang     = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	$lang_key = $lang ?: '_default';

	if (isset($cached_ids[$lang_key])) {
		return $cached_ids[$lang_key];
	}

	$page    = get_page_by_path(tersa_get_global_settings_slug());
	$page_id = $page ? (int) $page->ID : 0;

	// Polylang: dohvati prevedenu verziju stranice za trenutni jezik.
	if ($page_id && $lang && function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page_id, $lang);
		if ($translated_id) {
			$page_id = (int) $translated_id;
		}
	}

	return $cached_ids[$lang_key] = $page_id;
}
