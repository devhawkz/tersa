<?php
if (!defined('ABSPATH')) {
	exit;
}

// Uklanjamo id="menu-item-N" sa <li> elemenata jer se isti markup renderuje dva puta
// (desktop + mobilni meni) što bi uzrokovalo duplirane ID-ove u DOM-u (HTML invalid).
add_filter('nav_menu_item_id', '__return_empty_string');

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

remove_filter('nav_menu_item_id', '__return_empty_string');