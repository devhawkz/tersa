<?php
if (!defined('ABSPATH')) {
	exit;
}

$map_shortcode = ' <iframe class="embed-map-frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=Nikole%20Tesle%2071%2C%2031553%20%C4%8Crnkovci&t=m&z=14&ie=UTF8&iwloc=B&output=embed"></iframe>
  <a href="https://embed-maps.org" style="font-size:2px!important;color:gray!important;position:absolute;bottom:0;left:0;z-index:1;max-height:1px;overflow:hidden"></a>';
?>

<section class="contact-map-section" aria-labelledby="contact-map-title">
	<h2 id="contact-map-title" class="screen-reader-text">
		<?php esc_html_e('Company location map', 'tersa-shop'); ?>
	</h2>

	<div class="contact-map">
	<div class="embed-map-responsive">
<div class="embed-map-container">
 <?php echo $map_shortcode; ?>
</div>
<style>
  .embed-map-responsive{position:relative;text-align:right;width:100%;height:0;padding-bottom:0;}
  .embed-map-container{overflow:hidden;background:none!important;width:100%;height:100%;position:absolute;top:0;left:0;}
  .embed-map-frame{width:100%!important;height:100%!important;position:absolute;top:0;left:0;}
</style>
</div>
	</div>
</section>