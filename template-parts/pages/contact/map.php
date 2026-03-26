<?php
if (!defined('ABSPATH')) {
	exit;
}

$allowed_map_tags = [
	'iframe' => [
		'class'         => true,
		'title'         => true,
		'frameborder'   => true,
		'scrolling'     => true,
		'marginheight'  => true,
		'marginwidth'   => true,
		'src'           => true,
		'loading'       => true,
	],
];

$settings_page_id = function_exists('tersa_get_global_settings_page_id') ? tersa_get_global_settings_page_id() : 0;
$map_embed = (function_exists('get_field') && $settings_page_id)
	? (string) get_field('contact_map_embed', $settings_page_id)
	: '';

if (trim($map_embed) === '') {
	$map_embed = '<iframe
	class="embed-map-frame"
	title="' . esc_attr__('Company location on Google Maps', 'tersa-shop') . '"
	frameborder="0"
	scrolling="no" 
	marginheight="0"
	marginwidth="0"
	loading="lazy"
	src="https://maps.google.com/maps?q=Nikole%20Tesle%2071%2C%2031553%20%C4%8Crnkovci&t=m&z=14&ie=UTF8&iwloc=B&output=embed"
></iframe>';
}
?>

<section class="contact-map-section" aria-labelledby="contact-map-title">
	<h2 id="contact-map-title" class="screen-reader-text">
		<?php esc_html_e('Company location map', 'tersa-shop'); ?>
	</h2>

	<div class="contact-map">
		<div class="embed-map-responsive">
			<div class="embed-map-container">
				<?php echo wp_kses($map_embed, $allowed_map_tags); ?>
			</div>
		</div>
	</div>
</section>
