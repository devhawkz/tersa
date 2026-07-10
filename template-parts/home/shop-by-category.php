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
$fallback_icon_file = 'icon-4-2x.png';
$fallback_icon_path = $theme_dir . '/assets/img/' . $fallback_icon_file;
$fallback_icon_url  = file_exists($fallback_icon_path)
	? $theme_uri . '/assets/img/' . $fallback_icon_file
	: $theme_uri . '/assets/img/heart-svgrepo-com.svg';

if (!function_exists('tersa_shop_category_icon_data')) {
	function tersa_shop_category_icon_data($image, $fallback_url) {
		if (is_array($image)) {
			$image_id = isset($image['ID']) ? absint($image['ID']) : (isset($image['id']) ? absint($image['id']) : 0);

			if ($image_id) {
				$src = wp_get_attachment_image_src($image_id, 'medium');

				if (!empty($src[0])) {
					return [
						'url'    => esc_url_raw((string) $src[0]),
						'alt'    => isset($image['alt']) && is_string($image['alt']) ? $image['alt'] : '',
						'width'  => isset($src[1]) ? absint($src[1]) : 86,
						'height' => isset($src[2]) ? absint($src[2]) : 86,
					];
				}

				$attachment_url = wp_get_attachment_url($image_id);

				if ($attachment_url) {
					return [
						'url'    => esc_url_raw((string) $attachment_url),
						'alt'    => isset($image['alt']) && is_string($image['alt']) ? $image['alt'] : '',
						'width'  => 86,
						'height' => 86,
					];
				}
			}

			if (!empty($image['url']) && is_string($image['url'])) {
				return [
					'url'    => esc_url_raw($image['url']),
					'alt'    => isset($image['alt']) && is_string($image['alt']) ? $image['alt'] : '',
					'width'  => isset($image['width']) ? absint($image['width']) : 86,
					'height' => isset($image['height']) ? absint($image['height']) : 86,
				];
			}
		}

		if (is_numeric($image)) {
			$image_id = absint($image);
			$src      = wp_get_attachment_image_src($image_id, 'medium');

			if (!empty($src[0])) {
				return [
					'url'    => esc_url_raw((string) $src[0]),
					'alt'    => (string) get_post_meta($image_id, '_wp_attachment_image_alt', true),
					'width'  => isset($src[1]) ? absint($src[1]) : 86,
					'height' => isset($src[2]) ? absint($src[2]) : 86,
				];
			}

			$attachment_url = wp_get_attachment_url($image_id);

			if ($attachment_url) {
				return [
					'url'    => esc_url_raw((string) $attachment_url),
					'alt'    => (string) get_post_meta($image_id, '_wp_attachment_image_alt', true),
					'width'  => 86,
					'height' => 86,
				];
			}
		}

		if (is_string($image) && '' !== trim($image)) {
			return [
				'url'    => esc_url_raw($image),
				'alt'    => '',
				'width'  => 86,
				'height' => 86,
			];
		}

		return [
			'url'    => esc_url_raw((string) $fallback_url),
			'alt'    => '',
			'width'  => 86,
			'height' => 86,
		];
	}
}

$items = [];

foreach ($items_raw as $item) {
	if (
		empty($item) ||
		empty($item['title']) ||
		empty($item['link'])
	) {
		continue;
	}

	$icon = tersa_shop_category_icon_data($item['image'] ?? null, $fallback_icon_url);

	$items[] = [
		'title'       => $item['title'],
		'url'         => $item['link'],
		'icon_url'    => $icon['url'],
		'icon_alt'    => $icon['alt'],
		'icon_width'  => $icon['width'],
		'icon_height' => $icon['height'],
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
								alt="<?php echo esc_attr($item['icon_alt']); ?>"
								loading="lazy"
								decoding="async"
								width="<?php echo esc_attr($item['icon_width']); ?>"
								height="<?php echo esc_attr($item['icon_height']); ?>"
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
