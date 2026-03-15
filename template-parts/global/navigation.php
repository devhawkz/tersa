<?php
if (!defined('ABSPATH')) {
	exit;
}

// Uklanjamo id="menu-item-N" sa <li> elemenata — markup se renderuje jednom,
// ali identifikatori nisu potrebni i čiste HTML izvor.
add_filter('nav_menu_item_id', '__return_empty_string');

// Dodaje rel="noopener noreferrer" na sve eksterne linkove u navigaciji.
// WordPress automatski dodaje rel na target="_blank" linkove (WP 5.1+),
// ali ne i na eksterne linkove koji se otvaraju u istoj kartici.
if (!function_exists('tersa_nav_link_external_rel')) {
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
}
add_filter('nav_menu_link_attributes', 'tersa_nav_link_external_rel', 10, 4);

wp_nav_menu([
	'theme_location' => 'primary',
	'container'      => false,
	'menu_class'     => 'menu',
	'menu_id'        => '',
	'fallback_cb'    => false,
	// role="list" je neophodan jer CSS list-style:none uklanja list semantiku u Safari VoiceOver
	// aria-current="page" se automatski dodaje od WordPress 5.7+
	'items_wrap'     => '<ul class="%2$s" role="list">%3$s</ul>',
]);

remove_filter('nav_menu_link_attributes', 'tersa_nav_link_external_rel', 10, 4);
remove_filter('nav_menu_item_id', '__return_empty_string');