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
 *
 * @return string
 */
function tersa_get_footer_settings_cache_key(): string {
	return 'tersa_footer_settings';
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

	$page      = get_page_by_path(tersa_get_footer_settings_slug());
	$page_id   = $page ? (int) $page->ID : 0;
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

	delete_transient(tersa_get_footer_settings_cache_key());
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
	$page = get_page_by_path($slug);

	if (!$page instanceof WP_Post) {
		return home_url('/' . $slug . '/');
	}

	if (function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page->ID);
		if ($translated_id) {
			$url = get_permalink($translated_id);
			return $url ?: get_permalink($page->ID) ?: home_url('/' . $slug . '/');
		}
	}

	return get_permalink($page->ID) ?: home_url('/' . $slug . '/');
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
 * ID global settings stranice (ACF Free pristup).
 *
 * @return int
 */
function tersa_get_global_settings_page_id(): int {
	$page = get_page_by_path(tersa_get_global_settings_slug());
	return $page ? (int) $page->ID : 0;
}
