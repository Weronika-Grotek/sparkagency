<?php
/**
 * Dynamic template for the Case Studies block.
 *
 * @package SparkAgency
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$post_type = sanitize_key( $attributes['postType'] ?? 'case-study' );

if ( ! post_type_exists( $post_type ) ) {
	foreach ( array( 'case-study', 'case_study' ) as $candidate_post_type ) {
		if ( post_type_exists( $candidate_post_type ) ) {
			$post_type = $candidate_post_type;
			break;
		}
	}
}

$posts_per_page       = min( 12, max( 1, absint( $attributes['postsPerPage'] ?? 5 ) ) );
$brand_taxonomy       = sanitize_key( $attributes['brandTaxonomy'] ?? '' );
$client_meta_key      = sanitize_key( $attributes['clientMetaKey'] ?? 'client' );
$metric_value_meta_key = sanitize_key( $attributes['metricValueMetaKey'] ?? 'metric_value' );
$metric_label_meta_key = sanitize_key( $attributes['metricLabelMetaKey'] ?? 'metric_label' );
$contact_url           = esc_url( $attributes['contactUrl'] ?? '' );
$contact_label         = trim( wp_strip_all_tags( $attributes['contactLabel'] ?? __( 'Porozmawiajmy o Twoim projekcie', 'sparkagency' ) ) );
$has_contact_cta       = '' !== $contact_url && '' !== $contact_label;
$case_query           = new WP_Query(
	array(
		'post_type'           => $post_type,
		'post_status'         => 'publish',
		'posts_per_page'      => $posts_per_page,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);

$brand_aliases = array(
	'agency' => array( 'agency', 'sparkagency' ),
	'exp'    => array( 'exp', 'sparkexp' ),
	'warsaw' => array( 'warsaw', 'sparkwarsaw' ),
	'health' => array( 'health', 'sparkhealth' ),
	'studio' => array( 'studio', 'sparkstudio' ),
	'works'  => array( 'works', 'sparkworks' ),
);
$get_case_terms = static function ( int $post_id, string $type, string $taxonomy ): array {
	if ( '' !== $taxonomy && taxonomy_exists( $taxonomy ) ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		return is_array( $terms ) ? $terms : array();
	}

	foreach ( get_object_taxonomies( $type, 'names' ) as $object_taxonomy ) {
		if ( in_array( $object_taxonomy, array( 'post_format', 'case_client', 'case_category' ), true ) ) {
			continue;
		}
		$terms = get_the_terms( $post_id, $object_taxonomy );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			return $terms;
		}
	}

	return array();
};
$get_case_brands = static function ( int $post_id, string $type, string $taxonomy ) use ( $brand_aliases ): array {
	$taxonomies = '' !== $taxonomy && taxonomy_exists( $taxonomy )
		? array( $taxonomy )
		: get_object_taxonomies( $type, 'names' );
	$brands = array();

	foreach ( $taxonomies as $object_taxonomy ) {
		if ( in_array( $object_taxonomy, array( 'post_format', 'case_client', 'case_category' ), true ) ) {
			continue;
		}

		$terms = get_the_terms( $post_id, $object_taxonomy );
		if ( ! is_array( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$term_slug = sanitize_key( $term->slug );
			foreach ( $brand_aliases as $brand => $aliases ) {
				if ( in_array( $term_slug, $aliases, true ) && ! isset( $brands[ $brand ] ) ) {
					$brands[ $brand ] = array(
						'slug' => $brand,
						'name' => $term->name,
					);
				}
			}
		}
	}

	return array_values( $brands );
};
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-case-studies' . ( $has_contact_cta ? ' has-case-studies-contact' : '' ) ) ) ); ?> data-spark-case-studies>
	<header class="spark-case-studies__header">
		<?php if ( ! empty( $attributes['eyebrow'] ) ) : ?>
			<p class="spark-case-studies__eyebrow"><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
		<?php endif; ?>
		<div>
			<?php if ( ! empty( $attributes['title'] ) ) : ?>
				<h2><?php echo wp_kses_post( $attributes['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $attributes['lede'] ) ) : ?>
				<p class="spark-case-studies__lede"><?php echo wp_kses_post( $attributes['lede'] ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php if ( ! $case_query->have_posts() ) : ?>
		<p class="spark-case-studies__empty"><?php esc_html_e( 'Nie znaleziono opublikowanych realizacji dla wybranego typu wpisu.', 'sparkagency' ); ?></p>
	<?php else : ?>
		<div class="spark-case-studies__pin" data-case-pin>
			<div class="spark-case-studies__sticky">
				<div class="spark-case-studies__progress" aria-hidden="true"><span><b data-case-current>01</b> / <?php echo esc_html( sprintf( '%02d', $case_query->post_count ) ); ?></span><i data-case-progress></i></div>
				<div class="spark-case-studies__window">
					<div class="spark-case-studies__track" data-case-track>
						<?php $case_index = 0; ?>
						<?php while ( $case_query->have_posts() ) : ?>
							<?php
							$case_query->the_post();
							++$case_index;
							$case_id      = get_the_ID();
							$terms        = $get_case_terms( $case_id, $post_type, $brand_taxonomy );
							$case_brands  = $get_case_brands( $case_id, $post_type, $brand_taxonomy );
							$brand        = $case_brands[0]['slug'] ?? 'exp';
							$client_terms = sparkagency_get_case_study_term_names( $case_id, 'case_client' );
							$client       = ! empty( $client_terms ) ? implode( ', ', $client_terms ) : ( '' !== $client_meta_key ? get_post_meta( $case_id, $client_meta_key, true ) : '' );
							$metric_value = '' !== $metric_value_meta_key ? get_post_meta( $case_id, $metric_value_meta_key, true ) : '';
							$metric_label = '' !== $metric_label_meta_key ? get_post_meta( $case_id, $metric_label_meta_key, true ) : '';
							$accent_note  = get_post_meta( $case_id, 'case_study_card_note_accent', true );
							$plain_note   = get_post_meta( $case_id, 'case_study_card_note_plain', true );
							$category_terms = sparkagency_get_case_study_term_names( $case_id, 'case_category' );
							$category       = ! empty( $category_terms ) ? implode( ', ', $category_terms ) : ( ! empty( $terms ) ? $terms[0]->name : get_the_date( 'Y' ) );
							$excerpt      = get_the_excerpt();
							$is_wide_case = 0 === ( ( $case_index - 1 ) % 4 );
							?>
							<a class="spark-case-studies__card has-case-brand-<?php echo esc_attr( $brand ); ?><?php echo $is_wide_case ? ' is-case-wide' : ''; ?>" href="<?php the_permalink(); ?>" data-case-card style="--case-index:<?php echo esc_attr( (string) ( $case_index - 1 ) ); ?>">
								<div class="spark-case-studies__art<?php echo has_post_thumbnail() ? ' has-case-image' : ''; ?>">
									<?php
									$blob_brands    = ! empty( $case_brands ) ? $case_brands : array( array( 'slug' => $brand ) );
									$blob_modifiers = array( 'one', 'two', 'three', 'four' );
									$blob_count     = min( count( $blob_modifiers ), max( 3, count( $blob_brands ) ) );
									?>
									<?php for ( $blob_index = 0; $blob_index < $blob_count; ++$blob_index ) : ?>
										<?php $blob_brand = $blob_brands[ $blob_index % count( $blob_brands ) ]['slug']; ?>
										<span class="spark-case-studies__blob spark-case-studies__blob--<?php echo esc_attr( $blob_modifiers[ $blob_index ] ); ?> has-case-brand-<?php echo esc_attr( $blob_brand ); ?>" aria-hidden="true"></span>
									<?php endfor; ?>
									<span class="spark-case-studies__art-grid" aria-hidden="true"></span>
									<?php if ( ! empty( $case_brands ) ) : ?>
										<div class="spark-case-studies__chips" aria-hidden="true">
											<?php foreach ( $case_brands as $case_brand ) : ?>
												<span class="has-case-brand-<?php echo esc_attr( $case_brand['slug'] ); ?>"><?php echo esc_html( $case_brand['name'] ); ?></span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
									<span class="spark-case-studies__number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $case_index ) ); ?></span>
									<?php if ( has_post_thumbnail() ) : ?>
										<?php
										$image_attributes = array(
											'class'    => 'spark-case-studies__image',
											'loading'  => 1 === $case_index ? 'eager' : 'lazy',
											'decoding' => 'async',
										);
										if ( 1 === $case_index ) {
											$image_attributes['fetchpriority'] = 'high';
										}
										echo get_the_post_thumbnail( $case_id, 'large', $image_attributes );
										?>
									<?php endif; ?>
								</div>
								<div class="spark-case-studies__body">
									<div class="spark-case-studies__meta"><span><?php echo esc_html( $client ?: get_the_title() ); ?></span><span><?php echo esc_html( $category ); ?></span></div>
									<h3 class="spark-case-studies__title"><?php the_title(); ?></h3>
									<?php if ( $excerpt ) : ?>
										<p class="spark-case-studies__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 26 ) ); ?></p>
									<?php endif; ?>
									<span class="spark-case-studies__more"><?php esc_html_e( 'Zobacz case study', 'sparkagency' ); ?></span>
									<?php if ( '' !== (string) $metric_value && '' !== (string) $metric_label ) : ?>
										<div class="spark-case-studies__metric"><strong><?php echo esc_html( $metric_value ); ?></strong><span><?php echo esc_html( $metric_label ); ?></span></div>
									<?php endif; ?>
									<?php if ( $accent_note || $plain_note ) : ?>
										<div class="spark-case-studies__notes">
											<?php if ( $accent_note ) : ?><p class="spark-case-studies__note spark-case-studies__note--accent"><?php echo nl2br( esc_html( $accent_note ) ); ?></p><?php endif; ?>
											<?php if ( $plain_note ) : ?><p class="spark-case-studies__note spark-case-studies__note--plain"><?php echo nl2br( esc_html( $plain_note ) ); ?></p><?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</a>
						<?php endwhile; ?>
						<?php if ( ! empty( $attributes['allUrl'] ) ) : ?>
							<a class="spark-case-studies__all" href="<?php echo esc_url( $attributes['allUrl'] ); ?>"><span><?php echo esc_html( $attributes['allLabel'] ?? __( 'Wszystkie realizacje', 'sparkagency' ) ); ?></span><b aria-hidden="true">→</b></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $has_contact_cta ) : ?>
		<footer class="spark-case-studies__footer">
			<a class="spark-case-studies__contact" href="<?php echo $contact_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped with esc_url above. ?>"<?php echo ! empty( $attributes['contactNewTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><span><?php echo esc_html( $contact_label ); ?></span><span class="spark-case-studies__contact-icon" aria-hidden="true">→</span></a>
		</footer>
	<?php endif; ?>
</section>
<?php wp_reset_postdata(); ?>
