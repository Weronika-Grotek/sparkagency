<?php
/** Dynamic application panel block for a career offer. */

defined( 'ABSPATH' ) || exit;

$job_id       = get_the_ID();
$email        = sanitize_email( get_post_meta( $job_id, 'career_email', true ) );
$email        = $email ? $email : 'kariera@sparkagency.pl';
$apply_url    = 'mailto:' . antispambot( $email ) . '?subject=' . rawurlencode( get_the_title( $job_id ) );
$eyebrow      = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
$description  = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$button_label = isset( $attributes['buttonLabel'] ) ? (string) $attributes['buttonLabel'] : __( 'Aplikuj', 'sparkagency' );
?>
<aside <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-career__apply' ) ) ); ?> aria-label="<?php echo esc_attr( $eyebrow ? wp_strip_all_tags( $eyebrow ) : __( 'Aplikuj na stanowisko', 'sparkagency' ) ); ?>">
	<?php if ( $eyebrow ) : ?><span><?php echo wp_kses_post( $eyebrow ); ?></span><?php endif; ?>
	<?php if ( $description ) : ?><div class="spark-career__apply-copy"><?php echo wp_kses_post( wpautop( $description ) ); ?></div><?php endif; ?>
	<a class="spark-career__button" href="<?php echo esc_url( $apply_url ); ?>"><?php echo esc_html( $button_label ); ?><span aria-hidden="true">→</span></a>
</aside>
