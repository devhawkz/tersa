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

$show_slider = !empty($fields['show_home_hero_slider']);

if (!$show_slider) {
	return;
}

$slides_raw = [
	$fields['hero_slide_1'] ?? null,
	$fields['hero_slide_2'] ?? null,
	$fields['hero_slide_3'] ?? null,
];

$slides = [];

foreach ($slides_raw as $slide) {
	if (
		empty($slide) ||
		empty($slide['title']) ||
		empty($slide['description']) ||
		empty($slide['cta_text']) ||
		empty($slide['cta_link']) ||
		empty($slide['image_desktop'])
	) {
		continue;
	}

	$slides[] = $slide;
}

if (empty($slides)) {
	return;
}
?>

<section class="home-hero" aria-label="<?php echo esc_attr__('Featured promotions', 'tersa-shop'); ?>">
	<div
		class="home-hero__slider js-home-hero-slider"
		data-slide-count="<?php echo esc_attr(count($slides)); ?>"
		data-autoplay-ms="<?php echo esc_attr((string) (int) apply_filters('tersa_home_hero_autoplay_ms', 3000)); ?>"
	>
		<div class="home-hero__track">
			<?php foreach ($slides as $index => $slide) : ?>
				<?php
				$is_active = $index === 0;
				$bg_color  = !empty($slide['background_color']) ? $slide['background_color'] : '#f3ead7';

				$current_price     = isset($slide['current_price']) ? $slide['current_price'] : '';
				$old_price         = isset($slide['old_price']) ? $slide['old_price'] : '';
				$title             = isset($slide['title']) ? $slide['title'] : '';
				$description       = isset($slide['description']) ? $slide['description'] : '';
				$cta_text          = isset($slide['cta_text']) ? $slide['cta_text'] : '';
				$cta_url           = isset($slide['cta_link']) ? $slide['cta_link'] : '';
				$show_badge        = !empty($slide['show_badge']);
				$badge_top_text    = isset($slide['badge_top_text']) ? $slide['badge_top_text'] : '';
				$badge_main_text   = isset($slide['badge_main_text']) ? $slide['badge_main_text'] : '';
				$badge_bottom_text = isset($slide['badge_bottom_text']) ? $slide['badge_bottom_text'] : '';

				$desktop_image_id = !empty($slide['image_desktop']) ? (int) $slide['image_desktop'] : 0;
				$mobile_image_id  = !empty($slide['image_mobile']) ? (int) $slide['image_mobile'] : 0;
				$primary_image_id = $desktop_image_id ?: $mobile_image_id;
				?>
			<div
				class="home-hero__slide<?php echo $is_active ? ' is-active' : ''; ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr(sprintf(__('Slide %1$d of %2$d', 'tersa-shop'), $index + 1, count($slides))); ?>"
				data-index="<?php echo esc_attr($index); ?>"
				style="--hero-slide-bg: <?php echo esc_attr($bg_color); ?>;"
				<?php echo $is_active ? '' : 'hidden'; ?>
			>
					<div class="home-hero__inner">
						<div class="home-hero__content">
							<?php if ($current_price || $old_price) : ?>
								<div class="home-hero__prices" aria-label="<?php echo esc_attr__('Product pricing', 'tersa-shop'); ?>">
									<?php if ($current_price) : ?>
										<span class="home-hero__price home-hero__price--current">
											<?php echo esc_html($current_price); ?>
										</span>
									<?php endif; ?>

									<?php if ($old_price) : ?>
										<span class="home-hero__price home-hero__price--old">
											<?php echo esc_html($old_price); ?>
										</span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

						<p class="home-hero__title">
							<?php echo nl2br(esc_html($title)); ?>
						</p>

							<p class="home-hero__description">
								<?php echo nl2br(esc_html($description)); ?>
							</p>

							<a class="home-hero__cta" href="<?php echo esc_url($cta_url); ?>">
								<?php echo esc_html($cta_text); ?>
							</a>
						</div>

						<div class="home-hero__media">
							<?php if ($show_badge && ($badge_top_text || $badge_main_text || $badge_bottom_text)) : ?>
								<div class="home-hero__badge" aria-hidden="true">
									<?php if ($badge_top_text) : ?>
										<span class="home-hero__badge-top"><?php echo esc_html($badge_top_text); ?></span>
									<?php endif; ?>

									<?php if ($badge_main_text) : ?>
										<span class="home-hero__badge-main"><?php echo esc_html($badge_main_text); ?></span>
									<?php endif; ?>

									<?php if ($badge_bottom_text) : ?>
										<span class="home-hero__badge-bottom"><?php echo nl2br(esc_html($badge_bottom_text)); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($primary_image_id) : ?>
								<?php
								$desktop_src     = $desktop_image_id ? wp_get_attachment_image_url($desktop_image_id, 'tersa-hero') : wp_get_attachment_image_url($primary_image_id, 'tersa-hero');
								$desktop_srcset  = $desktop_image_id ? wp_get_attachment_image_srcset($desktop_image_id, 'tersa-hero') : wp_get_attachment_image_srcset($primary_image_id, 'tersa-hero');
								$mobile_srcset   = $mobile_image_id ? wp_get_attachment_image_srcset($mobile_image_id, 'tersa-hero-mobile') : '';
								$desktop_sizes   = '(max-width: 1024px) 100vw, 50vw';
								$mobile_sizes    = '100vw';
								$hero_image_alt  = get_post_meta($primary_image_id, '_wp_attachment_image_alt', true);
								$hero_image_alt  = is_string($hero_image_alt) && $hero_image_alt !== '' ? $hero_image_alt : $title;
								?>
								<div class="home-hero__image">
									<picture class="home-hero__picture">
										<?php if ($mobile_image_id && $mobile_srcset) : ?>
											<source
												media="(max-width: 1024px)"
												srcset="<?php echo esc_attr($mobile_srcset); ?>"
												sizes="<?php echo esc_attr($mobile_sizes); ?>"
											>
										<?php endif; ?>
										<img
											class="home-hero__img"
											src="<?php echo esc_url((string) $desktop_src); ?>"
											<?php if ($desktop_srcset) : ?>srcset="<?php echo esc_attr($desktop_srcset); ?>"<?php endif; ?>
											sizes="<?php echo esc_attr($desktop_sizes); ?>"
											alt="<?php echo esc_attr($hero_image_alt); ?>"
											decoding="async"
											fetchpriority="<?php echo esc_attr($is_active ? 'high' : 'auto'); ?>"
											loading="<?php echo esc_attr($is_active ? 'eager' : 'lazy'); ?>"
										>
									</picture>
								</div>
							<?php endif; ?>
						</div>
					</div>
					</div>
			<?php endforeach; ?>
		</div>

		<?php if (count($slides) > 1) : ?>
			<div class="home-hero__pagination" aria-label="<?php echo esc_attr__('Slider pagination', 'tersa-shop'); ?>">
				<?php foreach ($slides as $index => $slide) : ?>
					<button
						class="home-hero__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
						type="button"
						aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'tersa-shop'), $index + 1)); ?>"
						aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
						data-slide-to="<?php echo esc_attr($index); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>