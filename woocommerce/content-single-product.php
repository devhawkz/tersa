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
		'label'   => function_exists('pll__') ? pll__('On sale') : __('On sale', 'tersa-shop'),
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
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('product-single', $product); ?>>
	<div class="product-single__inner">
		<div class="product-single__gallery-column">
			<div class="product-single__gallery">
				<div class="product-single__main-media">
					<?php if (!empty($badge_items)) : ?>
						<div class="product-single__badges" aria-label="<?php echo esc_attr__('Product badges', 'tersa-shop'); ?>">
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
							aria-label="<?php echo esc_attr__('Open product image', 'tersa-shop'); ?>"
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
									'alt'      => esc_attr($product_name),
								]
							);
							?>
						</a>

						<button
							class="product-single__zoom-button"
							type="button"
							aria-label="<?php echo esc_attr__('Open image gallery', 'tersa-shop'); ?>"
							data-image-target="<?php echo esc_attr((string) $main_image_id); ?>"
						>
							<span aria-hidden="true">⌕</span>
						</button>
					<?php endif; ?>
				</div>

				<?php if (count($attachment_ids) > 1) : ?>
					<div class="product-single__thumbs" role="list" aria-label="<?php echo esc_attr__('Product gallery thumbnails', 'tersa-shop'); ?>">
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
								aria-label="<?php echo esc_attr(sprintf(__('Show image %d', 'tersa-shop'), $index + 1)); ?>"
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
				<nav class="product-single__breadcrumbs" aria-label="<?php echo esc_attr__('Breadcrumb', 'tersa-shop'); ?>">
					<?php
					woocommerce_breadcrumb([
						'delimiter'   => ' / ',
						'wrap_before' => '<ol class="product-single__breadcrumb-list">',
						'wrap_after'  => '</ol>',
						'before'      => '<li class="product-single__breadcrumb-item">',
						'after'       => '</li>',
						'home'        => esc_html__('Naslovna', 'tersa-shop'),
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
							<?php echo esc_html($is_in_stock ? __('Na stanju', 'tersa-shop') : __('Trenutačno nedostupno', 'tersa-shop')); ?>
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
							$tab_title     = function_exists('pll__') ? pll__($tab_title_raw) : $tab_title_raw;
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
									hidden
								>
									<div class="product-single__accordion-content">
										<?php
										if (isset($tab['callback']) && is_callable($tab['callback'])) {
											call_user_func($tab['callback'], $tab_key, $tab);
										}
										?>
									</div>
								</div>
							</section>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>