<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('get_field')) {
	return;
}

$page_id = isset($args['page_id']) ? (int) $args['page_id'] : get_queried_object_id();

if (!$page_id) {
	return;
}

$fields = isset($args['fields']) && is_array($args['fields'])
	? $args['fields']
	: (get_fields($page_id) ?: []);

$show_slider = !empty($fields['show_home_hero_slider']);

if (!$show_slider) {
	return;
}

$slides_raw = [
	$fields['hero_slide_1'] ?? null,
	$fields['hero_slide_2'] ?? null,
	$fields['hero_slide_3'] ?? null,
];

$resolve_product_by_sku = static function (string $sku) {
	$sku = trim($sku);

	if (
		$sku === '' ||
		!function_exists('wc_get_product_id_by_sku') ||
		!function_exists('wc_get_product') ||
		!class_exists('WC_Product')
	) {
		return null;
	}

	$product_id = wc_get_product_id_by_sku($sku);

	if (!$product_id) {
		return null;
	}

	$product = wc_get_product($product_id);

	return $product instanceof WC_Product ? $product : null;
};

$get_product_price_parts = static function ($product): array {
	if (!$product instanceof WC_Product) {
		return [
			'current' => '',
			'old'     => '',
		];
	}

	if ($product->is_type('variable')) {
		return [
			'current' => $product->get_price_html(),
			'old'     => '',
		];
	}

	$current_price = $product->get_price();

	if ($current_price === '') {
		return [
			'current' => $product->get_price_html(),
			'old'     => '',
		];
	}

	$current_price_html = function_exists('wc_price') && function_exists('wc_get_price_to_display')
		? wc_price(wc_get_price_to_display($product, ['price' => (float) $current_price]))
		: $product->get_price_html();

	$old_price_html = '';
	$regular_price  = $product->get_regular_price();

	if (
		$product->is_on_sale() &&
		$regular_price !== '' &&
		(float) $regular_price > (float) $current_price
	) {
		$old_price_html = function_exists('wc_price') && function_exists('wc_get_price_to_display')
			? wc_price(wc_get_price_to_display($product, ['price' => (float) $regular_price]))
			: '';
	}

	return [
		'current' => $current_price_html,
		'old'     => $old_price_html,
	];
};

$get_product_short_description = static function ($product): string {
	if (!$product instanceof WC_Product) {
		return '';
	}

	$description = '';

	if ($product->is_type('variation') && method_exists($product, 'get_description')) {
		$description = trim((string) $product->get_description());
	}

	if ($description === '' && method_exists($product, 'get_short_description')) {
		$description = trim((string) $product->get_short_description());
	}

	if (
		$description === '' &&
		$product->is_type('variation') &&
		method_exists($product, 'get_parent_id') &&
		function_exists('wc_get_product')
	) {
		$parent = wc_get_product((int) $product->get_parent_id());

		if ($parent instanceof WC_Product && method_exists($parent, 'get_short_description')) {
			$description = trim((string) $parent->get_short_description());
		}
	}

	return $description !== ''
		? (string) apply_filters('woocommerce_short_description', $description)
		: '';
};

$get_product_permalink = static function ($product): string {
	if (!$product instanceof WC_Product) {
		return '';
	}

	if (method_exists($product, 'get_permalink')) {
		return (string) $product->get_permalink();
	}

	$product_id = (int) $product->get_id();

	return $product_id ? (string) get_permalink($product_id) : '';
};

$get_product_parent_image_id = static function ($product): int {
	if (!$product instanceof WC_Product) {
		return 0;
	}

	if (
		$product->is_type('variation') &&
		method_exists($product, 'get_parent_id') &&
		function_exists('wc_get_product')
	) {
		$parent = wc_get_product((int) $product->get_parent_id());

		if ($parent instanceof WC_Product) {
			$parent_image_id = (int) $parent->get_image_id();

			if ($parent_image_id > 0) {
				return $parent_image_id;
			}
		}
	}

	return (int) $product->get_image_id();
};

$slides = [];

