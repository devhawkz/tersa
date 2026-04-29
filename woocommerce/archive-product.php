<?php
defined('ABSPATH') || exit;

get_header();

/**
 * Polylang-aware translator za statične stringove ovog templatea.
 * Ako je Polylang aktivan, koristi pll__() (registracije u
 * inc/woocommerce/translations-register-general-shop.php),
 * inače fallback na __() s tekstualnom domenom 'tersa-shop'.
 */
$tersa_t = static function (string $string): string {
	if (function_exists('pll__')) {
		return (string) pll__($string);
	}
	return (string) __($string, 'tersa-shop');
};

$page_title = woocommerce_page_title(false);

$current_view = isset($_GET['view']) ? sanitize_key(wp_unslash($_GET['view'])) : 'grid';
$current_view = in_array($current_view, ['grid', 'list'], true) ? $current_view : 'grid';

$current_orderby = function_exists('tersa_get_current_catalog_orderby')
	? tersa_get_current_catalog_orderby()
	: 'date';

$order_options = function_exists('tersa_get_allowed_catalog_orderby')
	? tersa_get_allowed_catalog_orderby()
	: [
		'date'       => $tersa_t('Sortiraj po najnovijim'),
		'menu_order' => $tersa_t('Zadano sortiranje'),
		'price'      => $tersa_t('Sortiraj po cijeni: nisko na visoko'),
		'price-desc' => $tersa_t('Sortiraj po cijeni: visoko na nisko'),
	];

$filter_taxonomies = [
	'product_cat' => $tersa_t('Kategorija'),
	'pa_color'    => $tersa_t('Boja'),
	'pa_material' => $tersa_t('Materijal'),
	'pa_size'     => $tersa_t('Dimenzija'),
];

if (taxonomy_exists('pa_patterns_textures')) {
	$filter_taxonomies['pa_patterns_textures'] = $tersa_t('Patterns & Textures');
} elseif (taxonomy_exists('pa_pattern')) {
	$filter_taxonomies['pa_pattern'] = $tersa_t('Patterns & Textures');
}

$archive_description = '';
$display_term        = null;
$is_filtered_term    = false;

if (is_product_category() || is_product_tag() || is_tax()) {
	$queried_object = get_queried_object();
	if ($queried_object instanceof WP_Term) {
		$display_term = $queried_object;
	}
} elseif (function_exists('tersa_get_active_filter_values')) {
	$active_cat_filters = tersa_get_active_filter_values('product_cat');
	if (count($active_cat_filters) === 1) {
		$maybe_term = get_term_by('slug', $active_cat_filters[0], 'product_cat');
		if ($maybe_term instanceof WP_Term) {
			$display_term     = $maybe_term;
			$is_filtered_term = true;
			$page_title       = $display_term->name;
		}
	}
}

if ($display_term instanceof WP_Term && !empty($display_term->description)) {
	$archive_description = term_description($display_term);
}

$archive_description_plain = $archive_description ? trim(wp_strip_all_tags($archive_description)) : '';
$archive_description_long  = $archive_description_plain && function_exists('mb_strlen')
	? mb_strlen($archive_description_plain) > 280
	: strlen((string) $archive_description_plain) > 280;

$reset_url = function_exists('tersa_get_archive_reset_url')
	? tersa_get_archive_reset_url()
	: get_permalink(wc_get_page_id('shop'));
?>

