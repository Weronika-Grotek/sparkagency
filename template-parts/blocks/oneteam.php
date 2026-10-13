<?php
/** Server-side section markup. @package SparkOneTeam */
defined( 'ABSPATH' ) || exit;

$text = static function ( $value ) {
	return is_string( $value ) ? trim( $value ) : '';
};
$theme   = in_array( $attributes['theme'] ?? '', array( 'dark', 'light', 'inherit' ), true ) ? $attributes['theme'] : 'dark';
$columns = max( 1, min( 3, (int) ( $attributes['columns'] ?? 3 ) ) );
$eyebrow = $text( $attributes['eyebrow'] ?? '' );
$title   = $text( $attributes['title'] ?? '' );
$lede    = $text( $attributes['lede'] ?? '' );
$note    = wp_kses( $text( $attributes['note'] ?? '' ), array( 'strong' => array(), 'em' => array(), 'br' => array() ) );
$label   = $text( $attributes['ctaLabel'] ?? '' );
$url     = esc_url( $text( $attributes['ctaUrl'] ?? '' ) );
$cards   = array();
$brands  = array( 'agency', 'warsaw', 'health', 'works', 'exp', 'studio', 'custom' );

foreach ( is_array( $attributes['cards'] ?? null ) ? $attributes['cards'] : array() as $card ) {
	if ( ! is_array( $card ) ) {
		continue;
	}
	$item = array(
		'title'       => $text( $card['title'] ?? '' ),
		'lead'        => $text( $card['lead'] ?? '' ),
		'description' => $text( $card['description'] ?? '' ),
		'brand'       => in_array( $card['brand'] ?? '', $brands, true ) ? $card['brand'] : 'agency',
		'color'       => sanitize_hex_color( $text( $card['color'] ?? '' ) ),
	);
	if ( '' !== $item['title'] || '' !== $item['lead'] || '' !== $item['description'] ) {
		$cards[] = $item;
	}
}

if ( '' === $eyebrow && '' === $title && '' === $lede && '' === trim( wp_strip_all_tags( $note ) ) && empty( $cards ) && ( '' === $label || '' === $url ) ) {
	return;
}

$heading_id = wp_unique_id( 'spark-oneteam-title-' );
$wrapper    = array(
	'class' => 'spark-oneteam' . ( 'inherit' !== $theme ? ' is-theme-' . $theme : '' ),
	'style' => '--teams-columns:' . $columns . ';',
);
if ( '' !== $title ) {
	$wrapper['aria-labelledby'] = $heading_id;
}
if ( '' !== $text( $attributes['anchor'] ?? '' ) ) {
	$wrapper['id'] = $text( $attributes['anchor'] );
}
?>
<section <?php echo get_block_wrapper_attributes( $wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes wrapper attributes. ?>>
	<div class="spark-oneteam__inner">
		<?php if ( '' !== $eyebrow || '' !== $title || '' !== $lede ) : ?>
			<div class="spark-oneteam__header">
				<span class="spark-oneteam__label"><?php echo esc_html( $eyebrow ); ?></span>
				<div>
					<?php if ( '' !== $title ) : ?>
						<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $lede ) : ?>
						<p class="spark-oneteam__lede"><?php echo esc_html( $lede ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $cards ) : ?>
			<div class="spark-oneteam__cards">
				<?php foreach ( $cards as $card ) : ?>
					<article class="spark-oneteam__card" data-team-brand="<?php echo esc_attr( $card['brand'] ); ?>"<?php if ( 'custom' === $card['brand'] && $card['color'] ) : ?> style="--team-accent:<?php echo esc_attr( $card['color'] ); ?>"<?php endif; ?>>
						<?php if ( '' !== $card['title'] ) : ?>
							<h3><?php echo esc_html( $card['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( '' !== $card['lead'] ) : ?>
							<span class="spark-oneteam__lead"><?php echo esc_html( $card['lead'] ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $card['description'] ) : ?>
							<p><?php echo esc_html( $card['description'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( '' !== trim( wp_strip_all_tags( $note ) ) ) : ?>
			<p class="spark-oneteam__note"><?php echo $note; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allowlist via wp_kses above. ?></p>
		<?php endif; ?>
		<?php if ( '' !== $label && '' !== $url ) : ?>
			<p class="spark-oneteam__cta"><a class="spark-oneteam__link" href="<?php echo $url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url above. ?>"><?php echo esc_html( $label ); ?> <span aria-hidden="true">→</span></a></p>
		<?php endif; ?>
	</div>
</section>
