<?php
defined('ABSPATH') || exit;

global $product;

if (!$product instanceof WC_Product) {
	return;
}

$product_id        = $product->get_id();
$product_name      = $product->get_name();

/**
 * Plugin (Tersa Variation Gallery) može preuzeti glavnu sliku i galeriju
 * kada postoji "default varijacija" sa vlastitom galerijom. Bez plugina,
 * filteri ostaju no-op i vraćaju default WC vrednosti.
 */
$main_image_id     = (int) apply_filters('tersa_main_product_image_id', (int) $product->get_image_id(), $product);
$gallery_image_ids = (array) apply_filters('tersa_product_gallery_image_ids', $product->get_gallery_image_ids(), $product);
$attachment_ids    = array_values(array_unique(array_filter(array_map('absint', array_merge([$main_image_id], $gallery_image_ids)))));

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

/*
 * Glavna slika se renderuje u kvadratu (aspect-ratio 1/1) koji zauzima ~50vw u
 * 2-kolonskom layoutu (≥1201px) i 100vw u 1-kolonskom (≤1200px). Eksplicitan
 * sizes atribut sprečava da browser bira premalu varijantu na Retina ekranima.
 */
$tersa_main_image_size  = '1536x1536';
$tersa_main_image_sizes = '(min-width: 1201px) 50vw, 100vw';

/**
 * Parent gallery payload za frontend JS — koristi se kao fallback kad varijacija
 * nema vlastitu galeriju. Sadrži i sliku i thumb varijantu (jedan upit po atačmentu;
 * URL-ovi prolaze kroz WP attachment cache).
 *
 * Gradimo iz neoverride-ovane parent liste (bez plugin filtera), tako da reset
 * uvek vraća pravu parent galeriju, čak i ako je default varijacija imala svoju.
 */
$tersa_parent_main_image_id     = (int) $product->get_image_id();
$tersa_parent_gallery_image_ids = $product->get_gallery_image_ids();
$tersa_parent_attachment_ids    = array_values(array_unique(array_filter(array_map(
	'absint',
	array_merge([$tersa_parent_main_image_id], $tersa_parent_gallery_image_ids)
))));

// Batch prime cache za sve attachment-e iz parent galerije (jedan SELECT).
if (!empty($tersa_parent_attachment_ids)) {
	_prime_post_caches($tersa_parent_attachment_ids, true, true);
}

$tersa_parent_gallery_payload = [];
foreach ($tersa_parent_attachment_ids as $tersa_pid) {
	$tersa_src = wp_get_attachment_image_url($tersa_pid, $tersa_main_image_size);
	if (!$tersa_src) {
		continue;
	}
	$tersa_parent_gallery_payload[] = [
		'id'     => $tersa_pid,
		'src'    => $tersa_src,
		'full'   => wp_get_attachment_image_url($tersa_pid, 'full') ?: $tersa_src,
		'thumb'  => wp_get_attachment_image_url($tersa_pid, 'thumbnail') ?: $tersa_src,
		'srcset' => (string) wp_get_attachment_image_srcset($tersa_pid, $tersa_main_image_size),
		'sizes'  => $tersa_main_image_sizes,
		'alt'    => (string) get_post_meta($tersa_pid, '_wp_attachment_image_alt', true),
	];
}
?>

<?php do_action('woocommerce_before_single_product'); ?>

<div
	id="product-<?php the_ID(); ?>"
	<?php wc_product_class('product-single', $product); ?>
	data-tersa-product-id="<?php echo esc_attr((string) $product_id); ?>"
