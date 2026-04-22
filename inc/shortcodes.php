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

	if (!preg_match('/^\[contact-form-7\b[^\]]*\]$/i', $shortcode)) {
		return '';
	}

	return (string) do_shortcode($shortcode);
}
