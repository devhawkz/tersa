<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="content" class="site-main" role="main">
	<?php get_template_part('template-parts/home/hero-slider'); ?>
	<?php get_template_part('template-parts/home/shop-by-category'); ?>
	<?php get_template_part('template-parts/home/promo-banners'); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, ['instance' => 1]); ?>
	<?php get_template_part('template-parts/home/promo-countdown'); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, ['instance' => 2]); ?>
</main>

<?php
$show_front_page_sidebar = (bool) get_theme_mod('tersa_show_front_page_sidebar', false);
$show_front_page_sidebar = (bool) apply_filters('tersa_show_front_page_sidebar', $show_front_page_sidebar);

if ($show_front_page_sidebar && is_active_sidebar('primary-sidebar')) {
	get_sidebar();
}
get_footer();