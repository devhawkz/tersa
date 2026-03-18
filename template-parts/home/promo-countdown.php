<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('get_field')) {
	return;
}

$page_id = get_queried_object_id();

if (!$page_id) {
	return;
}

$show_section = (bool) get_field('show_home_promo_countdown', $page_id);

if (!$show_section) {
	return;
}

$eyebrow = get_field('home_promo_countdown_eyebrow', $page_id);
$title   = get_field('home_promo_countdown_title', $page_id);
$image_id = get_field('home_promo_countdown_image', $page_id);
$end_raw = get_field('home_promo_countdown_end', $page_id);

if (empty($title) || empty($image_id)) {
	return;
}

$end_timestamp = 0;

if (!empty($end_raw)) {
	$parsed = strtotime((string) $end_raw);
	if ($parsed !== false) {
		$end_timestamp = (int) $parsed;
	}
}
?>

<section class="home-promo-countdown" aria-labelledby="home-promo-countdown-title">
	<div class="home-promo-countdown__inner">
		<div class="home-promo-countdown__content">
			<?php if (!empty($eyebrow)) : ?>
				<p class="home-promo-countdown__eyebrow">
					<?php echo esc_html($eyebrow); ?>
				</p>
			<?php endif; ?>

			<h2 id="home-promo-countdown-title" class="home-promo-countdown__title">
				<?php echo esc_html($title); ?>
			</h2>

			<?php if ($end_timestamp > 0) : ?>
				<?php
				// Polylang stringovi: ako postoji `pll__()`, uzmi prevod za trenutni jezik.
				// U suprotnom fallback na WP gettext prevod (standardni __()/esc_html__ mehanizam).
				// (Koristimo srpske ključeve da se ne dešava fallback na engleski.)
				$label_days    = function_exists('pll__') ? pll__('Dani') : 'Dani';
				$label_hours   = function_exists('pll__') ? pll__('Sati') : 'Sati';
				$label_minutes = function_exists('pll__') ? pll__('Minute') : 'Minute';
				$label_seconds = function_exists('pll__') ? pll__('Sekunde') : 'Sekunde';
				?>
				<div
					class="home-promo-countdown__timer js-home-promo-countdown"
					data-end="<?php echo esc_attr(gmdate('c', $end_timestamp)); ?>"
					aria-label="<?php echo esc_attr__('Promotion countdown', 'tersa-shop'); ?>"
				>
					<div class="home-promo-countdown__unit">
						<span class="home-promo-countdown__value" data-unit="days">00</span>
						<span class="home-promo-countdown__label"><?php echo esc_html($label_days); ?></span>
					</div>

					<div class="home-promo-countdown__unit">
						<span class="home-promo-countdown__value" data-unit="hours">00</span>
						<span class="home-promo-countdown__label"><?php echo esc_html($label_hours); ?></span>
					</div>

					<div class="home-promo-countdown__unit">
						<span class="home-promo-countdown__value" data-unit="minutes">00</span>
						<span class="home-promo-countdown__label"><?php echo esc_html($label_minutes); ?></span>
					</div>

					<div class="home-promo-countdown__unit">
						<span class="home-promo-countdown__value" data-unit="seconds">00</span>
						<span class="home-promo-countdown__label"><?php echo esc_html($label_seconds); ?></span>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="home-promo-countdown__media">
			<?php
			echo wp_get_attachment_image(
				(int) $image_id,
				'full',
				false,
				[
					'class'         => 'home-promo-countdown__image',
					'alt'           => !empty($title) ? esc_attr($title) : '',
					'loading'       => 'lazy',
					'decoding'      => 'async',
				]
			);
			?>
		</div>
	</div>
</section>