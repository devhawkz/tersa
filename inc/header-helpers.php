<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Vraća slug stranice koja čuva globalna header podešavanja za ACF Free.
 * Implementirano kao funkcija (ne konstanta) da bi child tema mogla da je override-uje.
 *
 * @return string
 */
function tersa_get_header_settings_slug(): string {
	return 'header-settings';
}

/**
 * Vraća osnovni transient key za header settings.
 *
 * @return string
 */
function tersa_get_header_settings_cache_base_key(): string {
	return 'tersa_header_settings_v2';
}

/**
 * Vraća transient key za header settings.
 * Uključuje Polylang jezični sufiks kako bi svaki jezik imao vlastiti cache.
 *
 * @return string
 */
function tersa_get_header_settings_cache_key(): string {
	$lang = tersa_get_current_language_slug();
	return tersa_get_header_settings_cache_base_key() . ($lang ? '_' . $lang : '');
}

/**
 * Vraća slug trenutnog Polylang jezika.
 *
 * @return string
 */
if (!function_exists('tersa_get_current_language_slug')) {
	function tersa_get_current_language_slug(): string {
		if (function_exists('pll_current_language')) {
			$lang = pll_current_language('slug');

			if (!empty($lang)) {
				return sanitize_key((string) $lang);
			}
		}

		return '';
	}
}

/**
 * Vraća slug defaultnog Polylang jezika.
 *
 * @return string
 */
function tersa_get_default_language_slug(): string {
	if (function_exists('pll_default_language')) {
		$lang = pll_default_language('slug');

		if (empty($lang)) {
			$lang = pll_default_language();
		}

		if (!empty($lang)) {
			return sanitize_key((string) $lang);
		}
	}

	return '';
}

/**
 * Vraća ID osnovne Header Settings stranice.
 *
 * @return int
 */
function tersa_get_header_settings_base_page_id(): int {
	$page = get_page_by_path(tersa_get_header_settings_slug());

	return $page instanceof WP_Post ? (int) $page->ID : 0;
}

/**
 * Vraća Header Settings stranicu za određeni jezik, uz fallback na osnovnu.
 *
 * @param string $lang Polylang slug jezika.
 * @return int
 */
function tersa_get_header_settings_page_id(string $lang = ''): int {
	$page_id = tersa_get_header_settings_base_page_id();

	if (!$page_id) {
		return 0;
	}

	if (function_exists('pll_get_post')) {
		$translated_id = $lang !== ''
			? pll_get_post($page_id, $lang)
			: pll_get_post($page_id);

		if ($translated_id) {
			return (int) $translated_id;
		}
	}

	return $page_id;
}

/**
 * Vraća sve poznate jezičke varijante Header Settings stranice.
 *
 * @return int[]
 */
