<?php
/**
 * Front-end template for the SparkAboutfirm block.
 *
 * @package SparkAgency
 *
 * @var array    $attributes Block attributes.
 * @var string   $content Block content.
 * @var WP_Block $block Block instance.
 */

defined( 'ABSPATH' ) || exit;

$eyebrow   = (string) ( $attributes['eyebrow'] ?? '' );
$title     = (string) ( $attributes['title'] ?? '' );
$lede      = (string) ( $attributes['lede'] ?? '' );
$companies = is_array( $attributes['companies'] ?? null ) ? $attributes['companies'] : array();
$theme     = in_array( $attributes['theme'] ?? '', array( 'dark', 'light', 'inherit' ), true ) ? $attributes['theme'] : 'dark';
$brands    = array( 'works', 'exp', 'warsaw', 'health', 'studio', 'custom' );
$classes   = 'spark-aboutfirm' . ( 'inherit' === $theme ? '' : ' is-theme-' . $theme );
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => $classes, 'aria-label' => trim( wp_strip_all_tags( $title ?: $eyebrow ) ) ) ) ); ?>>
	<div class="spark-aboutfirm__inner">
		<header class="spark-aboutfirm__header">
			<?php if ( '' !== trim( $eyebrow ) ) : ?><span class="spark-aboutfirm__eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></span><?php endif; ?>
			<div>
				<?php if ( '' !== trim( $title ) ) : ?><h2><?php echo wp_kses_post( $title ); ?></h2><?php endif; ?>
				<?php if ( '' !== trim( $lede ) ) : ?><p class="spark-aboutfirm__lede"><?php echo wp_kses_post( $lede ); ?></p><?php endif; ?>
			</div>
		</header>
		<?php if ( $companies ) : ?><div class="spark-aboutfirm__rows">
			<?php foreach ( $companies as $company ) : ?>
				<?php
				if ( ! is_array( $company ) ) { continue; }
				$brand = in_array( $company['brand'] ?? '', $brands, true ) ? $company['brand'] : 'custom';
				$color = 'custom' === $brand ? sanitize_hex_color( $company['color'] ?? '' ) : '';
				$url   = esc_url( $company['url'] ?? '' );
				?>
				<?php if ( $url ) : ?><a class="spark-aboutfirm__link" href="<?php echo esc_url( $url ); ?>"><?php else : ?><div class="spark-aboutfirm__link" aria-disabled="true"><?php endif; ?>
					<div class="spark-aboutfirm__row is-brand-<?php echo esc_attr( $brand ); ?>"<?php echo $color ? ' style="--aboutfirm-brand:' . esc_attr( $color ) . '"' : ''; ?>>
						<h3><span class="spark-aboutfirm__prefix"><?php echo wp_kses_post( $company['prefix'] ?? '' ); ?></span><span class="spark-aboutfirm__name"><?php echo wp_kses_post( $company['name'] ?? '' ); ?></span></h3>
						<span class="spark-aboutfirm__description"><?php echo wp_kses_post( $company['description'] ?? '' ); ?></span>
					</div>
				<?php if ( $url ) : ?></a><?php else : ?></div><?php endif; ?>
			<?php endforeach; ?>
		</div><?php endif; ?>
	</div>
</section>
