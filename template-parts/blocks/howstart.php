<?php
/** Server-side markup for Spark How Start. */
defined( 'ABSPATH' ) || exit;
$text = static function ( $value ): string { return is_string( $value ) ? trim( $value ) : ''; };
$theme = in_array( $attributes['theme'] ?? '', array( 'dark', 'light', 'inherit' ), true ) ? $attributes['theme'] : 'dark';
$eyebrow = $text( $attributes['eyebrow'] ?? '' ); $title = $text( $attributes['title'] ?? '' ); $lede = $text( $attributes['lede'] ?? '' );
$label = $text( $attributes['ctaLabel'] ?? '' ); $url = esc_url( $text( $attributes['ctaUrl'] ?? '' ) ); $accent = sanitize_hex_color( $text( $attributes['accentColor'] ?? '' ) ); $title_font_size = $text( $attributes['titleFontSize'] ?? '' ); $steps = array();
if ( ! preg_match( '/^\\d+(?:\\.\\d+)?(?:px|rem|em|%|vw|vh)$/', $title_font_size ) ) { $title_font_size = ''; }
foreach ( is_array( $attributes['steps'] ?? null ) ? $attributes['steps'] : array() as $step ) {
	if ( ! is_array( $step ) ) { continue; }
	$item = array( 'number' => $text( $step['number'] ?? '' ), 'title' => $text( $step['title'] ?? '' ), 'description' => $text( $step['description'] ?? '' ) );
	if ( '' !== $item['title'] || '' !== $item['description'] || '' !== $item['number'] ) { $steps[] = $item; }
}
if ( '' === $eyebrow && '' === $title && '' === $lede && empty( $steps ) && ( '' === $label || '' === $url ) ) { return; }
$heading_id = wp_unique_id( 'spark-howstart-title-' ); $wrapper = array( 'class' => 'spark-howstart' . ( 'inherit' !== $theme ? ' is-theme-' . $theme : '' ) );
$styles = array(); if ( $accent ) { $styles[] = '--howstart-accent:' . $accent; } if ( '' !== $title_font_size ) { $styles[] = '--howstart-title-font-size:' . $title_font_size; } if ( $styles ) { $wrapper['style'] = implode( ';', $styles ) . ';'; } if ( '' !== $title ) { $wrapper['aria-labelledby'] = $heading_id; }
?>
<section <?php echo get_block_wrapper_attributes( $wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="spark-howstart__inner"><div class="spark-howstart__layout">
		<div class="spark-howstart__intro">
			<?php if ( '' !== $eyebrow ) : ?><span class="spark-howstart__eyebrow"><?php echo esc_html( $eyebrow ); ?></span><?php endif; ?>
			<?php if ( '' !== $title ) : ?><h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
			<?php if ( '' !== $lede ) : ?><p class="spark-howstart__lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
			<?php if ( '' !== $label && '' !== $url ) : ?><p class="spark-howstart__cta"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?> <span aria-hidden="true">→</span></a></p><?php endif; ?>
		</div>
		<?php if ( $steps ) : ?><ol class="spark-howstart__steps"><?php foreach ( $steps as $index => $step ) : ?><li class="spark-howstart__step<?php echo 0 === $index ? ' is-active' : ''; ?>"><b aria-hidden="true"><?php echo esc_html( '' !== $step['number'] ? $step['number'] : str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></b><div><?php if ( '' !== $step['title'] ) : ?><h3><?php echo esc_html( $step['title'] ); ?></h3><?php endif; ?><?php if ( '' !== $step['description'] ) : ?><p><?php echo esc_html( $step['description'] ); ?></p><?php endif; ?></div></li><?php endforeach; ?></ol><?php endif; ?>
	</div></div>
</section>
