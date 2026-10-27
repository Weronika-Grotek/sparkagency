<?php

/** Dynamic list of case studies with filters from the Hub's branches taxonomy. */

defined('ABSPATH') || exit;

$case_types = array_values(array_filter(array('case-study', 'case_study'), 'post_type_exists'));
$case_types = $case_types ?: array('case-study');
$branch_taxonomy = taxonomy_exists('branches') ? 'branches' : '';
$case_query = new WP_Query(
	array(
		'post_type'           => $case_types,
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => array('menu_order' => 'ASC', 'date' => 'DESC'),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$filter_terms  = array();
$case_terms    = array();
$filter_counts = array();
if ($branch_taxonomy && $case_query->have_posts()) {
	foreach ($case_query->posts as $case_post) {
		$terms = get_the_terms($case_post->ID, $branch_taxonomy);
		if (! is_array($terms)) {
			continue;
		}
		$case_terms[$case_post->ID] = $terms;
		foreach ($terms as $term) {
			$filter_terms[$term->slug]  = $term;
			$filter_counts[$term->slug] = ($filter_counts[$term->slug] ?? 0) + 1;
		}
	}
}

$brand_aliases = array('sparkagency' => 'agency', 'sparkexp' => 'exp', 'sparkwarsaw' => 'warsaw', 'sparkhealth' => 'health', 'sparkstudio' => 'studio', 'sparkworks' => 'works');
?>
<section class="spark-case-archive" aria-label="<?php esc_attr_e('Archiwum case studies', 'sparkagency'); ?>">
	<div class="spark-case-archive__listing">
		<div class="spark-case-archive__wrap">
			<?php if (! empty($filter_terms)) : ?>
				<div class="spark-case-archive__filters" role="group" aria-label="<?php esc_attr_e('Filtruj realizacje według firmy', 'sparkagency'); ?>">
					<button class="spark-case-archive__filter is-active" type="button" data-case-filter="all" aria-pressed="true"><?php esc_html_e('Wszystkie', 'sparkagency'); ?><sup><?php echo esc_html((string) $case_query->post_count); ?></sup></button>
					<?php foreach ($filter_terms as $term) : ?>
						<?php $brand_key = sanitize_key($term->slug);
						$brand_key = $brand_aliases[$brand_key] ?? preg_replace('/^spark/', '', $brand_key); ?>
						<button class="spark-case-archive__filter" type="button" data-case-filter="<?php echo esc_attr($term->slug); ?>" data-brand="<?php echo esc_attr($brand_key); ?>" aria-pressed="false"><i aria-hidden="true"></i><?php echo esc_html($term->name); ?><sup><?php echo esc_html((string) $filter_counts[$term->slug]); ?></sup></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ($case_query->have_posts()) : ?>
				<div class="spark-case-archive__grid" data-case-grid>
					<?php $case_index = 0; ?>
					<?php while ($case_query->have_posts()) : $case_query->the_post(); ?>
						<?php
						++$case_index;
						$case_id       = get_the_ID();
						$terms         = $case_terms[$case_id] ?? array();
						$term_slugs    = wp_list_pluck($terms, 'slug');
						$term_names    = wp_list_pluck($terms, 'name');
						$brand_keys    = array_map(
							static function ($slug) use ($brand_aliases): string {
								$key = sanitize_key($slug);
								return $brand_aliases[$key] ?? preg_replace('/^spark/', '', $key);
							},
							$term_slugs
						);
						$brand_key          = $brand_keys[0] ?? 'agency';
						$secondary_brand_key = $brand_keys[1] ?? $brand_key;
						$glow_count         = min(4, max(3, count($brand_keys)));
						$client        = get_post_meta($case_id, 'client', true);
						$metric_value  = get_post_meta($case_id, 'metric_value', true);
						$metric_label  = get_post_meta($case_id, 'metric_label', true);
						$card_filters  = ! empty($term_slugs) ? $term_slugs : array('unassigned');
						$category_text = ! empty($term_names) ? implode(', ', $term_names) : get_the_date('Y');
						?>
						<a class="spark-case-archive__card<?php echo 0 === (($case_index - 1) % 4) || 3 === (($case_index - 1) % 4) ? ' is-wide' : ''; ?>" href="<?php the_permalink(); ?>" data-case-card data-case-categories="<?php echo esc_attr(implode(' ', $card_filters)); ?>" data-brand="<?php echo esc_attr($brand_key); ?>" style="--case-index:<?php echo esc_attr((string) ($case_index - 1)); ?>">
							<div class="spark-case-archive__art<?php echo has_post_thumbnail() ? ' has-image' : ''; ?>" data-brand="<?php echo esc_attr($brand_key); ?>" data-brand-secondary="<?php echo esc_attr($secondary_brand_key); ?>">
								<?php for ($glow_index = 0; $glow_index < $glow_count; ++$glow_index) : ?>
									<?php $glow_brand = $brand_keys ? $brand_keys[$glow_index % count($brand_keys)] : 'agency'; ?>
									<span class="spark-case-archive__glow spark-case-archive__glow--<?php echo esc_attr(array('one', 'two', 'three', 'four')[$glow_index]); ?>" data-brand="<?php echo esc_attr($glow_brand); ?>" aria-hidden="true"></span>
								<?php endfor; ?>
								<span class="spark-case-archive__art-grid" aria-hidden="true"></span>
								<?php if (has_post_thumbnail()) : ?><?php the_post_thumbnail('large', array('class' => 'spark-case-archive__image', 'loading' => $case_index > 2 ? 'lazy' : 'eager', 'decoding' => 'async')); ?><?php endif; ?>
								<?php if ($terms) : ?><span class="spark-case-archive__chips"><?php foreach ($terms as $term_index => $term) : ?><span data-brand="<?php echo esc_attr($brand_keys[$term_index] ?? 'agency'); ?>"><i aria-hidden="true"></i><?php echo esc_html($term->name); ?></span><?php endforeach; ?></span><?php endif; ?>
								<span class="spark-case-archive__number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $case_index)); ?></span>
							</div>
							<div class="spark-case-archive__body">
								<div class="spark-case-archive__meta"><span><?php echo esc_html($client ?: get_the_title()); ?></span><span><?php echo esc_html($category_text); ?></span></div>
								<h2 class="spark-case-archive__card-title"><?php the_title(); ?></h2>
								<?php if (has_excerpt()) : ?><p class="spark-case-archive__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 26)); ?></p><?php endif; ?>
								<span class="spark-case-archive__more"><?php esc_html_e('Zobacz case study', 'sparkagency'); ?></span>
								<?php if ($metric_value && $metric_label) : ?><div class="spark-case-archive__metric"><strong><?php echo esc_html($metric_value); ?></strong><span><?php echo esc_html($metric_label); ?></span></div><?php endif; ?>
							</div>
						</a>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<p class="spark-case-archive__empty"><?php esc_html_e('Brak opublikowanych case studies.', 'sparkagency'); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php wp_reset_postdata(); ?>