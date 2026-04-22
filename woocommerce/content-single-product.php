<?php
defined('ABSPATH') || exit;

global $product;

if (!$product instanceof WC_Product) {
	return;
}

$product_id        = $product->get_id();
$product_name      = $product->get_name();
$main_image_id     = $product->get_image_id();
$gallery_image_ids = $product->get_gallery_image_ids();
$attachment_ids    = array_values(array_unique(array_filter(array_merge([$main_image_id], $gallery_image_ids))));

$current_price_html = $product->get_price_html();
$average_rating     = (float) $product->get_average_rating();
$review_count       = (int) $product->get_review_count();
$is_in_stock        = $product->is_in_stock();
$short_description  = apply_filters('woocommerce_short_description', $product->get_short_description());
$sku                = $product->get_sku();
$categories         = wc_get_product_category_list($product_id, ', ');
$tabs               = apply_filters('woocommerce_product_tabs', []);
$badge_items        = [];

$product_tag_names = wp_get_post_terms(
	$product_id,
	'product_tag',
	[
		'fields' => 'names',
	]
);

if (is_array($product_tag_names) && !empty($product_tag_names)) {
	foreach ($product_tag_names as $tag_name) {
		$normalized = sanitize_title($tag_name);

		if (in_array($normalized, ['najprodavanije', 'best-seller', 'bestseller', 'hot'], true)) {
			$badge_items[] = [
				'label'   => $tag_name,
				'primary' => true,
			];
		}

		if (in_array($normalized, ['novo', 'new'], true)) {
			$badge_items[] = [
				'label'   => $tag_name,
				'primary' => false,
			];
		}
	}
}

if ($product->is_on_sale()) {
	$badge_items[] = [
		'label'   => function_exists('pll__') ? pll__('Na sniženju') : __('Na sniženju', 'tersa-shop'),
		'primary' => false,
	];
}

$badge_items = array_slice($badge_items, 0, 2);

$brand_output = '';
if (taxonomy_exists('product_brand')) {
	$brand_terms = get_the_terms($product_id, 'product_brand');
	if (!is_wp_error($brand_terms) && !empty($brand_terms)) {
		$brand_names  = wp_list_pluck($brand_terms, 'name');
		$brand_output = implode(', ', $brand_names);
	}
}

$discount_percent = '';
if ($product->is_on_sale()) {
	$regular_price = (float) $product->get_regular_price();
	$sale_price    = (float) $product->get_sale_price();

	if ($regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price) {
		$discount_percent = '-' . round((($regular_price - $sale_price) / $regular_price) * 100) . '%';
	}
}

$tersa_variation_gallery_reset_id = 0;
$tersa_variation_gallery_reset_alt = $product_name;
if ($product->is_type('variable') && !empty($attachment_ids)) {
	$tersa_variation_gallery_reset_id = (int) $attachment_ids[0];
	if ($tersa_variation_gallery_reset_id > 0) {
		$alt_meta = trim((string) get_post_meta($tersa_variation_gallery_reset_id, '_wp_attachment_image_alt', true));
		if ($alt_meta !== '') {
			$tersa_variation_gallery_reset_alt = $alt_meta;
		}
	}
}
?>

