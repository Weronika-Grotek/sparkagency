<?php
/** Dynamic career listing block. */

defined( 'ABSPATH' ) || exit;

$eyebrow           = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
$title             = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$description       = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$empty_title       = isset( $attributes['emptyTitle'] ) ? (string) $attributes['emptyTitle'] : __( 'Brak otwartych rekrutacji', 'sparkagency' );
$empty_description = isset( $attributes['emptyDescription'] ) ? (string) $attributes['emptyDescription'] : __( 'Nowe oferty pojawią się w tym miejscu.', 'sparkagency' );
$details_label     = isset( $attributes['detailsLabel'] ) ? (string) $attributes['detailsLabel'] : __( 'Szczegóły', 'sparkagency' );
$career_jobs       = new WP_Query(
	array(
		'post_type'           => 'career',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'ignore_sticky_posts' => true,
	)
);
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-career__listing' ) ) ); ?> aria-label="<?php esc_attr_e( 'Oferty pracy', 'sparkagency' ); ?>">
	<div class="spark-career__wrap">
		<?php if ( $eyebrow || $title || $description ) : ?>
			<header class="spark-career__listing-heading">
				<?php if ( $eyebrow ) : ?><p class="spark-career__listing-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
				<div class="spark-career__listing-heading-copy">
					<?php if ( $title ) : ?><h2 class="spark-career__listing-title"><?php echo wp_kses_post( $title ); ?></h2><?php endif; ?>
					<?php if ( $description ) : ?><p class="spark-career__listing-description"><?php echo wp_kses_post( $description ); ?></p><?php endif; ?>
				</div>
			</header>
		<?php endif; ?>
		<?php if ( $career_jobs->have_posts() ) : ?>
			<div class="spark-career__jobs">
				<?php while ( $career_jobs->have_posts() ) : ?>
					<?php
					$career_jobs->the_post();
					$job_id  = get_the_ID();
					$status  = sparkagency_get_career_option_label( $job_id, 'career_status', 'status' );
					$details = sparkagency_get_career_details( $job_id );
					$brand   = sparkagency_get_career_company( $job_id );
					$summary = get_the_excerpt();
					?>
					<article <?php post_class( 'spark-career__job' ); ?>>
						<div class="spark-career__job-top">
							<?php if ( $brand ) : ?><span class="spark-career__brand spark-career__brand--<?php echo esc_attr( $brand['slug'] ); ?>"><i aria-hidden="true"></i><?php echo esc_html( $brand['name'] ); ?></span><?php endif; ?>
							<?php if ( $status ) : ?><span class="spark-career__status"><?php echo esc_html( $status ); ?></span><?php endif; ?>
						</div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $details ) : ?><ul class="spark-career__details"><?php foreach ( $details as $detail ) : ?><li><?php echo esc_html( $detail ); ?></li><?php endforeach; ?></ul><?php endif; ?>
						<?php if ( $summary ) : ?><p class="spark-career__summary"><?php echo esc_html( wp_trim_words( $summary, 38 ) ); ?></p><?php endif; ?>
						<a class="spark-career__button spark-career__button--small" href="<?php the_permalink(); ?>"><?php echo esc_html( $details_label ); ?><span aria-hidden="true">→</span></a>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<div class="spark-career__empty"><span><?php echo esc_html( $empty_title ); ?></span><p><?php echo esc_html( $empty_description ); ?></p></div>
		<?php endif; ?>
	</div>
</section>
<?php wp_reset_postdata(); ?>
