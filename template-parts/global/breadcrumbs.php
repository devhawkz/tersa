<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('woocommerce_breadcrumb')) {
	return;
}
?>
<nav class="tersa-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'tersa-shop'); ?>" aria-hidden="false" style="display:none;">
	<?php
	woocommerce_breadcrumb([
		'delimiter'   => ' / ',
		'wrap_before' => '<ol class="tersa-breadcrumbs__list">',
		'wrap_after'  => '</ol>',
		'before'      => '<li class="tersa-breadcrumbs__item">',
		'after'       => '</li>',
		'home'        => esc_html__('Naslovnica', 'tersa-shop'),
	]);
	?>
</nav>
