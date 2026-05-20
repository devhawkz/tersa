<?php
/**
 * Payment & security badges blok — prikazuje:
 *  - logotipe prihvaćenih kartica (Mastercard, Maestro, Visa, Diners),
 *  - logotipe sigurnosnih programa (Mastercard Identity Check, Visa Secure, Diners sigurna kupnja),
 *  - CorvusPay logo (negativ za tamne pozadine, pozitiv za svijetle).
 *
 * Obavezno na checkout stranici po CorvusPay "Standardi logotipa v3.4":
 *   "Logotipi se obavezno moraju postaviti na stranicu odabira načina plaćanja!"
 *   "Logotipe je obavezno staviti na: stranicu s informacijama o sigurnosti, stranicu za plaćanje"
 *
 * Argumenti (set_query_var ili `$args` ako se zove preko get_template_part argumenata u WP 5.5+):
 *   - $args['variant']      string  'light' (bijela pozadina) ili 'dark' (tamna). Default: 'light'.
 *   - $args['title']        string  Opcionalni naslov bloka. Default: 'Sigurno plaćanje'.
 *   - $args['show_cards']   bool    Prikaži kartice. Default: true.
 *   - $args['show_security'] bool   Prikaži security badge-eve. Default: true.
 *   - $args['show_corvuspay'] bool  Prikaži CorvusPay logo. Default: true.
 */

if (!defined('ABSPATH')) {
	exit;
}

$args = is_array($args ?? null) ? $args : [];

$variant        = ($args['variant'] ?? 'light') === 'dark' ? 'dark' : 'light';
$block_title    = $args['title']         ?? __('Sigurno plaćanje', 'tersa-shop');
$show_cards     = $args['show_cards']    ?? true;
$show_security  = $args['show_security'] ?? true;
$show_corvuspay = $args['show_corvuspay'] ?? true;

$payment_methods  = function_exists('tersa_get_payment_methods') ? tersa_get_payment_methods() : [];
$security_badges  = function_exists('tersa_get_security_badges') ? tersa_get_security_badges($variant) : [];
$payments_dir_path = get_template_directory() . '/assets/img/payments/';
$payments_dir_uri  = get_template_directory_uri() . '/assets/img/payments/';

$corvuspay_file = $variant === 'dark' ? 'corvuspay.svg' : 'corvuspay-light.svg';
?>
<aside class="payment-security-badges payment-security-badges--<?php echo esc_attr($variant); ?>"
	aria-labelledby="payment-security-badges-title">
	<?php if (!empty($block_title)) : ?>
		<h2 id="payment-security-badges-title" class="payment-security-badges__title">
			<?php echo esc_html($block_title); ?>
		</h2>
	<?php endif; ?>

	<?php if ($show_cards && !empty($payment_methods)) : ?>
		<div class="payment-security-badges__group payment-security-badges__group--cards"
			aria-label="<?php esc_attr_e('Prihvaćene kartice', 'tersa-shop'); ?>">
			<h3 class="payment-security-badges__subheading">
				<?php esc_html_e('Prihvaćamo:', 'tersa-shop'); ?>
			</h3>
			<ul class="payment-security-badges__list payment-security-badges__list--cards">
				<?php foreach ($payment_methods as $pm) :
					$file = $pm['slug'] . '.' . $pm['ext'];
					if (!file_exists($payments_dir_path . $file)) {
						continue;
					}
					?>
					<li class="payment-security-badges__item">
						<a href="<?php echo esc_url($pm['url']); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr(sprintf(__('%s — službena stranica (otvara se u novom prozoru)', 'tersa-shop'), $pm['label'])); ?>">
							<img src="<?php echo esc_url($payments_dir_uri . $file); ?>"
								alt="<?php echo esc_attr($pm['label']); ?>"
								class="payment-security-badges__icon"
								loading="lazy"
								decoding="async" />
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ($show_security && !empty($security_badges)) : ?>
		<div class="payment-security-badges__group payment-security-badges__group--security"
			aria-label="<?php esc_attr_e('Sigurnosni programi', 'tersa-shop'); ?>">
			<h3 class="payment-security-badges__subheading">
				<?php esc_html_e('Provjereno i sigurno:', 'tersa-shop'); ?>
			</h3>
			<ul class="payment-security-badges__list payment-security-badges__list--security">
				<?php foreach ($security_badges as $sb) :
					$file = $sb['slug'] . '.' . $sb['ext'];
					if (!file_exists($payments_dir_path . 'security/' . $file)) {
						continue;
					}
					?>
					<li class="payment-security-badges__item payment-security-badges__item--security">
						<a href="<?php echo esc_url($sb['url']); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr(sprintf(__('%s — informacije o sigurnosnom programu (otvara se u novom prozoru)', 'tersa-shop'), $sb['label'])); ?>">
							<img src="<?php echo esc_url($payments_dir_uri . 'security/' . $file); ?>"
								alt="<?php echo esc_attr($sb['label']); ?>"
								class="payment-security-badges__icon payment-security-badges__icon--security"
								loading="lazy"
								decoding="async" />
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ($show_corvuspay && file_exists($payments_dir_path . $corvuspay_file)) : ?>
		<div class="payment-security-badges__group payment-security-badges__group--corvuspay">
			<a class="payment-security-badges__corvuspay"
				href="https://www.corvuspay.com/"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e('CorvusPay — sigurno online plaćanje karticama (otvara se u novom prozoru)', 'tersa-shop'); ?>">
				<img src="<?php echo esc_url($payments_dir_uri . $corvuspay_file); ?>"
					alt="CorvusPay"
					class="payment-security-badges__corvuspay-icon"
					loading="lazy"
					decoding="async" />
			</a>
		</div>
	<?php endif; ?>
</aside>
