<?php
/**
 * Sekcja opisu case study block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-case-study-description-item' ) ) ); ?>>
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h2 class="spark-case-study-description-item__title"><?php echo wp_kses_post( $attributes['title'] ); ?></h2>
	<?php endif; ?>
	<div class="spark-case-study-description-item__body">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered and sanitized by Gutenberg. ?>
	</div>
</section>