<?php
// Suppress WC's default notices output here — the cart drawer handles visual feedback.
// Plugin callbacks on this hook still fire normally.
remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
do_action('woocommerce_before_single_product');
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('product-single', $product); ?>>
	<?php
	// Prevent WC's default image output — custom gallery is handled above.
	remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
	do_action('woocommerce_before_single_product_summary');
	?>
	<div class="product-single__inner">
		<div class="product-single__gallery-column">
			<div class="product-single__gallery">
				<div
					class="product-single__main-media"
					<?php if ($tersa_variation_gallery_reset_id > 0) : ?>
						data-tersa-variation-gallery="1"
						data-tersa-default-large="<?php echo esc_url(wp_get_attachment_image_url($tersa_variation_gallery_reset_id, 'large')); ?>"
						data-tersa-default-full="<?php echo esc_url(wp_get_attachment_image_url($tersa_variation_gallery_reset_id, 'full')); ?>"
						data-tersa-default-srcset="<?php echo esc_attr((string) wp_get_attachment_image_srcset($tersa_variation_gallery_reset_id, 'large')); ?>"
						data-tersa-default-sizes="<?php echo esc_attr((string) wp_get_attachment_image_sizes($tersa_variation_gallery_reset_id, 'large')); ?>"
						data-tersa-default-alt="<?php echo esc_attr($tersa_variation_gallery_reset_alt); ?>"
					<?php endif; ?>
				>
					<?php if (!empty($badge_items)) : ?>
						<div class="product-single__badges" aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Označke proizvoda') : __('Označke proizvoda', 'tersa-shop')); ?>">
							<?php foreach ($badge_items as $badge) : ?>
								<span class="product-single__badge<?php echo !empty($badge['primary']) ? ' product-single__badge--primary' : ''; ?>">
									<?php echo esc_html($badge['label']); ?>
								</span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ($main_image_id) : ?>
						<a
							class="product-single__main-image-link"
							href="<?php echo esc_url(wp_get_attachment_image_url($main_image_id, 'full')); ?>"
							aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Otvori sliku proizvoda') : __('Otvori sliku proizvoda', 'tersa-shop')); ?>"
						>
							<?php
							echo wp_get_attachment_image(
								$main_image_id,
								'large',
								false,
								[
									'class'    => 'product-single__main-image',
									'loading'  => 'eager',
									'decoding' => 'async',
								'alt'      => $product_name,
							]
						);
						?>
						</a>

						
					<?php endif; ?>
				</div>

				<?php if (count($attachment_ids) > 1) : ?>
					<div class="product-single__thumbs" role="list" aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Minijature u galeriji') : __('Minijature u galeriji', 'tersa-shop')); ?>">
						<?php foreach ($attachment_ids as $index => $attachment_id) : ?>
							<?php
							$thumb_url = wp_get_attachment_image_url($attachment_id, 'medium');
							$full_url  = wp_get_attachment_image_url($attachment_id, 'full');

							if (!$thumb_url || !$full_url) {
								continue;
							}
							?>
							<button
								class="product-single__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
								type="button"
								data-full-image="<?php echo esc_url($full_url); ?>"
								data-large-image="<?php echo esc_url(wp_get_attachment_image_url($attachment_id, 'large')); ?>"
								data-image-id="<?php echo esc_attr((string) $attachment_id); ?>"
								aria-label="<?php echo esc_attr(sprintf(function_exists('pll__') ? pll__('Prikaži sliku %d') : __('Prikaži sliku %d', 'tersa-shop'), $index + 1)); ?>"
								aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
							>
								<?php
								echo wp_get_attachment_image(
									$attachment_id,
									'thumbnail',
									false,
									[
										'class'    => 'product-single__thumb-image',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'alt'      => '',
									]
								);
								?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="product-single__summary-column">
			<div class="product-single__summary">
				<nav class="product-single__breadcrumbs" aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Navigacija') : __('Navigacija', 'tersa-shop')); ?>">
					<?php
					woocommerce_breadcrumb([
						'delimiter'   => ' / ',
						'wrap_before' => '<ol class="product-single__breadcrumb-list">',
						'wrap_after'  => '</ol>',
						'before'      => '<li class="product-single__breadcrumb-item">',
						'after'       => '</li>',
						'home'        => esc_html__('Naslovnica', 'tersa-shop'),
					]);
					?>
				</nav>

				<h1 class="product-single__title"><?php echo esc_html($product_name); ?></h1>

				<div class="product-single__meta-top">
					<div class="product-single__reviews">
						<?php echo wc_get_rating_html($average_rating, $review_count); ?>
						<?php if ($review_count > 0) : ?>
							<span class="product-single__review-count">(<?php echo esc_html((string) $review_count); ?>)</span>
						<?php endif; ?>
					</div>

					<div class="product-single__stock">
						
						<span class="product-single__stock-value<?php echo $is_in_stock ? ' is-in-stock' : ' is-out-of-stock'; ?>">
							<?php
					$_t_stock = function_exists('pll__') ? 'pll__' : function (string $s): string { return __($s, 'tersa-shop'); };
					echo esc_html($is_in_stock ? $_t_stock('Na stanju') : $_t_stock('Trenutačno nedostupno'));
					?>
						</span>
					</div>
				</div>

				<div class="product-single__price-row">
					<div class="product-single__price">
						<?php echo wp_kses_post($current_price_html); ?>
					</div>

					<?php if (!empty($discount_percent)) : ?>
						<div class="product-single__discount">
							<?php echo esc_html('('.$discount_percent.')'); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if (!empty($short_description)) : ?>
					<div class="product-single__excerpt">
						<?php echo wp_kses_post($short_description); ?>
					</div>
				<?php endif; ?>

				<div class="product-single__purchase">
					<?php do_action('woocommerce_before_add_to_cart_form'); ?>

					<?php if ($product->is_type('variable')) : ?>
						<?php woocommerce_variable_add_to_cart(); ?>
					<?php else : ?>
						<?php woocommerce_simple_add_to_cart(); ?>
					<?php endif; ?>

					<?php do_action('woocommerce_after_add_to_cart_form'); ?>
				</div>

				<div class="product-single__actions-row">
					<?php if (function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist')) : ?>
						<div class="product-single__wishlist">
							<?php
							echo do_shortcode(
								sprintf(
									'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="product-single__wishlist-link"]',
									$product_id
								)
							);
							?>
						</div>
					<?php endif; ?>

					
				</div>

				<div class="product-single__meta">
					<?php if (!empty($sku)) : ?>
						<div class="product-single__meta-row">
							<span class="product-single__meta-label"><?php echo esc_html__('Šifra proizvoda:', 'tersa-shop'); ?></span>
							<span class="product-single__meta-value"><?php echo esc_html($sku); ?></span>
						</div>
					<?php endif; ?>

					<?php if (!empty($brand_output)) : ?>
						<div class="product-single__meta-row">
							<span class="product-single__meta-label"><?php echo esc_html__('Brendovi:', 'tersa-shop'); ?></span>
							<span class="product-single__meta-value"><?php echo esc_html($brand_output); ?></span>
						</div>
					<?php elseif (!empty($categories)) : ?>
						<div class="product-single__meta-row">
							<span class="product-single__meta-label"><?php echo esc_html__('Kategorija:', 'tersa-shop'); ?></span>
							<span class="product-single__meta-value"><?php echo wp_kses_post($categories); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if (!empty($tabs)) : ?>
					<div class="product-single__accordions">
						<?php foreach ($tabs as $tab_key => $tab) : ?>
							<?php
							$panel_id  = 'product-accordion-' . sanitize_html_class($tab_key);
							$button_id = 'product-accordion-button-' . sanitize_html_class($tab_key);
							?>
							<?php
							$tab_title_raw = wp_strip_all_tags($tab['title']);
							if (function_exists('pll__')) {
								if ($tab_key === 'reviews' && $product instanceof WC_Product) {
									$tab_title = sprintf(
										pll__('Recenzije (%d)'),
										(int) $product->get_review_count()
									);
								} else {
									$tab_title = pll__($tab_title_raw);
								}
							} else {
								$tab_title = $tab_title_raw;
							}
							?>
							<section class="product-single__accordion">
								<button
									id="<?php echo esc_attr($button_id); ?>"
									class="product-single__accordion-toggle"
									type="button"
									aria-expanded="false"
									aria-controls="<?php echo esc_attr($panel_id); ?>"
								>
									<span><?php echo esc_html($tab_title); ?></span>
									<span class="product-single__accordion-icon" aria-hidden="true">+</span>
								</button>

								<div
									id="<?php echo esc_attr($panel_id); ?>"
									class="product-single__accordion-panel"
									aria-labelledby="<?php echo esc_attr($button_id); ?>"
									aria-hidden="true"
								>
									<div class="product-single__accordion-panel-inner">
										<div class="product-single__accordion-content">
											<?php
											if (isset($tab['callback']) && is_callable($tab['callback'])) {
												call_user_func($tab['callback'], $tab_key, $tab);
											}
											?>
										</div>
									</div>
								</div>
							</section>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php
	// Remove WC default tab output (custom accordions used instead) and default related products
	// (custom template-parts/product/related-products.php used instead).
	// Upsells remain at priority 15 and will render here.
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
	do_action('woocommerce_after_single_product_summary');
	?>
	</div>
</div>

<?php do_action('woocommerce_after_single_product'); ?>

<?php
get_template_part(
	'template-parts/product/related-products',
	null,
	[
		'product_id' => $product_id,
	]
);
?>