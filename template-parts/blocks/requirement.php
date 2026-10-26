<?php
/** Editable career requirement list block. */

defined( 'ABSPATH' ) || exit;

$eyebrow = isset( $attributes['eyebrow'] ) ? wp_kses_post( (string) $attributes['eyebrow'] ) : '';
$title   = isset( $attributes['title'] ) ? wp_kses_post( (string) $attributes['title'] ) : '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$items   = array_filter( array_map( static fn( $item ) => wp_kses_post( (string) $item ), $items ), static fn( $item ) => '' !== trim( wp_strip_all_tags( $item ) ) );
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-requirement' ) ) ); ?>>
	<?php if ( $eyebrow ) : ?><span class="spark-requirement__eyebrow"><?php echo $eyebrow; ?></span><?php endif; ?>
	<div class="spark-requirement__content">
		<?php if ( $title ) : ?><h2 class="spark-requirement__title"><?php echo $title; ?></h2><?php endif; ?>
		<?php if ( $items ) : ?>
			<ul class="spark-requirement__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="spark-requirement__item"><svg class="spark-requirement__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12.5 4.5 4.5L19 7" /></svg><span><?php echo $item; ?></span></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
