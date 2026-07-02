<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('woocommerce_breadcrumb')) {
	return;
}
?>

<nav class="tersa-breadcrumbs" aria-label="<?php echo esc_attr(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Breadcrumb') : __('Breadcrumb', 'tersa-shop')); ?>">
	<?php
	woocommerce_breadcrumb([
		'delimiter'   => ' / ',
		'wrap_before' => '<ol class="tersa-breadcrumbs__list">',
		'wrap_after'  => '</ol>',
		'before'      => '<li class="tersa-breadcrumbs__item">',
		'after'       => '</li>',
		'home'        => esc_html(function_exists('tersa_translate_ui_string') ? tersa_translate_ui_string('Naslovnica') : __('Naslovnica', 'tersa-shop')),
	]);
	?>
</nav>
