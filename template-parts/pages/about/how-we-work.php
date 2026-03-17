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
$work_image_url = '';
if (function_exists('get_field')) {
	$img = get_field('about_work_image', $page_id);
	if (is_numeric($img)) {
		$work_image_url = wp_get_attachment_image_url((int) $img, 'full');
	} elseif (is_array($img) && !empty($img['url'])) {
		$work_image_url = $img['url'];
	} elseif (is_array($img) && !empty($img['ID'])) {
		$work_image_url = wp_get_attachment_image_url((int) $img['ID'], 'full');
	}
}
if (!$work_image_url) {
	$fallback = get_template_directory() . '/assets/img/about-work.jpg';
	$work_image_url = file_exists($fallback) ? get_template_directory_uri() . '/assets/img/about-work.jpg' : '';
}
$work_image_alt = $get ? $get('about_work_image_alt', __('Tersa team at work in the office', 'tersa-shop')) : __('Tersa team at work in the office', 'tersa-shop');

// Tekstovi
$work_title  = $get ? $get('about_work_title', __('How we works', 'tersa-shop')) : __('How we works', 'tersa-shop');
$item1_title = $get ? $get('about_work_item_1_title', __('Production Design', 'tersa-shop')) : __('Production Design', 'tersa-shop');
$item1_text  = $get ? $get('about_work_item_1_text', __('Integer dignissim sagittis quam. Maecenas sem eros, rutrum vitae risus eget, vulputate aliquam nisi.', 'tersa-shop')) : __('Integer dignissim sagittis quam. Maecenas sem eros, rutrum vitae risus eget, vulputate aliquam nisi.', 'tersa-shop');
$item2_title = $get ? $get('about_work_item_2_title', __('Manufacturing', 'tersa-shop')) : __('Manufacturing', 'tersa-shop');
$item2_text  = $get ? $get('about_work_item_2_text', __('Maecenas sem eros, rutrum vitae risus eget, vulputate aliquam nisi ex gravida neque tempus.', 'tersa-shop')) : __('Maecenas sem eros, rutrum vitae risus eget, vulputate aliquam nisi ex gravida neque tempus.', 'tersa-shop');
$item3_title = $get ? $get('about_work_item_3_title', __('Marketing and selling', 'tersa-shop')) : __('Marketing and selling', 'tersa-shop');
$item3_text  = $get ? $get('about_work_item_3_text', __('Rutrum vitae risus eget, vulputate aliquam nisi ex gravida neque tempus.', 'tersa-shop')) : __('Rutrum vitae risus eget, vulputate aliquam nisi ex gravida neque tempus.', 'tersa-shop');
?>

<section class="about-work section" aria-labelledby="about-work-title">
	<div class="container">
		<div class="about-work__grid">
			<?php if ($work_image_url) : ?>
			<div class="about-work__media">
				<img
					src="<?php echo esc_url($work_image_url); ?>"
					alt="<?php echo esc_attr($work_image_alt); ?>"
					class="about-work__image"
					loading="lazy"
					decoding="async"
				>
			</div>
			<?php endif; ?>

			<div class="about-work__content">
				<h2 id="about-work-title" class="about-work__title">
					<?php echo esc_html($work_title); ?>
				</h2>

				<div class="about-work__items">
					<article class="about-work__item">
						<h3 class="about-work__item-title"><?php echo esc_html($item1_title); ?></h3>
						<p><?php echo esc_html($item1_text); ?></p>
					</article>

					<article class="about-work__item">
						<h3 class="about-work__item-title"><?php echo esc_html($item2_title); ?></h3>
						<p><?php echo esc_html($item2_text); ?></p>
					</article>

					<article class="about-work__item">
						<h3 class="about-work__item-title"><?php echo esc_html($item3_title); ?></h3>
						<p><?php echo esc_html($item3_text); ?></p>
					</article>
				</div>
			</div>
		</div>
	</div>
</section>