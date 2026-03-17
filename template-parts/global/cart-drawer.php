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
					<?php esc_html_e('Moja košarica', 'tersa-shop'); ?>
				</h2>

				<button
					type="button"
					class="site-header__cart-close"
					aria-label="<?php esc_attr_e('Close cart', 'tersa-shop'); ?>"
					data-cart-close
				>
					<span></span>
					<span></span>
				</button>
			</div>

			<div class="site-header__cart-body">
				<div class="widget_shopping_cart_content">
					<?php woocommerce_mini_cart(); ?>
				</div>
			</div>
		</div>
	</div>
</div>