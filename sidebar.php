<?php
defined('ABSPATH') || exit;

if (!is_active_sidebar('primary-sidebar')) {
	return;
}
?>

<aside
	id="secondary"
	class="widget-area tersa-sidebar"
	aria-label="<?php echo esc_attr__('Bočna traka', 'tersa-shop'); ?>"
	role="complementary"
>
	<div class="tersa-sidebar__inner">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</div>
</aside>