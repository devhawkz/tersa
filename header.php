<?php
if (!defined('ABSPATH')) {
	exit;
}

// Sva biznis logika je u helpers.php — template sadrži samo prezentaciju
$data = tersa_get_header_template_data();

$home_url                = $data['home_url'];
$site_name               = $data['site_name'];
$logo_markup             = $data['logo_markup'];
$wishlist_url            = $data['wishlist_url'];
$wishlist_count          = $data['wishlist_count'];
$cart_count              = $data['cart_count'];
$topbar_enabled          = $data['topbar_enabled'];
$topbar_message          = $data['topbar_message'];
$topbar_link_text        = $data['topbar_link_text'];
$topbar_link_url         = $data['topbar_link_url'];
$topbar_link_is_external = $data['topbar_link_is_external'];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<?php if ($topbar_enabled && $topbar_message) : ?>
		<div class="site-header__topbar">
			<div class="container">
				<div class="site-header__topbar-inner">
					<p class="site-header__promo">
						<?php echo esc_html($topbar_message); ?>

						<?php if ($topbar_link_text && $topbar_link_url) : ?>
							<a
								href="<?php echo esc_url($topbar_link_url); ?>"
								class="site-header__promo-link"
								<?php if ($topbar_link_is_external) : ?>rel="noopener noreferrer"<?php endif; ?>
							>
								<?php echo esc_html($topbar_link_text); ?>
							</a>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<header class="site-header" aria-label="<?php esc_attr_e('Site header', 'tersa-shop'); ?>">

		<div class="site-header__main">
			<div class="container">
				<div class="site-header__main-inner">
					<div class="site-header__brand">
						<a
							href="<?php echo esc_url($home_url); ?>"
							class="site-header__logo-link"
							aria-label="<?php echo esc_attr(sprintf(__('Go to homepage, %s', 'tersa-shop'), $site_name)); ?>"
						>
							<?php echo $logo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>

					<?php get_template_part('template-parts/global/navigation'); ?>

					<div class="site-header__actions" role="group" aria-label="<?php esc_attr_e('Header actions', 'tersa-shop'); ?>">
						<div class="site-header__search">
							<button
								type="button"
								class="site-header__icon-link site-header__icon-link--search"
								aria-label="<?php esc_attr_e('Search products', 'tersa-shop'); ?>"
								aria-expanded="false"
								aria-controls="header-search-overlay"
								data-search-toggle
							>
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<circle cx="11" cy="11" r="7"></circle>
									<line x1="16.65" y1="16.65" x2="21" y2="21"></line>
								</svg>
							</button>
						</div>

						<?php if ($wishlist_url) : ?>
							<a
								class="site-header__icon-link site-header__icon-link--wishlist"
								href="<?php echo esc_url($wishlist_url); ?>"
								aria-label="<?php echo esc_attr(tersa_get_wishlist_aria_label()); ?>"
							>
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<path d="M12 20.5l-1.35-1.22C5.4 14.52 2 11.44 2 7.72 2 4.64 4.42 2.25 7.5 2.25c1.74 0 3.41.81 4.5 2.09 1.09-1.28 2.76-2.09 4.5-2.09 3.08 0 5.5 2.39 5.5 5.47 0 3.72-3.4 6.8-8.65 11.56L12 20.5z"></path>
								</svg>

								<!--
								<span class="site-header__badge site-header__badge--wishlist" aria-hidden="true">
									<?php echo esc_html($wishlist_count); ?>
								</span>
								-->
							</a>
						<?php endif; ?>

					<?php if (class_exists('WooCommerce')) : ?>
						<a
							class="site-header__icon-link site-header__icon-link--cart"
							href="<?php echo esc_url(wc_get_cart_url()); ?>"
							aria-label="<?php echo esc_attr(tersa_get_cart_aria_label()); ?>"
							aria-expanded="false"
							aria-controls="cart-drawer"
							data-cart-toggle
						>
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<circle cx="9" cy="20" r="1.5"></circle>
									<circle cx="18" cy="20" r="1.5"></circle>
									<path d="M3 4h2l2.2 9.2a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.76L20 7H7"></path>
								</svg>

								<span class="site-header__badge" aria-hidden="true">
									<?php echo esc_html($cart_count); ?>
								</span>
							</a>
						<?php endif; ?>

						<button
							class="site-header__toggle"
							type="button"
							aria-expanded="false"
							aria-controls="mobile-navigation"
							aria-label="<?php esc_attr_e('Open menu', 'tersa-shop'); ?>"
							data-open-label="<?php esc_attr_e('Open menu', 'tersa-shop'); ?>"
							data-close-label="<?php esc_attr_e('Close menu', 'tersa-shop'); ?>"
						>
							<span></span>
							<span></span>
							<span></span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<?php get_template_part('template-parts/global/search'); ?>

		<?php get_template_part('template-parts/global/cart-drawer'); ?>

		<?php get_template_part('template-parts/global/mobile-navigation'); ?>
	</header>
