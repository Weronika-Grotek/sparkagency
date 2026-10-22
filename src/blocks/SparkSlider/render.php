<?php
/**
 * Front-end template for the Spark slider block.
 *
 * @package DevTheme
 * @author dev.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$words = array();
$collect_words = static function ( $inner_blocks ) use ( &$collect_words, &$words ): void {
	foreach ( $inner_blocks as $inner_block ) {
		if ( 'core/list-item' === $inner_block->name ) {
			$word = trim( wp_strip_all_tags( $inner_block->attributes['content'] ?? '' ) );
			if ( '' !== $word ) {
				$words[] = $word;
			}
			continue;
		}

		if ( ! empty( $inner_block->inner_blocks ) ) {
			$collect_words( $inner_block->inner_blocks );
		}
	}
};
$collect_words( $block->inner_blocks );

if ( empty( $words ) ) {
	$words = $attributes['words'] ?? array( 'Packaging', 'Software', 'Print', 'Pharma', 'Motion', 'Retusz', 'ATL/BTL' );
	$words = array_values( array_filter( array_map( 'sanitize_text_field', $words ) ) );
}

if ( empty( $words ) ) {
	return;
}

$label = implode( ', ', $words );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'dev-spark-marquee' ) ); ?> data-dev-spark-marquee data-words="<?php echo esc_attr( wp_json_encode( $words ) ); ?>" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
	<?php foreach ( array( $words, array_reverse( $words ) ) as $row_index => $row_words ) : ?>
		<div class="dev-spark-marquee__row<?php echo 1 === $row_index ? ' dev-spark-marquee__row--outline' : ''; ?>" aria-hidden="true">
			<div class="dev-spark-marquee__group">
				<?php for ( $word_index = 0; $word_index < count( $row_words ) * 3; $word_index++ ) : ?>
					<?php $word = $row_words[ $word_index % count( $row_words ) ]; ?>
					<span class="dev-spark-marquee__word<?php echo 1 === $row_index && 1 === $word_index % 3 ? ' dev-spark-marquee__word--filled' : ''; ?>"><?php echo esc_html( $word ); ?></span>
				<?php endfor; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>