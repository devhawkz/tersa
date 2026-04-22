<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="main-content" class="site-main" role="main">
	<div class="container">
		<h1 class="screen-reader-text"><?php echo esc_html(get_bloginfo('name')); ?></h1>
		<p><?php esc_html_e('Content coming soon.', 'tersa-shop'); ?></p>
	</div>
</main>

<?php
get_sidebar();
get_footer();
