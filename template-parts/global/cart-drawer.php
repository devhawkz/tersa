<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WooCommerce')) {
	return;
}
?>
<div id="cart-drawer" class="site-header__cart-overlay" hidden>
	<div class="site-header__cart-backdrop" data-cart-close aria-hidden="true"></div>

	<div
		class="site-header__cart-panel"
		role="dialog"
		aria-modal="true"
		aria-labelledby="cart-drawer-title"
	>
		<div class="site-header__cart-panel-inner">
			<div class="site-header__cart-head">
				<h2 id="cart-drawer-title" class="site-header__cart-title">
					<?php echo esc_html(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Moja košarica') : __('Moja košarica', 'tersa-shop')); ?>
				</h2>

				<button
					type="button"
					class="site-header__cart-close"
					aria-label="<?php echo esc_attr(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Close cart') : __('Close cart', 'tersa-shop')); ?>"
					data-cart-close
				>
					<span></span>
					<span></span>
				</button>
			</div>

		<div class="site-header__cart-body">
			<div class="widget_shopping_cart_content" data-cart-ssr="1">
				<?php
				// SSR mini-cart: prvi klik na drawer je instant, bez AJAX round-tripa.
				// WC fragment sistem (tersa_wc_drawer_fragment) pre-hidrira ovaj markup
				// iz sessionStorage-a na svakom sledećem page load-u.
				if (function_exists('woocommerce_mini_cart')) {
					woocommerce_mini_cart();
				} else {
					?>
					<div class="site-header__cart-loading" data-cart-loading-message>
						<?php echo esc_html(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Učitavanje košarice...') : __('Učitavanje košarice...', 'tersa-shop')); ?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		</div>
	</div>
</div>
