<?php
defined('ABSPATH') || exit;

$product = $args['product'] ?? null;
$limit   = isset($args['limit']) ? absint($args['limit']) : 4;
$title   = $args['title'] ?? (function_exists('pll__') ? pll__('Related products') : __('Related products', 'tersa-shop'));

if (!$product instanceof WC_Product) {
	return;
}

$product_id   = $product->get_id();
$exclude_ids  = [$product_id];
$related_ids  = wc_get_related_products($product_id, $limit, $exclude_ids);
$related_list = [];

/**
 * 1) Primarni izbor: WooCommerce related products
 */
if (!empty($related_ids)) {
	foreach ($related_ids as $related_id) {
		$related_product = wc_get_product($related_id);

		if (
			$related_product instanceof WC_Product
			&& $related_product->is_visible()
			&& $related_product->get_status() === 'publish'
		) {
			$related_list[] = $related_product;
		}
	}
}

/**
 * 2) Fallback: ista kategorija
 */
if (count($related_list) < $limit) {
	$current_cat_ids = wc_get_product_term_ids($product_id, 'product_cat');

	if (!empty($current_cat_ids)) {
		$fallback_query = new WP_Query([
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'post__not_in'        => array_merge($exclude_ids, wp_list_pluck($related_list, 'id')),
			'ignore_sticky_posts' => true,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'tax_query'           => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $current_cat_ids,
					'operator' => 'IN',
				],
			],
		]);

		if (!empty($fallback_query->posts)) {
			foreach ($fallback_query->posts as $fallback_id) {
				$fallback_product = wc_get_product($fallback_id);

				if (
					$fallback_product instanceof WC_Product
					&& $fallback_product->is_visible()
					&& $fallback_product->get_status() === 'publish'
				) {
					$related_list[] = $fallback_product;
				}
			}
		}

		wp_reset_postdata();
	}
}

/**
 * 3) Završni fallback: najnoviji proizvodi
 */
if (count($related_list) < $limit) {
	$existing_ids = [$product_id];

	foreach ($related_list as $existing_product) {
		if ($existing_product instanceof WC_Product) {
			$existing_ids[] = $existing_product->get_id();
		}
	}

	$latest_query = new WP_Query([
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'post__not_in'        => array_unique($existing_ids),
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	]);

	if (!empty($latest_query->posts)) {
		foreach ($latest_query->posts as $latest_id) {
			$latest_product = wc_get_product($latest_id);

			if (
				$latest_product instanceof WC_Product
				&& $latest_product->is_visible()
				&& $latest_product->get_status() === 'publish'
			) {
				$related_list[] = $latest_product;
			}
		}
	}

	wp_reset_postdata();
}

/**
 * Očisti duplikate i limitiraj
 */
$unique_products = [];
$seen_ids        = [];

foreach ($related_list as $item) {
	if (!$item instanceof WC_Product) {
		continue;
	}

	$item_id = $item->get_id();

	if (in_array($item_id, $seen_ids, true)) {
		continue;
	}

	$seen_ids[]        = $item_id;
	$unique_products[] = $item;
}

$unique_products = array_slice($unique_products, 0, $limit);

/**
 * Ne prikazuj sekciju ako nema dovoljno smislenog sadržaja
 */
if (count($unique_products) < 2) {
	return;
}
?>

<section class="home-bestsellers product-related">
	<div class="home-bestsellers__inner">
		<header class="home-bestsellers__header product-related__header">
			<h2 class="home-bestsellers__title product-related__title">
				<?php echo esc_html($title); ?>
			</h2>
		</header>

		<ul class="home-bestsellers__grid home-bestsellers__grid--columns-4 product-related__grid">
			<?php foreach ($unique_products as $related_product) : ?>
				<?php
				get_template_part(
					'template-parts/product-card',
					null,
					[
						'product' => $related_product,
					]
				);
				?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>