<?php
/**
 * Contact handoff block template.
 *
 * @package SparkAgency
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Rendered nested form blocks.
 */

defined( 'ABSPATH' ) || exit;
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-contact-handoff' ) ) ); ?> data-spark-contact-handoff>
	<div class="spark-contact-handoff__summary" data-contact-summary hidden>
		<?php if ( ! empty( $attributes['title'] ) ) : ?><h2><?php echo wp_kses_post( $attributes['title'] ); ?></h2><?php endif; ?>
		<dl data-contact-list></dl>
		<?php if ( ! empty( $attributes['lede'] ) ) : ?><p><?php echo wp_kses_post( $attributes['lede'] ); ?></p><?php endif; ?>
		<a class="spark-contact-handoff__change" href="#" data-contact-change hidden><?php esc_html_e( 'Zmień odpowiedzi', 'sparkagency' ); ?></a>
	</div>
	<p class="spark-contact-handoff__empty" data-contact-empty><?php echo esc_html( $attributes['emptyText'] ?? '' ); ?></p>
	<div class="spark-contact-handoff__form">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered Gutenberg inner blocks. ?>
	</div>
</section>
