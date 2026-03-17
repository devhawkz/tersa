<?php
if (!defined('ABSPATH')) {
	exit;
}
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
					data-search-label="<?php echo esc_attr(__('Rezultati pretrage', 'tersa-shop')); ?>"
				>
					<?php echo esc_html(__('Rezultati pretrage', 'tersa-shop')); ?>
				</h2>

				<button
					type="button"
					class="site-header__search-close"
					aria-label="<?php esc_attr_e('Close search', 'tersa-shop'); ?>"
					data-search-close
				>
					<span></span>
					<span></span>
				</button>
			</div>

			<div class="site-header__search-body">
				<?php if (shortcode_exists('aws_search_form')) : ?>
					<?php echo do_shortcode('[aws_search_form]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