foreach ($slides_raw as $slide) {
	if (empty($slide) || !is_array($slide)) {
		continue;
	}

	$product_sku = isset($slide['product_variation_sku'])
		? sanitize_text_field((string) $slide['product_variation_sku'])
		: '';
	$product = $resolve_product_by_sku($product_sku);

	$product_price_parts = $get_product_price_parts($product);
	$product_description = $get_product_short_description($product);
	$product_url         = $get_product_permalink($product);
	$product_image_id    = $get_product_parent_image_id($product);

	$fallback_title = isset($slide['title']) ? trim((string) $slide['title']) : '';
	$title          = $product instanceof WC_Product
		? (string) $product->get_name()
		: $fallback_title;

	$fallback_description = isset($slide['description']) ? trim((string) $slide['description']) : '';
	$description          = $product_description !== '' ? $product_description : $fallback_description;
	$description_is_html  = $product_description !== '';

	$has_product_price = $product_price_parts['current'] !== '' || $product_price_parts['old'] !== '';
	$current_price     = $has_product_price
		? $product_price_parts['current']
		: (isset($slide['current_price']) ? (string) $slide['current_price'] : '');
	$old_price         = $has_product_price
		? $product_price_parts['old']
		: (isset($slide['old_price']) ? (string) $slide['old_price'] : '');
	$price_is_html     = $has_product_price;

	$cta_text    = isset($slide['cta_text']) ? trim((string) $slide['cta_text']) : '';
	$raw_cta_url = $product_url !== ''
		? $product_url
		: (!empty($slide['cta_link']) ? (string) $slide['cta_link'] : '');
	$cta_url = function_exists('tersa_sanitize_marketing_link_url')
		? tersa_sanitize_marketing_link_url($raw_cta_url)
		: esc_url_raw($raw_cta_url);

	if ($title === '' || $description === '' || $cta_text === '' || $cta_url === '') {
		continue;
	}

	if (!$product_image_id && empty($slide['image_desktop']) && empty($slide['image_mobile'])) {
		continue;
	}

	$product_id   = $product instanceof WC_Product ? (int) $product->get_id() : 0;
	$variation_id = ($product instanceof WC_Product && $product->is_type('variation')) ? $product_id : 0;

	$slide['_title']               = $title;
	$slide['_description']         = $description;
	$slide['_description_is_html'] = $description_is_html;
	$slide['_current_price']       = $current_price;
	$slide['_old_price']           = $old_price;
	$slide['_price_is_html']       = $price_is_html;
	$slide['_cta_text']            = $cta_text;
	$slide['_cta_url']             = $cta_url;
	$slide['_product_sku']         = $product_sku;
	$slide['_product_id']          = $product_id;
	$slide['_variation_id']        = $variation_id;
	$slide['_product_image_id']    = $product_image_id;

	$slides[] = $slide;
}

if (empty($slides)) {
	return;
}
?>

