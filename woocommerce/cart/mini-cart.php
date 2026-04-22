<?php
defined('ABSPATH') || exit;

do_action('woocommerce_before_mini_cart');

if (!WC()->cart->is_empty()) : ?>

	<ul class="woocommerce-mini-cart mini_cart_list product_list_widget <?php echo esc_attr($args['list_class'] ?? ''); ?>">
		<?php
		do_action('woocommerce_before_mini_cart_contents');

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
			$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

			if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) {
				continue;
			}

			$product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
			$product_permalink = apply_filters(
				'woocommerce_cart_item_permalink',
				$_product->is_visible() ? $_product->get_permalink($cart_item) : '',
				$cart_item,
				$cart_item_key
			);

			$thumbnail = apply_filters(
				'woocommerce_cart_item_thumbnail',
				$_product->get_image('woocommerce_thumbnail'),
				$cart_item,
				$cart_item_key
			);

			$product_price = apply_filters(
				'woocommerce_cart_item_price',
				WC()->cart->get_product_price($_product),
				$cart_item,
				$cart_item_key
			);

			$quantity = (int) $cart_item['quantity'];
			$max_qty  = $_product->get_max_purchase_quantity();
			?>

			<li class="woocommerce-mini-cart-item mini_cart_item tersa-mini-cart__item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
				<?php
				$trash_icon = '<svg class="mini-cart__remove-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" aria-hidden="true" focusable="false"><path d="M10.289 14.211h3.102l1.444 25.439c.029.529.468.943.998.943h18.933c.53 0 .969-.415.998-.944l1.421-25.438h3.104c.553 0 1-.448 1-1s-.447-1-1-1h-3.741a1.02 1.02 0 0 0-.309.031h-5.246V9.594c0-.552-.447-1-1-1h-9.409c-.553 0-1 .448-1 1v2.617h-5.248a1.02 1.02 0 0 0-.267.027h-3.779c-.553 0-1 .448-1 1s.447.973 1 .973zm11.295-3.617h7.409v1.617h-7.409zM35.182 14.211 33.82 38.594H16.778l-1.384-24.383z"/><path d="M20.395 36.718a1.004 1.004 0 0 0 1-1.056l-1.052-18.535a1.002 1.002 0 0 0-2 .113l1.052 18.535a1.003 1.003 0 0 0 1 .943zm9.752 0a1.003 1.003 0 0 0 1-.943l1.052-18.535a1.002 1.002 0 0 0-2-.113l-1.052 18.535a1.004 1.004 0 0 0 1.052 1.056zm-4.858.001a1 1 0 0 0 1-1V17.184a1 1 0 0 0-2 0v18.535a1 1 0 0 0 1 1z"/></svg>';

				echo apply_filters(
					'woocommerce_cart_item_remove_link',
					sprintf(
						'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
						esc_url(wc_get_cart_remove_url($cart_item_key)),
						esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
						esc_attr($product_id),
						esc_attr($cart_item_key),
						esc_attr($_product->get_sku()),
						$trash_icon
					),
					$cart_item_key
				);
				?>

			<div class="tersa-mini-cart__thumb">
				<?php if ($product_permalink) : ?>
					<a href="<?php echo esc_url($product_permalink); ?>">
						<?php echo wp_kses_post($thumbnail); ?>
					</a>
				<?php else : ?>
					<?php echo wp_kses_post($thumbnail); ?>
				<?php endif; ?>
				</div>

				<div class="tersa-mini-cart__content">
					<div class="tersa-mini-cart__title-wrap">
						<?php if ($product_permalink) : ?>
							<a class="tersa-mini-cart__title" href="<?php echo esc_url($product_permalink); ?>">
								<?php echo esc_html($product_name); ?>
							</a>
						<?php else : ?>
							<span class="tersa-mini-cart__title"><?php echo esc_html($product_name); ?></span>
						<?php endif; ?>
					</div>

					<?php echo wc_get_formatted_cart_item_data($cart_item); ?>

					<div class="tersa-mini-cart__meta">
						<div class="tersa-mini-cart__qty" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
							<button
								type="button"
								class="tersa-mini-cart__qty-btn"
								data-qty-action="decrease"
								aria-label="<?php esc_attr_e('Decrease quantity', 'tersa-shop'); ?>"
								<?php disabled($quantity <= 1); ?>
							>-</button>

							<span class="tersa-mini-cart__qty-value" aria-live="polite">
								<?php echo esc_html($quantity); ?>
							</span>

							<button
								type="button"
								class="tersa-mini-cart__qty-btn"
								data-qty-action="increase"
								aria-label="<?php esc_attr_e('Increase quantity', 'tersa-shop'); ?>"
								<?php disabled($max_qty > 0 && $quantity >= $max_qty); ?>
							>+</button>
						</div>

						<div class="tersa-mini-cart__price">
							<?php echo wp_kses_post($product_price); ?>
						</div>
					</div>
				</div>
			</li>

			<?php
		}

		do_action('woocommerce_mini_cart_contents');
		?>
	</ul>

	<p class="woocommerce-mini-cart__total total">
		<strong><?php esc_html_e('Subtotal', 'woocommerce'); ?></strong>
		<span><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></span>
	</p>

	<p class="woocommerce-mini-cart__buttons buttons">
		<?php do_action('woocommerce_widget_shopping_cart_buttons'); ?>
	</p>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message">
		<?php echo esc_html(function_exists('pll__') ? pll__('Trenutno nema proizvoda u košarici.') : __('Trenutno nema proizvoda u košarici.', 'tersa-shop')); ?>
	</p>

<?php endif; ?>

<?php do_action('woocommerce_after_mini_cart'); ?>