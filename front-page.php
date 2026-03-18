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
	<?php get_template_part('template-parts/home/bestsellers'); ?>
</main>

<?php
get_footer();