<section class="home-hero" aria-label="<?php echo esc_attr__('Featured promotions', 'tersa-shop'); ?>">
	<div
		class="home-hero__slider js-home-hero-slider"
		data-slide-count="<?php echo esc_attr(count($slides)); ?>"
		data-autoplay-ms="<?php echo esc_attr((string) (int) apply_filters('tersa_home_hero_autoplay_ms', 5000)); ?>"
	>
		<div class="home-hero__track">
			<?php foreach ($slides as $index => $slide) : ?>
				<?php
				$is_active = $index === 0;
				$bg_color  = !empty($slide['background_color']) ? sanitize_hex_color((string) $slide['background_color']) : '';
				$bg_color  = $bg_color ?: '#f3ead7';

				$current_price       = isset($slide['_current_price']) ? $slide['_current_price'] : '';
				$old_price           = isset($slide['_old_price']) ? $slide['_old_price'] : '';
				$price_is_html       = !empty($slide['_price_is_html']);
				$title               = isset($slide['_title']) ? $slide['_title'] : '';
				$description         = isset($slide['_description']) ? $slide['_description'] : '';
				$description_is_html = !empty($slide['_description_is_html']);
				$cta_text            = isset($slide['_cta_text']) ? $slide['_cta_text'] : '';
				$cta_url             = isset($slide['_cta_url']) ? $slide['_cta_url'] : '';
				$product_sku         = isset($slide['_product_sku']) ? (string) $slide['_product_sku'] : '';
				$product_id          = isset($slide['_product_id']) ? (int) $slide['_product_id'] : 0;
				$variation_id        = isset($slide['_variation_id']) ? (int) $slide['_variation_id'] : 0;
				$product_image_id    = isset($slide['_product_image_id']) ? (int) $slide['_product_image_id'] : 0;
				$show_badge          = !empty($slide['show_badge']);
				$badge_top_text      = isset($slide['badge_top_text']) ? $slide['badge_top_text'] : '';
				$badge_main_text     = isset($slide['badge_main_text']) ? $slide['badge_main_text'] : '';
				$badge_bottom_text   = isset($slide['badge_bottom_text']) ? $slide['badge_bottom_text'] : '';

				$desktop_image_id = $product_image_id ?: (!empty($slide['image_desktop']) ? (int) $slide['image_desktop'] : 0);
				$mobile_image_id  = $product_image_id ? 0 : (!empty($slide['image_mobile']) ? (int) $slide['image_mobile'] : 0);
				$primary_image_id = $desktop_image_id ?: $mobile_image_id;
				?>
			<div
				class="home-hero__slide<?php echo $is_active ? ' is-active' : ''; ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr(sprintf(__('Slide %1$d of %2$d', 'tersa-shop'), $index + 1, count($slides))); ?>"
				data-index="<?php echo esc_attr($index); ?>"
				<?php if ($product_sku) : ?>data-product-sku="<?php echo esc_attr($product_sku); ?>"<?php endif; ?>
				<?php if ($product_id) : ?>data-product-id="<?php echo esc_attr((string) $product_id); ?>"<?php endif; ?>
				<?php if ($variation_id) : ?>data-variation-id="<?php echo esc_attr((string) $variation_id); ?>"<?php endif; ?>
				style="--hero-slide-bg: <?php echo esc_attr($bg_color); ?>;"
				<?php echo $is_active ? '' : 'hidden'; ?>
			>
					<div class="home-hero__inner">
						<div class="home-hero__content">
							<?php if ($current_price || $old_price) : ?>
								<div class="home-hero__prices" aria-label="<?php echo esc_attr__('Product pricing', 'tersa-shop'); ?>">
									<?php if ($current_price) : ?>
										<span class="home-hero__price home-hero__price--current">
											<?php echo $price_is_html ? wp_kses_post($current_price) : esc_html($current_price); ?>
										</span>
									<?php endif; ?>

									<?php if ($old_price) : ?>
										<span class="home-hero__price home-hero__price--old">
											<?php echo $price_is_html ? wp_kses_post($old_price) : esc_html($old_price); ?>
										</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

						<?php $title_tag = $is_active ? 'h1' : 'p'; ?>
						<<?php echo $title_tag; ?> class="home-hero__title">
							<?php echo nl2br(esc_html($title)); ?>
						</<?php echo $title_tag; ?>>

							<div class="home-hero__description">
								<?php echo $description_is_html ? wp_kses_post($description) : nl2br(esc_html($description)); ?>
							</div>

							<a class="home-hero__cta" href="<?php echo esc_url($cta_url); ?>">
								<?php echo esc_html($cta_text); ?>
							</a>
						</div>

						<div class="home-hero__media">
							<?php if ($show_badge && ($badge_top_text || $badge_main_text || $badge_bottom_text)) : ?>
								<div class="home-hero__badge" aria-hidden="true">
									<?php if ($badge_top_text) : ?>
										<span class="home-hero__badge-top"><?php echo esc_html($badge_top_text); ?></span>
									<?php endif; ?>

									<?php if ($badge_main_text) : ?>
										<span class="home-hero__badge-main"><?php echo esc_html($badge_main_text); ?></span>
									<?php endif; ?>

									<?php if ($badge_bottom_text) : ?>
										<span class="home-hero__badge-bottom"><?php echo nl2br(esc_html($badge_bottom_text)); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($primary_image_id) : ?>
								<?php
								$desktop_src     = $desktop_image_id ? wp_get_attachment_image_url($desktop_image_id, 'tersa-hero') : wp_get_attachment_image_url($primary_image_id, 'tersa-hero');
								$desktop_srcset  = $desktop_image_id ? wp_get_attachment_image_srcset($desktop_image_id, 'tersa-hero') : wp_get_attachment_image_srcset($primary_image_id, 'tersa-hero');
								$mobile_srcset   = $mobile_image_id ? wp_get_attachment_image_srcset($mobile_image_id, 'tersa-hero-mobile') : '';
								$desktop_sizes   = '(max-width: 1024px) 100vw, 50vw';
								$mobile_sizes    = '100vw';
								$hero_image_alt  = get_post_meta($primary_image_id, '_wp_attachment_image_alt', true);
								$hero_image_alt  = is_string($hero_image_alt) && $hero_image_alt !== '' ? $hero_image_alt : $title;
								?>
								<div class="home-hero__image">
									<picture class="home-hero__picture">
										<?php if ($mobile_image_id && $mobile_srcset) : ?>
											<source
												media="(max-width: 1024px)"
												srcset="<?php echo esc_attr($mobile_srcset); ?>"
												sizes="<?php echo esc_attr($mobile_sizes); ?>"
											>
										<?php endif; ?>
										<img
											class="home-hero__img"
											src="<?php echo esc_url((string) $desktop_src); ?>"
											<?php if ($desktop_srcset) : ?>srcset="<?php echo esc_attr($desktop_srcset); ?>"<?php endif; ?>
											sizes="<?php echo esc_attr($desktop_sizes); ?>"
											alt="<?php echo esc_attr($hero_image_alt); ?>"
											decoding="async"
											fetchpriority="<?php echo esc_attr($is_active ? 'high' : 'auto'); ?>"
											loading="<?php echo esc_attr($is_active ? 'eager' : 'lazy'); ?>"
										>
									</picture>
								</div>
							<?php endif; ?>
						</div>
					</div>
					</div>
			<?php endforeach; ?>
		</div>

		<?php if (count($slides) > 1) : ?>
			<div class="home-hero__pagination" aria-label="<?php echo esc_attr__('Slider pagination', 'tersa-shop'); ?>">
				<?php foreach ($slides as $index => $slide) : ?>
					<button
						class="home-hero__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
						type="button"
						aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'tersa-shop'), $index + 1)); ?>"
						aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
						data-slide-to="<?php echo esc_attr($index); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
