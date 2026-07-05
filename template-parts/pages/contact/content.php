<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_the_ID();
$get = function_exists('get_field') ? function ($key, $fallback = '') use ($page_id) {
	$v = get_field($key, $page_id);
	return (is_string($v) && trim($v) !== '') ? $v : $fallback;
} : null;

$company_settings = function_exists('tersa_get_company_settings') ? tersa_get_company_settings() : [];
$impressum = function_exists('tersa_get_company_impressum') ? tersa_get_company_impressum() : [];
$company_value = static function (string $key, string $fallback = '') use ($company_settings): string {
	$value = (string) ($company_settings[$key] ?? '');

	return trim($value) !== '' ? $value : $fallback;
};

$card_heading    = $get ? $get('contact_card_heading', __('Contact Us', 'tersa-shop')) : __('Contact Us', 'tersa-shop');
$call_label       = $get ? $get('contact_call_label', __('Call to Us:', 'tersa-shop')) : __('Call to Us:', 'tersa-shop');
$call_text        = $get ? $get('contact_call_text', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$call_text_second = $get ? $get('contact_call_text_second', __("We're available 24/7, 7 days a week.", 'tersa-shop')) : __("We're available 24/7, 7 days a week.", 'tersa-shop');
$phone_default    = $impressum['phone'] ?? $company_value('company_phone_primary', '031/355 900');
$phone_2_default  = $impressum['phone_secondary'] ?? $company_value('company_phone_secondary', '');
$phone            = $get ? $get('contact_phone', $phone_default) : $phone_default;
$phone_second     = $get ? $get('contact_phone_second', $phone_2_default) : $phone_2_default;
$write_label      = $get ? $get('contact_write_label', __('Write to Us:', 'tersa-shop')) : __('Write to Us:', 'tersa-shop');
$write_text       = $get ? $get('contact_write_text', __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop')) : __('Fill out our form and we will contact you within 24 hours.', 'tersa-shop');
$email_label      = $get ? $get('contact_email_label', __('Email:', 'tersa-shop')) : __('Email:', 'tersa-shop');
$email_default    = $impressum['email'] ?? $company_value('company_email', 'tersa@tersa.hr');
$email            = $get ? $get('contact_email', $email_default) : $email_default;
$hq_label         = $get ? $get('contact_hq_label', __('Headquarter:', 'tersa-shop')) : __('Headquarter:', 'tersa-shop');
$hq_hours_week    = $get ? $get('contact_hq_hours_week', $impressum['support_hours'] ?? __('Pon – Pet: 08:00 – 14:00', 'tersa-shop')) : ($impressum['support_hours'] ?? __('Pon – Pet: 08:00 – 14:00', 'tersa-shop'));
$hq_hours_sat     = $get ? $get('contact_hq_hours_sat', '') : '';
$hq_address_default = $impressum['address'] ?? $company_value('company_address', __('Nikole Tesle 71, 31551 Črnkovci', 'tersa-shop'));
$hq_address       = $get ? $get('contact_hq_address', $hq_address_default) : $hq_address_default;
$form_heading     = $get ? $get('contact_form_heading', __('We would love to hear from you.', 'tersa-shop')) : __('We would love to hear from you.', 'tersa-shop');
$contact_cf7_shortcode = $company_value('contact_cf7_shortcode');
$hq_address_markup = function_exists('tersa_safe_rich_text')
	? tersa_safe_rich_text((string) $hq_address)
	: wp_kses_post(wpautop((string) $hq_address));
$contact_form_markup = ($contact_cf7_shortcode !== '' && function_exists('tersa_safe_cf7_shortcode_output'))
	? tersa_safe_cf7_shortcode_output((string) $contact_cf7_shortcode)
	: '';

$phone_href = preg_replace('/[^0-9+]/', '', (string) $phone);
$phone_href_second = preg_replace('/[^0-9+]/', '', (string) $phone_second);
$email_href = sanitize_email((string) $email);
$email_complaints_href = !empty($impressum['email_complaints'])
	? sanitize_email((string) $impressum['email_complaints'])
	: '';

$current_language = function_exists('tersa_get_footer_current_language_slug')
	? tersa_get_footer_current_language_slug()
	: '';
if ($current_language === '' && function_exists('pll_current_language')) {
	$current_language = (string) pll_current_language('slug');
}
if ($current_language === '' && function_exists('get_locale')) {
	$current_language = substr((string) get_locale(), 0, 2);
}
$current_language = strtolower(substr((string) $current_language, 0, 2));
$impressum_label_sets = [
	'hr' => [
		'heading'       => 'Podaci o tvrtki:',
		'name'          => 'Naziv:',
		'activity'      => 'Djelatnost:',
		'oib'           => 'OIB:',
		'mbs'           => 'MBS:',
		'registry'      => 'Registar:',
		'director'      => 'Odgovorna osoba:',
		'share_capital' => 'Temeljni kapital:',
		'iban'          => 'IBAN:',
		'vat_id'        => 'PDV ID:',
		'complaints'    => 'Reklamacije i povrati:',
	],
	'en' => [
		'heading'       => 'Company details:',
		'name'          => 'Name:',
		'activity'      => 'Activity:',
		'oib'           => 'OIB:',
		'mbs'           => 'MBS:',
		'registry'      => 'Registry:',
		'director'      => 'Responsible person:',
		'share_capital' => 'Share capital:',
		'iban'          => 'IBAN:',
		'vat_id'        => 'VAT ID:',
		'complaints'    => 'Complaints and returns:',
	],
	'de' => [
		'heading'       => 'Firmenname:',
		'name'          => 'Name:',
		'activity'      => 'Geschäftstätigkeit:',
		'oib'           => 'OIB:',
		'mbs'           => 'MBS:',
		'registry'      => 'Handelsregister:',
		'director'      => 'Geschäftsführer:',
		'share_capital' => 'Stammkapital:',
		'iban'          => 'IBAN:',
		'vat_id'        => 'USt-IdNr.:',
		'complaints'    => 'Reklamationen und Rücksendungen:',
	],
];
$impressum_labels = $impressum_label_sets[$current_language] ?? $impressum_label_sets['hr'];
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
							<?php if ($phone_href) : ?>
								<a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a>
							<?php else : ?>
								<?php echo esc_html($phone); ?>
							<?php endif; ?>
						</p>
						<p><?php echo esc_html($call_text); ?></p>
					</div>
					<?php if (!empty($phone_second)) : ?>
						<div class="contact-card__block">
							<p style="margin:0">
								<?php if ($phone_href_second) : ?>
									<a href="tel:<?php echo esc_attr($phone_href_second); ?>"><?php echo esc_html($phone_second); ?></a>
								<?php else : ?>
									<?php echo esc_html($phone_second); ?>
								<?php endif; ?>
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
							<?php if ($email_href) : ?>
								<a href="mailto:<?php echo esc_attr($email_href); ?>"><?php echo esc_html($email); ?></a>
							<?php else : ?>
								<?php echo esc_html($email); ?>
							<?php endif; ?>
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

				<?php if ($contact_form_markup !== '') : ?>
					<div class="contact-card__form">
						<h2 class="contact-card__heading">
							<?php echo esc_html($form_heading); ?>
						</h2>

						<div class="contact-card__form-wrap">
							<?php
							// tersa_safe_cf7_shortcode_output() validateira strogi regex — samo CF7 shortcode.
							// wp_kses_post() bi uklonilo <form> elemente pa ga ne koristimo ovdje.
							echo $contact_form_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					</div>
				<?php endif; ?>
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
					<h3 class="contact-card__label"><?php echo esc_html($impressum_labels['heading']); ?></h3>
					<dl class="contact-card__impressum">
						<dt><?php echo esc_html($impressum_labels['name']); ?></dt>
						<dd><?php echo esc_html($impressum['full_name']); ?></dd>

						<?php if (!empty($impressum['activity'])) : ?>
							<dt><?php echo esc_html($impressum_labels['activity']); ?></dt>
							<dd><?php echo esc_html($impressum['activity']); ?></dd>
						<?php endif; ?>

						<dt><?php echo esc_html($impressum_labels['oib']); ?></dt>
						<dd><?php echo esc_html($impressum['oib']); ?></dd>

						<dt><?php echo esc_html($impressum_labels['mbs']); ?></dt>
						<dd><?php echo esc_html($impressum['mbs']); ?></dd>

						<dt><?php echo esc_html($impressum_labels['registry']); ?></dt>
						<dd><?php echo esc_html($impressum['court']); ?></dd>

						<?php if (!empty($impressum['director'])) : ?>
							<dt><?php echo esc_html($impressum_labels['director']); ?></dt>
							<dd><?php echo esc_html($impressum['director']); ?></dd>
						<?php endif; ?>

						<?php if (!empty($impressum['share_capital'])) : ?>
							<dt><?php echo esc_html($impressum_labels['share_capital']); ?></dt>
							<dd><?php echo esc_html($impressum['share_capital']); ?></dd>
						<?php endif; ?>

						<?php if (!empty($impressum['iban'])) : ?>
							<dt><?php echo esc_html($impressum_labels['iban']); ?></dt>
							<dd>
								<?php echo esc_html($impressum['iban']); ?>
								<?php if (!empty($impressum['bank'])) : ?>
									<small>(<?php echo esc_html($impressum['bank']); ?>)</small>
								<?php endif; ?>
							</dd>
						<?php endif; ?>

						<?php if (!empty($impressum['vat_id'])) : ?>
							<dt><?php echo esc_html($impressum_labels['vat_id']); ?></dt>
							<dd><?php echo esc_html($impressum['vat_id']); ?></dd>
						<?php endif; ?>

						<?php if (!empty($impressum['email_complaints']) && $impressum['email_complaints'] !== $impressum['email']) : ?>
							<dt><?php echo esc_html($impressum_labels['complaints']); ?></dt>
							<dd>
								<?php if ($email_complaints_href) : ?>
									<a href="mailto:<?php echo esc_attr($email_complaints_href); ?>">
										<?php echo esc_html($impressum['email_complaints']); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html($impressum['email_complaints']); ?>
								<?php endif; ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
