<?php
/**
 * Case study archive, styled after the supplied Spark prototype.
 *
 * @package SparkAgency
 */

get_header();

$archive_copy = get_option( 'sparkagency_case_archive', array() );
$archive_copy = is_array( $archive_copy ) ? $archive_copy : array();
$eyebrow      = $archive_copy['eyebrow'] ?? 'Nasza praca';
$title        = $archive_copy['title'] ?? 'Case studies';
$lead         = $archive_copy['lead'] ?? 'Poznaj projekty, w których strategia i kreatywność przekładają się na konkretne rezultaty.';
$subtitle     = $archive_copy['subtitle'] ?? '';
$brand_aliases = array( 'agency', 'sparkagency', 'exp', 'sparkexp', 'warsaw', 'sparkwarsaw', 'health', 'sparkhealth', 'studio', 'sparkstudio', 'works', 'sparkworks' );
$cases = array();
$filter_terms = array();
$filter_counts = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$case_id       = get_the_ID();
		$case_post_type = get_post_type( $case_id );
		$brands        = array();
		$categories    = array();
		foreach ( get_object_taxonomies( $case_post_type, 'objects' ) as $taxonomy ) {
			if ( ! $taxonomy->public || 'post_format' === $taxonomy->name ) {
				continue;
			}
			$terms = get_the_terms( $case_id, $taxonomy->name );
			if ( ! is_array( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) {
				if ( in_array( sanitize_key( $term->slug ), $brand_aliases, true ) ) {
					$brands[] = $term;
				} else {
					$categories[] = $term;
					$filter_terms[ $term->slug ] = $term;
					$filter_counts[ $term->slug ] = ( $filter_counts[ $term->slug ] ?? 0 ) + 1;
				}
			}
		}
		$cases[] = array(
			'post_type'  => $case_post_type,
			'id'         => $case_id,
			'brands'     => $brands,
			'categories' => $categories,
		);
	}
	// Rewind the main archive query for the card output below.
	rewind_posts();
}

