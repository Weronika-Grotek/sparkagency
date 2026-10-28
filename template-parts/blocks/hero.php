<?php
/**
 * Front-end template for the Spark hero block.
 *
 * @package SparkAgency
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$themes     = array( 'light', 'dark' );
$brands     = array( 'agency', 'works', 'exp', 'warsaw', 'health', 'studio' );
$theme      = in_array( $attributes['theme'], $themes, true ) ? $attributes['theme'] : 'inherit';
$brand      = in_array( $attributes['brand'], $brands, true ) ? $attributes['brand'] : 'agency';
$background_style = 'none' === $attributes['backgroundStyle'] ? 'none' : 'brand';
$background_color = sanitize_hex_color( $attributes['backgroundColor'] );
$particle_color = sanitize_hex_color( $attributes['particleColor'] );
$particle_glow_color = sanitize_hex_color( $attributes['particleGlowColor'] );
$particle_style = '';
$graphic_id = absint( $attributes['graphicId'] );
$graphic_url = $graphic_id ? wp_get_attachment_image_url( $graphic_id, 'full' ) : esc_url_raw( $attributes['graphicUrl'] );
$show_graphic = ! empty( $attributes['showGraphic'] ) && ! empty( $graphic_url );
$show_brand_motif = 'brand' === $background_style;
$aria_label = trim( $attributes['headline'] . ' ' . $attributes['brand'] );
$show_navigation = ! empty( $attributes['showNavigation'] );
$navigation_root_label = trim( $attributes['navigationRootLabel'] ?? '' );
$navigation_root_label = '' !== $navigation_root_label ? $navigation_root_label : 'spark' . strtoupper( $brand );
$navigation_root_url = trim( $attributes['navigationRootUrl'] ?? '' );
$navigation_root_url = '' !== $navigation_root_url ? $navigation_root_url : home_url( '/' );
$navigation_current_label = trim( $attributes['navigationCurrentLabel'] ?? '' );
$show_primary_button = ! isset( $attributes['showPrimaryButton'] ) || true === $attributes['showPrimaryButton'];
$show_secondary_button = ! isset( $attributes['showSecondaryButton'] ) || true === $attributes['showSecondaryButton'];

$breadcrumb_markup = sprintf(
	'<nav class="dev-spark-hero__breadcrumb" aria-label="%1$s"><a href="%2$s">%3$s</a>%4$s</nav>',
	esc_attr__( 'Nawigacja okruszkowa', 'sparkagency' ),
	esc_url( $navigation_root_url ),
	esc_html( $navigation_root_label ),
	'' !== $navigation_current_label ? ' <span aria-hidden="true">/</span> <span>' . esc_html( $navigation_current_label ) . '</span>' : ''
);

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
<?php
$hero_classes = array( 'dev-spark-hero', 'is-brand-' . $brand );
if ( 'inherit' !== $theme ) {
	$hero_classes[] = 'is-theme-' . $theme;
}
if ( $show_navigation ) {
	$hero_classes[] = 'has-navigation';
}
if ( ! $show_primary_button ) {
	$hero_classes[] = 'is-primary-action-hidden';
}
if ( ! $show_secondary_button ) {
	$hero_classes[] = 'is-secondary-action-hidden';
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => implode( ' ', $hero_classes ), 'style' => $particle_style ) ); ?><?php echo 'inherit' !== $theme ? ' data-theme="' . esc_attr( $theme ) . '"' : ''; ?> data-brand="<?php echo esc_attr( $brand ); ?>" data-background-style="<?php echo esc_attr( $background_style ); ?>" data-particle-animation="<?php echo false === $attributes['particleAnimation'] ? 'false' : 'true'; ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<div class="dev-spark-hero__particles" aria-hidden="true">
		<canvas class="dev-spark-hero__canvas"></canvas>
	</div>
	<?php if ( $show_brand_motif ) : ?>
	<div class="dev-spark-hero__motifs" aria-hidden="true">
		<svg class="dev-spark-hero__motif dev-spark-hero__motif--works" viewBox="0 0 520 420" fill="none">
			<g stroke="currentColor" stroke-opacity=".55">
				<rect x="140" y="110" width="140" height="200" /><rect x="280" y="110" width="90" height="200" /><rect x="370" y="110" width="140" height="200" /><rect x="50" y="110" width="90" height="200" />
				<path d="M140 110 160 40h100l20 70M140 310l20 70h100l20-70M280 110V70h90v40M280 310v40h90v-40M50 110l-20 15v170l20 15" stroke-dasharray="6 5" />
			</g>
		</svg>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--exp">
			<div class="dev-spark-hero__code-card"><div class="dev-spark-hero__code-bar"><i></i><i></i><i></i><b>sparkEXP.tsx</b></div><code><span><em>const</em> idea = <strong>spark</strong>;</span><span><em>export</em> default idea;</span><span class="dev-spark-hero__caret"></span></code></div>
		</div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--warsaw">
			<span class="dev-spark-hero__register dev-spark-hero__register--tl"></span><span class="dev-spark-hero__register dev-spark-hero__register--tr"></span><span class="dev-spark-hero__register dev-spark-hero__register--bl"></span><span class="dev-spark-hero__register dev-spark-hero__register--br"></span>
			<span class="dev-spark-hero__cmyk"><i></i><i></i><i></i><i></i></span>
		</div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--health"><i></i><i></i><i></i></div>
		<div class="dev-spark-hero__motif dev-spark-hero__motif--studio"><span></span><span></span><b><i></i> REC 00:00:00:00</b></div>
	</div>
	<?php endif; ?>
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
		<?php
		$hero_content = $content;
		if ( $show_navigation ) {
			$hero_content = preg_replace(
				'/(<div\\b(?=[^>]*\\bclass=(["\\\'])[^"\\\']*\\bdev-spark-hero__meta\\b[^"\\\']*\\2)[^>]*>)/i',
				'$1' . $breadcrumb_markup,
				$content,
				1
			);
		}
		echo wp_kses_post( $hero_content );
		?>
	<?php else : ?>
		<div class="dev-spark-hero__wrap">
			<div class="dev-spark-hero__meta">
				<?php if ( $show_navigation ) : ?>
					<?php echo wp_kses_post( $breadcrumb_markup ); ?>
				<?php else : ?>
					<p class="dev-spark-hero__meta-item"><?php echo esc_html( $attributes['eyebrow'] ); ?></p>
				<?php endif; ?>
				<p class="dev-spark-hero__meta-item"><?php echo esc_html( $attributes['address'] ); ?></p>
			</div>
			<h1 class="dev-spark-hero__title"><?php echo esc_html( $attributes['headline'] ); ?> <strong><?php echo esc_html( $attributes['brand'] ); ?></strong></h1>
			<div class="dev-spark-hero__foot">
				<p class="dev-spark-hero__lead"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php if ( ! empty( $attributes['leadSecondary'] ) ) : ?>
					<p class="dev-spark-hero__lead-secondary"><?php echo esc_html( $attributes['leadSecondary'] ); ?></p>
				<?php endif; ?>
				<div class="dev-spark-hero__actions">
					<?php if ( $show_primary_button && $attributes['primaryLabel'] && $attributes['primaryUrl'] ) : ?>
						<div class="wp-block-button dev-spark-hero__button"><a class="wp-block-button__link" href="<?php echo esc_url( $attributes['primaryUrl'] ); ?>"><?php echo esc_html( $attributes['primaryLabel'] ); ?></a></div>
					<?php endif; ?>
					<?php if ( $show_secondary_button && $attributes['secondaryLabel'] && $attributes['secondaryUrl'] ) : ?>
						<div class="wp-block-button dev-spark-hero__button--ghost"><a class="wp-block-button__link" href="<?php echo esc_url( $attributes['secondaryUrl'] ); ?>"><?php echo esc_html( $attributes['secondaryLabel'] ); ?></a></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
