<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<style>
	.site-main {
		padding: 50px 0;
	}
</style>
<main id="content" class="site-main" role="main">
	<div class="container">
		<?php
		while (have_posts()) {
			the_post();
			the_content();
		}
		?>
	</div>
</main>

<?php
get_footer();
