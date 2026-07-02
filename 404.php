<?php
defined('ABSPATH') || exit;

get_header();

$home_url = tersa_get_current_language_home_url();
$shop_url = (class_exists('WooCommerce') && function_exists('tersa_get_wc_page_url')) ? tersa_get_wc_page_url('shop') : '';
$tersa_404_t = static function (string $string): string {
	if (function_exists('tersa_translate_ui_string')) {
		return tersa_translate_ui_string($string);
	}

	if (function_exists('pll__')) {
		return (string) pll__($string);
	}

	return (string) __($string, 'tersa-shop');
};
?>

<main id="main-content" class="site-main tersa-404" role="main">
	<section class="tersa-404__section page-spacing" aria-labelledby="tersa-404-title">
		<div class="container">
			<header class="tersa-404__header">
				<?php if (function_exists('woocommerce_breadcrumb')) : ?>
					<?php
					woocommerce_breadcrumb([
						'delimiter'   => '<span class="tersa-breadcrumbs__sep" aria-hidden="true">/</span>',
						'wrap_before' => '<nav class="woocommerce-breadcrumb tersa-breadcrumbs" aria-label="' . esc_attr(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Putanja stranice') : __('Putanja stranice', 'tersa-shop')) . '">',
						'wrap_after'  => '</nav>',
						'before'      => '<span class="tersa-breadcrumbs__item">',
						'after'       => '</span>',
						'home'        => esc_html(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Naslovnica') : __('Naslovnica', 'tersa-shop')),
					]);
					?>
				<?php endif; ?>
			</header>

			<div class="tersa-404__content">
				<p class="tersa-404__eyebrow">
					<?php echo esc_html($tersa_404_t('Greška 404')); ?>
				</p>

				<h1 id="tersa-404-title" class="tersa-404__title">
					<?php echo esc_html($tersa_404_t('Stranica nije pronađena')); ?>
				</h1>

				<p class="tersa-404__text">
					<?php echo esc_html($tersa_404_t('Nažalost, stranica koju tražite ne postoji, premještena je ili je privremeno nedostupna.')); ?>
				</p>

				<div class="tersa-404__actions" aria-label="<?php echo esc_attr($tersa_404_t('Mogućnosti navigacije')); ?>">
					<a class="tersa-button tersa-button--primary" href="<?php echo esc_url($home_url); ?>">
						<?php echo esc_html($tersa_404_t('Povratak na početnu')); ?>
					</a>

					<?php if ($shop_url !== '') : ?>
						<a class="tersa-button tersa-button--secondary" href="<?php echo esc_url($shop_url); ?>">
							<?php echo esc_html($tersa_404_t('Idi u trgovinu')); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_sidebar();
get_footer();
