<?php
/**
 * Front-end template for the Spark Why Agency block.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

$text = static function ( $value ): string {
	return is_string( $value ) ? trim( $value ) : '';
};

$visuals = array( 'specialization', 'ownership', 'delivery', 'technology' );
$colors  = array(
	'agency' => 'var(--spark-color-accent)',
	'exp'    => 'var(--wp--preset--color--exp, #01B57C)',
	'warsaw' => 'var(--wp--preset--color--warsaw, #43A0ED)',
	'health' => 'var(--wp--preset--color--health, #8E3FA3)',
	'works'  => 'var(--wp--preset--color--works, #8C6A2C)',
	'studio' => 'var(--wp--preset--color--studio, #D65251)',
);
$color_style = static function ( $color, $property = '--why-visual-accent' ) use ( $colors ): string {
	$value = $colors[ $color ] ?? $colors['agency'];
	return $property . ':' . $value . ';';
};
$default_visual_content = static function ( string $visual ): array {
	if ( 'ownership' === $visual ) {
		return array(
			'color'  => 'agency',
			'client' => 'Klient',
			'owner'  => 'Jeden właściciel projektu',
			'teams'  => array(
				array( 'label' => 'sparkHEALTH', 'color' => 'health' ),
				array( 'label' => 'sparkEXP', 'color' => 'exp' ),
				array( 'label' => 'sparkWORKS', 'color' => 'works' ),
			),
		);
	}
	if ( 'delivery' === $visual ) {
		return array( 'color' => 'agency', 'steps' => array( 'Rekomendacja', 'Materiały', 'Produkcja', 'Wdrożenie' ) );
	}
	if ( 'technology' === $visual ) {
		return array( 'color' => 'exp', 'tags' => array( 'Narzędzie', 'Integracja', 'Automatyzacja' ) );
	}
	return array(
		'color' => 'agency',
		'chips' => array(
			array( 'label' => 'Healthcare', 'brand' => 'sparkHEALTH', 'color' => 'health' ),
			array( 'label' => 'Packaging', 'brand' => 'sparkWORKS', 'color' => 'works' ),
			array( 'label' => 'Technologia', 'brand' => 'sparkEXP', 'color' => 'exp' ),
			array( 'label' => 'Video', 'brand' => 'sparkSTUDIO', 'color' => 'studio' ),
			array( 'label' => 'Multi-market', 'brand' => 'sparkWARSAW', 'color' => 'warsaw' ),
		),
	);
};

$cards = array();
foreach ( is_array( $attributes['cards'] ?? null ) ? $attributes['cards'] : array() as $index => $card ) {
	if ( ! is_array( $card ) ) {
		continue;
	}

	$visual = in_array( $card['visual'] ?? '', $visuals, true ) ? $card['visual'] : 'specialization';
	$content = array_merge( $default_visual_content( $visual ), is_array( $card['visualContent'] ?? null ) ? $card['visualContent'] : array() );
	$cards[] = array(
		'number'      => $text( $card['number'] ?? sprintf( '%02d', $index + 1 ) ),
		'title'       => $text( $card['title'] ?? '' ),
		'description' => $text( $card['description'] ?? '' ),
		'visual'      => $visual,
		'content'     => $content,
	);
}

$theme = in_array( $attributes['theme'] ?? '', array( 'light', 'dark', 'inherit' ), true ) ? $attributes['theme'] : 'inherit';
$class = 'spark-whyagency is-brand-agency';
if ( 'inherit' !== $theme ) {
	$class .= ' is-theme-' . $theme;
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $class ) ); ?>>
	<div class="spark-whyagency__inner">
		<header class="spark-whyagency__header">
			<p class="spark-whyagency__eyebrow"><?php echo esc_html( $text( $attributes['eyebrow'] ?? '' ) ); ?></p>
			<div class="spark-whyagency__intro">
				<h2><?php echo esc_html( $text( $attributes['title'] ?? '' ) ); ?></h2>
				<?php if ( '' !== $text( $attributes['lede'] ?? '' ) ) : ?><p><?php echo esc_html( $text( $attributes['lede'] ?? '' ) ); ?></p><?php endif; ?>
			</div>
		</header>

		<?php if ( $cards ) : ?><div class="spark-whyagency__grid">
			<?php foreach ( $cards as $card ) : ?>
				<?php $content = $card['content']; ?>
				<article class="spark-whyagency__card">
					<div class="spark-whyagency__visual spark-whyagency__visual--<?php echo esc_attr( $card['visual'] ); ?>" style="<?php echo esc_attr( $color_style( $text( $content['color'] ?? '' ) ) ); ?>" aria-hidden="true">
						<?php if ( 'specialization' === $card['visual'] ) : ?>
							<?php foreach ( array_slice( is_array( $content['chips'] ?? null ) ? $content['chips'] : array(), 0, 10 ) as $chip ) : ?>
								<?php if ( ! is_array( $chip ) ) { continue; } ?>
								<span class="spark-whyagency__chip" style="<?php echo esc_attr( $color_style( $text( $chip['color'] ?? '' ), '--why-chip' ) ); ?>"><i></i><?php echo esc_html( $text( $chip['label'] ?? '' ) ); ?> <b><?php echo esc_html( $text( $chip['brand'] ?? '' ) ); ?></b></span>
							<?php endforeach; ?>
						<?php elseif ( 'ownership' === $card['visual'] ) : ?>
							<span class="spark-whyagency__node"><?php echo esc_html( $text( $content['client'] ?? '' ) ); ?></span><span class="spark-whyagency__arrow"></span><span class="spark-whyagency__node spark-whyagency__node--accent"><?php echo esc_html( $text( $content['owner'] ?? '' ) ); ?></span><span class="spark-whyagency__arrow"></span><span class="spark-whyagency__team">
								<?php foreach ( array_slice( is_array( $content['teams'] ?? null ) ? $content['teams'] : array(), 0, 8 ) as $team_index => $team ) : ?>
									<?php $team = is_array( $team ) ? $team : array( 'label' => $team, 'color' => array( 'health', 'exp', 'works' )[ $team_index ] ?? 'agency' ); ?>
									<span style="<?php echo esc_attr( $color_style( $text( $team['color'] ?? '' ), '--why-chip' ) ); ?>"><i></i><?php echo esc_html( $text( $team['label'] ?? '' ) ); ?></span>
								<?php endforeach; ?>
							</span>
						<?php elseif ( 'delivery' === $card['visual'] ) : ?>
							<ol class="spark-whyagency__timeline"><?php foreach ( array_slice( is_array( $content['steps'] ?? null ) ? $content['steps'] : array(), 0, 8 ) as $step ) : ?><li><?php echo esc_html( $text( $step ) ); ?></li><?php endforeach; ?></ol>
						<?php else : ?>
							<span class="spark-whyagency__window"><i></i><i></i><i></i></span><span class="spark-whyagency__tags"><?php foreach ( array_slice( is_array( $content['tags'] ?? null ) ? $content['tags'] : array(), 0, 10 ) as $tag ) : ?><b><?php echo esc_html( $text( $tag ) ); ?></b><?php endforeach; ?></span>
						<?php endif; ?>
					</div>
					<div class="spark-whyagency__content"><span class="spark-whyagency__number"><?php echo esc_html( $card['number'] ); ?></span><h3><?php echo esc_html( $card['title'] ); ?></h3><p><?php echo esc_html( $card['description'] ); ?></p></div>
				</article>
			<?php endforeach; ?>
		</div><?php endif; ?>

		<?php if ( '' !== $text( $attributes['ctaLabel'] ?? '' ) && '' !== $text( $attributes['ctaUrl'] ?? '' ) ) : ?>
			<p class="spark-whyagency__cta"><a href="<?php echo esc_url( $text( $attributes['ctaUrl'] ) ); ?>"><?php echo esc_html( $text( $attributes['ctaLabel'] ) ); ?> <span aria-hidden="true">→</span></a></p>
		<?php endif; ?>
	</div>
</section>
