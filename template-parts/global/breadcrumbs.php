<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('woocommerce_breadcrumb')) {
	return;
}
?>

<style>
	.screen-reader-only {
	position: absolute !important;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}
</style>

<nav class="tersa-breadcrumbs .screen-reader-only" aria-label="<?php esc_attr_e('Breadcrumb', 'tersa-shop'); ?>" aria-hidden="false">
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
