<?php
if (!defined('ABSPATH')) {
	exit;
}

// Uklanjamo id="menu-item-N" sa <li> elemenata — markup se renderuje jednom,
// ali identifikatori nisu potrebni i čiste HTML izvor.
add_filter('nav_menu_item_id', '__return_empty_string');



// tersa_nav_link_external_rel() je definisana u inc/header-helpers.php
add_filter('nav_menu_link_attributes', 'tersa_nav_link_external_rel', 10, 4);
?>
<nav
	class="site-header__nav site-header__nav--desktop"
	aria-label="<?php esc_attr_e('Primary navigation', 'tersa-shop'); ?>"
	data-submenu-label="<?php echo esc_attr(__('Open submenu for %s', 'tersa-shop')); ?>"
>
	<?php
	wp_nav_menu(tersa_get_primary_nav_menu_args());
	?>
</nav>
<?php
remove_filter('nav_menu_link_attributes', 'tersa_nav_link_external_rel', 10, 4);
remove_filter('nav_menu_item_id', '__return_empty_string');
