<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Execute only Contact Form 7 shortcodes from theme options.
 * This avoids running arbitrary shortcodes from editable settings.
 */
function tersa_safe_cf7_shortcode_output(string $shortcode): string {
	$shortcode = trim($shortcode);
	if ($shortcode === '' || !function_exists('do_shortcode')) {
		return '';
	}

	if (!preg_match('/^\[contact-form-7\b([^\]]*)\]$/i', $shortcode, $matches)) {
		return '';
	}

	$raw_atts = shortcode_parse_atts($matches[1] ?? '');
	$raw_atts = is_array($raw_atts) ? $raw_atts : [];
	$allowed  = ['id', 'title', 'html_id', 'html_class'];
	$atts     = [];

	foreach ($raw_atts as $name => $value) {
		if (!is_string($name)) {
			return '';
		}

		$name = sanitize_key($name);
		if (!in_array($name, $allowed, true)) {
			return '';
		}

		if (is_array($value) || is_object($value)) {
			return '';
		}

		$atts[$name] = sanitize_text_field((string) $value);
	}

	if (empty($atts['id'])) {
		return '';
	}

	$shortcode = '[contact-form-7';
	foreach ($atts as $name => $value) {
		$shortcode .= sprintf(' %s="%s"', $name, esc_attr($value));
	}
	$shortcode .= ']';

	return (string) do_shortcode($shortcode);
}
