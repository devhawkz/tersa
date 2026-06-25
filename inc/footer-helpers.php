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
 * Vraća osnovni transient key za footer newsletter settings.
 *
 * @return string
 */
function tersa_get_footer_settings_cache_base_key(): string {
	return 'tersa_footer_settings_v2';
}

/**
 * Vraća osnovni transient key za company/footer settings.
 *
 * @return string
 */
function tersa_get_company_settings_cache_base_key(): string {
	return 'tersa_company_settings_v4';
}

/**
 * Vraća slug trenutnog jezika.
 *
 * @return string
 */
function tersa_get_footer_current_language_slug(): string {
	if (function_exists('tersa_get_current_language_slug')) {
		return tersa_get_current_language_slug();
	}

	if (function_exists('pll_current_language')) {
		$lang = pll_current_language('slug');

		return is_string($lang) ? sanitize_key($lang) : '';
	}

	return '';
}

/**
 * Vraća slug defaultnog jezika.
 *
 * @return string
 */
function tersa_get_footer_default_language_slug(): string {
	if (function_exists('tersa_get_default_language_slug')) {
		return tersa_get_default_language_slug();
	}

	if (function_exists('pll_default_language')) {
		$lang = pll_default_language('slug');

		if (empty($lang)) {
			$lang = pll_default_language();
		}

		return is_string($lang) ? sanitize_key($lang) : '';
	}

	return '';
}

/**
 * Vraća transient key za footer settings.
 * Uključuje Polylang jezični sufiks kako bi svaki jezik imao vlastiti cache.
 *
 * @return string
 */
function tersa_get_footer_settings_cache_key(): string {
	$lang = tersa_get_footer_current_language_slug();
	return tersa_get_footer_settings_cache_base_key() . ($lang ? '_' . $lang : '');
}

/**
 * ID osnovne footer settings stranice.
 *
 * @return int
 */
function tersa_get_footer_settings_base_page_id(): int {
	$page = get_page_by_path(tersa_get_footer_settings_slug());

	return $page instanceof WP_Post ? (int) $page->ID : 0;
}

/**
 * ID footer settings stranice za zadati Polylang jezik (ACF Free pristup).
 *
 * @param string $lang Polylang slug jezika.
 * @return int
 */
function tersa_get_footer_settings_page_id(string $lang = ''): int {
	static $cached_ids = [];

	$lang     = $lang !== '' ? sanitize_key($lang) : tersa_get_footer_current_language_slug();
	$lang_key = $lang ?: '_base';

	if (isset($cached_ids[$lang_key])) {
		return $cached_ids[$lang_key];
	}

	$page_id = tersa_get_footer_settings_base_page_id();

	if ($page_id && $lang && function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page_id, $lang);
		if ($translated_id) {
			$page_id = (int) $translated_id;
		}
	}

	return $cached_ids[$lang_key] = $page_id;
}

/**
 * Vraća sve poznate jezičke varijante Footer Settings stranice.
 *
 * @return int[]
 */
function tersa_get_footer_settings_page_ids(): array {
	$page_id = tersa_get_footer_settings_base_page_id();

	if (!$page_id) {
		return [];
	}

	$page_ids = [$page_id];

	if (function_exists('pll_languages_list') && function_exists('pll_get_post')) {
		foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
			$translated_id = pll_get_post($page_id, (string) $lang_slug);

			if ($translated_id) {
				$page_ids[] = (int) $translated_id;
			}
		}
	}

	return array_values(array_unique(array_filter(array_map('intval', $page_ids))));
}

/**
 * Proširuje Footer Settings ACF grupu na sve Polylang prevode stranice.
 *
 * @param array<string, mixed> $field_group ACF field group.
 * @return array<string, mixed>
 */
function tersa_acf_extend_footer_settings_locations(array $field_group): array {
	if (($field_group['key'] ?? '') !== 'group_69b97bda6fd6a') {
		return $field_group;
	}

	$page_ids = tersa_get_footer_settings_page_ids();

	if (!$page_ids) {
		return $field_group;
	}

	$field_group['location'] = array_map(
		static function (int $page_id): array {
			return [
				[
					'param'    => 'page',
					'operator' => '==',
					'value'    => (string) $page_id,
				],
			];
		},
		$page_ids
	);

	return $field_group;
}
add_filter('acf/load_field_group', 'tersa_acf_extend_footer_settings_locations', 20);