<main id="main-content" class="shop-archive">
	<div class="shop-archive__inner">

		<?php if (function_exists('woocommerce_breadcrumb')) : ?>
			<nav class="shop-archive__breadcrumbs" aria-label="<?php echo esc_attr($tersa_t('Breadcrumb')); ?>">
				<?php
				woocommerce_breadcrumb([
					'delimiter'   => ' / ',
					'wrap_before' => '<ol class="shop-archive__breadcrumb-list">',
					'wrap_after'  => '</ol>',
					'before'      => '<li class="shop-archive__breadcrumb-item">',
					'after'       => '</li>',
					'home'        => esc_html($tersa_t('Naslovnica')),
				]);
				?>
			</nav>
		<?php endif; ?>

		<header class="shop-archive__header<?php echo $display_term instanceof WP_Term ? ' shop-archive__header--term' : ''; ?>">
			<?php if ($is_filtered_term) : ?>
				<p class="shop-archive__eyebrow">
					<span class="shop-archive__eyebrow-dot" aria-hidden="true"></span>
					<?php echo esc_html($tersa_t('Aktivni filter kategorije')); ?>
				</p>
			<?php endif; ?>

			<h1 class="shop-archive__title"><?php echo esc_html($page_title); ?></h1>

			<?php if (!empty($archive_description)) : ?>
				<?php
				$desc_id    = 'shop-archive-description';
				$label_more = $tersa_t('Pročitaj više');
				$label_less = $tersa_t('Sažmi opis');
				?>
				<div class="shop-archive__description-wrap<?php echo $archive_description_long ? ' is-collapsible is-collapsed' : ''; ?>">
					<div
						id="<?php echo esc_attr($desc_id); ?>"
						class="shop-archive__description"
					>
						<?php echo wp_kses_post($archive_description); ?>
					</div>

					<?php if ($archive_description_long) : ?>
						<button
							type="button"
							class="shop-archive__description-toggle"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr($desc_id); ?>"
							data-label-more="<?php echo esc_attr($label_more); ?>"
							data-label-less="<?php echo esc_attr($label_less); ?>"
						>
							<span class="shop-archive__description-toggle-text"><?php echo esc_html($label_more); ?></span>
							<svg class="shop-archive__description-toggle-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
								<path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($is_filtered_term) : ?>
				<p class="shop-archive__header-reset">
					<a href="<?php echo esc_url($reset_url); ?>" class="shop-archive__header-reset-link">
						<svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" focusable="false">
							<path d="M4 4l8 8M12 4l-8 8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
						</svg>
						<?php echo esc_html($tersa_t('Prikaži sve proizvode')); ?>
					</a>
				</p>
			<?php endif; ?>
		</header>

		<form class="shop-archive__toolbar" method="get" action="">
			<div class="shop-archive__view-toggle" role="group" aria-label="<?php echo esc_attr($tersa_t('Product view')); ?>">
				<button
					class="shop-archive__view-button<?php echo $current_view === 'list' ? ' is-active' : ''; ?>"
					type="submit"
					name="view"
					value="list"
					aria-pressed="<?php echo $current_view === 'list' ? 'true' : 'false'; ?>"
				>
					<span class="screen-reader-text"><?php echo esc_html($tersa_t('List view')); ?></span>
					<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false">
						<rect x="4" y="6" width="24" height="8" rx="2"></rect>
						<rect x="4" y="18" width="24" height="8" rx="2"></rect>
					</svg>
				</button>

				<button
					class="shop-archive__view-button<?php echo $current_view === 'grid' ? ' is-active' : ''; ?>"
					type="submit"
					name="view"
					value="grid"
					aria-pressed="<?php echo $current_view === 'grid' ? 'true' : 'false'; ?>"
				>
					<span class="screen-reader-text"><?php echo esc_html($tersa_t('Grid view')); ?></span>
					<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false">
						<rect x="4" y="4" width="9" height="9" rx="1.5"></rect>
						<rect x="19" y="4" width="9" height="9" rx="1.5"></rect>
						<rect x="4" y="19" width="9" height="9" rx="1.5"></rect>
						<rect x="19" y="19" width="9" height="9" rx="1.5"></rect>
					</svg>
				</button>
			</div>

			<div class="shop-archive__toolbar-actions">
		<label class="shop-archive__sale-toggle">
				<input
					type="checkbox"
					name="on_sale"
					value="1"
					<?php checked(function_exists('tersa_is_sale_filter_active') && tersa_is_sale_filter_active()); ?>
				>
				<span><?php echo esc_html($tersa_t('Prikaži samo proizvode na popustu')); ?></span>
			</label>

			<label class="shop-archive__orderby-label">
				<span class="screen-reader-text"><?php echo esc_html($tersa_t('Sort products')); ?></span>
				<select name="orderby" class="shop-archive__orderby">
					<?php foreach ($order_options as $order_value => $order_label) : ?>
						<option value="<?php echo esc_attr($order_value); ?>" <?php selected($current_orderby, $order_value); ?>>
							<?php echo esc_html($order_label); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<button type="submit" class="shop-archive__toolbar-apply">
				<?php echo esc_html($tersa_t('Primijeni')); ?>
			</button>
		</div>

		<?php
		$toolbar_skip = ['view', 'orderby', 'on_sale'];

		$allowed_passthrough = ['paged', 'min_price', 'max_price', 'rating_filter'];
		foreach (array_keys($filter_taxonomies) as $tax) {
			$allowed_passthrough[] = 'filter_' . $tax;
		}

		foreach ($_GET as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$key = sanitize_key($key);

			if (in_array($key, $toolbar_skip, true)) {
				continue;
			}

			if (!in_array($key, $allowed_passthrough, true)) {
				continue;
			}

			if (is_array($value)) {
				foreach ($value as $item) {
					echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr(sanitize_text_field(wp_unslash($item))) . '">';
				}
			} else {
				echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(sanitize_text_field(wp_unslash($value))) . '">';
			}
		}
		?>
		</form>

		<div class="shop-archive__body shop-archive__body--<?php echo esc_attr($current_view); ?>">
			<aside class="shop-archive__filters" aria-label="<?php echo esc_attr($tersa_t('Product filters')); ?>">
				<form class="shop-archive__filters-form" method="get" action="">
					<input type="hidden" name="view" value="<?php echo esc_attr($current_view); ?>">
					<input type="hidden" name="orderby" value="<?php echo esc_attr($current_orderby); ?>">

					<?php if (function_exists('tersa_is_sale_filter_active') && tersa_is_sale_filter_active()) : ?>
						<input type="hidden" name="on_sale" value="1">
					<?php endif; ?>

					<?php foreach ($filter_taxonomies as $taxonomy => $label) : ?>
						<?php
						if (!taxonomy_exists($taxonomy)) {
							continue;
						}

						$active_values = function_exists('tersa_get_active_filter_values')
							? tersa_get_active_filter_values($taxonomy)
							: [];

					// Transient cache za terme filtera — izbjegava DB upit po taksonomiji na svakom requestu.
					$_fl_lang       = function_exists('pll_current_language') ? (string) pll_current_language() : '';
					$_fl_cache_key  = 'tersa_filter_terms_' . $taxonomy . ($_fl_lang ? '_' . $_fl_lang : '');
					$terms          = get_transient($_fl_cache_key);

					if (false === $terms) {
						$terms = get_terms([
							'taxonomy'   => $taxonomy,
							'hide_empty' => true,
							'orderby'    => 'name',
							'order'      => 'ASC',
						]);
						if (!is_wp_error($terms)) {
							set_transient($_fl_cache_key, $terms, 6 * HOUR_IN_SECONDS);
						}
					}

						if (is_wp_error($terms) || empty($terms)) {
							continue;
						}

						$panel_id  = 'filter-panel-' . sanitize_html_class($taxonomy);
						$button_id = 'filter-button-' . sanitize_html_class($taxonomy);
						$is_open   = !empty($active_values);
						?>
						<section class="shop-archive__filter-group">
							<button
								id="<?php echo esc_attr($button_id); ?>"
								class="shop-archive__filter-toggle"
								type="button"
								aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
								aria-controls="<?php echo esc_attr($panel_id); ?>"
							>
								<span><?php echo esc_html($label); ?></span>
								<span class="shop-archive__filter-icon" aria-hidden="true"><?php echo $is_open ? '−' : '+'; ?></span>
							</button>

							<div
								id="<?php echo esc_attr($panel_id); ?>"
								class="shop-archive__filter-panel<?php echo $is_open ? ' is-open' : ''; ?>"
								aria-labelledby="<?php echo esc_attr($button_id); ?>"
								<?php echo $is_open ? '' : 'hidden'; ?>
							>
								<ul class="shop-archive__filter-list">
									<?php foreach ($terms as $term) : ?>
										<?php
										$is_checked = in_array($term->slug, $active_values, true);
										$field_name = 'filter_' . $taxonomy . '[]';
										$input_id   = 'filter-' . sanitize_html_class($taxonomy . '-' . $term->slug);
										?>
										<li class="shop-archive__filter-item">
											<label class="shop-archive__filter-option" for="<?php echo esc_attr($input_id); ?>">
												<input
													id="<?php echo esc_attr($input_id); ?>"
													type="checkbox"
													name="<?php echo esc_attr($field_name); ?>"
													value="<?php echo esc_attr($term->slug); ?>"
													<?php checked($is_checked); ?>
												>
												<span class="shop-archive__filter-check" aria-hidden="true"></span>
												<span class="shop-archive__filter-text">
													<?php echo esc_html($term->name); ?>
													<span class="shop-archive__filter-count">(<?php echo esc_html((string) $term->count); ?>)</span>
												</span>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</section>
					<?php endforeach; ?>

					<div class="shop-archive__filter-actions">
						<button class="shop-archive__apply" type="submit">
							<?php echo esc_html($tersa_t('Primijeni filtre')); ?>
						</button>

						<a class="shop-archive__reset" href="<?php echo esc_url($reset_url); ?>">
							<?php echo esc_html($tersa_t('Poništi filtre')); ?>
						</a>
					</div>
				</form>
			</aside>

			<section class="shop-archive__products" aria-label="<?php echo esc_attr($tersa_t('Products')); ?>">
				<?php if (woocommerce_product_loop()) : ?>
					<ul class="products shop-archive__products-grid shop-archive__products-grid--<?php echo esc_attr($current_view); ?>">
						<?php while (have_posts()) : the_post(); ?>
							<?php wc_get_template_part('content', 'product'); ?>
						<?php endwhile; ?>
					</ul>

					<?php
					the_posts_pagination([
						'screen_reader_text' => $tersa_t('Navigacija kroz proizvode'),
						'mid_size'  => 1,
						'prev_text' => function_exists('tersa_pagination_prev_text') ? tersa_pagination_prev_text() : $tersa_t('Prethodna'),
						'next_text' => function_exists('tersa_pagination_next_text') ? tersa_pagination_next_text() : $tersa_t('Sljedeća'),
					]);
					?>
				<?php else : ?>
				<div class="shop-archive__empty">
					<p><?php echo esc_html($tersa_t('Nema pronađenih proizvoda za odabrane filtere.')); ?></p>
				</div>
				<?php endif; ?>
			</section>
		</div>
	</div>
</main>

<?php
get_sidebar();
get_footer();
?>