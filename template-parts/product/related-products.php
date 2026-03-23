<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WooCommerce')) {
	return;
}

$product_id = isset($args['product_id']) ? (int) $args['product_id'] : 0;

if ($product_id <= 0) {
	return;
}

$transient_key = 'tersa_related_' . $product_id;
$related_ids   = get_transient($transient_key);

if (false === $related_ids) {
	$related_ids = wc_get_related_products($product_id, 4);
	set_transient($transient_key, $related_ids ?: [], 12 * HOUR_IN_SECONDS);
}

if (empty($related_ids)) {
	return;
}

// Batch-warm term cache da bi se izbegli individual DB upiti po proizvodu.
update_object_term_cache($related_ids, 'product');

$badge_color = '#000000';
?>

<section class="product-related" aria-labelledby="product-related-title">
	<div class="product-related__inner">
		<h2 id="product-related-title" class="product-related__title">
			<?php echo esc_html(function_exists('pll__') ? pll__('Slični proizvodi') : __('Slični proizvodi', 'tersa-shop')); ?>
		</h2>

		<ul class="home-bestsellers__grid" role="list">
			<?php foreach ($related_ids as $related_id) :
				$rel_product = wc_get_product($related_id);

				if (!$rel_product instanceof WC_Product) {
					continue;
				}

				$rel_id             = $rel_product->get_id();
				$rel_url            = get_permalink($rel_id);
				$rel_name           = $rel_product->get_name();
				$rel_main_image_id  = $rel_product->get_image_id();
				$rel_gallery_ids    = $rel_product->get_gallery_image_ids();
				$rel_hover_image_id = !empty($rel_gallery_ids) ? (int) $rel_gallery_ids[0] : 0;

				$rel_current_price = $rel_product->get_price();
				$rel_regular_price = $rel_product->get_regular_price();

				$rel_current_price_html = $rel_current_price !== ''
					? wc_price(wc_get_price_to_display($rel_product, ['price' => (float) $rel_current_price]))
					: '';

				$rel_regular_price_html = ($rel_product->is_on_sale() && $rel_regular_price !== '')
					? wc_price(wc_get_price_to_display($rel_product, ['price' => (float) $rel_regular_price]))
					: '';

				$rel_children_ids = method_exists($rel_product, 'get_children') ? $rel_product->get_children() : [];
				$rel_has_variants = $rel_product->is_type('variable') && !empty($rel_children_ids);

				$rel_button_classes = [
					'button',
					'product_type_' . $rel_product->get_type(),
					'home-bestsellers__cart-button',
				];

				if (
					!$rel_has_variants
					&& $rel_product->supports('ajax_add_to_cart')
					&& $rel_product->is_purchasable()
					&& $rel_product->is_in_stock()
				) {
					$rel_button_classes[] = 'add_to_cart_button';
					$rel_button_classes[] = 'ajax_add_to_cart';
				}

				$rel_label_options = function_exists('pll__') ? pll__('Vidi opcije') : 'Vidi opcije';

				if ($rel_has_variants) {
					$rel_button_attrs = [
						'href'       => $rel_url,
						'class'      => implode(' ', array_map('sanitize_html_class', $rel_button_classes)),
						'aria-label' => esc_attr(sprintf('%s: %s', $rel_label_options, $rel_name)),
						'rel'        => 'nofollow',
					];
				} else {
					$rel_button_attrs = [
						'href'             => $rel_product->add_to_cart_url(),
						'data-quantity'    => '1',
						'class'            => implode(' ', array_map('sanitize_html_class', $rel_button_classes)),
						'data-product_id'  => (string) $rel_id,
						'data-product_sku' => $rel_product->get_sku(),
						'aria-label'       => wp_strip_all_tags($rel_product->add_to_cart_description()),
						'rel'              => 'nofollow',
					];
				}

				$rel_tag_names = wp_get_post_terms(
					$rel_id,
					'product_tag',
					[
						'fields'  => 'names',
						'orderby' => 'count',
						'order'   => 'DESC',
					]
				);
				$rel_tag_names = is_array($rel_tag_names) ? array_slice(array_values($rel_tag_names), 0, 2) : [];
		?>
		<li>
		<article class="home-bestsellers__card">
				<div class="home-bestsellers__media-wrap">
					<a class="home-bestsellers__media-link" href="<?php echo esc_url($rel_url); ?>">
						<div class="home-bestsellers__media">
							<?php if (!empty($rel_tag_names)) : ?>
								<div class="home-bestsellers__tags" aria-label="<?php echo esc_attr__('Product tags', 'tersa-shop'); ?>">
									<?php foreach ($rel_tag_names as $tag_name) : ?>
										<span
											class="home-bestsellers__badge"
											style="--bestseller-badge-color: <?php echo esc_attr($badge_color); ?>;"
										>
											<?php echo esc_html($tag_name); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ($rel_main_image_id) :
								echo wp_get_attachment_image(
									$rel_main_image_id,
									'large',
									false,
									[
										'class'    => 'home-bestsellers__image home-bestsellers__image--primary',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'alt'      => esc_attr($rel_name),
									]
								);
							endif; ?>

							<?php if ($rel_hover_image_id) :
								echo wp_get_attachment_image(
									$rel_hover_image_id,
									'large',
									false,
									[
										'class'    => 'home-bestsellers__image home-bestsellers__image--hover',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'alt'      => '',
									]
								);
							endif; ?>
						</div>
					</a>

					<?php if (function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist')) : ?>
						<div class="home-bestsellers__wishlist">
							<?php
							echo do_shortcode(
								sprintf(
									'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="home-bestsellers__wishlist-link"]',
									$rel_id
								)
							);
							?>
						</div>
					<?php endif; ?>

					<div class="home-bestsellers__cart-wrap">
						<?php
						if ($rel_has_variants) {
							echo sprintf(
								'<a %s>%s</a>',
								wc_implode_html_attributes($rel_button_attrs),
								esc_html($rel_label_options)
							);
						} else {
							echo apply_filters(
								'woocommerce_loop_add_to_cart_link',
								sprintf(
									'<a %s>%s</a>',
									wc_implode_html_attributes($rel_button_attrs),
									esc_html($rel_product->add_to_cart_text())
								),
								$rel_product,
								[
									'class'      => implode(' ', $rel_button_classes),
									'attributes' => $rel_button_attrs,
								]
							);
						}
						?>
					</div>
				</div>

				<div class="home-bestsellers__content">
					<h3 class="home-bestsellers__product-title">
						<a href="<?php echo esc_url($rel_url); ?>">
							<?php echo esc_html($rel_name); ?>
						</a>
					</h3>

					<div class="home-bestsellers__price">
						<?php if ($rel_current_price_html) : ?>
							<span class="home-bestsellers__price-current">
								<?php echo wp_kses_post($rel_current_price_html); ?>
							</span>
						<?php endif; ?>

						<?php if ($rel_regular_price_html) : ?>
							<span class="home-bestsellers__price-regular">
								<?php echo wp_kses_post($rel_regular_price_html); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
		</article>
		</li>
		<?php endforeach; ?>
		</ul>
	</div>
</section>
