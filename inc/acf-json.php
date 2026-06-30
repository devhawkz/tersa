<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Keep ACF Local JSON tied to the parent theme.
 *
 * ACF defaults to the active stylesheet directory. On environments using a child
 * theme or a different admin setup, that can make field groups fall back to the
 * older database copy instead of this theme's committed JSON files.
 */
function tersa_acf_json_path(): string {
	return trailingslashit(TERSA_THEME_DIR) . 'acf-json';
}

function tersa_acf_save_json_path(string $path): string {
	$theme_path = tersa_acf_json_path();

	return is_dir($theme_path) ? $theme_path : $path;
}
add_filter('acf/settings/save_json', 'tersa_acf_save_json_path');

/**
 * @param string[] $paths ACF Local JSON load paths.
 * @return string[]
 */
function tersa_acf_load_json_paths(array $paths): array {
	$theme_path = tersa_acf_json_path();

	if (is_dir($theme_path) && !in_array($theme_path, $paths, true)) {
		$paths[] = $theme_path;
	}

	return $paths;
}
add_filter('acf/settings/load_json', 'tersa_acf_load_json_paths');
