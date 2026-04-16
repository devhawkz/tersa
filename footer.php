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

$company_name    = !empty($company_settings['company_name'])
	? $company_settings['company_name']
	: 'Tersa d.o.o.';
$company_activity = !empty($company_settings['company_activity'])
	? $company_settings['company_activity']
	: __('Prerada drva i trgovina drvnim proizvodima', 'tersa-shop');
$company_address  = !empty($company_settings['company_address'])
	? $company_settings['company_address']
	: __('Nikole Tesle 71, 31553 Črnk​ovci', 'tersa-shop');
$company_email    = !empty($company_settings['company_email'])
	? $company_settings['company_email']
	: 'tersa@tersa.hr';
$footer_newsletter_shortcode = !empty($company_settings['footer_newsletter_cf7_shortcode'])
	? $company_settings['footer_newsletter_cf7_shortcode']
	: '[contact-form-7 id="02a3794" title="Contact form 1"]';

$about_fallback = [
	[
		'label' => __('Our Story', 'tersa-shop'),
		'url'   => home_url('/o-nama/'),
	],
	[
		'label' => __('Careers', 'tersa-shop'),
		'url'   => home_url('/karijera/'),
	],
	[
		'label' => __('Influencers', 'tersa-shop'),
		'url'   => home_url('/suradnja/'),
	],
	[
		'label' => __('Join our team', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
];

$services_fallback = [
	[
		'label' => __('Contact Us', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
	[
		'label' => __('Customer Service', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
	[
		'label' => __('Find Store', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
	[
		'label' => __('Book appointment', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
	[
		'label' => __('Shipping & Returns', 'tersa-shop'),
		'url'   => home_url('/dostava-i-povrat/'),
	],
];

$legal_fallback = [
	[
		'label' => __('Privacy Policy', 'tersa-shop'),
		'url'   => home_url('/politika-privatnosti/'),
	],
	[
		'label' => __('Help', 'tersa-shop'),
		'url'   => home_url('/kontakt/'),
	],
	[
		'label' => __('FAQs', 'tersa-shop'),
		'url'   => home_url('/faq/'),
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
							<p><?php echo esc_html($company_address); ?></p>
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
						if (function_exists('do_shortcode')) {
							echo do_shortcode($footer_newsletter_shortcode);
						}
						?>
					</section>
				</div>
			</div>
		</div>

		<div class="site-footer__bottom">
			<div class="container">
				<div class="site-footer__bottom-inner">
					<nav class="site-footer__legal" aria-label="<?php esc_attr_e('Legal footer navigation', 'tersa-shop'); ?>">
						<?php if (has_nav_menu('footer_legal')) : ?>
							<?php
							wp_nav_menu([
								'theme_location' => 'footer_legal',
								'container'      => false,
								'menu_class'     => 'site-footer__legal-menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							]);
							?>
						<?php else : ?>
							<ul class="site-footer__legal-menu">
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

					<ul class="site-footer__payments" aria-label="<?php esc_attr_e('Accepted payment methods', 'tersa-shop'); ?>">
	<li class="site-footer__payment-badge site-footer__payment-badge--icon" aria-label="<?php esc_attr_e('Apple Pay', 'tersa-shop'); ?>">
		<svg class="site-footer__payment-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" aria-hidden="true" focusable="false">
			<path d="M116.9 158.5c-7.5 8.9-19.5 15.9-31.5 14.9-1.5-12 4.4-24.8 11.3-32.6 7.5-9.1 20.6-15.6 31.3-16.1 1.2 12.4-3.7 24.7-11.1 33.8m10.9 17.2c-17.4-1-32.3 9.9-40.5 9.9-8.4 0-21-9.4-34.8-9.1-17.9.3-34.5 10.4-43.6 26.5-18.8 32.3-4.9 80 13.3 106.3 8.9 13 19.5 27.3 33.5 26.8 13.3-.5 18.5-8.6 34.5-8.6 16.1 0 20.8 8.6 34.8 8.4 14.5-.3 23.6-13 32.5-26 10.1-14.8 14.3-29.1 14.5-29.9-.3-.3-28-10.9-28.3-42.9-.3-26.8 21.9-39.5 22.9-40.3-12.5-18.6-32-20.6-38.8-21.1m100.4-36.2v194.9h30.3v-66.6h41.9c38.3 0 65.1-26.3 65.1-64.3s-26.4-64-64.1-64h-73.2zm30.3 25.5h34.9c26.3 0 41.3 14 41.3 38.6s-15 38.8-41.4 38.8h-34.8V165zm162.2 170.9c19 0 36.6-9.6 44.6-24.9h.6v23.4h28v-97c0-28.1-22.5-46.3-57.1-46.3-32.1 0-55.9 18.4-56.8 43.6h27.3c2.3-12 13.4-19.9 28.6-19.9 18.5 0 28.9 8.6 28.9 24.5v10.8l-37.8 2.3c-35.1 2.1-54.1 16.5-54.1 41.5.1 25.2 19.7 42 47.8 42zm8.2-23.1c-16.1 0-26.4-7.8-26.4-19.6 0-12.3 9.9-19.4 28.8-20.5l33.6-2.1v11c0 18.2-15.5 31.2-36 31.2zm102.5 74.6c29.5 0 43.4-11.3 55.5-45.4L640 193h-30.8l-35.6 115.1h-.6L537.4 193h-31.6L557 334.9l-2.8 8.6c-4.6 14.6-12.1 20.3-25.5 20.3-2.4 0-7-.3-8.9-.5v23.4c1.8.4 9.3.7 11.6.7z"/>
		</svg>
		<span class="screen-reader-text"><?php esc_html_e('Apple Pay', 'tersa-shop'); ?></span>
	</li>

	<li class="site-footer__payment-badge">
		<svg class="site-footer__payment-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
			<path d="M105.7 279L105.7 320.2L162.8 320.2C161.6 326.8 159.2 333.1 155.6 338.7C152 344.3 147.2 349.1 141.7 352.8C132.2 359.4 120 363.1 105.7 363.1C78.1 363.1 54.8 344.2 46.4 318.9C42 305.6 42 291.2 46.4 277.9C54.8 252.4 78.1 233.5 105.7 233.5C113.2 233.4 120.6 234.7 127.6 237.5C134.6 240.3 140.9 244.4 146.2 249.6L176.5 219C157.4 200.9 132.1 190.9 105.8 191.2C86.1 191.3 66.9 196.9 50.2 207.3C33.5 217.7 20.1 232.6 11.4 250.3C3.9 265.2 0 281.7 0 298.4C0 315.1 3.9 331.6 11.3 346.5L11.3 346.7C20 364.4 33.4 379.2 50.1 389.7C66.8 400.2 86 405.7 105.7 405.7C134.2 405.7 158.2 396.2 175.7 379.8C195.7 361.2 207.1 333.6 207.1 300.9C207.1 293.6 206.5 286.3 205.3 279.1L105.6 279.1zM495.1 275C485 265.6 471.2 260.9 453.7 260.9C431.2 260.9 414.4 269.2 403.2 285.8L424.1 299C431.7 287.7 442.2 282 455.4 282C463.8 282 471.9 285.2 478.1 290.8C481.1 293.4 483.6 296.7 485.2 300.4C486.8 304.1 487.7 308 487.7 312.1L487.7 317.6C478.6 312.5 467.1 309.8 453.1 309.8C436.7 309.8 423.5 313.7 413.6 321.6C403.7 329.5 398.8 339.9 398.8 353.2C398.6 359.1 399.8 365 402.2 370.4C404.6 375.8 408.2 380.6 412.7 384.5C421.9 392.8 433.7 397 447.5 397C463.8 397 476.7 389.7 486.5 375.1L487.5 375.1L487.5 392.8L510.1 392.8L510.1 314.1C510.2 297.5 505.2 284.4 495.1 275.1zM475.9 364.3C472.4 367.8 468.3 370.6 463.7 372.5C459.1 374.4 454.2 375.4 449.3 375.4C442.6 375.5 436.2 373.3 431 369.2C428.6 367.4 426.6 365 425.3 362.3C424 359.6 423.3 356.6 423.3 353.6C423.3 346.6 426.5 340.8 432.8 336.2C439.1 331.6 447.3 329.2 456.9 329.2C470.1 329 480.4 332 487.7 338C487.7 348.1 483.7 356.9 476 364.4zM382.2 222.3C376.9 217 370.6 212.8 363.6 210C356.6 207.2 349.2 205.8 341.7 206L279 206L279 392.7L302.6 392.7L302.6 317.1L341.6 317.1C357.6 317.1 371.1 311.7 382.1 301.2C383 300.3 383.9 299.4 384.7 298.5C394.3 288 399.5 274.1 399 259.9C398.5 245.7 392.4 232.2 382.1 222.3L382.1 222.3zM365.6 284.5C362.6 287.7 359 290.2 355 291.9C351 293.6 346.6 294.4 342.3 294.2L302.7 294.2L302.7 229L342.3 229C350.8 229 358.9 232.3 364.9 238.2C371 244.3 374.5 252.5 374.7 261.2C374.9 269.9 371.6 278.2 365.7 284.5L365.7 284.5zM614.3 265L577.8 356.7L577.3 356.7L539.9 265L514.2 265L566 384.6L536.6 448.9L560.9 448.9L639.9 265L614.2 265z"/>
		</svg>
	</li>
</ul>
				</div>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>