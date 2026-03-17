<?php
if (!defined('ABSPATH')) {
	exit;
}

$page_id = get_the_ID() ?: get_queried_object_id();
$get = function_exists('get_field') ? function ($key, $fallback = '') use ($page_id) {
	$v = get_field($key, $page_id);
	return (is_string($v) && trim($v) !== '') ? $v : $fallback;
} : null;

// Slika
$cta_image_url = '';
if (function_exists('get_field')) {
	$img = get_field('about_cta_image', $page_id);
	if (is_numeric($img)) {
		$cta_image_url = wp_get_attachment_image_url((int) $img, 'full');
	} elseif (is_array($img) && !empty($img['url'])) {
		$cta_image_url = $img['url'];
	} elseif (is_array($img) && !empty($img['ID'])) {
		$cta_image_url = wp_get_attachment_image_url((int) $img['ID'], 'full');
	}
}
if (!$cta_image_url) {
	$cta_image_url = get_the_post_thumbnail_url($page_id, 'full');
}
if (!$cta_image_url) {
	$fallback_path = get_template_directory() . '/assets/img/contact-hero.jpg';
	$cta_image_url = file_exists($fallback_path) ? get_template_directory_uri() . '/assets/img/contact-hero.jpg' : '';
}
$cta_image_alt = $get ? $get('about_cta_image_alt', '') : '';

// Tekstovi i dugme
$cta_title   = $get ? $get('about_cta_title', __('We Deliver Genuine Products', 'tersa-shop')) : __('We Deliver Genuine Products', 'tersa-shop');
$cta_text    = $get ? $get('about_cta_text', __('Sed viverra consectetur risus nec ultricies. Curabitur tincidunt tincidunt urna id maximus.', 'tersa-shop')) : __('Sed viverra consectetur risus nec ultricies. Curabitur tincidunt tincidunt urna id maximus.', 'tersa-shop');
$cta_btn     = $get ? $get('about_cta_button_text', __('Contact Us', 'tersa-shop')) : __('Contact Us', 'tersa-shop');
$cta_btn_url = $get ? $get('about_cta_button_url', home_url('/kontakt/')) : home_url('/kontakt/');
?>

<section class="about-cta" aria-labelledby="about-cta-title">
	<div
		class="about-cta__media about-cta__media--parallax<?php echo $cta_image_url ? '' : ' about-cta__media--no-image'; ?>"
		<?php if ($cta_image_url) : ?>style="background-image: url(<?php echo esc_url($cta_image_url); ?>);"<?php endif; ?>
		role="img"
		aria-label="<?php echo esc_attr($cta_image_alt); ?>"
	>
		<div class="about-cta__overlay"></div>
	</div>

	<div class="about-cta__inner container">
		<div class="about-cta__content">
			<h2 id="about-cta-title" class="about-cta__title">
				<?php echo esc_html($cta_title); ?>
			</h2>

			<p class="about-cta__text">
				<?php echo esc_html($cta_text); ?>
			</p>

			<?php if ($cta_btn && $cta_btn_url) : ?>
			<a href="<?php echo esc_url($cta_btn_url); ?>" class="about-cta__button btn btn--secondary">
				<?php echo esc_html($cta_btn); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</section>