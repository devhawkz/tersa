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
 * Vraća transient key za header settings.
 * Implementirano kao funkcija (ne konstanta) da bi child tema mogla da je override-uje.
 *
 * @return string
 */
function tersa_get_header_settings_cache_key(): string {
	return 'tersa_header_settings';
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

	$page      = get_page_by_path(tersa_get_header_settings_slug());
	$page_id   = $page ? (int) $page->ID : 0;
	$get_field = function_exists('get_field');

	$settings = [
		'topbar_enabled'   => ($page_id && $get_field) ? (bool) get_field('topbar_enabled', $page_id) : false,
		'topbar_message'   => ($page_id && $get_field) ? (string) get_field('topbar_message', $page_id) : '',
		'topbar_link_text' => ($page_id && $get_field) ? (string) get_field('topbar_link_text', $page_id) : '',
		'topbar_link_url'  => ($page_id && $get_field) ? (string) get_field('topbar_link_url', $page_id) : '',
	];

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

	if (tersa_get_header_settings_slug() !== $post_obj->post_name) {
		return;
	}

	delete_transient(tersa_get_header_settings_cache_key());
}
add_action('save_post_page', 'tersa_maybe_clear_header_settings_cache', 10, 2);

/**
 * Vraća shop/search URL.
 *
 * @return string
 */
function tersa_get_search_url(): string {
	if (class_exists('WooCommerce')) {
		$shop_url = wc_get_page_permalink('shop');

		if ($shop_url) {
			return $shop_url;
		}
	}

	return home_url('/');
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
				'class'   => 'site-header__logo-image',
				'loading' => 'eager',
				'alt'     => $site_name,
			]
		);

		if ($logo) {
			return $logo;
		}
	}

	$fallback_logo_path = get_template_directory() . '/assets/img/tersa-logo.png';
	if (file_exists($fallback_logo_path)) {
		$fallback_logo_url = get_template_directory_uri() . '/assets/img/tersa-logo.png';

		// Keširanje dimenzija po requestu — sprečava CLS (Cumulative Layout Shift)
		$logo_size = wp_cache_get('tersa_fallback_logo_dims', 'tersa_theme');
		if (false === $logo_size) {
			$logo_size = function_exists('wp_getimagesize')
				? wp_getimagesize($fallback_logo_path)
				: @getimagesize($fallback_logo_path); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			wp_cache_set('tersa_fallback_logo_dims', $logo_size ?: [], 'tersa_theme');
		}

		$width_attr  = !empty($logo_size[0]) ? ' width="' . (int) $logo_size[0] . '"' : '';
		$height_attr = !empty($logo_size[1]) ? ' height="' . (int) $logo_size[1] . '"' : '';

		return sprintf(
			'<img src="%1$s" alt="%2$s" class="site-header__logo-image" loading="eager"%3$s%4$s>',
			esc_url($fallback_logo_url),
			esc_attr($site_name),
			$width_attr,
			$height_attr
		);
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
		'home_url'               => home_url('/'),
		'site_name'              => get_bloginfo('name'),
		'logo_markup'            => tersa_get_header_logo_markup(),
		'wishlist_url'           => tersa_get_wishlist_url(),
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
