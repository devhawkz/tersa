<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

while (have_posts()) :
	the_post();

	$has_acf = function_exists('get_field');

	$status       = $has_acf ? get_field('eu_project_status') : '';
	$program      = $has_acf ? get_field('eu_project_program') : '';
	$full_title   = $has_acf ? get_field('eu_project_full_title') : '';
	$beneficiary  = $has_acf ? get_field('eu_project_beneficiary') : '';
	$description  = $has_acf ? get_field('eu_project_short_description') : '';
	$total_value  = $has_acf ? get_field('eu_project_total_value') : '';
	$eu_funding   = $has_acf ? get_field('eu_project_eu_funding') : '';
	$start_date   = $has_acf ? get_field('eu_project_start_date') : '';
	$end_date     = $has_acf ? get_field('eu_project_end_date') : '';
	$contact_info = $has_acf ? get_field('eu_project_contact_info') : '';
	$pdf          = $has_acf ? get_field('eu_project_pdf') : '';
	$cta_label    = $has_acf ? get_field('eu_project_cta_label') : '';
	$cta_url      = $has_acf ? get_field('eu_project_cta_url') : '';

	$logo_one   = $has_acf ? get_field('eu_project_logos_one') : '';
	$logo_two   = $has_acf ? get_field('eu_project_logos_two') : '';
	$logo_three = $has_acf ? get_field('eu_project_logos_three') : '';
	$logo_four  = $has_acf ? get_field('eu_project_logos_four') : '';
	$logo_five  = $has_acf ? get_field('eu_project_logos_five') : '';

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

		if (preg_match('/^\d{2}\.\d{2}\.\d{4}\.?$/', (string) $date_value)) {
			return $date_value;
		}

		$timestamp = strtotime((string) $date_value);
		if (!$timestamp) {
			return (string) $date_value;
		}

		return date_i18n('d.m.Y.', $timestamp);
	};

	$get_image_data = static function ($image, $fallback_alt = '') {
		$data = [
			'url' => '',
			'alt' => $fallback_alt,
		];

		if (empty($image)) {
			return $data;
		}

		if (is_array($image)) {
			if (!empty($image['sizes']['medium_large'])) {
				$data['url'] = $image['sizes']['medium_large'];
			} elseif (!empty($image['url'])) {
				$data['url'] = $image['url'];
			}

			if (!empty($image['alt'])) {
				$data['alt'] = $image['alt'];
			}

			return $data;
		}

		if (is_numeric($image)) {
			$image_id = (int) $image;
			$data['url'] = wp_get_attachment_image_url($image_id, 'medium_large');

			$attachment_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
			if (!empty($attachment_alt)) {
				$data['alt'] = $attachment_alt;
			}

			return $data;
		}

		if (is_string($image)) {
			$data['url'] = $image;
		}

		return $data;
	};

	$start_date_formatted = $format_date($start_date);
	$end_date_formatted   = $format_date($end_date);
	$period_label         = trim($start_date_formatted . ' – ' . $end_date_formatted, ' –');

	$archive_url = get_post_type_archive_link('eu_project');
	if (!$archive_url) {
		$archive_url = home_url('/');
	}

	$content = get_the_content();
	?>
	<main id="main-content" class="site-main eu-project-single">
		<section class="eu-project-single__hero">
			<div class="container container--narrow">
				<nav class="eu-project-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'tersa-shop'); ?>">
					<a href="<?php echo esc_url($archive_url); ?>">
						<?php esc_html_e('EU projekti', 'tersa-shop'); ?>
					</a>
					<span>/</span>
					<span><?php echo esc_html(get_the_title()); ?></span>
				</nav>

				<?php if (!empty($logos)) : ?>
					<div class="eu-project-single__logos-wrap">
						<div class="eu-project-single__logos">
							<?php foreach ($logos as $logo) : ?>
								<?php
								$logo_data = $get_image_data($logo, $display_title);
								if (empty($logo_data['url'])) {
									continue;
								}
								?>
								<div class="eu-project-single__logo-item">
									<img
										src="<?php echo esc_url($logo_data['url']); ?>"
										alt="<?php echo esc_attr($logo_data['alt']); ?>"
										loading="lazy"
										decoding="async"
									>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="eu-project-single__hero-card">
					<?php if ($status || $program) : ?>
						<div class="eu-project-single__meta">
							<?php if ($status) : ?>
								<span class="eu-project-single__badge">
									<?php echo esc_html($status); ?>
								</span>
							<?php endif; ?>

							<?php if ($program) : ?>
								<span class="eu-project-single__program">
									<?php echo esc_html($program); ?>
								</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<h1 class="eu-project-single__title">
						<?php echo esc_html($display_title); ?>
					</h1>

					<?php if ($period_label) : ?>
						<div class="eu-project-single__period">
							<p>
								<strong><?php esc_html_e('Razdoblje projekta:', 'tersa-shop'); ?></strong>
								<?php echo esc_html($period_label); ?>
							</p>
						</div>
					<?php endif; ?>

					<?php if ($pdf_url || ($cta_url && $cta_label)) : ?>
						<div class="eu-project-single__actions">
							<?php if ($pdf_url) : ?>
								<a class="button button--secondary" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener">
									<?php esc_html_e('Preuzmi dokument', 'tersa-shop'); ?>
								</a>
							<?php endif; ?>

							<!--
							<?php if ($cta_url && $cta_label) : ?>
								<a class="button button--primary" href="<?php echo esc_url($cta_url); ?>">
									<?php echo esc_html($cta_label); ?>
								</a>
							<?php endif; ?>
							-->
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="eu-project-single__overview">
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
			<section class="eu-project-single__section">
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
			<section class="eu-project-single__section eu-project-single__section--contact">
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

		<?php if (!empty(trim(wp_strip_all_tags($content)))) : ?>
			<section class="eu-project-single__section">
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