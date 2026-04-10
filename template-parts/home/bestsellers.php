<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WooCommerce')) {
	return;
}

$page_id = isset($args['page_id']) ? (int) $args['page_id'] : get_queried_object_id();

$instance = 1;
if (isset($args) && is_array($args) && isset($args['instance'])) {
	$instance = max(1, (int) $args['instance']);
}

$fields = function_exists('get_field') ? get_fields($page_id) : [];
$fields = is_array($fields) ? $fields : [];

$get_acf_value = static function (array $source, string $key, $fallback = null) {
	return array_key_exists($key, $source) ? $source[$key] : $fallback;
};

$show_section_field = $instance === 2 ? 'show_home_bestsellers_section_2' : 'show_home_bestsellers_section_1';
$title_field        = $instance === 2 ? 'home_bestsellers_section_title_2' : 'home_bestsellers_section_title_1';
$badge_color_field  = $instance === 2 ? 'home_bestsellers_badge_color_2' : 'home_bestsellers_badge_color_1';
$tag_slug_field     = $instance === 2 ? 'home_bestsellers_product_tag_slug_2' : 'home_bestsellers_product_tag_slug_1';

$show_section = false;
if (function_exists('get_field')) {
	$show_value = $get_acf_value($fields, $show_section_field, null);
	if ($show_value === null) {
		$show_value = $get_acf_value($fields, 'show_home_bestsellers_section', false);
	}
	$show_section = (bool) $show_value;
}

if (!$show_section) {
	return;
}

$section_title = '';
$badge_color   = '';
if (function_exists('get_field')) {
	$title_value = $get_acf_value($fields, $title_field, null);
	if ($title_value === null) {
		$title_value = $get_acf_value($fields, 'home_bestsellers_section_title', '');
	}
	$section_title = (string) $title_value;

	$badge_value = $get_acf_value($fields, $badge_color_field, null);
	if ($badge_value === null) {
		$badge_value = $get_acf_value($fields, 'home_bestsellers_badge_color', '');
	}
	$badge_color = (string) $badge_value;
}

$section_title = !empty($section_title) ? $section_title : 'Bestsellers';
$badge_color   = !empty($badge_color) ? $badge_color : '#000000';

$product_tag_slug = 'najprodavanije';

if (function_exists('get_field')) {
	// Izbor taga mora da bude eksplicitan (ACF), ne izveden iz naslova sekcije.
	$acf_product_tag_slug = $get_acf_value($fields, $tag_slug_field, null);
	if ($acf_product_tag_slug === null) {
		$acf_product_tag_slug = $get_acf_value($fields, 'home_bestsellers_product_tag_slug', '');
	}

	$acf_product_tag_slug = (string) $acf_product_tag_slug;
	if ($acf_product_tag_slug === 'novo') {
		$acf_product_tag_slug = 'najnovije';
	}

	if (in_array($acf_product_tag_slug, ['najprodavanije', 'najnovije'], true)) {
		$product_tag_slug = $acf_product_tag_slug;
	}
}

$current_lang     = function_exists('pll_current_language') ? (string) call_user_func('pll_current_language') : '';
$transient_key    = 'tersa_bestsellers_' . $product_tag_slug . '_' . $instance . ($current_lang ? '_' . $current_lang : '');
$cached_post_ids  = get_transient($transient_key);
static $request_post_ids_cache = [];

$translate = static function (string $text): string {
	if (function_exists('pll__')) {
		return (string) call_user_func('pll__', $text);
	}
	return $text;
};

if (false === $cached_post_ids) {
	$request_cache_key = $product_tag_slug . ($current_lang ? '_' . $current_lang : '');

	if (!array_key_exists($request_cache_key, $request_post_ids_cache)) {
		$query_args = [
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 4,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
			'fields'                 => 'ids',
			'tax_query'              => [
				[
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => $product_tag_slug,
				],
			],
		];

		if ($current_lang) {
			$query_args['lang'] = $current_lang;
		}

		$id_query                                 = new WP_Query($query_args);
		$request_post_ids_cache[$request_cache_key] = $id_query->posts ?: [];
		wp_reset_postdata();
	}

	$cached_post_ids = $request_post_ids_cache[$request_cache_key];
	set_transient($transient_key, $cached_post_ids, 6 * HOUR_IN_SECONDS);
}

