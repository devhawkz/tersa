<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * URL helpers shared by templates.
 *
 * Marketing links are intentionally stricter than esc_url(): they may point to
 * this site, relative paths, or hosts explicitly allow-listed by a project
 * filter. This keeps editable CTA fields from becoming an easy phishing pivot.
 */

function tersa_get_url_host(string $url): string {
	$host = wp_parse_url($url, PHP_URL_HOST);

	return is_string($host) ? strtolower($host) : '';
}

function tersa_is_same_site_url(string $url): bool {
	$url = trim($url);

	if ($url === '') {
		return false;
	}

	if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
		return true;
	}

	$host = tersa_get_url_host($url);
	if ($host === '') {
		return true;
	}

	$home_host = tersa_get_url_host(home_url('/'));

	return $home_host !== '' && $host === $home_host;
}

function tersa_get_allowed_marketing_link_hosts(): array {
	/**
	 * Allow external CTA hosts when a campaign genuinely needs them.
	 *
	 * Example:
	 * add_filter('tersa_allowed_marketing_link_hosts', fn () => ['partner.example']);
	 *
	 * @param array<int, string> $hosts
	 */
	$hosts = apply_filters('tersa_allowed_marketing_link_hosts', []);

	return array_values(array_unique(array_filter(array_map(
		static function ($host): string {
			return strtolower(trim((string) $host));
		},
		is_array($hosts) ? $hosts : []
	))));
}

function tersa_is_allowed_marketing_link_url(string $url): bool {
	$url = trim($url);

	if ($url === '') {
		return false;
	}

	$sanitized = esc_url_raw($url, ['http', 'https']);
	if ($sanitized === '') {
		return false;
	}

	if (tersa_is_same_site_url($sanitized)) {
		return true;
	}

	$host = tersa_get_url_host($sanitized);
	if ($host === '') {
		return false;
	}

	foreach (tersa_get_allowed_marketing_link_hosts() as $allowed_host) {
		$suffix = '.' . $allowed_host;
		if ($host === $allowed_host || substr($host, -strlen($suffix)) === $suffix) {
			return true;
		}
	}

	return false;
}

function tersa_sanitize_marketing_link_url(string $url, string $fallback = ''): string {
	if (tersa_is_allowed_marketing_link_url($url)) {
		return esc_url_raw($url, ['http', 'https']);
	}

	if ($fallback !== '' && tersa_is_allowed_marketing_link_url($fallback)) {
		return esc_url_raw($fallback, ['http', 'https']);
	}

	return '';
}

function tersa_is_external_url(string $url): bool {
	$url = trim($url);

	return $url !== '' && !tersa_is_same_site_url($url);
}