function tersa_get_header_settings_page_ids(): array {
	$page_id = tersa_get_header_settings_base_page_id();

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
 * Učitava ACF header podešavanja sa jedne stranice.
 *
 * @param int $page_id ID stranice.
 * @return array{topbar_enabled: bool|null, topbar_message: string, topbar_link_text: string, topbar_link_url: string}
 */
function tersa_read_header_settings_from_page(int $page_id): array {
	$settings = [
		'topbar_enabled'   => null,
		'topbar_message'   => '',
		'topbar_link_text' => '',
		'topbar_link_url'  => '',
	];

	if (!$page_id || !function_exists('get_field')) {
		return $settings;
	}

	if (get_post_meta($page_id, 'topbar_enabled', true) !== '') {
		$settings['topbar_enabled'] = (bool) get_field('topbar_enabled', $page_id);
	}

	$settings['topbar_message']   = (string) get_field('topbar_message', $page_id);
	$settings['topbar_link_text'] = (string) get_field('topbar_link_text', $page_id);
	$settings['topbar_link_url']  = (string) get_field('topbar_link_url', $page_id);

	return $settings;
}

/**
 * Spaja header podešavanja tako da prazna prevedena polja nasleđuju default jezik.
 *
 * @param array<string, mixed> $base
 * @param array<string, mixed> $override
 * @return array<string, mixed>
 */
function tersa_merge_header_settings(array $base, array $override): array {
	if (array_key_exists('topbar_enabled', $override) && null !== $override['topbar_enabled']) {
		$base['topbar_enabled'] = (bool) $override['topbar_enabled'];
	}

	foreach (['topbar_message', 'topbar_link_text', 'topbar_link_url'] as $key) {
		if (!empty($override[$key])) {
			$base[$key] = (string) $override[$key];
		}
	}

	return $base;
}

/**
 * Dohvata ACF header settings sa fallback vrednostima.
 * Rezultat se kešira transientom na jedan dan — uklanjaj pri promeni stranice.
 *
 * @return array<string, mixed>
 */
function tersa_get_header_settings(): array {
	$cache_key = tersa_get_header_settings_cache_key();
	$settings  = get_transient($cache_key);

	if (false !== $settings && is_array($settings)) {
		return wp_parse_args(
			$settings,
			[
				'topbar_enabled'   => false,
				'topbar_message'   => '',
				'topbar_link_text' => '',
				'topbar_link_url'  => '',
			]
		);
	}

	$settings = [
		'topbar_enabled'   => false,
		'topbar_message'   => '',
		'topbar_link_text' => '',
		'topbar_link_url'  => '',
	];

	$base_page_id    = tersa_get_header_settings_base_page_id();
	$current_lang    = tersa_get_current_language_slug();
	$default_lang    = tersa_get_default_language_slug();
	$default_page_id = $default_lang ? tersa_get_header_settings_page_id($default_lang) : $base_page_id;
	$current_page_id = $current_lang ? tersa_get_header_settings_page_id($current_lang) : $base_page_id;

	$default_settings = tersa_read_header_settings_from_page($default_page_id ?: $base_page_id);
	$current_settings = $current_page_id === $default_page_id
		? $default_settings
		: tersa_read_header_settings_from_page($current_page_id);

	$settings = tersa_merge_header_settings($settings, $default_settings);
	$settings = tersa_merge_header_settings($settings, $current_settings);

	set_transient($cache_key, $settings, DAY_IN_SECONDS);

	return $settings;
}

/**
 * Briše keš kada se sačuva Header Settings stranica.
 *
 * @param int          $post_id
 * @param WP_Post|null $post
 * @return void
 */
function tersa_maybe_clear_header_settings_cache(int $post_id, $post = null): void {
	if (wp_is_post_revision($post_id) || 'page' !== get_post_type($post_id)) {
		return;
	}

	$post_obj = $post instanceof WP_Post ? $post : get_post($post_id);
	if (!$post_obj instanceof WP_Post) {
		return;
	}

	$settings_page_ids = tersa_get_header_settings_page_ids();

	if (!in_array($post_id, $settings_page_ids, true) && tersa_get_header_settings_slug() !== $post_obj->post_name) {
		return;
	}

	// Briše base key + sve jezičke varijante, uključujući legacy key bez _v2.
	foreach (['tersa_header_settings', tersa_get_header_settings_cache_base_key()] as $base) {
		delete_transient($base);
		if (function_exists('pll_languages_list')) {
			foreach ((array) pll_languages_list(['fields' => 'slug']) as $lang_slug) {
				delete_transient($base . '_' . $lang_slug);
			}
		}
	}
}
add_action('save_post_page', 'tersa_maybe_clear_header_settings_cache', 10, 2);

/**
 * Vraća početnu stranicu za trenutni Polylang jezik.
 *
 * @return string
 */
function tersa_get_current_language_home_url(): string {
	if (function_exists('pll_home_url')) {
		$url = pll_home_url();

		if (!empty($url)) {
			return (string) $url;
		}
	}

	return home_url('/');
}

/**
 * Vraća WooCommerce page URL mapiran na trenutni Polylang jezik.
 *
 * @param string $page WooCommerce page key: shop, cart, checkout, myaccount.
 * @return string
 */
function tersa_get_wc_page_url(string $page): string {
	$page = sanitize_key($page);

	if (function_exists('wc_get_page_id')) {
		$page_id = (int) wc_get_page_id($page);

		if ($page_id > 0) {
			$translated_id = 0;

			if (function_exists('pll_get_post')) {
				$translated_id = (int) pll_get_post($page_id);
			}

			$url = get_permalink($translated_id ?: $page_id);

			if (!empty($url)) {
				return (string) $url;
			}
		}
	}

	if (function_exists('wc_get_page_permalink')) {
		$url = wc_get_page_permalink($page);

		if (!empty($url)) {
			return (string) $url;
		}
	}

	return tersa_get_current_language_home_url();
}

/**
 * Vraća shop/search URL.
 *
 * @return string
 */
function tersa_get_search_url(): string {
	if (class_exists('WooCommerce')) {
		return tersa_get_wc_page_url('shop');
	}

	return tersa_get_current_language_home_url();
}

/**
 * Vraća URL wishlist stranice.
 *
 * @return string
 */
function tersa_get_wishlist_url(): string {
	if (defined('YITH_WCWL') && function_exists('YITH_WCWL')) {
		$instance = YITH_WCWL();

		if (is_object($instance) && method_exists($instance, 'get_wishlist_url')) {
			return (string) $instance->get_wishlist_url();
		}
	}

	return '';
}

/**
 * Vraća broj wishlist stavki.
 * Preferira YITH shortcode jer se najčešće osvežava preko AJAX-a.
 * Rezultat se kešira statički — shortcode se ne izvršava dva puta po requestu.
 *
 * @return int
 */
function tersa_get_wishlist_count(): int {
	static $cached = null;

	if (null !== $cached) {
		return $cached;
	}

	$count = 0;

	// Direktan YITH API — bez parsera shortcode-a (manje CPU-a po requestu).
	if (function_exists('yith_wcwl_count_products')) {
		$count = (int) yith_wcwl_count_products();
	} elseif (shortcode_exists('yith_wcwl_items_count')) {
		$output = do_shortcode('[yith_wcwl_items_count]');
		$output = wp_strip_all_tags((string) $output);
		$output = trim($output);

		if (is_numeric($output)) {
			$count = (int) $output;
		}
	}

	$cached = max(0, $count);
	return $cached;
}

/**
 * Vraća broj proizvoda u korpi.
 * Rezultat se kešira statički — WC()->cart se ne poziva više puta po requestu.
 *
 * @return int
 */
function tersa_get_cart_count(): int {
	static $cached = null;

	if (null !== $cached) {
		return $cached;
	}

	$cached = 0;

	if (class_exists('WooCommerce') && function_exists('WC')) {
		$wc = WC();

		if ($wc && isset($wc->cart) && $wc->cart) {
			$cached = (int) $wc->cart->get_cart_contents_count();
		}
	}

	return $cached;
}

/**
 * Fallback logo iz teme (PNG + opcioni WebP u istom folderu).
 *
 * @param string $img_class       Klasa na <img> (npr. site-header__logo-image).
 * @param string $loading         eager|lazy.
 * @param string $decoding        async|auto.
 * @param string $fetchpriority   high|low|auto (prazan = ne dodaje atribut).
 * @return string Prazno ako PNG ne postoji.
 */
function tersa_get_theme_fallback_logo_markup(
	string $img_class,
	string $loading = 'lazy',
	string $decoding = 'async',
	string $fetchpriority = ''
): string {
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$png_path  = $theme_dir . '/assets/img/tersa-logo.png';
	$webp_path = $theme_dir . '/assets/img/tersa-logo.webp';

	if (!file_exists($png_path)) {
		return '';
	}

	$site_name = get_bloginfo('name');

	$logo_size = wp_cache_get('tersa_fallback_logo_dims', 'tersa_theme');
	if (false === $logo_size) {
		$logo_size = function_exists('wp_getimagesize')
			? wp_getimagesize($png_path)
			: @getimagesize($png_path); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		wp_cache_set('tersa_fallback_logo_dims', $logo_size ?: [], 'tersa_theme');
	}

	$width_attr  = !empty($logo_size[0]) ? ' width="' . (int) $logo_size[0] . '"' : '';
	$height_attr = !empty($logo_size[1]) ? ' height="' . (int) $logo_size[1] . '"' : '';

	$fetch_attr = '';
	if ($fetchpriority !== '') {
		$fetchpriority = in_array($fetchpriority, ['high', 'low', 'auto'], true) ? $fetchpriority : 'auto';
		$fetch_attr    = ' fetchpriority="' . esc_attr($fetchpriority) . '"';
	}

	$common_img_attrs = sprintf(
		' class="%s" alt="%s" loading="%s" decoding="%s"%s%s%s',
		esc_attr($img_class),
		esc_attr($site_name),
		esc_attr($loading),
		esc_attr($decoding),
		$fetch_attr,
		$width_attr,
		$height_attr
	);

	$png_url = $theme_uri . '/assets/img/tersa-logo.png';

	if (file_exists($webp_path)) {
		$webp_url = $theme_uri . '/assets/img/tersa-logo.webp';

		return sprintf(
			'<picture><source srcset="%1$s" type="image/webp" /><img src="%2$s"%3$s /></picture>',
			esc_url($webp_url),
			esc_url($png_url),
			$common_img_attrs
		);
	}

	return sprintf(
		'<img src="%1$s"%2$s />',
		esc_url($png_url),
		$common_img_attrs
	);
}

/**
 * Vraća markup za logo sa fallback-om.
 *
 * @return string
 */
function tersa_get_header_logo_markup(): string {
	$site_name      = get_bloginfo('name');
	$custom_logo_id = (int) get_theme_mod('custom_logo');

	if ($custom_logo_id) {
		$logo = wp_get_attachment_image(
			$custom_logo_id,
			'tersa-logo',
			false,
			[
				'class'         => 'site-header__logo-image',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
				'alt'           => $site_name,
			]
		);

		if ($logo) {
			return $logo;
		}
	}

	$fallback = tersa_get_theme_fallback_logo_markup('site-header__logo-image', 'eager', 'async', 'high');
	if ($fallback !== '') {
		return $fallback;
	}

	return sprintf(
		'<span class="site-header__logo-text">%s</span>',
		esc_html($site_name)
	);
}

/**
 * Vraća aria label za wishlist link.
 *
 * @return string
 */
function tersa_get_wishlist_aria_label(): string {
	$count = tersa_get_wishlist_count();

	return sprintf(
		/* translators: %d: wishlist item count */
		_n('Wishlist, %d item', 'Wishlist, %d items', $count, 'tersa-shop'),
		$count
	);
}

/**
 * Vraća aria label za cart link.
 *
 * @return string
 */
function tersa_get_cart_aria_label(): string {
	$count = tersa_get_cart_count();

	return sprintf(
		/* translators: %d: cart item count */
		_n('Cart, %d item', 'Cart, %d items', $count, 'tersa-shop'),
		$count
	);
}

/**
 * Prevodi string koristeći Polylang ako je dostupan, inače vraća originalni string.
 *
 * @param string $string String za prevod.
 * @return string
 */
function tersa_translate_string(string $string): string {
	if (!$string) {
		return $string;
	}

	if (function_exists('pll__')) {
		return (string) pll__($string);
	}

	return $string;
}

/**
 * Registruje ACF topbar stringove u Polylang String Translations.
 * Potrebno da prevodioci mogu prevesti topbar poruku i link tekst/URL.
 *
 * @return void
 */
function tersa_register_polylang_strings(): void {
	if (!function_exists('pll_register_string')) {
		return;
	}

	$settings = tersa_get_header_settings();
	$group    = 'Tersa Shop Header';

	if (!empty($settings['topbar_message'])) {
		pll_register_string('topbar_message', $settings['topbar_message'], $group, true);
	}

	if (!empty($settings['topbar_link_text'])) {
		pll_register_string('topbar_link_text', $settings['topbar_link_text'], $group);
	}

	if (!empty($settings['topbar_link_url'])) {
		pll_register_string('topbar_link_url', $settings['topbar_link_url'], $group);
	}
}
add_action('init', 'tersa_register_polylang_strings');

/**
 * Dodaje rel="noopener noreferrer" na eksterne linkove u primarnoj navigaciji.
 * Koristi se kao filter u navigation.php oko wp_nav_menu() poziva.
 * WordPress automatski dodaje rel na target="_blank" linkove (WP 5.1+),
 * ali ne i na eksterne linkove koji se otvaraju u istoj kartici.
 *
 * @param array<string, string> $atts
 * @param \WP_Post              $item
 * @param \stdClass             $args
 * @param int                   $depth
 * @return array<string, string>
 */
function tersa_nav_link_external_rel(array $atts, $item, $args, $depth): array {
	if (empty($atts['href'])) {
		return $atts;
	}

	$link_host = wp_parse_url($atts['href'], PHP_URL_HOST);
	$home_host = wp_parse_url(home_url(), PHP_URL_HOST);

	if ($link_host && $home_host && $link_host !== $home_host) {
		// Sačuvaj postojeći rel ako postoji (npr. rel="nofollow" dodat ručno)
		$existing    = !empty($atts['rel']) ? rtrim((string) $atts['rel']) . ' ' : '';
		$atts['rel'] = $existing . 'noopener noreferrer';
	}

	return $atts;
}

/**
 * Vraća ID menija za primarnu navigaciju sa Polylang fallback-om.
 *
 * @return int
 */
function tersa_get_primary_nav_menu_id(): int {
	$locations = get_nav_menu_locations();
	$menu_id   = !empty($locations['primary']) ? (int) $locations['primary'] : 0;

	if (!$menu_id) {
		$theme_mods = get_option('theme_mods_' . get_option('stylesheet'), []);

		if (is_array($theme_mods) && !empty($theme_mods['nav_menu_locations']['primary'])) {
			$menu_id = (int) $theme_mods['nav_menu_locations']['primary'];
		}
	}

	if ($menu_id && function_exists('pll_get_term')) {
		$current_lang = tersa_get_current_language_slug();
		$translated   = $current_lang ? (int) pll_get_term($menu_id, $current_lang) : 0;

		if ($translated) {
			return $translated;
		}
	}

	return $menu_id > 0 ? $menu_id : 0;
}

/**
 * Vraća argumente za primarni meni.
 *
 * @return array<string, mixed>
 */
function tersa_get_primary_nav_menu_args(): array {
	$args = [
		'theme_location' => 'primary',
		'container'      => false,
		'menu_class'     => 'menu',
		'menu_id'        => '',
		'fallback_cb'    => false,
		// role="list" je neophodan jer CSS list-style:none uklanja list semantiku u Safari VoiceOver
		// aria-current="page" se automatski dodaje od WordPress 5.7+
		'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
	];

	$menu_id = tersa_get_primary_nav_menu_id();

	if ($menu_id) {
		$args['menu'] = $menu_id;
	}

	return $args;
}

/**
 * Vraća prevedeni URL topbar linka i da li je eksterni.
 * Centralizuje detekciju eksternog linka van template fajla.
 *
 * Napomena o Polylang-u: topbar_link_url se čuva kao string u ACF i registruje
 * putem pll_register_string(), što prevodiocima omogućava unos različitog URL-a
 * po jeziku direktno u Polylang → String translations. Ovaj pristup je ispravan
 * za polja koja mogu sadržavati i interne i eksterne URL-ove. Za stranice koje
 * postoje kao WP page post type, koristi tersa_pll_page_url() umesto ovog poziva.
 *
 * @param string $url Sirovi URL iz ACF podešavanja.
 * @return array{ url: string, is_external: bool }
 */
function tersa_get_topbar_link_data(string $url): array {
	if (!$url) {
		return ['url' => '', 'is_external' => false];
	}

	$translated = tersa_translate_string($url);
	$link_host  = wp_parse_url($translated, PHP_URL_HOST);
	$home_host  = wp_parse_url(home_url(), PHP_URL_HOST);

	return [
		'url'         => $translated,
		'is_external' => (bool) ($link_host && $home_host && $link_host !== $home_host),
	];
}

/**
 * Priprema sve promenljive potrebne za header.php template.
 * Centralizuje biznis logiku van template fajla — template sadrži samo prezentaciju.
 * Rezultat se kešira statički — funkcija je skupa (nav markup, logo, settings, count).
 *
 * @return array<string, mixed>
 */
function tersa_get_header_template_data(): array {
	static $cached = null;

	if (null !== $cached) {
		return $cached;
	}

	$header_data = tersa_get_header_settings();
	$topbar_url  = !empty($header_data['topbar_link_url']) ? (string) $header_data['topbar_link_url'] : '';
	$link_data   = tersa_get_topbar_link_data($topbar_url);

	$cached = [
		'home_url'               => tersa_get_current_language_home_url(),
		'site_name'              => get_bloginfo('name'),
		'logo_markup'            => tersa_get_header_logo_markup(),
		'wishlist_url'           => tersa_get_wishlist_url(),
		'cart_url'               => class_exists('WooCommerce') ? tersa_get_wc_page_url('cart') : '',
		'wishlist_count'         => tersa_get_wishlist_count(),
		'cart_count'             => tersa_get_cart_count(),
		'topbar_enabled'         => !empty($header_data['topbar_enabled']),
		'topbar_message'         => !empty($header_data['topbar_message'])
		                           ? tersa_translate_string((string) $header_data['topbar_message'])
		                           : '',
		'topbar_link_text'       => !empty($header_data['topbar_link_text'])
		                           ? tersa_translate_string((string) $header_data['topbar_link_text'])
		                           : '',
		'topbar_link_url'        => $link_data['url'],
		'topbar_link_is_external'=> $link_data['is_external'],
	];

	return $cached;
}
