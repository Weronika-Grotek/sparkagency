<?php
/**
 * Front-end template for the Spark hero block.
 *
 * @package DevTheme
 * @author dev.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$theme      = 'light' === $attributes['theme'] ? 'light' : 'dark';
$background_color = sanitize_hex_color( $attributes['backgroundColor'] );
$particle_color = sanitize_hex_color( $attributes['particleColor'] );
$particle_glow_color = sanitize_hex_color( $attributes['particleGlowColor'] );
$particle_style = '';
$graphic_id = absint( $attributes['graphicId'] );
$graphic_url = $graphic_id ? wp_get_attachment_image_url( $graphic_id, 'full' ) : esc_url_raw( $attributes['graphicUrl'] );
$show_graphic = ! empty( $attributes['showGraphic'] ) && ! empty( $graphic_url );
$aria_label = trim( $attributes['headline'] . ' ' . $attributes['brand'] );

if ( $background_color ) {
	$particle_style .= '--hero-bg:' . $background_color . ';';
}

if ( $particle_color ) {
	$particle_style .= '--hero-particle-color:' . $particle_color . ';';
}

if ( $particle_glow_color ) {
	$particle_style .= '--hero-particle-glow-color:' . $particle_glow_color . ';';
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'dev-spark-hero is-theme-' . $theme . ' is-brand-agency', 'style' => $particle_style ) ); ?> data-theme="<?php echo esc_attr( $theme ); ?>" data-particle-animation="<?php echo false === $attributes['particleAnimation'] ? 'false' : 'true'; ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="dev-spark-hero__particles" aria-hidden="true">
		<canvas class="dev-spark-hero__canvas"></canvas>
	</div>
	<div class="dev-spark-hero__motifs" aria-hidden="true">
		<svg class="dev-spark-hero__motif dev-spark-hero__motif--works" viewBox="0 0 520 420" fill="none">
			<g stroke="currentColor" stroke-opacity=".55">
				<rect x="140" y="110" width="140" height="200" /><rect x="280" y="110" width="90" height="200" /><rect x="370" y="110" width="140" height="200" /><rect x="50" y="110" width="90" height="200" />
				<path d="M140 110 160 40h100l20 70M140 310l20 70h100l20-70M280 110V70h90v40M280 310v40h90v-40M50 110l-20 15v170l20 15" stroke-dasharray="6 5" />
			</g>
		</svg>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--exp"></div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--warsaw">
			<span class="dev-spark-hero__register dev-spark-hero__register--tl"></span><span class="dev-spark-hero__register dev-spark-hero__register--tr"></span><span class="dev-spark-hero__register dev-spark-hero__register--bl"></span><span class="dev-spark-hero__register dev-spark-hero__register--br"></span>
			<span class="dev-spark-hero__cmyk"><i></i><i></i><i></i><i></i></span>
		</div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--health"><i></i><i></i><i></i></div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--studio"><span></span><span></span><b><i></i></b></div>
	</div>
	<?php if ( $show_graphic ) : ?>
		<figure class="dev-spark-hero__graphic">
			<?php
			if ( $graphic_id ) {
				echo wp_get_attachment_image( $graphic_id, 'full', false, array( 'loading' => 'eager', 'fetchpriority' => 'high' ) );
			} else {
				printf( '<img src="%1$s" alt="%2$s" loading="eager" fetchpriority="high">', esc_url( $graphic_url ), esc_attr( $attributes['graphicAlt'] ) );
			}
			?>
		</figure>
	<?php endif; ?>
	<?php if ( '' !== trim( $content ) ) : ?>
		<?php echo wp_kses_post( $content ); ?>
	<?php else : ?>
		<div class="dev-spark-hero__wrap">
			<div class="dev-spark-hero__meta">
				<p class="dev-spark-hero__meta-item"><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
				<p class="dev-spark-hero__meta-item"><?php echo esc_html( $attributes['address'] ); ?></p>
			</div>
			<h1 class="dev-spark-hero__title"><?php echo esc_html( $attributes['headline'] ); ?> <strong><?php echo esc_html( $attributes['brand'] ); ?></strong></h1>
			<div class="dev-spark-hero__foot">
				<p class="dev-spark-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<div class="dev-spark-hero__actions">
					<?php if ( $attributes['primaryLabel'] && $attributes['primaryUrl'] ) : ?>
						<div class="wp-block-button dev-spark-hero__button"><a class="wp-block-button__link" href="<?php echo esc_url( $attributes['primaryUrl'] ); ?>"><?php echo esc_html( $attributes['primaryLabel'] ); ?></a></div>
					<?php endif; ?>
					<?php if ( $attributes['secondaryLabel'] && $attributes['secondaryUrl'] ) : ?>
						<div class="wp-block-button dev-spark-hero__button--ghost"><a class="wp-block-button__link" href="<?php echo esc_url( $attributes['secondaryUrl'] ); ?>"><?php echo esc_html( $attributes['secondaryLabel'] ); ?></a></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
