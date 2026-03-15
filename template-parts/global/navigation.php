<?php
if (!defined('ABSPATH')) {
	exit;
}

wp_nav_menu([
	'theme_location' => 'primary',
	'container'      => false,
	'menu_class'     => 'menu',
	'fallback_cb'    => false,
	// role="list" je neophodan jer CSS list-style:none uklanja list semantiku u Safari VoiceOver
	// aria-current="page" se automatski dodaje od WordPress 5.7+
	'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
]);