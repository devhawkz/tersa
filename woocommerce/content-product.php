<?php
defined('ABSPATH') || exit;

global $product;

if (!$product instanceof WC_Product || !$product->is_visible()) {
	return;
}

$product_id        = $product->get_id();
$product_url       = get_permalink($product_id);
$product_name      = $product->get_name();
$main_image_id     = $product->get_image_id();
$gallery_image_ids = $product->get_gallery_image_ids();
$hover_image_id    = !empty($gallery_image_ids) ? (int) $gallery_image_ids[0] : 0;

$current_price = $product->get_price();
$regular_price = $product->get_regular_price();

$current_price_html = $current_price !== ''
	? wc_price(wc_get_price_to_display($product, ['price' => (float) $current_price]))
	: '';

$regular_price_html = ($product->is_on_sale() && $regular_price !== '')
	? wc_price(wc_get_price_to_display($product, ['price' => (float) $regular_price]))
	: '';

$children_ids = method_exists($product, 'get_children') ? $product->get_children() : [];

$has_multiple_variants = $product->is_type('variable') && is_array($children_ids) && count($children_ids) > 0;

$button_classes = [
	'button',
	'product_type_' . $product->get_type(),
	'shop-card__button',
];

if (
	!$has_multiple_variants
	&& $product->supports('ajax_add_to_cart')
	&& $product->is_purchasable()
	&& $product->is_in_stock()
) {
	$button_classes[] = 'add_to_cart_button';
	$button_classes[] = 'ajax_add_to_cart';
}

$button_label_options = function_exists('pll__')
	? pll__('Vidi opcije')
	: __('Vidi opcije', 'tersa-shop');

$button_label_add_to_cart = function_exists('pll__')
	? pll__('Dodaj u košaricu')
	: __('Dodaj u košaricu', 'tersa-shop');

if ($has_multiple_variants) {
	$button_attributes = [
		'href'       => $product_url,
		'class'      => implode(' ', array_map('sanitize_html_class', $button_classes)),
		'aria-label' => esc_attr(sprintf('%s: %s', $button_label_options, $product_name)),
		'rel'        => 'nofollow',
	];
} else {
	$button_attributes = [
		'href'             => $product->add_to_cart_url(),
		'data-quantity'    => '1',
		'class'            => implode(' ', array_map('sanitize_html_class', $button_classes)),
		'data-product_id'  => (string) $product_id,
		'data-product_sku' => $product->get_sku(),
		'aria-label'       => wp_strip_all_tags($product->add_to_cart_description()),
		'rel'              => 'nofollow',
	];
}

/**
 * Badge logika:
 * - tagovi tipa "najprodavanije", "novo"
 * - ako je proizvod na sniženju, dodaje se "On sale"
 */
$badges = [];

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
			$badges[] = [
				'label'   => $tag_name,
				'primary' => true,
			];
		}

		if (in_array($normalized, ['novo', 'new'], true)) {
			$badges[] = [
				'label'   => $tag_name,
				'primary' => false,
			];
		}
	}
}

if ($product->is_on_sale()) {
	$badges[] = [
		'label'   => function_exists('pll__') ? pll__('Na sniženju') : __('Na sniženju', 'tersa-shop'),
		'primary' => false,
	];
}

$badges = array_slice($badges, 0, 2);

$short_description = wp_kses_post(
	apply_filters('woocommerce_short_description', $product->get_short_description())
);
?>

<li <?php wc_product_class('shop-card', $product); ?>>
	<article class="shop-card__inner">
		<div class="shop-card__media-wrap">
			<a class="shop-card__media-link" href="<?php echo esc_url($product_url); ?>">
				<div class="shop-card__media">
					<?php if (!empty($badges)) : ?>
						<div class="shop-card__tags" aria-label="<?php echo esc_attr__('Product badges', 'tersa-shop'); ?>">
							<?php foreach ($badges as $badge) : ?>
								<span class="shop-card__badge<?php echo !empty($badge['primary']) ? ' shop-card__badge--primary' : ''; ?>">
									<?php echo esc_html($badge['label']); ?>
								</span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ($main_image_id) : ?>
						<?php
						echo wp_get_attachment_image(
							$main_image_id,
							'tersa-card',
							false,
							[
								'class'    => 'shop-card__image shop-card__image--primary',
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
							'tersa-card',
							false,
							[
								'class'    => 'shop-card__image shop-card__image--hover',
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
				<div class="shop-card__wishlist">
					<?php
					echo do_shortcode(
						sprintf(
							'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="shop-card__wishlist-link"]',
							$product_id
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="shop-card__cta-wrap">
				<?php
				if ($has_multiple_variants) {
					echo sprintf(
						'<a %s>%s</a>',
						wc_implode_html_attributes($button_attributes),
						esc_html($button_label_options)
					);
				} else {
					echo apply_filters(
						'woocommerce_loop_add_to_cart_link',
						sprintf(
							'<a %s>%s</a>',
							wc_implode_html_attributes($button_attributes),
							esc_html($button_label_add_to_cart)
						),
						$product,
						[
							'class'      => implode(' ', $button_classes),
							'attributes' => $button_attributes,
						]
					);
				}
				?>
			</div>
		</div>

		<div class="shop-card__content">
			<h2 class="shop-card__title">
				<a href="<?php echo esc_url($product_url); ?>">
					<?php echo esc_html($product_name); ?>
				</a>
			</h2>

			<div class="shop-card__price">
				<?php if ($current_price_html) : ?>
					<span class="shop-card__price-current">
						<?php echo wp_kses_post($current_price_html); ?>
					</span>
				<?php endif; ?>

				<?php if ($regular_price_html) : ?>
					<span class="shop-card__price-regular">
						<?php echo wp_kses_post($regular_price_html); ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if (!empty($short_description)) : ?>
				<div class="shop-card__excerpt">
					<?php echo $short_description; ?>
				</div>
			<?php endif; ?>

			<div class="shop-card__list-cta">
				<?php
				if ($has_multiple_variants) {
					echo sprintf(
						'<a %s>%s</a>',
						wc_implode_html_attributes($button_attributes),
						esc_html($button_label_options)
					);
				} else {
					echo apply_filters(
						'woocommerce_loop_add_to_cart_link',
						sprintf(
							'<a %s>%s</a>',
							wc_implode_html_attributes($button_attributes),
							esc_html($button_label_add_to_cart)
						),
						$product,
						[
							'class'      => implode(' ', $button_classes),
							'attributes' => $button_attributes,
						]
					);
				}
				?>
			</div>
		</div>
	</article>
</li>