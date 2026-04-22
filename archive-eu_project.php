<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="main-content" class="site-main eu-projects-archive">
	<section class="eu-projects-archive__hero">
		<div class="container container--narrow">
		<nav class="eu-projects-archive__breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'tersa-shop'); ?>">
				<a href="<?php echo esc_url(home_url('/')); ?>">
					<?php esc_html_e('Naslovnica', 'tersa-shop'); ?>
				</a>
				<span>/</span>
				<span><?php esc_html_e('EU Projekti', 'tersa-shop'); ?></span>
			</nav>
		</div>
		<div class="container container--narrow">
			<h1 class="eu-projects-archive__title">
				<?php esc_html_e('EU Projekti', 'tersa-shop'); ?>
			</h1>
		</div>
	</section>

	<section class="eu-projects-archive__list section">
		<div class="container container--narrow">
			<?php if (have_posts()) : ?>
				<div class="eu-projects-archive__grid">
				<?php
				$has_acf = function_exists('get_field');

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

				while (have_posts()) :
					the_post();

					$card_title = $has_acf ? get_field('eu_project_card_title') : '';
					$card_desc  = $has_acf ? get_field('eu_project_card_description') : '';
					$status     = $has_acf ? get_field('eu_project_status') : '';
					$program    = $has_acf ? get_field('eu_project_program') : '';
					$start_date = $has_acf ? get_field('eu_project_start_date') : '';
					$end_date   = $has_acf ? get_field('eu_project_end_date') : '';

					$display_title = !empty($card_title) ? $card_title : get_the_title();

					if (!empty($card_desc)) {
						$display_desc = $card_desc;
					} elseif (has_excerpt()) {
						$display_desc = get_the_excerpt();
					} else {
						$display_desc = wp_trim_words(wp_strip_all_tags(get_the_content()), 22);
					}

					$start_date_formatted = $format_date($start_date);
						$end_date_formatted   = $format_date($end_date);

						$period = '';
						if ($start_date_formatted && $end_date_formatted) {
							$period = $start_date_formatted . ' – ' . $end_date_formatted;
						} elseif ($start_date_formatted) {
							$period = $start_date_formatted;
						} elseif ($end_date_formatted) {
							$period = $end_date_formatted;
						}
						?>

						<article <?php post_class('eu-project-archive-card'); ?>>
							<a
								class="eu-project-archive-card__media"
								href="<?php the_permalink(); ?>"
								aria-label="<?php echo esc_attr($display_title); ?>"
							>
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('large', [
										'loading'  => 'lazy',
										'decoding' => 'async',
									]); ?>
								<?php else : ?>
									<div class="eu-project-archive-card__placeholder" aria-hidden="true">
										<span>EU</span>
									</div>
								<?php endif; ?>
							</a>

							<div class="eu-project-archive-card__content">
								<div class="eu-project-archive-card__top">
									<?php if ($status) : ?>
										<span class="eu-project-archive-card__badge">
											<?php echo esc_html($status); ?>
										</span>
									<?php endif; ?>

									<?php if ($program) : ?>
										<p class="eu-project-archive-card__program">
											<?php echo esc_html($program); ?>
										</p>
									<?php endif; ?>
								</div>

								<h2 class="eu-project-archive-card__title">
									<a href="<?php the_permalink(); ?>">
										<?php echo esc_html($display_title); ?>
									</a>
								</h2>

								<?php if ($period) : ?>
									<p class="eu-project-archive-card__period">
										<?php echo esc_html($period); ?>
									</p>
								<?php endif; ?>

								<p class="eu-project-archive-card__description">
									<?php echo esc_html($display_desc); ?>
								</p>

								<div class="eu-project-archive-card__footer">
									<a class="eu-project-archive-card__link" href="<?php the_permalink(); ?>">
										<?php esc_html_e('Vidi projekt', 'tersa-shop'); ?>
										<span aria-hidden="true">→</span>
									</a>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

				<div class="eu-projects-archive__pagination">
					<?php
					the_posts_pagination([
						'mid_size'  => 1,
						'prev_text' => '← ' . (function_exists('tersa_pagination_prev_text') ? tersa_pagination_prev_text() : __('Prethodna', 'tersa-shop')),
						'next_text' => (function_exists('tersa_pagination_next_text') ? tersa_pagination_next_text() : __('Sljedeća', 'tersa-shop')) . ' →',
					]);
					?>
				</div>
			<?php else : ?>
				<div class="eu-projects-archive__empty">
					<h2 class="eu-projects-archive__empty-title">
						<?php esc_html_e('Trenutno nema dostupnih EU projekata.', 'tersa-shop'); ?>
					</h2>

					<p class="eu-projects-archive__empty-text">
						<?php esc_html_e('Kada projekti budu objavljeni, pojavit će se na ovoj stranici.', 'tersa-shop'); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();