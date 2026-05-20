<?php
if (!defined('ABSPATH')) {
	exit;
}

$site_name = get_bloginfo('name');

/**
 * Footer logo:
 * koristi custom logo ako postoji,
 * fallback na assets/img/tersa-logo.png
 */
$footer_logo_markup = '';
$custom_logo_id     = (int) get_theme_mod('custom_logo');

if ($custom_logo_id) {
	$footer_logo_markup = wp_get_attachment_image(
		$custom_logo_id,
		'tersa-logo',
		false,
		[
			'class'    => 'site-footer__logo-image',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => $site_name,
		]
	);
}

if (!$footer_logo_markup && function_exists('tersa_get_theme_fallback_logo_markup')) {
	$footer_logo_markup = tersa_get_theme_fallback_logo_markup('site-footer__logo-image', 'lazy', 'async', '');
}

$footer_settings  = function_exists('tersa_get_footer_settings') ? tersa_get_footer_settings() : [];
$company_settings = function_exists('tersa_get_company_settings') ? tersa_get_company_settings() : [];
$impressum        = function_exists('tersa_get_company_impressum') ? tersa_get_company_impressum() : [];

$company_name     = $impressum['name']     ?? 'Tersa d.o.o.';
$company_activity = $impressum['activity'] ?? __('Prerada drva i trgovina drvnim proizvodima', 'tersa-shop');
$company_address  = $impressum['address']  ?? __('Nikole Tesle 71, 31551 Črnkovci', 'tersa-shop');
$company_email    = $impressum['email']    ?? 'tersa@tersa.hr';
$company_address_markup = function_exists('tersa_safe_rich_text')
	? tersa_safe_rich_text((string) $company_address)
	: wp_kses_post(wpautop((string) $company_address));
$company_address_plain = wp_strip_all_tags((string) $company_address);
$footer_newsletter_shortcode = !empty($company_settings['footer_newsletter_cf7_shortcode'])
	? $company_settings['footer_newsletter_cf7_shortcode']
	: '[contact-form-7 id="02a3794" title="Contact form 1"]';

$tersa_payment_methods   = function_exists('tersa_get_payment_methods') ? tersa_get_payment_methods() : [];
$tersa_security_badges   = function_exists('tersa_get_security_badges') ? tersa_get_security_badges('light') : [];
$tersa_payments_dir_path = get_template_directory() . '/assets/img/payments/';
$tersa_payments_dir_uri  = get_template_directory_uri() . '/assets/img/payments/';

$_pll_url = function_exists('tersa_pll_page_url') ? 'tersa_pll_page_url' : function (string $s): string { return home_url('/' . $s . '/'); };

$about_fallback = [
	[
		'label' => __('Our Story', 'tersa-shop'),
		'url'   => $_pll_url('o-nama'),
	],
	[
		'label' => __('Careers', 'tersa-shop'),
		'url'   => $_pll_url('karijera'),
	],
	[
		'label' => __('Influencers', 'tersa-shop'),
		'url'   => $_pll_url('suradnja'),
	],
	[
		'label' => __('Join our team', 'tersa-shop'),
		'url'   => $_pll_url('kontakt'),
	],
];

