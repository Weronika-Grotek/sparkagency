<?php
/** Dynamic single career header block. */

defined( 'ABSPATH' ) || exit;

$job_id      = get_the_ID();
$status      = sparkagency_get_career_option_label( $job_id, 'career_status', 'status' );
$details     = sparkagency_get_career_details( $job_id );
$brand       = sparkagency_get_career_company( $job_id );
$brand_slug  = $brand ? $brand['slug'] : 'agency';
$career_page = get_page_by_path( 'kariera' );
$career_url  = $career_page instanceof WP_Post ? get_permalink( $career_page ) : home_url( '/kariera/' );
$email       = sanitize_email( get_post_meta( $job_id, 'career_email', true ) );
$email       = $email ? $email : 'kariera@sparkagency.pl';
$apply_url   = 'mailto:' . antispambot( $email ) . '?subject=' . rawurlencode( get_the_title( $job_id ) );
$home_label  = isset( $attributes['homeLabel'] ) ? (string) $attributes['homeLabel'] : __( 'Start', 'sparkagency' );
$career_label = isset( $attributes['careerLabel'] ) ? (string) $attributes['careerLabel'] : __( 'Kariera', 'sparkagency' );
$apply_label = isset( $attributes['applyLabel'] ) ? (string) $attributes['applyLabel'] : __( 'Aplikuj', 'sparkagency' );
$all_label   = isset( $attributes['allOffersLabel'] ) ? (string) $attributes['allOffersLabel'] : __( 'Wszystkie oferty', 'sparkagency' );
$hero_lead   = implode( ' · ', array_filter( array( $status, $brand['name'] ?? '' ) ) );
?>
<header <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-career__job-hero dev-spark-hero dev-spark-hero--secondary is-brand-' . $brand_slug, 'data-brand' => $brand_slug, 'data-spark-fireworks' => '', 'data-particle-animation' => 'true' ) )); ?>>
	<div class="spark-career__particles" aria-hidden="true"><canvas></canvas></div>
	<div class="spark-career__wrap spark-career__job-hero-wrap">
		<div class="spark-career__job-hero-meta">
			<nav class="spark-career__crumbs" aria-label="<?php esc_attr_e( 'Okruszki', 'sparkagency' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $home_label ); ?></a><span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $career_url ); ?>"><?php echo esc_html( $career_label ); ?></a><span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>
			<?php if ( $brand ) : ?><span class="spark-career__brand spark-career__brand--<?php echo esc_attr( $brand_slug ); ?>"><i aria-hidden="true"></i><?php echo esc_html( $brand['name'] ); ?></span><?php endif; ?>
		</div>
		<h1 class="spark-career__job-hero-title dev-spark-hero__title"><?php the_title(); ?></h1>
		<div class="spark-career__job-hero-foot">
			<div class="spark-career__job-hero-copy">
				<?php if ( $hero_lead ) : ?><p class="spark-career__job-hero-lead"><?php echo esc_html( $hero_lead ); ?></p><?php endif; ?>
				<?php if ( has_excerpt( $job_id ) || $details ) : ?><div class="spark-career__job-hero-sub"><?php if ( has_excerpt( $job_id ) ) : ?><p><?php echo esc_html( get_the_excerpt( $job_id ) ); ?></p><?php endif; ?><?php if ( $details ) : ?><p><?php echo esc_html( implode( ' · ', $details ) ); ?></p><?php endif; ?></div><?php endif; ?>
			</div>
			<div class="spark-career__job-hero-actions">
				<a class="spark-career__button" href="<?php echo esc_url( $apply_url ); ?>"><?php echo esc_html( $apply_label ); ?><span aria-hidden="true">→</span></a>
				<a class="spark-career__button spark-career__button--ghost" href="<?php echo esc_url( $career_url ); ?>"><?php echo esc_html( $all_label ); ?></a>
			</div>
		</div>
	</div>
</header>
