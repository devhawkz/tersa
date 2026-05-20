<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_the_ID();
$settings_page_id = function_exists('tersa_get_global_settings_page_id') ? tersa_get_global_settings_page_id() : 0;
$get = function_exists('get_field') ? function ($key, $fallback = '') use ($page_id) {
	$v = get_field($key, $page_id);
	return (is_string($v) && trim($v) !== '') ? $v : $fallback;
} : null;
$get_option = function_exists('get_field') ? function ($key, $fallback = '') use ($settings_page_id) {
	if (!$settings_page_id) {
		return $fallback;
	}
	$v = get_field($key, $settings_page_id);
	return (is_string($v) && trim($v) !== '') ? $v : $fallback;
} : null;

$impressum = function_exists('tersa_get_company_impressum') ? tersa_get_company_impressum() : [];

$card_heading    = $get ? $get('contact_card_heading', __('Contact Us', 'tersa-shop')) : __('Contact Us', 'tersa-shop');
$call_label       = $get ? $get('contact_call_label', __('Call to Us:', 'tersa-shop')) : __('Call to Us:', 'tersa-shop');
$call_text        = $get ? $get('contact_call_text', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$call_text_second = $get ? $get('contact_call_text_second', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$phone_default    = $impressum['phone'] ?? ($get_option ? $get_option('company_phone_primary', '031/355 900') : '031/355 900');
$phone_2_default  = $impressum['phone_secondary'] ?? ($get_option ? $get_option('company_phone_secondary', '') : '');
$phone            = $get ? $get('contact_phone', $phone_default) : $phone_default;
$phone_second     = $get ? $get('contact_phone_second', $phone_2_default) : $phone_2_default;
$write_label      = $get ? $get('contact_write_label', __('Write to Us:', 'tersa-shop')) : __('Write to Us:', 'tersa-shop');
$write_text       = $get ? $get('contact_write_text', __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop')) : __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop');
$email_label      = $get ? $get('contact_email_label', __('Email:', 'tersa-shop')) : __('Email:', 'tersa-shop');
$email_default    = $impressum['email'] ?? ($get_option ? $get_option('company_email', 'tersa@tersa.hr') : 'tersa@tersa.hr');
$email            = $get ? $get('contact_email', $email_default) : $email_default;
$hq_label         = $get ? $get('contact_hq_label', __('Headquarter:', 'tersa-shop')) : __('Headquarter:', 'tersa-shop');
$hq_hours_week    = $get ? $get('contact_hq_hours_week', $impressum['support_hours'] ?? __('Pon – Pet: 08:00 – 14:00', 'tersa-shop')) : ($impressum['support_hours'] ?? __('Pon – Pet: 08:00 – 14:00', 'tersa-shop'));
$hq_hours_sat     = $get ? $get('contact_hq_hours_sat', '') : '';
$hq_address_default = $impressum['address'] ?? ($get_option ? $get_option('company_address', __('Nikole Tesle 71, 31551 Črnkovci', 'tersa-shop')) : __('Nikole Tesle 71, 31551 Črnkovci', 'tersa-shop'));
$hq_address       = $get ? $get('contact_hq_address', $hq_address_default) : $hq_address_default;
$form_heading     = $get ? $get('contact_form_heading', __('We would love to hear from you.', 'tersa-shop')) : __('We would love to hear from you.', 'tersa-shop');
$contact_cf7_shortcode = $get_option ? $get_option('contact_cf7_shortcode', '[contact-form-7 id="6a84fef" title="Kontakt"]') : '[contact-form-7 id="6a84fef" title="Kontakt"]';
$hq_address_markup = function_exists('tersa_safe_rich_text')
	? tersa_safe_rich_text((string) $hq_address)
	: wp_kses_post(wpautop((string) $hq_address));

$phone_href = preg_replace('/\s+/', '', $phone);
$phone_href_second = preg_replace('/\s+/', '', $phone_second);
?>

<section class="contact-section">
	<div class="page-shell">
		<div class="contact-card card">
			<div class="contact-card__grid">
				<div class="contact-card__info">
					<h2 class="contact-card__heading">
						<?php echo esc_html($card_heading); ?>
					</h2>

					<div class="contact-card__block">
						<h3 class="contact-card__label"><?php echo esc_html($call_label); ?></h3>
						<p style="margin:0">
							<a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a>
						</p>
						<p><?php echo esc_html($call_text); ?></p>
					</div>
					<?php if (!empty($phone_second)) : ?>
						<div class="contact-card__block">
							<p style="margin:0">
								<a href="tel:<?php echo esc_attr($phone_href_second); ?>"><?php echo esc_html($phone_second); ?></a>
							</p>
							<?php if (!empty($call_text_second)) : ?>
								<p><?php echo esc_html($call_text_second); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="contact-card__block">
						<h3 class="contact-card__label"><?php echo esc_html($write_label); ?></h3>
						<p><?php echo esc_html($write_text); ?></p>
						<p>
							<?php echo esc_html($email_label); ?>
							<a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
						</p>
					</div>

					<div class="contact-card__block">
						<h3 class="contact-card__label"><?php echo esc_html($hq_label); ?></h3>
						<p><?php echo esc_html($hq_hours_week); ?></p>
						<?php if (!empty($hq_hours_sat)) : ?>
							<p><?php echo esc_html($hq_hours_sat); ?></p>
						<?php endif; ?>
						<div class="contact-card__rich-text">
							<?php echo $hq_address_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

				
				</div>
								
				<div class="contact-card__form">
					<h2 class="contact-card__heading">
						<?php echo esc_html($form_heading); ?>
					</h2>

					<div class="contact-card__form-wrap">
					<?php
					if (function_exists('tersa_safe_cf7_shortcode_output')) {
						// tersa_safe_cf7_shortcode_output() validateira strogi regex — samo CF7 shortcode.
						// wp_kses_post() bi uklonilo <form> elemente pa ga ne koristimo ovdje.
						echo tersa_safe_cf7_shortcode_output((string) $contact_cf7_shortcode); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					</div>
				</div>
			</div>
			<?php
					/**
					 * Impressum — pravni podaci o tvrtki obavezni za CorvusPay aktivaciju
					 * i sukladnost sa HR Zakonom o elektroničkoj trgovini (čl. 6).
					 *
					 * Klijent (Tersa d.o.o.) je u dokumentu "Popis podataka i sadržaja - upitnik"
					 * eksplicitno potvrdio da podaci o firmi idu na postojeću Kontakt stranicu.
					 */
					if (!empty($impressum)) : ?>
						<div class="contact-card__block contact-card__block--impressum">
							<h3 class="contact-card__label"><?php esc_html_e('Podaci o tvrtki:', 'tersa-shop'); ?></h3>
							<dl class="contact-card__impressum">
								<dt><?php esc_html_e('Naziv:', 'tersa-shop'); ?></dt>
								<dd><?php echo esc_html($impressum['full_name']); ?></dd>

								<?php if (!empty($impressum['activity'])) : ?>
									<dt><?php esc_html_e('Djelatnost:', 'tersa-shop'); ?></dt>
									<dd><?php echo esc_html($impressum['activity']); ?></dd>
								<?php endif; ?>

								<dt><?php esc_html_e('OIB:', 'tersa-shop'); ?></dt>
								<dd><?php echo esc_html($impressum['oib']); ?></dd>

								<dt><?php esc_html_e('MBS:', 'tersa-shop'); ?></dt>
								<dd><?php echo esc_html($impressum['mbs']); ?></dd>

								<dt><?php esc_html_e('Registar:', 'tersa-shop'); ?></dt>
								<dd><?php echo esc_html($impressum['court']); ?></dd>

								<?php if (!empty($impressum['director'])) : ?>
									<dt><?php esc_html_e('Odgovorna osoba:', 'tersa-shop'); ?></dt>
									<dd><?php echo esc_html($impressum['director']); ?></dd>
								<?php endif; ?>

								<?php if (!empty($impressum['share_capital'])) : ?>
									<dt><?php esc_html_e('Temeljni kapital:', 'tersa-shop'); ?></dt>
									<dd><?php echo esc_html($impressum['share_capital']); ?></dd>
								<?php endif; ?>

								<?php if (!empty($impressum['iban'])) : ?>
									<dt><?php esc_html_e('IBAN:', 'tersa-shop'); ?></dt>
									<dd>
										<?php echo esc_html($impressum['iban']); ?>
										<?php if (!empty($impressum['bank'])) : ?>
											<small>(<?php echo esc_html($impressum['bank']); ?>)</small>
										<?php endif; ?>
									</dd>
								<?php endif; ?>

								<?php if (!empty($impressum['vat_id'])) : ?>
									<dt><?php esc_html_e('PDV ID:', 'tersa-shop'); ?></dt>
									<dd><?php echo esc_html($impressum['vat_id']); ?></dd>
								<?php endif; ?>

								<?php if (!empty($impressum['email_complaints']) && $impressum['email_complaints'] !== $impressum['email']) : ?>
									<dt><?php esc_html_e('Reklamacije i povrati:', 'tersa-shop'); ?></dt>
									<dd>
										<a href="mailto:<?php echo esc_attr($impressum['email_complaints']); ?>">
											<?php echo esc_html($impressum['email_complaints']); ?>
										</a>
									</dd>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>
		</div>
	</div>
</section>