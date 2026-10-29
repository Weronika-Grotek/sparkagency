<?php
/**
 * Single case study template.

 *
 * @package SparkAgency
 */

get_header();
?>

<main id="site-content" class="spark-single-case-study" role="main">
	<?php while ( have_posts() ) : ?>
		<?php
		the_post();

		$case_id        = get_the_ID();
		$title          = get_the_title( $case_id );
		$post_type       = get_post_type( $case_id );
		$archive_url     = $post_type ? get_post_type_archive_link( $post_type ) : false;
		$archive_label   = post_type_exists( (string) $post_type ) ? get_post_type_object( $post_type )->labels->name : __( 'Case studies', 'sparkagency' );
		$case_terms      = array();
		$brand_terms     = array();
		$category_terms  = array();
		$taxonomies      = get_object_taxonomies( (string) $post_type, 'objects' );
		$client_terms    = sparkagency_get_case_study_term_names( $case_id, 'case_client' );
		$client          = ! empty( $client_terms ) ? implode( ', ', $client_terms ) : get_post_meta( $case_id, 'client', true );
		$category_names  = sparkagency_get_case_study_term_names( $case_id, 'case_category' );
		$wp_categories   = sparkagency_get_case_study_term_names( $case_id, 'category' );
		$category_meta   = get_post_meta( $case_id, 'case_study_category', true );
		if ( empty( $category_meta ) ) {
			$category_meta = get_post_meta( $case_id, 'category', true );
		}
		$brand           = 'agency';
		$brand_aliases   = array(
			'agency' => array( 'agency', 'sparkagency' ),
			'exp'    => array( 'exp', 'sparkexp' ),
			'warsaw' => array( 'warsaw', 'sparkwarsaw' ),
			'health' => array( 'health', 'sparkhealth' ),
			'studio' => array( 'studio', 'sparkstudio' ),
			'works'  => array( 'works', 'sparkworks' ),
		);
		$excerpt         = get_post_field( 'post_excerpt', $case_id );
		$metadata        = array();
		$next_case       = get_adjacent_post( false, '', true );
		$next_brand      = 'agency';

		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_label = $taxonomy->labels->singular_name ?? '';
			if ( preg_match( '/categor|kategor/i', $taxonomy->name . ' ' . $taxonomy_label ) ) {
				$taxonomy_category_names = sparkagency_get_case_study_term_names( $case_id, $taxonomy->name );
				$category_names          = array_merge( $category_names, $taxonomy_category_names );
			}

			if ( ! $taxonomy->public || in_array( $taxonomy->name, array( 'post_format', 'case_client', 'case_category' ), true ) ) {
				continue;
			}

			$terms = get_the_terms( $case_id, $taxonomy->name );
			if ( is_array( $terms ) ) {
				$case_terms = array_merge( $case_terms, $terms );
			} 
		}

		$case_terms = array_values(
			array_unique(
				$case_terms,
				SORT_REGULAR
			)
		);

		foreach ( $case_terms as $term ) {
			$is_brand_term = false;

			foreach ( $brand_aliases as $candidate_brand => $aliases ) {
				if ( in_array( sanitize_key( $term->slug ), $aliases, true ) ) {
					$brand = $candidate_brand;
					$brand_terms[] = array(
						'name'  => $term->name,
						'brand' => $candidate_brand,
					);
					$is_brand_term = true;
					break;
				}
			}

			if ( ! $is_brand_term ) {
				$category_terms[] = $term;
			}
		}

		if ( '' === (string) $client && ! empty( $category_terms ) ) {
			$client = $category_terms[0]->name;
			array_shift( $category_terms );
		}

		$category_names = array_values( array_unique( array_merge( $category_names, $wp_categories ) ) );
		if ( empty( $category_names ) && ! empty( $category_meta ) ) {
			$category_names = is_array( $category_meta ) ? $category_meta : array( $category_meta );
		}
		$category_names = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( $category ): string {
							if ( $category instanceof WP_Term ) {
								return trim( $category->name );
							}

							return is_scalar( $category ) ? trim( (string) $category ) : '';
						},
						$category_names
					)
				)
			)
		);
		$category_label = ! empty( $category_names ) ? implode( ', ', $category_names ) : implode( ', ', wp_list_pluck( $category_terms, 'name' ) );

		if ( '' !== (string) $client ) {
			$metadata[ __( 'Klient / marka', 'sparkagency' ) ] = $client;
		}

		if ( '' !== $category_label ) {
			$metadata[ __( 'Kategoria', 'sparkagency' ) ] = $category_label;
		}

		if ( ! empty( $brand_terms ) ) {
			$metadata[ __( 'Zespoły', 'sparkagency' ) ] = implode( ', ', wp_list_pluck( $brand_terms, 'name' ) );
		} elseif ( ! empty( $metadata ) ) {
			$metadata[ __( 'Rok', 'sparkagency' ) ] = get_the_date( 'Y', $case_id );
		}
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'spark-single-case-study__article' ); ?> data-brand="<?php echo esc_attr( $brand ); ?>">
			<header class="spark-single-case-study__hero" data-brand="<?php echo esc_attr( $brand ); ?>" data-spark-fireworks data-particle-animation="true">
				<div class="spark-single-case-study__particles" aria-hidden="true"><canvas></canvas></div>
				<div class="spark-single-case-study__wrap">
					<div class="spark-single-case-study__topline">
						<div class="spark-single-case-study__crumbs" aria-label="<?php esc_attr_e( 'Okruszki', 'sparkagency' ); ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'sparkagency' ); ?></a>
							<span aria-hidden="true">/</span>
							<?php if ( $archive_url ) : ?>
								<a href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $archive_label ); ?></a>
								<span aria-hidden="true">/</span>
							<?php endif; ?>
							<span aria-current="page"><?php echo esc_html( $title ); ?></span>
						</div>

						<?php if ( '' !== $category_label ) : ?>
							<p class="spark-single-case-study__category"><?php echo esc_html( $category_label ); ?></p>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $brand_terms ) ) : ?>
						<ul class="spark-single-case-study__chips" aria-label="<?php esc_attr_e( 'Zespoły realizacji', 'sparkagency' ); ?>">
							<?php foreach ( $brand_terms as $term ) : ?>
								<li data-brand="<?php echo esc_attr( $term['brand'] ); ?>"><span aria-hidden="true"></span><?php echo esc_html( $term['name'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<h1 class="spark-single-case-study__title"><?php echo esc_html( $title ); ?></h1>

					<?php if ( ! empty( $metadata ) ) : ?>
						<dl class="spark-single-case-study__meta">
							<?php foreach ( $metadata as $label => $value ) : ?>
								<div>
									<dt><?php echo esc_html( $label ); ?></dt>
									<dd><?php echo esc_html( $value ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="spark-single-case-study__cover">
					<?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="spark-single-case-study__body">
				<?php /* if ( $excerpt ) : ?>
					<p class="spark-single-case-study__lead"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; */ ?>

				<div class="spark-single-case-study__content">
					<?php the_content(); ?>
				</div>
			</div>

			<?php
			if ( $next_case ) {
				foreach ( $taxonomies as $taxonomy ) {
					$next_terms = get_the_terms( $next_case->ID, $taxonomy->name );

					if ( is_array( $next_terms ) ) {
						foreach ( $next_terms as $next_term ) {
							foreach ( $brand_aliases as $candidate_brand => $aliases ) {
								if ( in_array( sanitize_key( $next_term->slug ), $aliases, true ) ) {
									$next_brand = $candidate_brand;
									break 3;
								}
							}
						}
					}
				}
			}
			?>

			<a
				class="spark-single-case-study__next"
				href="<?php echo esc_url( $next_case ? get_permalink( $next_case ) : ( $archive_url ?: home_url( '/' ) ) ); ?>"
				data-brand="<?php echo esc_attr( $next_case ? $next_brand : 'agency' ); ?>"
				aria-label="<?php echo esc_attr( $next_case ? sprintf( __( 'Następne case study: %s', 'sparkagency' ), get_the_title( $next_case ) ) : __( 'Wróć do wszystkich case studies', 'sparkagency' ) ); ?>"
			>
				<span class="spark-single-case-study__next-inner">
					<span class="spark-single-case-study__next-label"><?php echo esc_html( $next_case ? __( 'Następny case →', 'sparkagency' ) : __( 'Wróć do listy', 'sparkagency' ) ); ?></span>
					<span class="spark-single-case-study__next-title"><?php echo esc_html( $next_case ? get_the_title( $next_case ) : __( 'Wszystkie realizacje', 'sparkagency' ) ); ?></span>
				</span>
			</a>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
