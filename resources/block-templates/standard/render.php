<?php
/**
 * {{TITLE}} block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-{{SLUG}}' ) ) ); ?>>
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h2><?php echo wp_kses_post( $attributes['title'] ); ?></h2>
	<?php endif; ?>
</section>
