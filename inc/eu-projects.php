<?php
if (!defined('ABSPATH')) {
	exit;
}

function tersa_register_eu_projects_cpt(): void {
	$labels = [
		'name'               => tersa_get_eu_project_text('cpt_name'),
		'singular_name'      => tersa_get_eu_project_text('cpt_singular_name'),
		'menu_name'          => tersa_get_eu_project_text('cpt_menu_name'),
		'add_new'            => tersa_get_eu_project_text('cpt_add_new'),
		'add_new_item'       => tersa_get_eu_project_text('cpt_add_new_item'),
		'edit_item'          => tersa_get_eu_project_text('cpt_edit_item'),
		'new_item'           => tersa_get_eu_project_text('cpt_new_item'),
		'view_item'          => tersa_get_eu_project_text('cpt_view_item'),
		'all_items'          => tersa_get_eu_project_text('cpt_all_items'),
		'search_items'       => tersa_get_eu_project_text('cpt_search_items'),
		'not_found'          => tersa_get_eu_project_text('cpt_not_found'),
		'not_found_in_trash' => tersa_get_eu_project_text('cpt_not_found_in_trash'),
	];

	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'has_archive'        => true,
		'rewrite'            => [
			'slug'       => 'eu-projekti',
			'with_front' => false,
		],
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
	];

	register_post_type('eu_project', $args);
}
add_action('init', 'tersa_register_eu_projects_cpt');

/**
 * Make EU projects translatable in Polylang, matching regular Pages behavior.
 *
 * @param array<string, string> $post_types Polylang translatable post types.
 * @param bool                 $is_settings Whether Polylang is rendering settings.
 *
 * @return array<string, string>
 */
function tersa_enable_eu_projects_polylang(array $post_types, bool $is_settings = false): array {
	unset($is_settings);

	$post_types['eu_project'] = 'eu_project';

	return $post_types;
}
add_filter('pll_get_post_types', 'tersa_enable_eu_projects_polylang', 10, 2);

/**
 * Return the real Polylang language slug for frontend queries.
 */
function tersa_get_eu_project_query_language_slug(): string {
	if (function_exists('pll_current_language')) {
		$lang = pll_current_language('slug');
		if (is_string($lang) && '' !== $lang) {
			return sanitize_key($lang);
		}
	}

	$query_lang = get_query_var('lang');
	if (is_scalar($query_lang) && '' !== $query_lang) {
		return sanitize_key((string) $query_lang);
	}

	return '';
}

/**
 * Identify frontend EU project collection queries that must stay language-scoped.
 */
function tersa_is_eu_project_collection_query(WP_Query $query): bool {
	if ($query->is_singular('eu_project')) {
		return false;
	}

	if ($query->is_post_type_archive('eu_project')) {
		return true;
	}

	$post_type = $query->get('post_type');
	if (empty($post_type) || 'any' === $post_type) {
		return false;
	}

	return in_array('eu_project', (array) $post_type, true);
}

/**
 * Keep EU project archives/collections limited to the active Polylang language.
 */
function tersa_filter_eu_project_collections_by_language(WP_Query $query): void {
	if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
		return;
	}

	if ($query->get('tersa_all_languages')) {
		return;
	}

	if (!tersa_is_eu_project_collection_query($query)) {
		return;
	}

	$lang = tersa_get_eu_project_query_language_slug();
	if ('' === $lang) {
		return;
	}

	$query->set('lang', $lang);
	$query->set('suppress_filters', false);
}
add_action('pre_get_posts', 'tersa_filter_eu_project_collections_by_language', 20);

/**
 * Existing EU projects created before Polylang support may have no language.
 * Assign only those unlabelled posts to the default language so default-language
 * archives do not appear empty. Already translated EN/DE posts are untouched.
 */
