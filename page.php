<?php
defined('ABSPATH') || exit;

get_header();
?>



<main id="primary" class="site-main tersa-page" role="main">
	<section class="tersa-page__section page-spacing" aria-labelledby="tersa-page-title">
		<div class="container">
			<?php while (have_posts()) : the_post(); ?>
				<header class="tersa-page__header">
					<?php if (function_exists('woocommerce_breadcrumb')) : ?>
						<?php
						woocommerce_breadcrumb([
							'delimiter'   => '<span class="tersa-breadcrumbs__sep" aria-hidden="true">/</span>',
							'wrap_before' => '<nav class="woocommerce-breadcrumb tersa-breadcrumbs" aria-label="' . esc_attr__('Putanja stranice', 'tersa-shop') . '">',
							'wrap_after'  => '</nav>',
							'before'      => '<span class="tersa-breadcrumbs__item">',
							'after'       => '</span>',
							'home'        => esc_html__('Naslovnica ', 'tersa-shop'),
						]);
						?>
					<?php endif; ?>

					<h1 id="tersa-page-title" class="tersa-page__title">
						<?php the_title(); ?>
					</h1>
				</header>

				<article
					id="post-<?php the_ID(); ?>"
					<?php post_class('tersa-page__article'); ?>
				>
					<div class="tersa-page__content entry-content">
						<?php
						the_content();

						wp_link_pages([
							'before'      => '<nav class="page-links" aria-label="' . esc_attr__('Navigacija unutar stranice', 'tersa-shop') . '">',
							'after'       => '</nav>',
							'pagelink'    => '<span class="screen-reader-text">' . esc_html__('Stranica', 'tersa-shop') . ' </span>%',
							'separator'   => '',
						]);
						?>
					</div>
				</article>

				<?php
				if (comments_open() || get_comments_number()) {
					comments_template();
				}
				?>
			<?php endwhile; ?>
		</div>
	</section>
</main>

<?php
get_sidebar();
get_footer();