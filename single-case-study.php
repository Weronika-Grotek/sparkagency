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
		$taxonomies      = get_object_taxonomies( (string) $post_type, 'objects' );
		$client          = get_post_meta( $case_id, 'client', true );
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

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! $taxonomy->public || 'post_format' === $taxonomy->name ) {
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
			foreach ( $brand_aliases as $candidate_brand => $aliases ) {
				if ( in_array( sanitize_key( $term->slug ), $aliases, true ) ) {
					$brand = $candidate_brand;
					break 2;
				}
			}
		}

		if ( '' !== (string) $client ) {
			$metadata[ __( 'Klient / marka', 'sparkagency' ) ] = $client;
		}

		if ( ! empty( $case_terms ) ) {
			$metadata[ __( 'Kategoria', 'sparkagency' ) ] = implode( ', ', wp_list_pluck( $case_terms, 'name' ) );
		}

		$metadata[ __( 'Rok', 'sparkagency' ) ] = get_the_date( 'Y', $case_id );
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'spark-single-case-study__article' ); ?> data-brand="<?php echo esc_attr( $brand ); ?>">
			<header class="spark-single-case-study__hero" data-brand="<?php echo esc_attr( $brand ); ?>" data-spark-fireworks data-particle-animation="true">
				<div class="spark-single-case-study__particles" aria-hidden="true"><canvas></canvas></div>
				<div class="spark-single-case-study__wrap">
					<div class="spark-single-case-study__crumbs" aria-label="<?php esc_attr_e( 'Okruszki', 'sparkagency' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'sparkagency' ); ?></a>
						<span aria-hidden="true">/</span>
						<?php if ( $archive_url ) : ?>
							<a href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $archive_label ); ?></a>
							<span aria-hidden="true">/</span>
						<?php endif; ?>
						<span aria-current="page"><?php echo esc_html( $title ); ?></span>
					</div>

					<?php if ( ! empty( $case_terms ) ) : ?>
						<ul class="spark-single-case-study__terms" aria-label="<?php esc_attr_e( 'Kategorie realizacji', 'sparkagency' ); ?>">
							<?php foreach ( $case_terms as $term ) : ?>
								<li><?php echo esc_html( $term->name ); ?></li>
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
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
