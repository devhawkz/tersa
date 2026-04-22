<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

// Jedan get_fields() poziv za cijelu naslovnicu — rezultat se prosljeđuje
// svim sekcijama kroz $args kako bi svaka izbjegla vlastiti DB upit.
$_tersa_fp_id     = function_exists('get_queried_object_id') ? get_queried_object_id() : 0;
$_tersa_fp_fields = ($_tersa_fp_id && function_exists('get_fields'))
	? (get_fields($_tersa_fp_id) ?: [])
	: [];

$_tersa_home_args = [
	'page_id' => $_tersa_fp_id,
	'fields'  => $_tersa_fp_fields,
];
?>

<main id="main-content" class="site-main" role="main">
	<?php get_template_part('template-parts/home/hero-slider', null, $_tersa_home_args); ?>
	<?php get_template_part('template-parts/home/shop-by-category', null, $_tersa_home_args); ?>
	<?php get_template_part('template-parts/home/promo-banners', null, $_tersa_home_args); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, array_merge($_tersa_home_args, ['instance' => 1])); ?>
	<?php get_template_part('template-parts/home/promo-countdown', null, $_tersa_home_args); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, array_merge($_tersa_home_args, ['instance' => 2])); ?>
</main>

<?php
$show_front_page_sidebar = (bool) get_theme_mod('tersa_show_front_page_sidebar', false);
$show_front_page_sidebar = (bool) apply_filters('tersa_show_front_page_sidebar', $show_front_page_sidebar);

if ($show_front_page_sidebar && is_active_sidebar('primary-sidebar')) {
	get_sidebar();
}
get_footer();