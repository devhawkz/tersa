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
 * ID footer settings stranice za trenutni Polylang jezik (ACF Free pristup).
 *
 * @return int
 */
function tersa_get_footer_settings_page_id(): int {
	static $cached_ids = [];

	$lang     = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	$lang_key = $lang ?: '_default';

	if (isset($cached_ids[$lang_key])) {
		return $cached_ids[$lang_key];
	}

	$page    = get_page_by_path(tersa_get_footer_settings_slug());
	$page_id = $page ? (int) $page->ID : 0;

	// Polylang: dohvati prevedenu verziju settings stranice za trenutni jezik.
	if ($page_id && $lang && function_exists('pll_get_post')) {
		$translated_id = pll_get_post($page_id, $lang);
		if ($translated_id) {
			$page_id = (int) $translated_id;
		}
	}

	return $cached_ids[$lang_key] = $page_id;
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

	$page_id   = tersa_get_footer_settings_page_id();
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
	$bases = ['tersa_footer_settings', 'tersa_company_settings'];
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
	$lang = function_exists('pll_current_language') ? (string) pll_current_language() : '';
	return 'tersa_company_settings' . ($lang ? '_' . $lang : '');
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
		return $settings;
	}

	$page_id   = tersa_get_footer_settings_page_id();
	$get_field = function_exists('get_field');

	$company_phone_secondary = '';
	if ($page_id && $get_field) {
		$company_phone_secondary_raw = get_field('company_phone_secondary', $page_id);

		if (!is_scalar($company_phone_secondary_raw) || '' === trim((string) $company_phone_secondary_raw)) {
			$company_phone_secondary_raw = get_field('company_phone_primary_secondary', $page_id);
		}

		$company_phone_secondary = is_scalar($company_phone_secondary_raw) ? (string) $company_phone_secondary_raw : '';
	}

	$settings = [
		'company_name'                    => ($page_id && $get_field) ? (string) get_field('company_name', $page_id) : '',
		'company_full_name'               => ($page_id && $get_field) ? (string) get_field('company_full_name', $page_id) : '',
		'company_activity'                => ($page_id && $get_field) ? (string) get_field('company_activity', $page_id) : '',
		'company_address'                 => ($page_id && $get_field) ? (string) get_field('company_address', $page_id) : '',
		'company_email'                   => ($page_id && $get_field) ? (string) get_field('company_email', $page_id) : '',
		'company_email_complaints'        => ($page_id && $get_field) ? (string) get_field('company_email_complaints', $page_id) : '',
		'company_phone_primary'           => ($page_id && $get_field) ? (string) get_field('company_phone_primary', $page_id) : '',
		'company_phone_secondary'         => $company_phone_secondary,
		'company_oib'                     => ($page_id && $get_field) ? (string) get_field('company_oib', $page_id) : '',
		'company_mbs'                     => ($page_id && $get_field) ? (string) get_field('company_mbs', $page_id) : '',
		'company_court'                   => ($page_id && $get_field) ? (string) get_field('company_court', $page_id) : '',
		'company_share_capital'           => ($page_id && $get_field) ? (string) get_field('company_share_capital', $page_id) : '',
		'company_director'                => ($page_id && $get_field) ? (string) get_field('company_director', $page_id) : '',
		'company_iban'                    => ($page_id && $get_field) ? (string) get_field('company_iban', $page_id) : '',
		'company_bank'                    => ($page_id && $get_field) ? (string) get_field('company_bank', $page_id) : '',
		'company_vat_id'                  => ($page_id && $get_field) ? (string) get_field('company_vat_id', $page_id) : '',
		'company_support_hours'           => ($page_id && $get_field) ? (string) get_field('company_support_hours', $page_id) : '',
		'footer_newsletter_cf7_shortcode' => ($page_id && $get_field) ? (string) get_field('footer_newsletter_cf7_shortcode', $page_id) : '',
	];

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

	if (tersa_get_footer_settings_slug() !== $post_obj->post_name) {
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
