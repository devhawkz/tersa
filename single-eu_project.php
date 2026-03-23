<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();

	$status       = function_exists('get_field') ? get_field('eu_project_status') : '';
	$program      = function_exists('get_field') ? get_field('eu_project_program') : '';
	$full_title   = function_exists('get_field') ? get_field('eu_project_full_title') : '';
	$beneficiary  = function_exists('get_field') ? get_field('eu_project_beneficiary') : '';
	$description  = function_exists('get_field') ? get_field('eu_project_short_description') : '';
	$total_value  = function_exists('get_field') ? get_field('eu_project_total_value') : '';
	$eu_funding   = function_exists('get_field') ? get_field('eu_project_eu_funding') : '';
	$start_date   = function_exists('get_field') ? get_field('eu_project_start_date') : '';
	$end_date     = function_exists('get_field') ? get_field('eu_project_end_date') : '';
	$contact_info = function_exists('get_field') ? get_field('eu_project_contact_info') : '';
	$pdf          = function_exists('get_field') ? get_field('eu_project_pdf') : '';
	$cta_label    = function_exists('get_field') ? get_field('eu_project_cta_label') : '';
	$cta_url      = function_exists('get_field') ? get_field('eu_project_cta_url') : '';

	$logo_one   = function_exists('get_field') ? get_field('eu_project_logos_one') : '';
	$logo_two   = function_exists('get_field') ? get_field('eu_project_logos_two') : '';
	$logo_three = function_exists('get_field') ? get_field('eu_project_logos_three') : '';
	$logo_four  = function_exists('get_field') ? get_field('eu_project_logos_four') : '';
	$logo_five  = function_exists('get_field') ? get_field('eu_project_logos_five') : '';

	$logos = array_filter([
		$logo_one,
		$logo_two,
		$logo_three,
		$logo_four,
		$logo_five,
	]);

	$display_title = $full_title ? $full_title : get_the_title();

	$pdf_url = '';
	if (is_array($pdf) && !empty($pdf['url'])) {
		$pdf_url = $pdf['url'];
	} elseif (is_string($pdf) && !empty($pdf)) {
		$pdf_url = $pdf;
	}

	$format_date = static function ($date_value) {
		if (empty($date_value)) {
			return '';
		}

		if (preg_match('/^\d{2}\.\d{2}\.\d{4}\.?$/', $date_value)) {
			return $date_value;
		}

		$timestamp = strtotime($date_value);
		if (!$timestamp) {
			return $date_value;
		}

		return date_i18n('d.m.Y.', $timestamp);
	};

	$start_date_formatted = $format_date($start_date);
	$end_date_formatted   = $format_date($end_date);
	?>

	<main id="primary" class="site-main eu-project-single">
		<section class="eu-project-single__hero">
			<div class="container container--narrow">
				<nav class="eu-project-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'tersa-shop'); ?>">
					<a href="<?php echo esc_url(get_post_type_archive_link('eu_project')); ?>">
						<?php esc_html_e('EU projekti', 'tersa-shop'); ?>
					</a>
					<span>/</span>
					<span><?php the_title(); ?></span>
				</nav>

				<?php if (!empty($logos)) : ?>
					<div class="eu-project-single__logos-wrap">
						<div class="eu-project-single__logos">
							<?php foreach ($logos as $logo) : ?>
								<?php
								$logo_url = '';
								$logo_alt = get_the_title();

								if (is_array($logo)) {
									$logo_url = !empty($logo['sizes']['medium_large'])
										? $logo['sizes']['medium_large']
										: (!empty($logo['url']) ? $logo['url'] : '');

									if (!empty($logo['alt'])) {
										$logo_alt = $logo['alt'];
									}
								} elseif (is_numeric($logo)) {
									$logo_url = wp_get_attachment_image_url((int) $logo, 'medium_large');
									$logo_alt = get_post_meta((int) $logo, '_wp_attachment_image_alt', true) ?: get_the_title();
								} elseif (is_string($logo)) {
									$logo_url = $logo;
								}

								if (!$logo_url) {
									continue;
								}
								?>
								<div class="eu-project-single__logo-item">
									<img
										src="<?php echo esc_url($logo_url); ?>"
										alt="<?php echo esc_attr($logo_alt); ?>"
										loading="lazy"
										decoding="async"
									>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="eu-project-single__hero-card">
					

					<h1 class="eu-project-single__title">
						<?php echo esc_html($display_title); ?>
					</h1>

					<div class="eu-project-single__period">
						<?php if ($start_date_formatted || $end_date_formatted) : ?>
							<p>
								<strong><?php esc_html_e('Razdoblje projekta:', 'tersa-shop'); ?></strong>
								<?php echo esc_html(trim($start_date_formatted . ' – ' . $end_date_formatted, ' –')); ?>
							</p>
						<?php endif; ?>
					</div>

					<?php if ($pdf_url || ($cta_url && $cta_label)) : ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="eu-project-single__overview section">
			<div class="container container--narrow">
				<div class="eu-project-single__facts-grid">
					<?php if ($beneficiary) : ?>
						<div class="eu-project-single__fact-card">
							<h2><?php esc_html_e('Korisnik sredstava', 'tersa-shop'); ?></h2>
							<div class="eu-project-single__fact-content">
								<?php echo wpautop(wp_kses_post($beneficiary)); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($total_value) : ?>
						<div class="eu-project-single__fact-card">
							<h2><?php esc_html_e('Vrijednost projekta', 'tersa-shop'); ?></h2>
							<div class="eu-project-single__fact-content">
								<p><?php echo esc_html($total_value); ?></p>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($eu_funding) : ?>
						<div class="eu-project-single__fact-card">
							<h2><?php esc_html_e('Iznos koji sufinancira EU', 'tersa-shop'); ?></h2>
							<div class="eu-project-single__fact-content">
								<?php echo wpautop(wp_kses_post($eu_funding)); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($start_date_formatted || $end_date_formatted) : ?>
						<div class="eu-project-single__fact-card">
							<h2><?php esc_html_e('Trajanje projekta', 'tersa-shop'); ?></h2>
							<div class="eu-project-single__fact-content">
								<?php if ($start_date_formatted) : ?>
									<p>
										<strong><?php esc_html_e('Početak:', 'tersa-shop'); ?></strong>
										<?php echo esc_html($start_date_formatted); ?>
									</p>
								<?php endif; ?>

								<?php if ($end_date_formatted) : ?>
									<p>
										<strong><?php esc_html_e('Završetak:', 'tersa-shop'); ?></strong>
										<?php echo esc_html($end_date_formatted); ?>
									</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ($description) : ?>
			<section class="eu-project-single__section section">
				<div class="container container--narrow">
					<div class="eu-project-single__content-card">
						<div class="eu-project-single__section-head">
							<span class="eu-project-single__section-kicker"><?php esc_html_e('Projekt', 'tersa-shop'); ?></span>
							<h2><?php esc_html_e('Kratki opis projekta', 'tersa-shop'); ?></h2>
						</div>

						<div class="eu-project-single__richtext">
							<?php echo wpautop(wp_kses_post($description)); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($contact_info) : ?>
			<section class="eu-project-single__section eu-project-single__section--contact section">
				<div class="container container--narrow">
					<div class="eu-project-single__content-card">
						<div class="eu-project-single__section-head">
							<span class="eu-project-single__section-kicker"><?php esc_html_e('Informacije', 'tersa-shop'); ?></span>
							<h2><?php esc_html_e('Kontakt osobe za više informacija', 'tersa-shop'); ?></h2>
						</div>

						<div class="eu-project-single__richtext">
							<?php echo wpautop(wp_kses_post($contact_info)); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (get_the_content()) : ?>
			<section class="eu-project-single__section section">
				<div class="container container--narrow">
					<div class="eu-project-single__content-card">
						<div class="eu-project-single__section-head">
							<span class="eu-project-single__section-kicker"><?php esc_html_e('Dodatno', 'tersa-shop'); ?></span>
							<h2><?php esc_html_e('Dodatne informacije', 'tersa-shop'); ?></h2>
						</div>

						<div class="eu-project-single__richtext entry-content">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();