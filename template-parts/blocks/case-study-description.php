<?php
/**
 * Opis case study block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-case-study-description' ) ) ); ?> aria-label="<?php esc_attr_e( 'Opis realizacji', 'sparkagency' ); ?>">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered and sanitized by Gutenberg. ?>
</section>