$queried_archive = get_queried_object();
$archive_type    = isset( $queried_archive->name ) && post_type_exists( $queried_archive->name ) ? $queried_archive->name : 'case-study';
$archive_object = get_post_type_object( $archive_type );
$archive_label  = $archive_object ? $archive_object->labels->name : __( 'Case studies', 'sparkagency' );
?>
<main id="site-content" class="spark-case-archive" role="main">
	<header class="spark-case-archive__hero" data-brand="agency">
		<div class="spark-case-archive__wrap">
			<div class="spark-case-archive__topline">
				<nav class="spark-case-archive__crumbs" aria-label="<?php esc_attr_e( 'Okruszki', 'sparkagency' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'sparkagency' ); ?></a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html( $archive_label ); ?></span></nav>
				<div class="spark-case-archive__eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			</div>
			<h1 class="spark-case-archive__title"><?php echo esc_html( $title ); ?></h1>
			<div class="spark-case-archive__intro">
				<?php if ( '' !== trim( (string) $lead ) ) : ?><p class="spark-case-archive__lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
				<?php if ( '' !== trim( (string) $subtitle ) ) : ?><div class="spark-case-archive__subtitle"><?php echo wp_kses_post( wpautop( $subtitle ) ); ?></div><?php endif; ?>
			</div>
		</div>
	</header>

	<section class="spark-case-archive__listing" aria-label="<?php esc_attr_e( 'Lista realizacji', 'sparkagency' ); ?>">
		<div class="spark-case-archive__wrap">
			<?php if ( ! empty( $filter_terms ) ) : ?>
				<div class="spark-case-archive__filters" role="group" aria-label="<?php esc_attr_e( 'Filtruj realizacje', 'sparkagency' ); ?>">
					<button class="spark-case-archive__filter is-active" type="button" data-case-filter="all" aria-pressed="true"><?php esc_html_e( 'Wszystkie', 'sparkagency' ); ?><sup><?php echo esc_html( (string) count( $cases ) ); ?></sup></button>
					<?php foreach ( $filter_terms as $term ) : ?>
						<button class="spark-case-archive__filter" type="button" data-case-filter="<?php echo esc_attr( $term->slug ); ?>" aria-pressed="false"><i aria-hidden="true"></i><?php echo esc_html( $term->name ); ?><sup><?php echo esc_html( (string) $filter_counts[ $term->slug ] ); ?></sup></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="spark-case-archive__grid" data-case-grid>
					<?php $case_index = 0; ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php
						$case = $cases[ $case_index ] ?? array( 'brands' => array(), 'categories' => array() );
						++$case_index;
						$brand = 'agency';
						if ( ! empty( $case['brands'] ) ) {
							$brand = sanitize_key( $case['brands'][0]->slug );
							$brand = str_replace( 'spark', '', $brand );
						}
						$brand = in_array( $brand, array( 'agency', 'exp', 'warsaw', 'health', 'studio', 'works' ), true ) ? $brand : 'agency';
						$category_slugs = wp_list_pluck( $case['categories'], 'slug' );
						$category_names = wp_list_pluck( $case['categories'], 'name' );
						$category_label = implode( ', ', $category_names );
						$client = get_post_meta( get_the_ID(), 'client', true );
						$metric_value = get_post_meta( get_the_ID(), 'metric_value', true );
						$metric_label = get_post_meta( get_the_ID(), 'metric_label', true );
						$card_filters = ! empty( $category_slugs ) ? $category_slugs : array( 'uncategorized' );
						?>
						<a class="spark-case-archive__card<?php echo 0 === ( ( $case_index - 1 ) % 3 ) ? ' is-wide' : ''; ?>" href="<?php the_permalink(); ?>" data-case-card data-case-categories="<?php echo esc_attr( implode( ' ', $card_filters ) ); ?>" data-brand="<?php echo esc_attr( $brand ); ?>">
							<div class="spark-case-archive__art<?php echo has_post_thumbnail() ? ' has-image' : ''; ?>">
								<span class="spark-case-archive__art-grid" aria-hidden="true"></span>
								<?php if ( has_post_thumbnail() ) : ?><?php the_post_thumbnail( 'large', array( 'class' => 'spark-case-archive__image', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?><?php endif; ?>
								<?php if ( ! empty( $case['brands'] ) ) : ?><span class="spark-case-archive__chips"><?php foreach ( $case['brands'] as $brand_term ) : ?><span><?php echo esc_html( $brand_term->name ); ?></span><?php endforeach; ?></span><?php endif; ?>
								<span class="spark-case-archive__number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $case_index ) ); ?></span>
							</div>
							<div class="spark-case-archive__body">
								<div class="spark-case-archive__meta"><span><?php echo esc_html( $client ?: get_the_title() ); ?></span><span><?php echo esc_html( $category_label ?: get_the_date( 'Y' ) ); ?></span></div>
								<h2 class="spark-case-archive__card-title"><?php the_title(); ?></h2>
								<?php if ( has_excerpt() ) : ?><p class="spark-case-archive__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p><?php endif; ?>
								<span class="spark-case-archive__more"><?php esc_html_e( 'Zobacz case study', 'sparkagency' ); ?></span>
								<?php if ( $metric_value && $metric_label ) : ?><div class="spark-case-archive__metric"><strong><?php echo esc_html( $metric_value ); ?></strong><span><?php echo esc_html( $metric_label ); ?></span></div><?php endif; ?>
							</div>
						</a>
					<?php endwhile; ?>
				</div>
				<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '← Nowsze', 'sparkagency' ), 'next_text' => __( 'Starsze →', 'sparkagency' ) ) ); ?>
			<?php else : ?>
				<p class="spark-case-archive__empty"><?php esc_html_e( 'Wkrótce opublikujemy tutaj nowe realizacje.', 'sparkagency' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