/**
 * Admin obavijest na Footer Settings stranicama — prikazuje jezik i linkove na prevode.
 *
 * @return void
 */
function tersa_footer_settings_admin_language_notice(): void {
	if (!is_admin() || !function_exists('get_current_screen')) {
		return;
	}

	$screen = get_current_screen();

	if (!$screen || 'post' !== $screen->base || 'page' !== $screen->post_type) {
		return;
	}

	global $post;

	if (!$post instanceof WP_Post) {
		return;
	}

	$page_ids = tersa_get_footer_settings_page_ids();

	if (!in_array((int) $post->ID, $page_ids, true)) {
		return;
	}

	$lang_label = '';

	if (function_exists('pll_get_post_language')) {
		$lang_slug = pll_get_post_language($post->ID, 'slug');
		$lang_name = pll_get_post_language($post->ID, 'name');

		if ($lang_name) {
			$lang_label = (string) $lang_name;
		} elseif ($lang_slug) {
			$lang_label = strtoupper((string) $lang_slug);
		}
	}

	$translation_links = [];

	if (function_exists('pll_get_post_translations')) {
		$translations = pll_get_post_translations($post->ID);

		foreach ((array) $translations as $translation_lang => $translation_id) {
			$translation_id = (int) $translation_id;

			if ($translation_id <= 0 || $translation_id === (int) $post->ID) {
				continue;
			}

			$edit_url = get_edit_post_link($translation_id, '');

			if (!$edit_url) {
				continue;
			}

			$label = strtoupper((string) $translation_lang);

			if (function_exists('PLL')) {
				$language = PLL()->model->get_language($translation_lang);

				if ($language && !empty($language->name)) {
					$label = (string) $language->name;
				}
			}

			$translation_links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url($edit_url),
				esc_html($label)
			);
		}
	}

	$message = $lang_label
		? sprintf(
			/* translators: %s: language name */
			__('Uređujete Footer Settings za jezik: %s. Polja na ovoj stranici vrijede samo za taj jezik. Prazna polja na frontendu nasljeđuju vrijednost iz default jezika.', 'tersa-shop'),
			'<strong>' . esc_html($lang_label) . '</strong>'
		)
		: __('Uređujete Footer Settings. Za višejezični sadržaj povežite stranicu u Polylangu i popunite polja na svakom jezičnom prevodu.', 'tersa-shop');

	if ($translation_links) {
		$message .= ' ' . sprintf(
			/* translators: %s: comma-separated edit links */
			__('Otvori prevod: %s', 'tersa-shop'),
			implode(', ', $translation_links)
		);
	}

	printf(
		'<div class="notice notice-info"><p>%s</p></div>',
		wp_kses_post($message)
	);
}
add_action('admin_notices', 'tersa_footer_settings_admin_language_notice');

/**
 * Učitava newsletter footer podešavanja sa jedne stranice.
 *
 * @param int $page_id ID stranice.
 * @return array<string, string>
 */
function tersa_read_footer_settings_from_page(int $page_id): array {
	$settings = [
		'footer_newsletter_heading' => '',
		'footer_newsletter_text'    => '',
	];

	if (!$page_id || !function_exists('get_field')) {
		return $settings;
	}

	foreach (array_keys($settings) as $field) {
		$settings[$field] = (string) get_field($field, $page_id);
	}

	return $settings;
}

/**
 * Spaja footer podešavanja tako da prazna prevedena polja nasleđuju default jezik.
 *
 * @param array<string, string> $base
 * @param array<string, string> $override
 * @return array<string, string>
 */
