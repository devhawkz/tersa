<?php
if (!defined('ABSPATH')) {
	exit;
}

// Sva biznis logika je u helpers.php — template sadrži samo prezentaciju
$data = tersa_get_header_template_data();

$home_url                = $data['home_url'];
$site_name               = $data['site_name'];
$logo_markup             = $data['logo_markup'];
$nav_markup              = $data['nav_markup'];
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

					<nav
						class="site-header__nav site-header__nav--desktop"
						aria-label="<?php esc_attr_e('Primary navigation', 'tersa-shop'); ?>"
						data-submenu-label="<?php echo esc_attr(__('Open submenu for %s', 'tersa-shop')); ?>"
					>
						<?php echo $nav_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</nav>

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

								<span class="site-header__badge site-header__badge--wishlist" aria-hidden="true">
									<?php echo esc_html($wishlist_count); ?>
								</span>
							</a>
						<?php endif; ?>

						<?php if (class_exists('WooCommerce')) : ?>
							<a
								class="site-header__icon-link site-header__icon-link--cart"
								href="<?php echo esc_url(wc_get_cart_url()); ?>"
								aria-label="<?php echo esc_attr(tersa_get_cart_aria_label()); ?>"
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
							data-search-label="<?php echo esc_attr(__('Search for products', 'tersa-shop')); ?>"
						>
							<?php echo esc_html(sprintf(__('Search for products (%d)', 'tersa-shop'), 0)); ?>
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
							<?php echo do_shortcode('[aws_search_form]'); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div
			id="mobile-navigation"
			class="site-header__mobile-panel"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e('Mobile menu', 'tersa-shop'); ?>"
			data-mobile-nav-label="<?php esc_attr_e('Mobile navigation', 'tersa-shop'); ?>"
			inert>

			<!-- Logo (centar) + close dugme (desno) -->
			<div class="mobile-nav__header">
				<a
					href="<?php echo esc_url($home_url); ?>"
					class="mobile-nav__logo-link"
					aria-hidden="true"
					tabindex="-1"
				>
					<?php echo $logo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>

				<button
					type="button"
					class="mobile-nav__close"
					aria-label="<?php esc_attr_e('Close menu', 'tersa-shop'); ?>"
					data-mobile-close
				>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
			</div>

			<!-- Navigacija — JS klonira desktop nav pri prvom otvaranju (sprečava duplikate u HTML izvoru) -->
			<div class="mobile-nav__body">
			</div>

		</div>
	</header>
