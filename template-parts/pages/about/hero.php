<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_queried_object_id() ?: get_the_ID();

$hero_title = function_exists('get_field')
	? get_field('about_hero_title', $page_id)
	: '';

$hero_image_raw = get_post_meta($page_id, 'about_hero_image', true);

if (empty($hero_image_raw) && function_exists('get_field')) {
	$hero_image_raw = get_field('about_hero_image', $page_id);
}

$hero_image_url = '';

if (is_numeric($hero_image_raw)) {
	$hero_image_url = wp_get_attachment_image_url((int) $hero_image_raw, 'full');
} elseif (is_array($hero_image_raw) && !empty($hero_image_raw['ID'])) {
	$hero_image_url = wp_get_attachment_image_url((int) $hero_image_raw['ID'], 'full');
} elseif (is_array($hero_image_raw) && !empty($hero_image_raw['url'])) {
	$hero_image_url = $hero_image_raw['url'];
} elseif (is_string($hero_image_raw) && trim($hero_image_raw) !== '') {
	$hero_image_url = $hero_image_raw;
}

if (!$hero_image_url) {
	$hero_image_url = get_the_post_thumbnail_url($page_id, 'full');
}

if (!$hero_image_url) {
	$hero_image_url = get_template_directory_uri() . '/assets/img/contact-hero.jpg';
}

if (!is_string($hero_title) || trim($hero_title) === '') {
	$hero_title = __('We Deliver Genuine Products', 'tersa-shop');
}
?>

<section class="contact-hero contact-hero--about" aria-labelledby="about-hero-title">
	<div
		class="contact-hero__media contact-hero__media--parallax<?php echo $hero_image_url ? '' : ' contact-hero__media--no-image'; ?>"
		<?php if ($hero_image_url) : ?>style="background-image: url(<?php echo esc_url($hero_image_url); ?>);"<?php endif; ?>
		role="img"
		aria-label=""
	>
		<div class="contact-hero__overlay"></div>
	</div>

	<div class="contact-hero__inner page-shell">
		<div class="contact-hero__content">
			<h1 id="about-hero-title" class="contact-hero__title">
				<?php echo esc_html($hero_title); ?>
			</h1>
		</div>
	</div>
</section>