function tersa_merge_string_settings(array $base, array $override): array {
	foreach ($override as $key => $value) {
		if (is_scalar($value) && trim((string) $value) !== '') {
			$base[$key] = (string) $value;
		}
	}

	return $base;
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

	$settings = [
		'footer_newsletter_heading' => '',
		'footer_newsletter_text'    => '',
	];

	$base_page_id    = tersa_get_footer_settings_base_page_id();
	$current_lang    = tersa_get_footer_current_language_slug();
	$default_lang    = tersa_get_footer_default_language_slug();
	$default_page_id = $default_lang ? tersa_get_footer_settings_page_id($default_lang) : $base_page_id;
	$current_page_id = $current_lang ? tersa_get_footer_settings_page_id($current_lang) : $base_page_id;

	$default_settings = tersa_read_footer_settings_from_page($default_page_id ?: $base_page_id);
	$current_settings = $current_page_id === $default_page_id
		? $default_settings
		: tersa_read_footer_settings_from_page($current_page_id);

	$settings = tersa_merge_string_settings($settings, $default_settings);
	$settings = tersa_merge_string_settings($settings, $current_settings);

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

	$settings_page_ids = tersa_get_footer_settings_page_ids();

	if (!in_array($post_id, $settings_page_ids, true) && tersa_get_footer_settings_slug() !== $post_obj->post_name) {
		return;
	}

	// Briše base key + sve jezičke varijante, uključujući legacy keys bez _v2.
	$bases = [
		'tersa_footer_settings',
		'tersa_company_settings',
		tersa_get_footer_settings_cache_base_key(),
		tersa_get_company_settings_cache_base_key(),
	];
	foreach ($bases as $base) {
		delete_transient($base);
	}

	if (function_exists('pll_languages_list')) {
		foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
			foreach ($bases as $base) {
				delete_transient($base . '_' . $lang_slug);
			}
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

	$slug      = trim(sanitize_title($slug), '/');
	$lang      = function_exists('pll_current_language') ? (string) pll_current_language('slug') : '';
	$cache_key = $lang . '|' . $slug;
	$fallback  = function_exists('tersa_get_current_language_home_url')
		? trailingslashit(tersa_get_current_language_home_url()) . $slug . '/'
		: home_url('/' . $slug . '/');

	if (isset($cache[$cache_key])) {
		return $cache[$cache_key];
	}

	$page = get_page_by_path($slug);

	if (!$page instanceof WP_Post) {
		return $cache[$cache_key] = $fallback;
	}

	if (function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page->ID);
		if ($translated_id) {
			$url = get_permalink($translated_id);
			return $cache[$cache_key] = ($url ?: get_permalink($page->ID) ?: $fallback);
		}
	}

	return $cache[$cache_key] = (get_permalink($page->ID) ?: $fallback);
}

/**
 * Sanitizuje ACF rich text vrednost i čuva postojeće plain-text line breakove.
 *
 * @param string $value
 * @return string
 */
function tersa_safe_rich_text(string $value): string {
	$value = trim($value);

	if ('' === $value) {
		return '';
	}

	if ($value === wp_strip_all_tags($value)) {
		$value = wpautop($value);
	}

	return wp_kses_post($value);
}

/**
 * Validira Google Maps iframe src.
 *
 * @param string $src Iframe src URL.
 * @return bool
 */
function tersa_is_allowed_map_embed_src(string $src): bool {
	$src = esc_url_raw(trim($src), ['https']);

	if ($src === '') {
		return false;
	}

	$parts = wp_parse_url($src);

	if (!is_array($parts) || empty($parts['host'])) {
		return false;
	}

	$scheme = strtolower((string) ($parts['scheme'] ?? ''));
	$host   = strtolower(trim((string) $parts['host'], '.'));
	$path   = '/' . ltrim((string) ($parts['path'] ?? ''), '/');

	if ($scheme !== 'https') {
		return false;
	}

	$allowed_hosts = ['maps.google.com', 'www.google.com', 'google.com'];
	if (function_exists('apply_filters')) {
		$allowed_hosts = (array) apply_filters('tersa_allowed_map_embed_hosts', $allowed_hosts);
	}
	$allowed_hosts = array_map(
		static function ($allowed_host): string {
			return strtolower(trim((string) $allowed_host, '.'));
		},
		$allowed_hosts
	);

	if (!in_array($host, $allowed_hosts, true)) {
		return false;
	}

	return $path === '/maps' || strpos($path, '/maps/') === 0;
}

/**
 * Izvlači atribute prvog iframe-a iz embed HTML-a.
 *
 * @param string $embed Iframe HTML.
 * @return array<string, string>
 */
