<?php
if (!defined('ABSPATH')) {
	exit;
}

$search_results_label = function_exists('tersa_translate_search_string')
	? tersa_translate_search_string('results')
	: __('Rezultati pretrage', 'tersa-shop');
$search_placeholder = function_exists('tersa_translate_search_string')
	? tersa_translate_search_string('placeholder')
	: __('Pretraga', 'tersa-shop');
$search_close_label = function_exists('tersa_translate_search_string')
	? tersa_translate_search_string('close')
	: __('Zatvori pretragu', 'tersa-shop');

$search_lang = function_exists('tersa_get_current_language_slug')
	? tersa_get_current_language_slug()
	: (function_exists('pll_current_language') ? sanitize_key((string) pll_current_language('slug')) : '');

$prepare_search_markup = static function (string $markup) use ($search_lang, $search_placeholder): string {
	if ($markup === '') {
		return $markup;
	}

	$placeholder_attr = 'placeholder="' . esc_attr($search_placeholder) . '"';
	$markup           = (string) preg_replace('/placeholder=(["\']).*?\1/i', $placeholder_attr, $markup);

	if (stripos($markup, 'placeholder=') === false) {
		$markup = (string) preg_replace(
			'/(<input\b(?=[^>]*\btype=["\'](?:search|text)["\'])(?![^>]*\bplaceholder=))/i',
			'$1 ' . $placeholder_attr,
			$markup,
			1
		);
	}

	if ($search_lang === '' || stripos($markup, 'name="lang"') !== false || stripos($markup, "name='lang'") !== false) {
		return $markup;
	}

	$hidden = '<input type="hidden" name="lang" value="' . esc_attr($search_lang) . '">';

	return (string) preg_replace('/<\/form>/i', $hidden . '</form>', $markup, 1);
};
?>
<div id="header-search-overlay" class="site-header__search-overlay" hidden>
	<div class="site-header__search-backdrop" data-search-close></div>

	<div
		class="site-header__search-panel"
		role="dialog"
		aria-modal="true"
		aria-labelledby="header-search-title"
	>
		<div class="site-header__search-panel-inner">
				<div class="site-header__search-head">
					<h2
						id="header-search-title"
						class="site-header__search-title"
						aria-live="polite"
						data-search-label="<?php echo esc_attr($search_results_label); ?>"
					>
						<?php echo esc_html($search_results_label); ?>
					</h2>

				<button
					type="button"
					class="site-header__search-close"
					aria-label="<?php echo esc_attr($search_close_label); ?>"
					data-search-close
				>
					<span></span>
					<span></span>
				</button>
			</div>

			<div class="site-header__search-body">
				<?php if (shortcode_exists('aws_search_form')) : ?>
					<?php echo $prepare_search_markup((string) do_shortcode('[aws_search_form]')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php elseif (function_exists('woocommerce_product_search')) : ?>
					<?php
					ob_start();
					woocommerce_product_search();
					echo $prepare_search_markup((string) ob_get_clean()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
