<?php
/**
 * Front-end template for the Spark group block.
 *
 * @package SparkAgency
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$eyebrow = trim( (string) ( $attributes['eyebrow'] ?? '' ) );
$panels  = is_array( $attributes['panels'] ?? null ) ? $attributes['panels'] : array();
$brands  = array( 'agency', 'works', 'exp', 'warsaw', 'health', 'studio' );
$motifs  = array( 'works', 'exp', 'warsaw', 'health', 'studio' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'dev-spark-group is-theme-dark', 'aria-label' => $eyebrow ) ); ?> data-dev-spark-group="">
	<div class="dev-spark-group__wrap">
		<div class="dev-spark-group__head">
			<span class="dev-spark-group__mono dev-spark-group__muted"><?php echo wp_kses_post( $attributes['eyebrow'] ?? '' ); ?></span>
			<div>
				<h2><?php echo wp_kses_post( $attributes['title'] ?? '' ); ?></h2>
				<p class="dev-spark-group__lede"><?php echo wp_kses_post( $attributes['lede'] ?? '' ); ?></p>
			</div>
		</div>
		<div class="dev-spark-group__stack">
			<?php foreach ( $panels as $index => $panel ) : ?>
				<?php
				if ( ! is_array( $panel ) ) {
					continue;
				}

				$brand = in_array( $panel['brand'] ?? '', $brands, true ) ? $panel['brand'] : 'agency';
				$motif = in_array( $panel['motif'] ?? '', $motifs, true ) ? $panel['motif'] : '';
				$url   = esc_url( $panel['url'] ?? '' );
				$label = trim( wp_strip_all_tags( (string) ( $panel['ctaLabel'] ?? '' ) ) . ' ' . wp_strip_all_tags( (string) ( $panel['prefix'] ?? '' ) ) . wp_strip_all_tags( (string) ( $panel['name'] ?? '' ) ) );
				?>
				<article class="dev-spark-group__panel is-brand-<?php echo esc_attr( $brand ); ?>" style="--i:<?php echo esc_attr( (string) $index ); ?>">
					<?php if ( 'works' === $motif ) : ?>
						<svg class="dev-spark-group__motif dev-spark-group__m-works" viewBox="0 0 520 420" fill="none" aria-hidden="true" style="color:var(--pc)">
							<g stroke="currentColor" stroke-opacity=".6">
								<rect x="140" y="110" width="140" height="200" /><rect x="280" y="110" width="90" height="200" /><rect x="370" y="110" width="140" height="200" /><rect x="50" y="110" width="90" height="200" />
								<path d="M140 110 L160 40 H260 L280 110 M140 310 L160 380 H260 L280 310" /><path d="M280 110 V70 H370 V110 M280 310 V350 H370 V310" stroke-dasharray="6 5" />
							</g>
						</svg>
					<?php elseif ( 'exp' === $motif ) : ?>
						<div class="dev-spark-group__motif dev-spark-group__m-exp" aria-hidden="true"></div>
					<?php elseif ( 'warsaw' === $motif ) : ?>
						<div class="dev-spark-group__motif" style="inset:16px" aria-hidden="true">
							<span class="dev-spark-group__reg" style="right:0;top:0"></span><span class="dev-spark-group__reg" style="right:0;bottom:0"></span>
							<span class="dev-spark-group__cmyk" style="top:auto;bottom:48px;right:4px"><i style="background:#00AEEF"></i><i style="background:#EC008C"></i><i style="background:#FFF200"></i><i style="background:#000;outline:1px solid #444"></i></span>
						</div>
					<?php elseif ( 'health' === $motif ) : ?>
						<div class="dev-spark-group__motif dev-spark-group__caps" style="right:4%;bottom:10%;width:45%;height:60%" aria-hidden="true">
							<i style="width:220px;height:78px;left:20%;top:10%;transform:rotate(-28deg)"></i><i style="width:130px;height:46px;right:0;top:55%;transform:rotate(18deg)"></i>
						</div>
					<?php elseif ( 'studio' === $motif ) : ?>
						<div class="dev-spark-group__motif dev-spark-group__m-studio" aria-hidden="true">
							<span class="dev-spark-group__lbox" style="top:0"></span><span class="dev-spark-group__lbox" style="bottom:0"></span>
						</div>
					<?php endif; ?>
					<div class="dev-spark-group__panel-1">
						<div class="dev-spark-group__panel-idx dev-spark-group__mono dev-spark-group__muted">
							<b><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></b>
							<span><?php echo esc_html( '/ ' . sprintf( '%02d', count( $panels ) ) ); ?></span>
							<span>· <?php echo wp_kses_post( $panel['category'] ?? '' ); ?></span>
						</div>
						<h3>
							<span class="group__panel-1-subtitle"><?php echo wp_kses_post( $panel['prefix'] ?? '' ); ?></span>
							<span class="group__panel-1-title group__panel-1-title--warsaw"><?php echo wp_kses_post( $panel['name'] ?? '' ); ?></span>
						</h3>
					</div>
					<div class="dev-spark-group__panel-2">
						<p><?php echo wp_kses_post( $panel['text'] ?? '' ); ?></p>
						<div class="dev-spark-group__pills">
							<?php foreach ( ( is_array( $panel['pills'] ?? null ) ? $panel['pills'] : array() ) as $pill ) : ?>
								<span><?php echo wp_kses_post( $pill ); ?></span>
							<?php endforeach; ?>
						</div>
						<a class="dev-spark-group__go"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ' aria-disabled="true"'; ?> aria-label="<?php echo esc_attr( $label ); ?>">
							<?php echo wp_kses_post( $panel['ctaLabel'] ?? '' ); ?> <span aria-hidden="true">→</span>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		$note     = trim( (string) ( $attributes['note'] ?? '' ) );
		$cta_label = trim( (string) ( $attributes['ctaLabel'] ?? '' ) );
		$cta_url   = esc_url( $attributes['ctaUrl'] ?? '' );
		?>
		<?php if ( '' !== $note || ( '' !== $cta_label && '' !== $cta_url ) ) : ?>
			<div class="dev-spark-group__footer">
				<?php if ( '' !== $note ) : ?>
					<p class="dev-spark-group__note"><?php echo wp_kses_post( $note ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $cta_label && '' !== $cta_url ) : ?>
					<p class="dev-spark-group__cta">
						<a class="dev-spark-group__link-arrow" href="<?php echo esc_url( $cta_url ); ?>">
							<?php echo wp_kses_post( $cta_label ); ?> <span aria-hidden="true">→</span>
						</a>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