function tersa_get_map_iframe_attributes(string $embed): array {
	$attrs = [];

	if (class_exists('DOMDocument')) {
		$previous_errors = function_exists('libxml_use_internal_errors')
			? libxml_use_internal_errors(true)
			: null;
		$dom             = new DOMDocument();
		$loaded          = $dom->loadHTML('<?xml encoding="utf-8" ?><!doctype html><html><body>' . $embed . '</body></html>');

		if (function_exists('libxml_clear_errors')) {
			libxml_clear_errors();
		}
		if ($previous_errors !== null && function_exists('libxml_use_internal_errors')) {
			libxml_use_internal_errors($previous_errors);
		}

		if ($loaded) {
			$iframe = $dom->getElementsByTagName('iframe')->item(0);

			if ($iframe instanceof DOMElement) {
				foreach ($iframe->attributes as $attribute) {
					$attrs[strtolower((string) $attribute->name)] = html_entity_decode(
						(string) $attribute->value,
						ENT_QUOTES | ENT_HTML5,
						'UTF-8'
					);
				}
			}
		}
	}

	if (!$attrs && preg_match('/<iframe\b([^>]*)>/i', $embed, $tag_match)) {
		if (preg_match_all('/\s([a-zA-Z:-]+)\s*=\s*([\'"])(.*?)\2/s', $tag_match[1], $attr_matches, PREG_SET_ORDER)) {
			foreach ($attr_matches as $attr_match) {
				$attrs[strtolower((string) $attr_match[1])] = html_entity_decode(
					(string) $attr_match[3],
					ENT_QUOTES | ENT_HTML5,
					'UTF-8'
				);
			}
		}
	}

	return $attrs;
}

/**
 * Sanitizuje Google Maps iframe embed iz ACF-a.
 *
 * @param string $embed Iframe HTML.
 * @return string
 */