>
	<script type="application/json" class="tersa-product-fallback-gallery">
	<?php echo wp_json_encode($tersa_parent_gallery_payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
	</script>
	<?php
	// Prevent WC's default image output — custom gallery is handled above.
	remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
	do_action('woocommerce_before_single_product_summary');
	?>
	<div class="product-single__inner">
		<div class="product-single__gallery-column">
			<div class="product-single__gallery">
				<div class="product-single__main-media">
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
								$tersa_main_image_size,
								false,
								[
									'class'    => 'product-single__main-image',
									'loading'  => 'eager',
									'decoding' => 'async',
									'sizes'    => $tersa_main_image_sizes,
								'alt'      => $product_name,
							]
						);
						?>
						</a>

						
					<?php endif; ?>
				</div>

				<?php if (count($attachment_ids) > 1) :
					$tersa_thumbs_slider = count($attachment_ids) >= 4;
					?>
					<div class="product-single__thumbs-wrap<?php echo $tersa_thumbs_slider ? ' product-single__thumbs-wrap--slider' : ''; ?>"
						<?php if ($tersa_thumbs_slider) : ?>data-tersa-thumbs-slider="1"<?php endif; ?>>
						<?php if ($tersa_thumbs_slider) : ?>
							<button
								type="button"
								class="product-single__thumbs-nav product-single__thumbs-nav--prev"
								data-tersa-thumbs-prev
								aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Prethodne slike') : __('Prethodne slike', 'tersa-shop')); ?>"
								disabled
							>
								<span aria-hidden="true">&#8249;</span>
							</button>
						<?php endif; ?>
						<div class="product-single__thumbs" role="list" aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Minijature u galeriji') : __('Minijature u galeriji', 'tersa-shop')); ?>">
							<?php foreach ($attachment_ids as $index => $attachment_id) : ?>
								<?php
								$thumb_url = wp_get_attachment_image_url($attachment_id, 'medium');
								$full_url  = wp_get_attachment_image_url($attachment_id, 'full');

								if (!$thumb_url || !$full_url) {
									continue;
								}

								$large_srcset = (string) wp_get_attachment_image_srcset($attachment_id, $tersa_main_image_size);
								?>
								<button
									class="product-single__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
									type="button"
									data-full-image="<?php echo esc_url($full_url); ?>"
									data-large-image="<?php echo esc_url(wp_get_attachment_image_url($attachment_id, $tersa_main_image_size)); ?>"
									data-large-srcset="<?php echo esc_attr($large_srcset); ?>"
									data-large-sizes="<?php echo esc_attr($tersa_main_image_sizes); ?>"
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
						<?php if ($tersa_thumbs_slider) : ?>
							<button
								type="button"
								class="product-single__thumbs-nav product-single__thumbs-nav--next"
								data-tersa-thumbs-next
								aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Sljedeće slike') : __('Sljedeće slike', 'tersa-shop')); ?>"
							>
								<span aria-hidden="true">&#8250;</span>
							</button>
						<?php endif; ?>
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
			<?php
			/**
			 * woocommerce_single_product_summary hook.
			 *
			 * Svi WC dodaci (Subscriptions, Product Add-ons, Bookings, Composite Products,
			 * Points & Rewards itd.) dodaju vlastiti sadržaj na ovaj hook.
			 * Default WC callbacki su uklonjeni u inc/woocommerce/single.php jer ih tema
			 * renderuje ručno — ovdje se okidaju samo callback-i iz pluginova.
			 *
			 * @hooked (custom plugins only — defaults removed)
			 */
			do_action('woocommerce_single_product_summary');
			?>
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

	<div
		class="product-single__lightbox"
		role="dialog"
		aria-modal="true"
		aria-hidden="true"
		aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Pregled slike proizvoda') : __('Pregled slike proizvoda', 'tersa-shop')); ?>"
		hidden
	>
		<div class="product-single__lightbox-backdrop" data-tersa-lightbox-close></div>
		<button
			type="button"
			class="product-single__lightbox-close"
			data-tersa-lightbox-close
			aria-label="<?php echo esc_attr(function_exists('pll__') ? pll__('Zatvori pregled') : __('Zatvori pregled', 'tersa-shop')); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</button>
		<figure class="product-single__lightbox-figure">
			<img class="product-single__lightbox-image" src="" alt="" />
		</figure>
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