function tersa_backfill_eu_project_default_language(): void {
	if (!is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
		return;
	}

	if (!current_user_can('edit_posts')) {
		return;
	}

	if (!function_exists('pll_get_post_language') || !function_exists('pll_set_post_language')) {
		return;
	}

	$default_lang = '';
	if (function_exists('pll_default_language')) {
		$default_lang = pll_default_language('slug');
		if (!is_string($default_lang) || '' === $default_lang) {
			$default_lang = pll_default_language();
		}
	}

	$default_lang = is_string($default_lang) ? sanitize_key($default_lang) : '';
	if ('' === $default_lang) {
		return;
	}

	$project_ids = get_posts([
		'post_type'        => 'eu_project',
		'post_status'      => 'any',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'suppress_filters' => true,
	]);

	foreach ($project_ids as $project_id) {
		$project_id = absint($project_id);
		if (!$project_id) {
			continue;
		}

		$post_lang = pll_get_post_language($project_id, 'slug');
		if (is_string($post_lang) && '' !== $post_lang) {
			continue;
		}

		pll_set_post_language($project_id, $default_lang);
	}
}
add_action('admin_init', 'tersa_backfill_eu_project_default_language');

/**
 * Normalize a Polylang/WP locale to the language set supported by this theme.
 */
function tersa_normalize_eu_project_language_slug(string $lang = ''): string {
	$lang = strtolower(str_replace('_', '-', trim($lang)));
	$lang = $lang ? substr($lang, 0, 2) : '';

	return in_array($lang, ['hr', 'en', 'de'], true) ? $lang : 'hr';
}

/**
 * Detect the correct language for EU project admin/frontend context.
 */
