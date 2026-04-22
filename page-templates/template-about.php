<?php
/**
 * Template Name: About Page
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="main-content" class="site-main about-page">
	<?php
	while (have_posts()) :
		the_post();

		get_template_part('template-parts/global/breadcrumbs');
		get_template_part('template-parts/pages/about/hero');
		get_template_part('template-parts/pages/about/how-we-work');
		get_template_part('template-parts/pages/about/cta-banner');

	endwhile;
	?>
</main>

<?php
get_sidebar();
get_footer();