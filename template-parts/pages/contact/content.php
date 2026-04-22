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

$card_heading    = $get ? $get('contact_card_heading', __('Contact Us', 'tersa-shop')) : __('Contact Us', 'tersa-shop');
$call_label       = $get ? $get('contact_call_label', __('Call to Us:', 'tersa-shop')) : __('Call to Us:', 'tersa-shop');
$call_text        = $get ? $get('contact_call_text', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$call_text_second = $get ? $get('contact_call_text_second', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$phone_default    = $get_option ? $get_option('company_phone_primary', '+385 98 252 562') : '+385 98 252 562';
$phone_2_default  = $get_option ? $get_option('company_phone_secondary', '+385 98 252 562') : '+385 98 252 562';
$phone            = $get ? $get('contact_phone', $phone_default) : $phone_default;
$phone_second     = $get ? $get('contact_phone_second', $phone_2_default) : $phone_2_default;
$write_label      = $get ? $get('contact_write_label', __('Write to Us:', 'tersa-shop')) : __('Write to Us:', 'tersa-shop');
$write_text       = $get ? $get('contact_write_text', __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop')) : __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop');
$email_label      = $get ? $get('contact_email_label', __('Email:', 'tersa-shop')) : __('Email:', 'tersa-shop');
$email_default    = $get_option ? $get_option('company_email', 'tersa@tersa.hr') : 'tersa@tersa.hr';
$email            = $get ? $get('contact_email', $email_default) : $email_default;
$hq_label         = $get ? $get('contact_hq_label', __('Headquarter:', 'tersa-shop')) : __('Headquarter:', 'tersa-shop');
$hq_hours_week    = $get ? $get('contact_hq_hours_week', __('Monday – Friday: 9:00–20:00', 'tersa-shop')) : __('Monday – Friday: 9:00–20:00', 'tersa-shop');
$hq_hours_sat     = $get ? $get('contact_hq_hours_sat', __('Saturday: 11:00 – 15:00', 'tersa-shop')) : __('Saturday: 11:00 – 15:00', 'tersa-shop');
$hq_address_default = $get_option ? $get_option('company_address', __('Nikole Tesle 71, 31553 Črnkovci', 'tersa-shop')) : __('Nikole Tesle 71, 31553 Črnkovci', 'tersa-shop');
$hq_address       = $get ? $get('contact_hq_address', $hq_address_default) : $hq_address_default;
$form_heading     = $get ? $get('contact_form_heading', __('We would love to hear from you.', 'tersa-shop')) : __('We would love to hear from you.', 'tersa-shop');
$contact_cf7_shortcode = $get_option ? $get_option('contact_cf7_shortcode', '[contact-form-7 id="6a84fef" title="Kontakt"]') : '[contact-form-7 id="6a84fef" title="Kontakt"]';

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
					<div class="contact-card__block">
						<p style="margin:0">
							<a href="tel:<?php echo esc_attr($phone_href_second); ?>"><?php echo esc_html($phone_second); ?></a>
						</p>
						<p><?php echo esc_html($call_text_second); ?></p>
					</div>

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
						<p><?php echo esc_html($hq_hours_sat); ?></p>
						<p><?php echo esc_html($hq_address); ?></p>
					</div>
				</div>

				<div class="contact-card__form">
					<h2 class="contact-card__heading">
						<?php echo esc_html($form_heading); ?>
					</h2>

					<div class="contact-card__form-wrap">
						<?php
						if (function_exists('tersa_safe_cf7_shortcode_output')) {
							echo wp_kses_post(tersa_safe_cf7_shortcode_output((string) $contact_cf7_shortcode));
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>