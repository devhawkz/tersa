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

// Jedan SQL upit donosi terme za sve related proizvode odjednom (isti pattern kao bestsellers.php).
$rel_tags_by_id = [];
$rel_terms_raw  = wp_get_object_terms(
	$related_ids,
	'product_tag',
	['fields' => 'all_with_object_id']
);

if (!is_wp_error($rel_terms_raw) && is_array($rel_terms_raw)) {
	foreach ($rel_terms_raw as $term) {
		if (!isset($term->object_id)) {
			continue;
		}
		$rel_tags_by_id[(int) $term->object_id][] = $term;
	}

	foreach ($rel_tags_by_id as $pid => $tag_terms) {
		usort($tag_terms, static function ($a, $b) {
			return (int) $b->count <=> (int) $a->count;
		});
		$rel_tags_by_id[$pid] = array_map(
			static function ($t) { return (string) $t->name; },
			array_slice($tag_terms, 0, 2)
		);
	}
}

$has_yith_related  = function_exists('shortcode_exists') && shortcode_exists('yith_wcwl_add_to_wishlist');
$rel_label_options = function_exists('pll__') ? pll__('Vidi opcije') : 'Vidi opcije';

$badge_color = '#000000';

// Jedan WP upit za sve related proizvode umjesto N pojedinačnih wc_get_product() poziva.
$rel_products_batch = wc_get_products([
	'include' => $related_ids,
	'orderby' => 'post__in',
	'order'   => 'ASC',
	'limit'   => count($related_ids),
	'status'  => 'publish',
]);

$rel_products_map = [];
foreach ($rel_products_batch as $_p) {
	$rel_products_map[$_p->get_id()] = $_p;
}
unset($rel_products_batch, $_p);
?>

<section class="product-related" aria-labelledby="product-related-title">
	<div class="product-related__inner">
		<h2 id="product-related-title" class="product-related__title">
			<?php echo esc_html(function_exists('pll__') ? pll__('Slični proizvodi') : __('Slični proizvodi', 'tersa-shop')); ?>
		</h2>

		<ul class="home-bestsellers__grid" role="list">
			<?php foreach ($related_ids as $related_id) :
				$rel_product = $rel_products_map[$related_id] ?? null;

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

			$rel_tag_names = $rel_tags_by_id[$rel_id] ?? [];
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

					<?php if ($has_yith_related) : ?>
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
