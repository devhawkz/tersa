<?php
if (!defined('ABSPATH')) {
	exit;
}

// Statički keš je već popunjen u header.php — nema ponovnog računanja
$data        = tersa_get_header_template_data();
$home_url    = $data['home_url'];
$logo_markup = $data['logo_markup'];
$mobile_logo_markup = str_replace(' loading="eager"', ' loading="lazy"', $logo_markup);
?>
<div class="site-header__mobile-backdrop" data-mobile-close aria-hidden="true"></div>

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
			<?php echo $mobile_logo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
