<?php
defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main tersa-404" role="main">
	<section class="tersa-404__section page-spacing" aria-labelledby="tersa-404-title">
		<div class="container">
			<header class="tersa-404__header">
				<?php if (function_exists('woocommerce_breadcrumb')) : ?>
					<?php
					woocommerce_breadcrumb([
						'delimiter'   => '<span class="tersa-breadcrumbs__sep" aria-hidden="true">/</span>',
						'wrap_before' => '<nav class="woocommerce-breadcrumb tersa-breadcrumbs" aria-label="' . esc_attr__('Putanja stranice', 'tersa-shop') . '">',
						'wrap_after'  => '</nav>',
						'before'      => '<span class="tersa-breadcrumbs__item">',
						'after'       => '</span>',
						'home'        => esc_html__('Početna', 'tersa-shop'),
					]);
					?>
				<?php endif; ?>
			</header>

			<div class="tersa-404__content">
				<p class="tersa-404__eyebrow">
					<?php echo esc_html__('Greška 404', 'tersa-shop'); ?>
				</p>

				<h1 id="tersa-404-title" class="tersa-404__title">
					<?php echo esc_html__('Stranica nije pronađena', 'tersa-shop'); ?>
				</h1>

				<p class="tersa-404__text">
					<?php echo esc_html__('Nažalost, stranica koju tražite ne postoji, premještena je ili je privremeno nedostupna.', 'tersa-shop'); ?>
				</p>

				<div class="tersa-404__actions" aria-label="<?php echo esc_attr__('Mogućnosti navigacije', 'tersa-shop'); ?>">
					<a class="tersa-button tersa-button--primary" href="<?php echo esc_url(home_url('/')); ?>">
						<?php echo esc_html__('Povratak na početnu', 'tersa-shop'); ?>
					</a>

					<?php if (function_exists('wc_get_page_permalink')) : ?>
						<a class="tersa-button tersa-button--secondary" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
							<?php echo esc_html__('Idi u trgovinu', 'tersa-shop'); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php
				$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
				?>
			</div>
		</div>
	</section>
</main>

<?php
get_sidebar();
get_footer();