if (empty($cached_post_ids)) {
	return;
}

$query = new WP_Query([
	'post_type'              => 'product',
	'post_status'            => 'publish',
	'post__in'               => $cached_post_ids,
	'orderby'                => 'post__in',
	'no_found_rows'          => true,
	'ignore_sticky_posts'    => true,
	'update_post_meta_cache' => true,
	'update_post_term_cache' => true,
]);

if (!$query->have_posts()) {
	wp_reset_postdata();
	return;
}
?>

<?php
$product_ids = array_map('intval', $query->posts);
$product_tags_by_id = [];

if (!empty($product_ids)) {
	$terms = wp_get_object_terms(
		$product_ids,
		'product_tag',
		[
			'fields' => 'all_with_object_id',
		]
	);

	if (!is_wp_error($terms) && is_array($terms)) {
		foreach ($terms as $term) {
			if (!isset($term->object_id)) {
				continue;
			}

			$product_id = (int) $term->object_id;
			if (!isset($product_tags_by_id[$product_id])) {
				$product_tags_by_id[$product_id] = [];
			}
			$product_tags_by_id[$product_id][] = $term;
		}

		foreach ($product_tags_by_id as $product_id => $tag_terms) {
			usort($tag_terms, static function ($a, $b) {
				return (int) $b->count <=> (int) $a->count;
			});
			$product_tags_by_id[$product_id] = array_map(
				static function ($term) {
					return (string) $term->name;
				},
				array_slice($tag_terms, 0, 2)
			);
		}
	}
}
?>

<section class="home-bestsellers cart" aria-labelledby="home-bestsellers-title">
	<div class="home-bestsellers__inner">
		<h2 id="home-bestsellers-title" class="home-bestsellers__title">
			<?php echo esc_html($section_title); ?>
		</h2>

		<ul class="home-bestsellers__grid" role="list">
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

				$children_ids = [];
				if (method_exists($product, 'get_children')) {
					$children_ids = $product->get_children();
				}

				// Za variable proizvode treba prikazati "Vidi opcije" (tj. ne klasičan Add to cart),
				// bez obzira da li postoji 1 ili više varijanti.
				$has_multiple_variants = $product->is_type('variable') && is_array($children_ids) && count($children_ids) > 0;

				$button_classes = [
					'button',
					'product_type_' . $product->get_type(),
					'home-bestsellers__cart-button',
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

				$button_label_options = $translate('Vidi opcije');

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
			?>
			<li>
			<article class="home-bestsellers__card">
					<div class="home-bestsellers__media-wrap">
						<a class="home-bestsellers__media-link" href="<?php echo esc_url($product_url); ?>">
							<div class="home-bestsellers__media">
								<?php
								// Prikaži do 2 taga iz WooCommerce proizvoda (umesto "sale badge").
								$tag_names = $product_tags_by_id[$product_id] ?? [];
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
										'tersa-bestseller',
										false,
										[
											'class'    => 'home-bestsellers__image home-bestsellers__image--primary',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'sizes'    => '(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 25vw',
											'alt'      => esc_attr($product_name),
										]
									);
									?>
								<?php endif; ?>

								<?php if ($hover_image_id) : ?>
									<?php
									echo wp_get_attachment_image(
										$hover_image_id,
										'tersa-bestseller',
										false,
										[
											'class'    => 'home-bestsellers__image home-bestsellers__image--hover',
											'loading'  => 'lazy',
											'decoding' => 'async',
											'sizes'    => '(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 25vw',
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
								static $wishlist_markup_cache = [];
								$wishlist_key = (string) $product_id;

								if (!isset($wishlist_markup_cache[$wishlist_key])) {
									$wishlist_markup_cache[$wishlist_key] = do_shortcode(
										sprintf(
											'[yith_wcwl_add_to_wishlist product_id="%d" link_classes="home-bestsellers__wishlist-link"]',
											$product_id
										)
									);
								}

								echo $wishlist_markup_cache[$wishlist_key];
								?>
							</div>
						<?php endif; ?>

						<div class="home-bestsellers__cart-wrap">
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
										esc_html($product->add_to_cart_text())
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
			</li>
		<?php endwhile; ?>
		</ul>
	</div>
</section>

<?php wp_reset_postdata(); ?>