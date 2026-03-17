<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_the_ID();

// Slika: ACF polje contact_hero_image (Image, return Image ID) → inače featured image → inače fallback
$hero_image_url = '';
if (function_exists('get_field')) {
	$acf_image_id = get_field('contact_hero_image', $page_id);
	if ($acf_image_id && is_numeric($acf_image_id)) {
		$hero_image_url = wp_get_attachment_image_url((int) $acf_image_id, 'full');
	}
}
if (!$hero_image_url) {
	$hero_image_url = get_the_post_thumbnail_url($page_id, 'full');
}
if (!$hero_image_url) {
	$hero_image_url = get_template_directory_uri() . '/assets/img/contact-hero.jpg';
}

// Tekst: ACF polje contact_hero_title (Text) → inače podrazumevani prevod
$hero_title = '';
if (function_exists('get_field')) {
	$hero_title = get_field('contact_hero_title', $page_id);
}
if (!is_string($hero_title) || trim($hero_title) === '') {
	$hero_title = __('Get in touch with us. Send us a message', 'tersa-shop');
}
?>

<section class="contact-hero" aria-labelledby="contact-hero-title">
	<div
		class="contact-hero__media contact-hero__media--parallax"
		style="background-image: url(<?php echo esc_url($hero_image_url); ?>);"
		role="img"
		aria-label=""
	>
		<div class="contact-hero__overlay"></div>
	</div>

	<div class="contact-hero__inner page-shell">
		<div class="contact-hero__content">
			<h1 id="contact-hero-title" class="contact-hero__title">
				<?php echo esc_html($hero_title); ?>
			</h1>
		</div>
	</div>
</section>