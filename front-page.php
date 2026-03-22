<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<?php echo '<!-- FRONT PAGE LOADED -->'; ?>

<main id="content" class="site-main" role="main">
	<?php get_template_part('template-parts/home/hero-slider'); ?>
	<?php get_template_part('template-parts/home/shop-by-category'); ?>
	<?php get_template_part('template-parts/home/promo-banners'); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, ['instance' => 1]); ?>
	<?php get_template_part('template-parts/home/promo-countdown'); ?>
	<?php get_template_part('template-parts/home/bestsellers', null, ['instance' => 2]); ?>
</main>

<?php
get_sidebar();
get_footer();