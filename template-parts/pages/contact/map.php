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
	'a' => [
		'href'  => true,
		'style' => true,
	],
];

$map_shortcode = '<iframe
	class="embed-map-frame"
	title="' . esc_attr__('Company location on Google Maps', 'tersa-shop') . '"
	frameborder="0"
	scrolling="no"
	marginheight="0"
	marginwidth="0"
	loading="lazy"
	src="https://maps.google.com/maps?q=Nikole%20Tesle%2071%2C%2031553%20%C4%8Crnkovci&t=m&z=14&ie=UTF8&iwloc=B&output=embed"
></iframe>
<a href="https://embed-maps.org" style="font-size:2px!important;color:gray!important;position:absolute;bottom:0;left:0;z-index:1;max-height:1px;overflow:hidden"></a>';
?>

<section class="contact-map-section" aria-labelledby="contact-map-title">
	<h2 id="contact-map-title" class="screen-reader-text">
		<?php esc_html_e('Company location map', 'tersa-shop'); ?>
	</h2>

	<div class="contact-map">
		<div class="embed-map-responsive">
			<div class="embed-map-container">
				<?php echo wp_kses($map_shortcode, $allowed_map_tags); ?>
			</div>
		</div>
	</div>
</section>
