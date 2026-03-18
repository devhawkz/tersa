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

$show_section = (bool) get_field('show_home_promo_banners', $page_id);

if (!$show_section) {
	return;
}

$banners_raw = [
	get_field('home_promo_banner_1', $page_id),
	get_field('home_promo_banner_2', $page_id),
];

$banners = [];

foreach ($banners_raw as $banner) {
	if (
		empty($banner) ||
		empty($banner['title']) ||
		empty($banner['link']) ||
		empty($banner['image'])
	) {
		continue;
	}

	$banners[] = [
		'eyebrow'     => !empty($banner['eyebrow']) ? $banner['eyebrow'] : '',
		'title'       => $banner['title'],
		'link'        => $banner['link'],
		'image_id'    => (int) $banner['image'],
	];
}

if (empty($banners)) {
	return;
}
?>

<section class="home-promo-banners" aria-label="<?php echo esc_attr__('Promotional banners', 'tersa-shop'); ?>">
	<div class="home-promo-banners__inner">
		<div class="home-promo-banners__grid">
			<?php foreach ($banners as $banner) : ?>
				<a class="home-promo-banners__item" href="<?php echo esc_url($banner['link']); ?>">
					<span class="home-promo-banners__media">
						<?php
						echo wp_get_attachment_image(
							$banner['image_id'],
							'full',
							false,
							[
								'class'    => 'home-promo-banners__image',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'alt'      => '',
							]
						);
						?>
						<span class="home-promo-banners__frame" aria-hidden="true">
							<span class="home-promo-banners__line home-promo-banners__line--top"></span>
							<span class="home-promo-banners__line home-promo-banners__line--right"></span>
							<span class="home-promo-banners__line home-promo-banners__line--bottom"></span>
							<span class="home-promo-banners__line home-promo-banners__line--left"></span>
						</span>
					</span>

					<span class="home-promo-banners__content">
						<?php if (!empty($banner['eyebrow'])) : ?>
							<span class="home-promo-banners__eyebrow">
								<?php echo esc_html($banner['eyebrow']); ?>
							</span>
						<?php endif; ?>

						<span class="home-promo-banners__title">
							<?php echo esc_html($banner['title']); ?>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>