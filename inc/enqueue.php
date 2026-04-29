<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Vraća verzijski string za asset baziran na filemtime() — automatsko cache busting.
 * Ako fajl ne postoji, fallback na trenutnu verziju teme (style.css).
 *
 * Static cache izbjegava ponovljene filesystem stat pozive za isti asset
 * unutar istog requesta.
 */
function tersa_asset_ver(string $relative_path): string {
	static $cache = [];
	static $theme_version = null;

	if ($theme_version === null) {
		$theme_version = (string) wp_get_theme()->get('Version');
	}

	$relative_path = ltrim($relative_path, '/');

	if (isset($cache[$relative_path])) {
		return $cache[$relative_path];
	}

	$abs_path = get_template_directory() . '/' . $relative_path;
	$mtime    = file_exists($abs_path) ? @filemtime($abs_path) : false;

	$cache[$relative_path] = $mtime ? (string) $mtime : $theme_version;

	return $cache[$relative_path];
}

function tersa_enqueue_assets() {
	$theme_uri = get_template_directory_uri();

	wp_enqueue_style('tersa-base', $theme_uri . '/assets/css/base.css', [], tersa_asset_ver('assets/css/base.css'));
	wp_enqueue_style('tersa-layout', $theme_uri . '/assets/css/layout.css', ['tersa-base'], tersa_asset_ver('assets/css/layout.css'));
	wp_enqueue_style('tersa-header', $theme_uri . '/assets/css/header.css', ['tersa-base'], tersa_asset_ver('assets/css/header.css'));
	wp_enqueue_style('tersa-footer', $theme_uri . '/assets/css/footer.css', ['tersa-base'], tersa_asset_ver('assets/css/footer.css'));

	// Sidebar CSS — učitava se samo na stranicama koje realno prikazuju sidebar.
	// EU project archive/single isključeni jer ne pozivaju get_sidebar().
	// Za page.php, index.php, 404.php sidebar je isključen po defaultu —
	// aktiviraj filterom: add_filter('tersa_sidebar_on_general_pages', '__return_true');
	$needs_sidebar_shop = (
		(function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()))
		|| (is_archive() && !is_post_type_archive('eu_project'))
		|| is_search()
	);
	$needs_sidebar_general = (bool) apply_filters('tersa_sidebar_on_general_pages', false)
		&& (is_page() || is_home() || is_404());

	if ($needs_sidebar_shop || $needs_sidebar_general) {
		wp_enqueue_style('tersa-sidebar', $theme_uri . '/assets/css/sidebar.css', ['tersa-base'], tersa_asset_ver('assets/css/sidebar.css'));
	}

	//contact page style
	if (is_page_template('page-templates/template-contact.php')) {
		wp_enqueue_style(
			'tersa-contact',
			$theme_uri . '/assets/css/contact.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/contact.css')
		);
	}

	//about page style
	if (is_page_template('page-templates/template-about.php')) {
		wp_enqueue_style(
			'tersa-about',
			$theme_uri . '/assets/css/about.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/about.css')
		);
	}
	$is_wishlist = function_exists('tersa_is_wishlist_page') && tersa_is_wishlist_page();
	//hompeage
	if (is_front_page() && !$is_wishlist) {
		wp_enqueue_style(
			'tersa-home',
			$theme_uri . '/assets/css/home.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/home.css')
		);

		wp_enqueue_style(
			'tersa-bestsellers',
			$theme_uri . '/assets/css/bestsellers.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/bestsellers.css')
		);
	
		wp_enqueue_script(
			'tersa-home',
			$theme_uri . '/assets/js/home.js',
			[],
			tersa_asset_ver('assets/js/home.js'),
			true
		);
	}

	if ($is_wishlist) {
		wp_enqueue_style(
			'tersa-wishlist',
			$theme_uri . '/assets/css/wishlist.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/wishlist.css')
		);

	}

	//shop page
	if (
		function_exists('is_shop')
		&& (
			is_shop()
			|| is_product_category()
			|| is_product_tag()
			|| is_product_taxonomy()
		)
	) {
		wp_enqueue_style(
			'tersa-shop',
			$theme_uri . '/assets/css/shop.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/shop.css')
		);

		wp_enqueue_script(
			'tersa-shop',
			$theme_uri . '/assets/js/shop.js',
			[],
			tersa_asset_ver('assets/js/shop.js'),
			true
		);
	}

	if (function_exists('is_product') && is_product()) {
		// Provjeri transient za related products — ako je prazan niz, sekcija se neće prikazati
		// i bestsellers.css nije potreban. false = transient nije postavljen (prvi posjet) → učitaj CSS.
		$_eq_lang              = function_exists('pll_current_language') ? (string) pll_current_language() : '';
		$product_related_cache = get_transient('tersa_related_' . get_queried_object_id() . ($_eq_lang ? '_' . $_eq_lang : ''));
		$has_related_products  = ($product_related_cache === false) || !empty($product_related_cache);

		if ($has_related_products) {
			wp_enqueue_style(
				'tersa-bestsellers',
				$theme_uri . '/assets/css/bestsellers.css',
				['tersa-base', 'tersa-layout'],
				tersa_asset_ver('assets/css/bestsellers.css')
			);
		}

		wp_enqueue_style(
			'tersa-product',
			$theme_uri . '/assets/css/product.css',
			$has_related_products
				? ['tersa-base', 'tersa-layout', 'tersa-bestsellers']
				: ['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/product.css')
		);
	
		wp_enqueue_script(
			'tersa-product',
			$theme_uri . '/assets/js/product.js',
			['jquery'],
			tersa_asset_ver('assets/js/product.js'),
			true
		);
	}

	if (function_exists('is_cart') && is_cart()) {
		wp_enqueue_style(
			'tersa-bestsellers',
			$theme_uri . '/assets/css/bestsellers.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/bestsellers.css')
		);

		wp_enqueue_script(
			'tersa-home',
			$theme_uri . '/assets/js/home.js',
			[],
			tersa_asset_ver('assets/js/home.js'),
			true
		);
	}

	if (is_404()) {
		wp_enqueue_style(
			'tersa-404',
			$theme_uri . '/assets/css/404.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/404.css')
		);
	}

	if (is_page() && !is_front_page() && (!function_exists('tersa_is_wishlist_page') || !tersa_is_wishlist_page())) {
		wp_enqueue_style(
			'tersa-page',
			$theme_uri . '/assets/css/page.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/page.css')
		);
	}

	// CPT slug u kodu je 'eu_project' (rewrite URL je /eu-projekti/ — to ne ide ovdje).
	if (is_post_type_archive('eu_project') || is_singular('eu_project')) {
		wp_enqueue_style(
			'tersa-eu-project',
			$theme_uri . '/assets/css/eu-project.css',
			['tersa-base', 'tersa-layout'],
			tersa_asset_ver('assets/css/eu-project.css')
		);
	}
	

	wp_enqueue_script('tersa-header-js', $theme_uri . '/assets/js/header.js', [], tersa_asset_ver('assets/js/header.js'), true);

	wp_localize_script('tersa-header-js', 'tersaCartDrawer', [
		'ajaxUrl'         => admin_url('admin-ajax.php'),
		'ajaxUrlRelative' => admin_url('admin-ajax.php', 'relative'),
		// wc-ajax endpoints — lakša ruta od admin-ajax.php (preferirano na frontend-u).
		'wcAjaxFragments' => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('tersa_get_cart_drawer_fragments') : '',
		'wcAjaxQty'       => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('tersa_update_mini_cart_qty') : '',
		'nonce'           => wp_create_nonce('tersa_cart_nonce'),
	]);

	wp_localize_script('tersa-header-js', 'tersaHeaderI18n', [
		'openSubmenuFor' => function_exists('pll__')
			? pll__('Otvori podizbornik za %s')
			: __('Otvori podizbornik za %s', 'tersa-shop'),
		'cartLoadError' => function_exists('pll__')
			? pll__('Greška pri učitavanju košarice.')
			: __('Greška pri učitavanju košarice.', 'tersa-shop'),
	]);
}
add_action('wp_enqueue_scripts', 'tersa_enqueue_assets');
