<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('get_field')) {
	return;
}

$page_id = isset($args['page_id']) ? (int) $args['page_id'] : get_queried_object_id();

if (!$page_id) {
	return;
}

$fields = isset($args['fields']) && is_array($args['fields'])
	? $args['fields']
	: (get_fields($page_id) ?: []);

$show_section = !empty($fields['show_home_shop_by_category']);

if (!$show_section) {
	return;
}

$section_title = $fields['home_shop_by_category_title'] ?? '';

$items_raw = [
	$fields['home_category_item_1'] ?? null,
	$fields['home_category_item_2'] ?? null,
	$fields['home_category_item_3'] ?? null,
	$fields['home_category_item_4'] ?? null,
];

$theme_uri = get_template_directory_uri();
$theme_dir = get_template_directory();

$icon_files = [
	'icon-4-2x.png',
	'icon-4-2x.png',
	'icon-4-2x.png',
	'icon-4-2x.png'
];

$items = [];

foreach ($items_raw as $index => $item) {
	if (
		empty($item) ||
		empty($item['title']) ||
		empty($item['link'])
	) {
		continue;
	}

	$icon_file = $icon_files[$index] ?? 'icon-1-2x.svg';
	$icon_path = $theme_dir . '/assets/img/' . $icon_file;
	$icon_url  = file_exists($icon_path)
		? $theme_uri . '/assets/img/' . $icon_file
		: $theme_uri . '/assets/img/heart-svgrepo-com.svg';

	$items[] = [
		'title'    => $item['title'],
		'url'      => $item['link'],
		'icon_url' => $icon_url,
	];
}

if (empty($items)) {
	return;
}
?>

<section class="home-shop-categories" aria-labelledby="home-shop-categories-title">
	<div class="home-shop-categories__inner">
		<?php if (!empty($section_title)) : ?>
			<h2 id="home-shop-categories-title" class="home-shop-categories__title">
				<?php echo esc_html($section_title); ?>
			</h2>
		<?php endif; ?>

		<div class="home-shop-categories__grid">
			<?php foreach ($items as $item) : ?>
				<a class="home-shop-categories__item" href="<?php echo esc_url($item['url']); ?>">
					<span class="home-shop-categories__icon-wrap" aria-hidden="true">
						<span class="home-shop-categories__icon-bg"></span>
						<span class="home-shop-categories__icon">
							<img
								src="<?php echo esc_url($item['icon_url']); ?>"
								alt=""
								loading="lazy"
								decoding="async"
								width="86"
								height="86"
							>
						</span>
					</span>

					<span class="home-shop-categories__text">
						<span class="home-shop-categories__name">
							<?php echo esc_html($item['title']); ?>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>