$legal_fallback = [
	[
		'label' => __('Privacy Policy', 'tersa-shop'),
		'url'   => $_pll_url('politika-privatnosti'),
	],
	[
		'label' => __('Help', 'tersa-shop'),
		'url'   => $_pll_url('kontakt'),
	],
	[
		'label' => __('FAQs', 'tersa-shop'),
		'url'   => $_pll_url('faq'),
	],
];
?>
	<footer class="site-footer" role="contentinfo">
		<div class="site-footer__main">
			<div class="container">
				<div class="site-footer__grid">
					<section class="site-footer__brand-col" aria-labelledby="footer-company-heading">
						<div class="site-footer__logo-wrap">
							<a href="<?php echo esc_url(home_url('/')); ?>" class="site-footer__logo-link" aria-label="<?php echo esc_attr(sprintf(__('Go to homepage, %s', 'tersa-shop'), $site_name)); ?>">
								<?php echo $footer_logo_markup ?: '<span class="site-footer__logo-text">' . esc_html($site_name) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</div>

						<h2 id="footer-company-heading" class="screen-reader-text">
							<?php esc_html_e('Company information', 'tersa-shop'); ?>
						</h2>

						<address class="site-footer__company">
							<p><?php echo esc_html($company_activity); ?></p>
							<div class="site-footer__company-address">
								<?php echo $company_address_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<p>
								<span class="site-footer__label"><?php esc_html_e('E-mail:', 'tersa-shop'); ?></span>
								<a href="mailto:<?php echo esc_attr($company_email); ?>"><?php echo esc_html($company_email); ?></a>
							</p>
							
						</address>
					</section>

					<nav class="site-footer__nav-col" aria-label="<?php esc_attr_e('About footer navigation', 'tersa-shop'); ?>">
						<h2 class="site-footer__heading"><?php esc_html_e('KORISNE POVEZNICE', 'tersa-shop'); ?></h2>

						<?php if (has_nav_menu('footer_about')) : ?>
							<?php
							wp_nav_menu([
								'theme_location' => 'footer_about',
								'container'      => false,
								'menu_class'     => 'site-footer__menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							]);
							?>
						<?php else : ?>
							<ul class="site-footer__menu">
								<?php foreach ($about_fallback as $item) : ?>
									<li>
										<a href="<?php echo esc_url($item['url']); ?>">
											<?php echo esc_html($item['label']); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</nav>

					<nav class="site-footer__nav-col" aria-label="<?php esc_attr_e('Legal footer navigation', 'tersa-shop'); ?>">
						<h2 class="site-footer__heading"><?php esc_html_e('PRAVNE STRANICE', 'tersa-shop'); ?></h2>

						<?php if (has_nav_menu('footer_legal')) : ?>
							<?php
							wp_nav_menu([
								'theme_location' => 'footer_legal',
								'container'      => false,
								'menu_class'     => 'site-footer__menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							]);
							?>
						<?php else : ?>
							<ul class="site-footer__menu">
								<?php foreach ($legal_fallback as $item) : ?>
									<li>
										<a href="<?php echo esc_url($item['url']); ?>">
											<?php echo esc_html($item['label']); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</nav>

					<section class="site-footer__newsletter-col" aria-labelledby="footer-newsletter-heading">
						<?php
						$newsletter_heading = !empty($footer_settings['footer_newsletter_heading'])
							? $footer_settings['footer_newsletter_heading']
							: __('Prijavi se na naš newsletter', 'tersa-shop');
						$newsletter_text = !empty($footer_settings['footer_newsletter_text'])
							? $footer_settings['footer_newsletter_text']
							: __('Promotivna poruka za newsletter', 'tersa-shop');
						?>
						<h2 id="footer-newsletter-heading" class="site-footer__heading">
							<?php echo esc_html($newsletter_heading); ?>
						</h2>

						<p class="site-footer__newsletter-text">
							<?php echo esc_html($newsletter_text); ?>
						</p>

						<?php
						// Contact Form 7 shortcode — forma sama dobija klase i markup.
						// Stilove primenjuješ tako što u CF7 form template-u dodaš klase
						// npr. text polju: [email* your-email class:site-footer__newsletter-input]
						// i dugmetu: [submit class:site-footer__newsletter-button \"Prijavi se\"]
					if (function_exists('tersa_safe_cf7_shortcode_output')) {
						// tersa_safe_cf7_shortcode_output() validateira strogi regex — samo CF7 shortcode.
						// wp_kses_post() bi uklonilo <form> elemente pa ga ne koristimo ovdje.
						echo tersa_safe_cf7_shortcode_output((string) $footer_newsletter_shortcode); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
						?>
					</section>
				</div>
			</div>
		</div>

		<div class="site-footer__bottom">
			<div class="container">
				<?php $corvuspay_logo = $tersa_payments_dir_path . 'corvuspay.svg'; ?>
				<div class="site-footer__bottom-inner">

					<div class="site-footer__bottom-brand">
						<?php if (file_exists($corvuspay_logo)) : ?>
							<a class="site-footer__corvuspay"
								href="https://www.corvuspay.com/"
								target="_blank"
								rel="noopener noreferrer"
								aria-label="<?php esc_attr_e('CorvusPay — sigurno online plaćanje karticama (otvara se u novom prozoru)', 'tersa-shop'); ?>">
								<img src="<?php echo esc_url($tersa_payments_dir_uri . 'corvuspay.svg'); ?>"
									alt="CorvusPay"
									class="site-footer__corvuspay-icon"
									loading="lazy"
									decoding="async" />
							</a>
						<?php endif; ?>
					</div>

					<div class="site-footer__bottom-center">
						<p class="site-footer__copyright">
							<?php
							$company_link = sprintf(
								'<a href="%s">%s</a>',
								esc_url(home_url('/')),
								esc_html($company_name)
							);

							$copyright_markup = sprintf(
								__('© %1$s %2$s, sva prava pridržana', 'tersa-shop'),
								date_i18n('Y'),
								$company_link
							);

							echo wp_kses_post($copyright_markup);
							?>
						</p>
					</div>

					<?php if (!empty($tersa_payment_methods) || !empty($tersa_security_badges)) : ?>
						<div class="site-footer__payments-wrap"
							aria-label="<?php esc_attr_e('Prihvaćene kartice i sigurnosni programi', 'tersa-shop'); ?>">
							<div class="site-footer__payments-row">

								<?php if (!empty($tersa_payment_methods)) : ?>
									<ul class="site-footer__payments site-footer__payments-rack"
										aria-label="<?php esc_attr_e('Prihvaćene kartice', 'tersa-shop'); ?>">
										<?php foreach ($tersa_payment_methods as $pm) :
											$file = $pm['slug'] . '.' . $pm['ext'];
											if (!file_exists($tersa_payments_dir_path . $file)) {
												continue;
											}
											?>
											<li class="site-footer__payment-badge">
												<a href="<?php echo esc_url($pm['url']); ?>"
													target="_blank"
													rel="noopener noreferrer"
													aria-label="<?php echo esc_attr(sprintf(__('%s — službena stranica (otvara se u novom prozoru)', 'tersa-shop'), $pm['label'])); ?>">
													<img src="<?php echo esc_url($tersa_payments_dir_uri . $file); ?>"
														alt="<?php echo esc_attr($pm['label']); ?>"
														class="site-footer__payment-icon"
														loading="lazy"
														decoding="async" />
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>

								<?php if (!empty($tersa_security_badges)) : ?>
									<ul class="site-footer__payments-rack site-footer__security-rack"
										aria-label="<?php esc_attr_e('Sigurnosni programi', 'tersa-shop'); ?>">
										<?php foreach ($tersa_security_badges as $sb) :
											$file = $sb['slug'] . '.' . $sb['ext'];
											if (!file_exists($tersa_payments_dir_path . 'security/' . $file)) {
												continue;
											}
											?>
											<li class="site-footer__payment-badge site-footer__payment-badge--security">
												<a href="<?php echo esc_url($sb['url']); ?>"
													target="_blank"
													rel="noopener noreferrer"
													aria-label="<?php echo esc_attr(sprintf(__('%s — informacije o sigurnosnom programu (otvara se u novom prozoru)', 'tersa-shop'), $sb['label'])); ?>">
													<img src="<?php echo esc_url($tersa_payments_dir_uri . 'security/' . $file); ?>"
														alt="<?php echo esc_attr($sb['label']); ?>"
														class="site-footer__payment-icon"
														loading="lazy"
														decoding="async" />
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>

							</div>
						</div>
					<?php endif; ?>

				</div>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>