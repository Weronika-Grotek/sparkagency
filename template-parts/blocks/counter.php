<?php
/**
 * Counter block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

$value    = isset( $attributes['value'] ) && is_numeric( $attributes['value'] ) ? (int) $attributes['value'] : 0;
$prefix   = isset( $attributes['prefix'] ) ? (string) $attributes['prefix'] : '';
$suffix   = isset( $attributes['suffix'] ) ? (string) $attributes['suffix'] : '';
$label    = isset( $attributes['label'] ) ? (string) $attributes['label'] : '';
$duration = isset( $attributes['duration'] ) && is_numeric( $attributes['duration'] ) ? (int) $attributes['duration'] : 1200;
$duration = min( 5000, max( 300, $duration ) );
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-counter' ) ) ); ?> data-spark-counter data-counter-value="<?php echo esc_attr( $value ); ?>" data-counter-duration="<?php echo esc_attr( $duration ); ?>">
	<p class="spark-counter__value"><span><?php echo esc_html( $prefix ); ?></span><strong data-spark-counter-value><?php echo esc_html( $value ); ?></strong><span><?php echo esc_html( $suffix ); ?></span></p>
	<?php if ( '' !== $label ) : ?>
		<p class="spark-counter__label"><?php echo wp_kses_post( $label ); ?></p>
	<?php endif; ?>
</section>
