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
	return 'tersa_company_settings_v2';
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
 * Registruje footer/global ACF stringove za Polylang String Translation.
 *
 * @return void
 */
function tersa_register_footer_polylang_strings(): void {
	if (!function_exists('pll_register_string')) {
		return;
	}

	$register = static function (string $name, string $value, string $group, bool $multiline = false): void {
		if (trim($value) === '') {
			return;
		}

		pll_register_string($name, $value, $group, $multiline);
	};

	$footer_settings = tersa_get_footer_settings();
	$company_settings = tersa_get_company_settings();

	$register('footer_newsletter_heading', (string) ($footer_settings['footer_newsletter_heading'] ?? ''), 'Tersa – footer', false);
	$register('footer_newsletter_text', (string) ($footer_settings['footer_newsletter_text'] ?? ''), 'Tersa – footer', true);
	$register('footer_newsletter_cf7_shortcode', (string) ($company_settings['footer_newsletter_cf7_shortcode'] ?? ''), 'Tersa – forme', false);

	$company_string_fields = [
		'company_name'          => false,
		'company_full_name'     => false,
		'company_activity'      => false,
		'company_address'       => true,
		'company_court'         => false,
		'company_director'      => false,
		'company_share_capital' => false,
		'company_bank'          => false,
		'company_support_hours' => false,
	];

	foreach ($company_string_fields as $field => $multiline) {
		$register($field, (string) ($company_settings[$field] ?? ''), 'Tersa – podaci tvrtke', (bool) $multiline);
	}

	$company_fallback_strings = [
		'company_activity_fallback'      => ['Prerada drva i trgovina drvnim proizvodima', false],
		'company_address_fallback'       => ['Nikole Tesle 71, 31551 Črnkovci, Hrvatska', true],
		'company_court_fallback'         => ['Trgovački sud u Osijeku', false],
		'company_director_fallback'      => ['Vlado Šakić, direktor', false],
		'company_support_hours_fallback' => ['Pon – Pet: 08:00 – 14:00', false],
	];

	foreach ($company_fallback_strings as $name => [$value, $multiline]) {
		$register($name, $value, 'Tersa – podaci tvrtke', (bool) $multiline);
	}

	$global_settings_page_id = tersa_get_global_settings_page_id();
	if ($global_settings_page_id && function_exists('get_field')) {
		$register('contact_cf7_shortcode', (string) get_field('contact_cf7_shortcode', $global_settings_page_id), 'Tersa – forme', false);
		$register('contact_map_embed', (string) get_field('contact_map_embed', $global_settings_page_id), 'Tersa – kontakt', true);
	}
}
add_action('init', 'tersa_register_footer_polylang_strings', 20);

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
	$translate = static function (string $value): string {
		return function_exists('tersa_translate_string') ? tersa_translate_string($value) : $value;
	};

	return [
		'name'           => $translate($pick($s['company_name']             ?? '', 'Tersa d.o.o.')),
		'full_name'      => $translate($pick($s['company_full_name']        ?? '', 'Tersa d.o.o.')),
		'activity'       => $translate($pick($s['company_activity']         ?? '', 'Prerada drva i trgovina drvnim proizvodima')),
		'address'        => $translate($pick($s['company_address']          ?? '', 'Nikole Tesle 71, 31551 Črnkovci, Hrvatska')),
		'oib'            => $pick($s['company_oib']              ?? '', '80835896442'),
		'mbs'            => $pick($s['company_mbs']              ?? '', '030014012'),
		'court'          => $translate($pick($s['company_court']            ?? '', 'Trgovački sud u Osijeku')),
		'director'       => $translate($pick($s['company_director']         ?? '', 'Vlado Šakić, direktor')),
		'share_capital'  => $translate((string) ($s['company_share_capital'] ?? '')),
		'iban'           => (string) ($s['company_iban']         ?? ''),
		'bank'           => $translate((string) ($s['company_bank']         ?? '')),
		'vat_id'         => (string) ($s['company_vat_id']       ?? ''),
		'email'          => $pick($s['company_email']            ?? '', 'tersa@tersa.hr'),
		'email_complaints' => $pick($s['company_email_complaints'] ?? '', 'tersa@tersa.hr'),
		'phone'          => $pick($s['company_phone_primary']    ?? '', '031/355 900'),
		'phone_secondary'=> (string) ($s['company_phone_secondary'] ?? ''),
		'support_hours'  => $translate($pick($s['company_support_hours']    ?? '', 'Pon – Pet: 08:00 – 14:00')),
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
