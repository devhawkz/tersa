<?php
/**
 * Template Name: Contact Page
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main contact-page">
	<?php
	while (have_posts()) :
		the_post();

		get_template_part('template-parts/global/breadcrumbs');
		get_template_part('template-parts/pages/contact/hero');
		get_template_part('template-parts/pages/contact/content');
		get_template_part('template-parts/pages/contact/map');

	endwhile;
	?>
</main>

<?php
get_footer();