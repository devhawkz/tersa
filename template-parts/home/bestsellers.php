<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WooCommerce')) {
	return;
}

$page_id = get_queried_object_id();

$show_section = function_exists('get_field') ? (bool) get_field('show_home_bestsellers_section', $page_id) : false;

if (!$show_section) {
	return;
}

$section_title = function_exists('get_field') ? get_field('home_bestsellers_section_title', $page_id) : '';
$badge_color   = function_exists('get_field') ? get_field('home_bestsellers_badge_color', $page_id) : '';

$section_title = !empty($section_title) ? $section_title : 'Bestsellers';
$badge_color   = !empty($badge_color) ? $badge_color : '#000000';

$product_tag_slug = 'najprodavanije';
if (function_exists('get_field')) {
	// Ovaj field može da bude string/select u ACF-u: 'najprodavanije' ili 'novo'
	$acf_product_tag_slug = get_field('home_bestsellers_product_tag_slug', $page_id);
	if (in_array((string) $acf_product_tag_slug, ['najprodavanije', 'novo'], true)) {
		$product_tag_slug = (string) $acf_product_tag_slug;
	}
}

$query = new WP_Query([
	'post_type'              => 'product',
	'post_status'            => 'publish',
	'posts_per_page'         => 4,
	'no_found_rows'          => true,
	'ignore_sticky_posts'    => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => true,
	'tax_query'              => [
		[
			'taxonomy' => 'product_tag',
			'field'    => 'slug',
			'terms'    => $product_tag_slug,
		],
	],
]);

if (!$query->have_posts()) {
	wp_reset_postdata();
	return;
}
?>

<section class="home-bestsellers" aria-labelledby="home-bestsellers-title">
	<div class="home-bestsellers__inner">
		<h2 id="home-bestsellers-title" class="home-bestsellers__title">
			<?php echo esc_html($section_title); ?>
		</h2>

		<div class="home-bestsellers__grid">
			<?php
			while ($query->have_posts()) :
				$query->the_post();

				$product = wc_get_product(get_the_ID());

				if (!$product instanceof WC_Product) {
					continue;
				}

				$product_id         = $product->get_id();
				$product_url        = get_permalink($product_id);
				$product_name       = $product->get_name();
				$main_image_id      = $product->get_image_id();
				$gallery_image_ids  = $product->get_gallery_image_ids();
				$hover_image_id     = !empty($gallery_image_ids) ? (int) $gallery_image_ids[0] : 0;

				$current_price = $product->get_price();
				$regular_price = $product->get_regular_price();

				$current_price_html = $current_price !== '' ? wc_price(wc_get_price_to_display($product, ['price' => (float) $current_price])) : '';
				$regular_price_html = ($product->is_on_sale() && $regular_price !== '')
					? wc_price(wc_get_price_to_display($product, ['price' => (float) $regular_price]))
					: '';

				$button_classes = [
					'button',
					'product_type_' . $product->get_type(),
					'home-bestsellers__cart-button',
				];

				if ($product->supports('ajax_add_to_cart') && $product->is_purchasable() && $product->is_in_stock()) {
					$button_classes[] = 'add_to_cart_button';
					$button_classes[] = 'ajax_add_to_cart';
				}

				$button_attributes = [
					'href'             => $product->add_to_cart_url(),
					'data-quantity'    => '1',
					'class'            => implode(' ', array_map('sanitize_html_class', $button_classes)),
					'data-product_id'  => (string) $product_id,
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => wp_strip_all_tags($product->add_to_cart_description()),
					'rel'              => 'nofollow',
				];
				?>
				<article class="home-bestsellers__card">
					<div class="home-bestsellers__media-wrap">
						<a class="home-bestsellers__media-link" href="<?php echo esc_url($product_url); ?>">
							<div class="home-bestsellers__media">
								<?php
								// Prikaži do 2 taga iz WooCommerce proizvoda (umesto "sale badge").
								$tag_names = wp_get_post_terms(
									$product_id,
									'product_tag',
									[
										'fields' => 'names',
										'orderby' => 'count',
										'order' => 'DESC',
									]
								);
								$tag_names = is_array($tag_names) ? array_values($tag_names) : [];
								$tag_names = array_slice($tag_names, 0, 2);
								?>
								<?php if (!empty($tag_names)) : ?>
									<div class="home-bestsellers__tags" aria-label="<?php echo esc_attr__('Product tags', 'tersa-shop'); ?>">
										<?php foreach ($tag_names as $tag_name) : ?>
											<span
												class="home-bestsellers__badge"
												style="--bestseller-badge-color: <?php echo esc_attr($badge_color); ?>;"
											>
												<?php echo esc_html($tag_name); ?>
											</span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<?php if ($main_image_id) : ?>
									<?php
									echo wp_get_attachment_image(
										$main_image_id,
										'large',
										false,
										[
											'class'    => 'home-bestsellers__image home-bestsellers__image--primary',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'alt'      => esc_attr($product_name),
										]
									);
									?>
								<?php endif; ?>

								<?php if ($hover_image_id) : ?>
									<?php
									echo wp_get_attachment_image(
										$hover_image_id,
										'large',
										false,
										[
											'class'    => 'home-bestsellers__image home-bestsellers__image--hover',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'alt'      => '',
										]
									);
									?>
								<?php endif; ?>
							</div>
						</a>

						<?php if (function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist')) : ?>
							<div class="home-bestsellers__wishlist">
								<?php
								echo do_shortcode(
									sprintf(
										'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="home-bestsellers__wishlist-link"]',
										$product_id
									)
								);
								?>
							</div>
						<?php endif; ?>

						<div class="home-bestsellers__cart-wrap">
							<?php
							echo apply_filters(
								'woocommerce_loop_add_to_cart_link',
								sprintf(
									'<a %s>%s</a>',
									wc_implode_html_attributes($button_attributes),
									esc_html($product->add_to_cart_text())
								),
								$product,
								[
									'class'      => implode(' ', $button_classes),
									'attributes' => $button_attributes,
								]
							);
							?>
						</div>
					</div>

					<div class="home-bestsellers__content">
						<h3 class="home-bestsellers__product-title">
							<a href="<?php echo esc_url($product_url); ?>">
								<?php echo esc_html($product_name); ?>
							</a>
						</h3>

						<div class="home-bestsellers__price">
							<?php if ($current_price_html) : ?>
								<span class="home-bestsellers__price-current">
									<?php echo wp_kses_post($current_price_html); ?>
								</span>
							<?php endif; ?>

							<?php if ($regular_price_html) : ?>
								<span class="home-bestsellers__price-regular">
									<?php echo wp_kses_post($regular_price_html); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
</section>

<?php wp_reset_postdata(); ?>