function tersa_get_eu_project_language_slug(string $fallback = ''): string {
	if (is_admin()) {
		$post_id = 0;
		if (isset($_GET['post']) && is_scalar($_GET['post'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id = absint(wp_unslash((string) $_GET['post'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ($post_id && function_exists('pll_get_post_language')) {
			$post_lang = pll_get_post_language($post_id, 'slug');
			if (is_string($post_lang) && '' !== $post_lang) {
				return tersa_normalize_eu_project_language_slug($post_lang);
			}
		}

		foreach (['new_lang', 'lang'] as $query_key) {
			if (empty($_GET[$query_key]) || !is_scalar($_GET[$query_key])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				continue;
			}

			$query_lang = sanitize_key(wp_unslash((string) $_GET[$query_key])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ('' !== $query_lang) {
				return tersa_normalize_eu_project_language_slug($query_lang);
			}
		}
	}

	if ('' !== $fallback) {
		return tersa_normalize_eu_project_language_slug($fallback);
	}

	if (function_exists('pll_current_language')) {
		$current_lang = pll_current_language('slug');
		if (is_string($current_lang) && '' !== $current_lang) {
			return tersa_normalize_eu_project_language_slug($current_lang);
		}
	}

	if (function_exists('determine_locale')) {
		return tersa_normalize_eu_project_language_slug((string) determine_locale());
	}

	return 'hr';
}

/**
 * UI labels used by the EU project CPT, archive and single templates.
 *
 * @return array<string, array<string, string>>
 */
function tersa_get_eu_project_text_labels(): array {
	static $labels = null;

	if (null !== $labels) {
		return $labels;
	}

	$labels = [
		'cpt_name'               => [
			'hr' => 'EU Projekti',
			'en' => 'EU Projects',
			'de' => 'EU-Projekte',
		],
		'cpt_singular_name'      => [
			'hr' => 'EU Projekt',
			'en' => 'EU Project',
			'de' => 'EU-Projekt',
		],
		'cpt_menu_name'          => [
			'hr' => 'EU Projekti',
			'en' => 'EU Projects',
			'de' => 'EU-Projekte',
		],
		'cpt_add_new'            => [
			'hr' => 'Dodaj novi',
			'en' => 'Add New',
			'de' => 'Neu hinzufügen',
		],
		'cpt_add_new_item'       => [
			'hr' => 'Dodaj novi EU projekt',
			'en' => 'Add New EU Project',
			'de' => 'Neues EU-Projekt hinzufügen',
		],
		'cpt_edit_item'          => [
			'hr' => 'Uredi EU projekt',
			'en' => 'Edit EU Project',
			'de' => 'EU-Projekt bearbeiten',
		],
		'cpt_new_item'           => [
			'hr' => 'Novi EU projekt',
			'en' => 'New EU Project',
			'de' => 'Neues EU-Projekt',
		],
		'cpt_view_item'          => [
			'hr' => 'Pogledaj EU projekt',
			'en' => 'View EU Project',
			'de' => 'EU-Projekt ansehen',
		],
		'cpt_all_items'          => [
			'hr' => 'Svi EU projekti',
			'en' => 'All EU Projects',
			'de' => 'Alle EU-Projekte',
		],
		'cpt_search_items'       => [
			'hr' => 'Traži EU projekte',
			'en' => 'Search EU Projects',
			'de' => 'EU-Projekte suchen',
		],
		'cpt_not_found'          => [
			'hr' => 'Nema EU projekata.',
			'en' => 'No EU projects found.',
			'de' => 'Keine EU-Projekte gefunden.',
		],
		'cpt_not_found_in_trash' => [
			'hr' => 'Nema EU projekata u smeću.',
			'en' => 'No EU projects found in Trash.',
			'de' => 'Keine EU-Projekte im Papierkorb gefunden.',
		],
		'breadcrumbs'            => [
			'hr' => 'Breadcrumbs',
			'en' => 'Breadcrumbs',
			'de' => 'Breadcrumbs',
		],
		'home'                   => [
			'hr' => 'Naslovnica',
			'en' => 'Home',
			'de' => 'Startseite',
		],
		'archive_title'          => [
			'hr' => 'EU Projekti',
			'en' => 'EU Projects',
			'de' => 'EU-Projekte',
		],
		'archive_link'           => [
			'hr' => 'EU projekti',
			'en' => 'EU projects',
			'de' => 'EU-Projekte',
		],
		'view_project'           => [
			'hr' => 'Vidi projekt',
			'en' => 'View project',
			'de' => 'Projekt ansehen',
		],
		'pagination_prev'        => [
			'hr' => 'Prethodna',
			'en' => 'Previous',
			'de' => 'Zurück',
		],
		'pagination_next'        => [
			'hr' => 'Sljedeća',
			'en' => 'Next',
			'de' => 'Weiter',
		],
		'archive_empty_title'    => [
			'hr' => 'Trenutno nema dostupnih EU projekata.',
			'en' => 'There are currently no EU projects available.',
			'de' => 'Derzeit sind keine EU-Projekte verfügbar.',
		],
		'archive_empty_text'     => [
			'hr' => 'Kada projekti budu objavljeni, pojavit će se na ovoj stranici.',
			'en' => 'When projects are published, they will appear on this page.',
			'de' => 'Sobald Projekte veröffentlicht werden, erscheinen sie auf dieser Seite.',
		],
		'project_period'         => [
			'hr' => 'Razdoblje projekta:',
			'en' => 'Project period:',
			'de' => 'Projektzeitraum:',
		],
		'download_document'      => [
			'hr' => 'Preuzmi dokument',
			'en' => 'Download document',
			'de' => 'Dokument herunterladen',
		],
		'beneficiary'            => [
			'hr' => 'Korisnik sredstava',
			'en' => 'Beneficiary',
			'de' => 'Begünstigter',
		],
		'project_value'          => [
			'hr' => 'Vrijednost projekta',
			'en' => 'Project value',
			'de' => 'Projektwert',
		],
		'eu_funding'             => [
			'hr' => 'Iznos koji sufinancira EU',
			'en' => 'Amount co-financed by the EU',
			'de' => 'Von der EU kofinanzierter Betrag',
		],
		'project_duration'       => [
			'hr' => 'Trajanje projekta',
			'en' => 'Project duration',
			'de' => 'Projektdauer',
		],
		'start'                  => [
			'hr' => 'Početak:',
			'en' => 'Start:',
			'de' => 'Beginn:',
		],
		'end'                    => [
			'hr' => 'Završetak:',
			'en' => 'End:',
			'de' => 'Ende:',
		],
		'project'                => [
			'hr' => 'Projekt',
			'en' => 'Project',
			'de' => 'Projekt',
		],
		'short_description'      => [
			'hr' => 'Kratki opis projekta',
			'en' => 'Short project description',
			'de' => 'Kurze Projektbeschreibung',
		],
		'information'            => [
			'hr' => 'Informacije',
			'en' => 'Information',
			'de' => 'Informationen',
		],
		'contact_more_info'      => [
			'hr' => 'Kontakt osobe za više informacija',
			'en' => 'Contact persons for more information',
			'de' => 'Kontaktpersonen für weitere Informationen',
		],
		'additional'             => [
			'hr' => 'Dodatno',
			'en' => 'Additional',
			'de' => 'Zusätzlich',
		],
		'additional_info'        => [
			'hr' => 'Dodatne informacije',
			'en' => 'Additional information',
			'de' => 'Zusätzliche Informationen',
		],
		'date_format'            => [
			'hr' => 'd.m.Y.',
			'en' => 'd/m/Y',
			'de' => 'd.m.Y',
		],
	];

	return $labels;
}

/**
 * Return a translated EU project UI label without relying on compiled gettext files.
 */
function tersa_get_eu_project_text(string $key, string $lang = ''): string {
	$labels = tersa_get_eu_project_text_labels();
	$lang   = tersa_get_eu_project_language_slug($lang);

	return $labels[$key][$lang] ?? $labels[$key]['hr'] ?? $key;
}

/**
 * EU project status labels keyed by a stable internal status id.
 *
 * @return array<string, array<string, string>>
 */
function tersa_get_eu_project_status_labels(): array {
	return [
		'completed'      => [
			'hr' => 'Završen',
			'en' => 'Completed',
			'de' => 'Abgeschlossen',
		],
		'active'         => [
			'hr' => 'Aktivan',
			'en' => 'Active',
			'de' => 'Aktiv',
		],
		'in_development' => [
			'hr' => 'U razvoju',
			'en' => 'In development',
			'de' => 'In Entwicklung',
		],
	];
}

/**
 * Existing Croatian values are kept as stored values for backward compatibility.
 */
function tersa_get_eu_project_status_stored_value(string $status_key): string {
	$labels = tersa_get_eu_project_status_labels();

	return $labels[$status_key]['hr'] ?? '';
}

/**
 * Return ACF choices using existing Croatian values and translated labels.
 *
 * @return array<string, string>
 */
function tersa_get_eu_project_status_choices(string $lang = ''): array {
	$lang    = tersa_get_eu_project_language_slug($lang);
	$choices = [];

	foreach (tersa_get_eu_project_status_labels() as $status_key => $labels) {
		$value           = tersa_get_eu_project_status_stored_value($status_key);
		$choices[$value] = $labels[$lang] ?? $labels['hr'];
	}

	return $choices;
}

/**
 * Normalize existing and future status values/labels to a stable status id.
 *
 * @param mixed $status Raw ACF status value.
 */
function tersa_get_eu_project_status_key($status): string {
	if (is_array($status)) {
		$status = $status['value'] ?? $status['label'] ?? reset($status);
	}

	if (!is_scalar($status)) {
		return '';
	}

	$normalized = str_replace('_', '-', sanitize_title((string) $status));
	$aliases    = [
		'zavrsen'        => 'completed',
		'completed'      => 'completed',
		'abgeschlossen'  => 'completed',
		'aktivan'        => 'active',
		'active'         => 'active',
		'aktiv'          => 'active',
		'u-razvoju'      => 'in_development',
		'in-development' => 'in_development',
		'in-entwicklung' => 'in_development',
	];

	return $aliases[$normalized] ?? '';
}

/**
 * Translate an EU project status value for the current language.
 *
 * @param mixed $status Raw ACF status value.
 */
function tersa_translate_eu_project_status($status, string $lang = ''): string {
	$key = tersa_get_eu_project_status_key($status);

	if ('' !== $key) {
		$labels = tersa_get_eu_project_status_labels();
		$lang   = tersa_get_eu_project_language_slug($lang);

		return $labels[$key][$lang] ?? $labels[$key]['hr'];
	}

	if (is_array($status)) {
		$status = $status['label'] ?? $status['value'] ?? reset($status);
	}

	return is_scalar($status) ? trim(wp_strip_all_tags((string) $status)) : '';
}

/**
 * Translate the ACF select choices in the admin UI.
 *
 * @param array<string, mixed> $field ACF field settings.
 *
 * @return array<string, mixed>
 */
function tersa_load_eu_project_status_field_choices(array $field): array {
	$field['choices'] = tersa_get_eu_project_status_choices();

	return $field;
}
add_filter('acf/load_field/name=eu_project_status', 'tersa_load_eu_project_status_field_choices');

/**
 * Normalize translated legacy labels back to the stored status value.
 *
 * @param mixed $value Raw ACF value.
 *
 * @return mixed
 */
function tersa_normalize_eu_project_status_value($value) {
	$status_key = tersa_get_eu_project_status_key($value);

	if ('' === $status_key) {
		return $value;
	}

	$stored_value = tersa_get_eu_project_status_stored_value($status_key);

	return '' !== $stored_value ? $stored_value : $value;
}
add_filter('acf/load_value/name=eu_project_status', 'tersa_normalize_eu_project_status_value');
add_filter('acf/update_value/name=eu_project_status', 'tersa_normalize_eu_project_status_value');

/**
 * EU project ACF/media meta fields must stay independent across translations.
 *
 * Polylang can copy/sync selected custom fields between translated posts. For
 * EU projects we deliberately exclude the public content fields so Croatian,
 * English and German project pages can have genuinely separate values.
 *
 * @return string[]
 */
function tersa_get_eu_project_independent_meta_keys(): array {
	static $keys = null;

	if (null !== $keys) {
		return $keys;
	}

	$keys = ['_thumbnail_id'];

	$fields = [
		'eu_project_card_title',
		'eu_project_card_description',
		'eu_project_status',
		'eu_project_program',
		'eu_project_full_title',
		'eu_project_beneficiary',
		'eu_project_short_description',
		'eu_project_total_value',
		'eu_project_eu_funding',
		'eu_project_start_date',
		'eu_project_end_date',
		'eu_project_contact_info',
		'eu_project_pdf',
		'eu_project_cta_label',
		'eu_project_cta_url',
		'eu_project_logos_one',
		'eu_project_logos_two',
		'eu_project_logos_three',
		'eu_project_logos_four',
		'eu_project_logos_five',
	];

	foreach ($fields as $field) {
		$keys[] = $field;
		$keys[] = '_' . $field;
	}

	return $keys;
}

/**
 * Remove EU project content fields from Polylang copy/sync meta lists.
 *
 * @param array<int, string> $meta_keys Meta keys Polylang is about to copy/sync.
 * @param bool              $sync      Whether this is a sync operation.
 * @param int|WP_Post       $from      Source post.
 * @param int|WP_Post       $to        Destination post.
 * @param string            $lang      Destination language.
 *
 * @return array<int, string>
 */
function tersa_keep_eu_project_meta_independent(array $meta_keys, bool $sync = false, $from = 0, $to = 0, string $lang = ''): array {
	unset($sync, $lang);

	$get_post_type = static function ($post): string {
		if ($post instanceof WP_Post) {
			return (string) $post->post_type;
		}

		if (!is_numeric($post)) {
			return '';
		}

		$post_id = absint($post);

		return $post_id ? (string) get_post_type($post_id) : '';
	};

	if ('eu_project' !== $get_post_type($from) && 'eu_project' !== $get_post_type($to)) {
		return $meta_keys;
	}

	return array_values(array_diff($meta_keys, tersa_get_eu_project_independent_meta_keys()));
}
add_filter('pll_copy_post_metas', 'tersa_keep_eu_project_meta_independent', 10, 5);