function tersa_safe_map_embed(string $embed): string {
	$attrs = tersa_get_map_iframe_attributes(trim($embed));
	$src   = (string) ($attrs['src'] ?? '');

	if (!tersa_is_allowed_map_embed_src($src)) {
		return '';
	}

	$class_names = preg_split('/\s+/', (string) ($attrs['class'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
	$class_names = is_array($class_names) ? $class_names : [];
	$class_names[] = 'embed-map-frame';
	$class_names = array_values(array_unique(array_filter(array_map('sanitize_html_class', $class_names))));

	$scrolling = sanitize_key((string) ($attrs['scrolling'] ?? 'no'));
	if (!in_array($scrolling, ['yes', 'no', 'auto'], true)) {
		$scrolling = 'no';
	}

	$loading = sanitize_key((string) ($attrs['loading'] ?? 'lazy'));
	if (!in_array($loading, ['lazy', 'eager'], true)) {
		$loading = 'lazy';
	}

	$output_attrs = [
		'class'        => implode(' ', $class_names),
		'title'        => sanitize_text_field((string) ($attrs['title'] ?? __('Company location on Google Maps', 'tersa-shop'))),
		'frameborder'  => preg_replace('/[^0-9]/', '', (string) ($attrs['frameborder'] ?? '0')) ?: '0',
		'scrolling'    => $scrolling,
		'marginheight' => (string) absint($attrs['marginheight'] ?? 0),
		'marginwidth'  => (string) absint($attrs['marginwidth'] ?? 0),
		'loading'      => $loading,
		'src'          => esc_url_raw($src, ['https']),
	];

	if (!empty($attrs['referrerpolicy'])) {
		$referrer_policy = sanitize_key((string) $attrs['referrerpolicy']);
		$allowed_policies = [
			'no-referrer',
			'no-referrer-when-downgrade',
			'origin',
			'origin-when-cross-origin',
			'same-origin',
			'strict-origin',
			'strict-origin-when-cross-origin',
			'unsafe-url',
		];

		if (in_array($referrer_policy, $allowed_policies, true)) {
			$output_attrs['referrerpolicy'] = $referrer_policy;
		}
	}

	$html = '<iframe';
	foreach ($output_attrs as $name => $value) {
		$html .= sprintf(
			' %s="%s"',
			esc_attr($name),
			'src' === $name ? esc_url((string) $value, ['https']) : esc_attr((string) $value)
		);
	}

	return $html . '></iframe>';
}

/**
 * Normalizuje ACF image vrednost (ID, array ili URL) u bezbedan URL.
 *
 * @param mixed  $image ACF image vrednost.
 * @param string $size  WordPress image size.
 * @return string
 */
function tersa_get_acf_image_url($image, string $size = 'full'): string {
	$url = '';

	if (is_numeric($image)) {
		$url = wp_get_attachment_image_url(absint($image), $size);
	} elseif (is_array($image)) {
		$image_id = isset($image['ID']) ? absint($image['ID']) : (isset($image['id']) ? absint($image['id']) : 0);

		if ($image_id) {
			$url = wp_get_attachment_image_url($image_id, $size);
		}

		if (!$url && !empty($image['url']) && is_string($image['url'])) {
			$url = $image['url'];
		}
	} elseif (is_string($image) && '' !== trim($image)) {
		$url = $image;
	}

	return $url ? esc_url_raw((string) $url) : '';
}

/**
 * Vraća transient key za company settings.
 * Uključuje Polylang jezični sufiks kako bi svaki jezik imao vlastiti cache.
 *
 * @return string
 */
function tersa_get_company_settings_cache_key(): string {
	$lang = tersa_get_footer_current_language_slug();
	return tersa_get_company_settings_cache_base_key() . ($lang ? '_' . $lang : '');
}

/**
 * Prazan shape za ACF company/footer podešavanja.
 *
 * @return array<string, string>
 */
function tersa_get_empty_company_settings(): array {
	return [
		'company_name'                    => '',
		'company_full_name'               => '',
		'company_activity'                => '',
		'company_address'                 => '',
		'company_email'                   => '',
		'company_email_complaints'        => '',
		'company_phone_primary'           => '',
		'company_phone_secondary'         => '',
		'company_oib'                     => '',
		'company_mbs'                     => '',
		'company_court'                   => '',
		'company_share_capital'           => '',
		'company_director'                => '',
		'company_iban'                    => '',
		'company_bank'                    => '',
		'company_vat_id'                  => '',
		'company_support_hours'           => '',
		'footer_newsletter_cf7_shortcode' => '',
		'contact_cf7_shortcode'           => '',
		'contact_map_embed'               => '',
	];
}

/**
 * Učitava ACF company/footer podešavanja sa jedne stranice.
 *
 * @param int $page_id ID stranice.
 * @return array<string, string>
 */
function tersa_read_company_settings_from_page(int $page_id): array {
	$settings = tersa_get_empty_company_settings();

	if (!$page_id || !function_exists('get_field')) {
		return $settings;
	}

	foreach (array_keys($settings) as $field) {
		if ('company_phone_secondary' === $field) {
			$value = get_field('company_phone_secondary', $page_id);

			if (!is_scalar($value) || '' === trim((string) $value)) {
				$value = get_field('company_phone_primary_secondary', $page_id);
			}

			$settings[$field] = is_scalar($value) ? (string) $value : '';
			continue;
		}

		$value = get_field($field, $page_id);
		$settings[$field] = is_scalar($value) ? (string) $value : '';
	}

	return $settings;
}

/**
 * Dohvata ACF company settings sa fallback vrednostima.
 * Rezultat se kešira transientom na jedan dan — uklanjaj pri promeni footer-settings stranice.
 *
 * @return array<string, string>
 */
function tersa_get_company_settings(): array {
	$cache_key = tersa_get_company_settings_cache_key();
	$settings  = get_transient($cache_key);

	if (false !== $settings && is_array($settings)) {
		return wp_parse_args($settings, tersa_get_empty_company_settings());
	}

	$settings = tersa_get_empty_company_settings();

	$base_page_id    = tersa_get_footer_settings_base_page_id();
	$current_lang    = tersa_get_footer_current_language_slug();
	$default_lang    = tersa_get_footer_default_language_slug();
	$default_page_id = $default_lang ? tersa_get_footer_settings_page_id($default_lang) : $base_page_id;
	$current_page_id = $current_lang ? tersa_get_footer_settings_page_id($current_lang) : $base_page_id;

	$default_settings = tersa_read_company_settings_from_page($default_page_id ?: $base_page_id);
	$current_settings = $current_page_id === $default_page_id
		? $default_settings
		: tersa_read_company_settings_from_page($current_page_id);

	$settings = tersa_merge_string_settings($settings, $default_settings);
	$settings = tersa_merge_string_settings($settings, $current_settings);

	set_transient($cache_key, $settings, DAY_IN_SECONDS);

	return $settings;
}

/**
 * Briše company keš kada se sačuva Footer Settings stranica.
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

	$settings_page_ids = tersa_get_footer_settings_page_ids();

	if (!in_array($post_id, $settings_page_ids, true) && tersa_get_footer_settings_slug() !== $post_obj->post_name) {
		return;
	}

	// Briše base key + sve jezičke varijante, uključujući legacy key bez _v2.
	foreach (['tersa_company_settings', tersa_get_company_settings_cache_base_key()] as $base) {
		delete_transient($base);
		if (function_exists('pll_languages_list')) {
			foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
				delete_transient($base . '_' . $lang_slug);
			}
		}
	}
}
add_action('save_post_page', 'tersa_maybe_clear_company_settings_cache', 10, 2);

/**
 * Vraća ID menija za footer lokaciju sa Polylang fallback-om.
 *
 * @param string $location Theme menu location.
 * @return int
 */
function tersa_get_footer_nav_menu_id(string $location): int {
	$location = sanitize_key($location);
	if ($location === '') {
		return 0;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = !empty($locations[$location]) ? (int) $locations[$location] : 0;

	if (!$menu_id) {
		$theme_mods = get_option('theme_mods_' . get_option('stylesheet'), []);

		if (is_array($theme_mods) && !empty($theme_mods['nav_menu_locations'][$location])) {
			$menu_id = (int) $theme_mods['nav_menu_locations'][$location];
		}
	}

	if ($menu_id && function_exists('pll_get_term')) {
		$current_lang = tersa_get_footer_current_language_slug();
		$translated   = $current_lang ? (int) pll_get_term($menu_id, $current_lang) : 0;

		if ($translated) {
			return $translated;
		}
	}

	return $menu_id > 0 ? $menu_id : 0;
}

/**
 * Vraća argumente za footer meni.
 *
 * @param string $location Theme menu location.
 * @return array<string, mixed>
 */
function tersa_get_footer_nav_menu_args(string $location): array {
	$location = sanitize_key($location);
	$args     = [
		'theme_location' => $location,
		'container'      => false,
		'menu_class'     => 'site-footer__menu',
		'fallback_cb'    => false,
		'depth'          => 1,
	];

	$menu_id = tersa_get_footer_nav_menu_id($location);

	if ($menu_id) {
		$args['menu'] = $menu_id;
	}

	return $args;
}

/**
 * Vraća kompletan impressum sa fallback vrijednostima iz dopisa klijenta (2026-05-20).
 * Koristi se na Kontakt stranici i u footeru.
 *
 * Fallback vrijednosti su zakonski obavezni Impressum podaci za Tersa d.o.o. (HR ZTD čl. 21, ZET čl. 6).
 * Kada se popune ACF polja na `footer-settings` stranici, ova funkcija će vratiti vrijednosti iz ACF-a.
 *
 * @return array<string, string>
 */
function tersa_get_company_impressum(): array {
	$s = tersa_get_company_settings();

	$pick = static function (string $value, string $fallback): string {
		return trim($value) !== '' ? $value : $fallback;
	};

	return [
		'name'           => $pick($s['company_name']             ?? '', 'Tersa d.o.o.'),
		'full_name'      => $pick($s['company_full_name']        ?? '', 'Tersa d.o.o.'),
		'activity'       => $pick($s['company_activity']         ?? '', 'Prerada drva i trgovina drvnim proizvodima'),
		'address'        => $pick($s['company_address']          ?? '', 'Nikole Tesle 71, 31551 Črnkovci, Hrvatska'),
		'oib'            => $pick($s['company_oib']              ?? '', '80835896442'),
		'mbs'            => $pick($s['company_mbs']              ?? '', '030014012'),
		'court'          => $pick($s['company_court']            ?? '', 'Trgovački sud u Osijeku'),
		'director'       => $pick($s['company_director']         ?? '', 'Vlado Šakić, direktor'),
		'share_capital'  => (string) ($s['company_share_capital'] ?? ''),
		'iban'           => (string) ($s['company_iban']         ?? ''),
		'bank'           => (string) ($s['company_bank']         ?? ''),
		'vat_id'         => (string) ($s['company_vat_id']       ?? ''),
		'email'          => $pick($s['company_email']            ?? '', 'tersa@tersa.hr'),
		'email_complaints' => $pick($s['company_email_complaints'] ?? '', 'tersa@tersa.hr'),
		'phone'          => $pick($s['company_phone_primary']    ?? '', '031/355 900'),
		'phone_secondary'=> (string) ($s['company_phone_secondary'] ?? ''),
		'support_hours'  => $pick($s['company_support_hours']    ?? '', 'Pon – Pet: 08:00 – 14:00'),
	];
}

/**
 * Lista prihvaćenih kartica i payment metoda.
 *
 * Redoslijed po "Standardi logotipa i naziva kartičnih proizvoda v3.4" (CorvusPay):
 *  - Mastercard UVIJEK prije Maestra; ništa drugo između njih.
 *  - Linkovi se otvaraju u novom prozoru, vode na zvanične brand stranice.
 *  - Logotipi su iz Corvus brand kit-a, ne modifikovati boje/proporcije.
 *
 * Svaki SVG/PNG mora postojati u `assets/img/payments/{slug}.{ext}` — nepostojeći se preskaču.
 *
 * @return array<int, array{slug:string,ext:string,label:string,url:string}>
 */
function tersa_get_payment_methods(): array {
	return [
		[
			'slug'  => 'mastercard',
			'ext'   => 'svg',
			'label' => 'Mastercard',
			'url'   => 'https://www.mastercard.com/',
		],
		[
			'slug'  => 'maestro',
			'ext'   => 'svg',
			'label' => 'Maestro',
			'url'   => 'https://www.mastercard.hr/hr-hr/consumers/find-card-products/debit-cards/maestro-debit.html',
		],
		[
			'slug'  => 'visa',
			'ext'   => 'png',
			'label' => 'Visa',
			'url'   => 'https://www.visa.com.hr/',
		],
		[
			'slug'  => 'diners',
			'ext'   => 'png',
			'label' => 'Diners',
			'url'   => 'https://www.diners.hr/',
		],
	];
}

/**
 * Lista logotipa sigurnosnih programa kartičnih kuća.
 *
 * Po "Standardi logotipa v3.4":
 *  - Obavezno prikazati na: stranici za plaćanje (checkout) i stranici sa informacijama o sigurnosti.
 *  - Preporučeno: i na početnoj stranici.
 *  - Na jednoj stranici logotip se prikazuje JEDANPUT.
 *  - Ne smiju se koristiti umjesto logotipa kartice (npr. umjesto Visa logoa).
 *  - Linkovi otvarati u novom prozoru.
 *
 * Logotipi se nalaze u `assets/img/payments/security/{slug}.png`.
 *
 * @param string $variant 'light' (bijela pozadina) ili 'dark' (tamna pozadina — reverse MC logo).
 * @return array<int, array{slug:string,ext:string,label:string,url:string}>
 */
function tersa_get_security_badges(string $variant = 'light'): array {
	$mc_slug = $variant === 'dark' ? 'mastercard-identity-check-rev' : 'mastercard-identity-check';

	return [
		[
			'slug'  => $mc_slug,
			'ext'   => 'png',
			'label' => 'Mastercard Identity Check',
			'url'   => 'https://www.mastercard.hr/hr-hr/issuers/identity-check.html',
		],
		[
			'slug'  => 'visa-secure',
			'ext'   => 'png',
			'label' => 'Visa Secure',
			'url'   => 'https://www.visa.com.hr/placajte-visa-karticom/featured-technologies/verified-by-visa.html',
		],
		[
			'slug'  => 'diners-sigurna-kupnja',
			'ext'   => 'png',
			'label' => 'Diners sigurna kupnja',
			'url'   => 'https://www.diners.hr/',
		],
	];
}
