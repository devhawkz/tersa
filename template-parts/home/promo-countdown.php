<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('get_field')) {
	return;
}

$page_id = isset($args['page_id']) ? (int) $args['page_id'] : get_queried_object_id();

if (!$page_id) {
	return;
}

$fields = isset($args['fields']) && is_array($args['fields'])
	? $args['fields']
	: (get_fields($page_id) ?: []);

$get_value = static function (array $source, string $key, $fallback = '') {
	return array_key_exists($key, $source) ? $source[$key] : $fallback;
};

$show_section = (bool) $get_value($fields, 'show_home_promo_countdown', false);

if (!$show_section) {
	return;
}

$eyebrow  = (string) $get_value($fields, 'home_promo_countdown_eyebrow', '');
$title    = (string) $get_value($fields, 'home_promo_countdown_title', '');
$image_id = (int) $get_value($fields, 'home_promo_countdown_image', 0);
$end_raw  = (string) $get_value($fields, 'home_promo_countdown_end', '');

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

$countdown_label_fallbacks = [
	'hr' => [
		'Dana'    => 'Dana',
		'Sati'    => 'Sati',
		'Minuta'  => 'Minuta',
		'Sekunde' => 'Sekunde',
	],
	'sr' => [
		'Dana'    => 'Dana',
		'Sati'    => 'Sati',
		'Minuta'  => 'Minuta',
		'Sekunde' => 'Sekunde',
	],
	'en' => [
		'Dana'    => 'Days',
		'Sati'    => 'Hours',
		'Minuta'  => 'Minutes',
		'Sekunde' => 'Seconds',
	],
	'de' => [
		'Dana'    => 'Tage',
		'Sati'    => 'Stunden',
		'Minuta'  => 'Minuten',
		'Sekunde' => 'Sekunden',
	],
];

$translate = static function (string $text) use ($countdown_label_fallbacks): string {
	$translated = $text;

	if (function_exists('pll__')) {
		$translated = (string) call_user_func('pll__', $text);
	}

	if ($translated !== '' && $translated !== $text) {
		return $translated;
	}

	$lang = '';

	if (function_exists('pll_current_language')) {
		$lang = (string) call_user_func('pll_current_language', 'slug');
	}

	if ($lang === '') {
		$lang = substr((string) determine_locale(), 0, 2);
	}

	$lang = strtolower(substr($lang, 0, 2));

	return $countdown_label_fallbacks[$lang][$text] ?? $translated;
};
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
				// Polylang prevod ima prednost; ako nije popunjen, koristi se jezični fallback iz ove sekcije.
				$label_days    = $translate('Dana');
				$label_hours   = $translate('Sati');
				$label_minutes = $translate('Minuta');
				$label_seconds = $translate('Sekunde');
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
				$image_id,
				'tersa-countdown',
				false,
				[
					'class'         => 'home-promo-countdown__image',
					'alt'           => !empty($title) ? $title : '',
					'loading'       => 'lazy',
					'decoding'      => 'async',
				]
			);
			?>
		</div>
	</div>
</section>
