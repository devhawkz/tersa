<?php
if (!defined('ABSPATH')) {
	exit;
}

$company_settings = function_exists('tersa_get_company_settings') ? tersa_get_company_settings() : [];
$map_embed_raw    = (string) ($company_settings['contact_map_embed'] ?? '');
$map_embed        = function_exists('tersa_safe_map_embed') ? tersa_safe_map_embed($map_embed_raw) : '';

if (trim($map_embed) === '') {
	$map_embed_fallback = '<iframe
	class="embed-map-frame"
	title="' . esc_attr__('Company location on Google Maps', 'tersa-shop') . '"
	frameborder="0"
	scrolling="no" 
	marginheight="0"
	marginwidth="0"
	loading="lazy"
	src="https://maps.google.com/maps?q=Nikole%20Tesle%2071%2C%2031553%20%C4%8Crnkovci&t=m&z=14&ie=UTF8&iwloc=B&output=embed"
></iframe>';
	$map_embed = function_exists('tersa_safe_map_embed') ? tersa_safe_map_embed($map_embed_fallback) : '';
}
?>

<section class="contact-map-section" aria-labelledby="contact-map-title">
	<h2 id="contact-map-title" class="screen-reader-text">
		<?php esc_html_e('Company location map', 'tersa-shop'); ?>
	</h2>

	<div class="contact-map">
		<div class="embed-map-responsive">
			<div class="embed-map-container">
				<?php echo $map_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</section>
