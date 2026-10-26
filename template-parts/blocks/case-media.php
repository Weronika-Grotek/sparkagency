<?php
/**
 * Case study media gallery block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

$eyebrow = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
$title   = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$lede    = isset( $attributes['lede'] ) ? (string) $attributes['lede'] : '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$ratios  = array( '21/9', '16/9', '4/3', '1/1', '3/4' );
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-case-media' ) ) ); ?>>
	<div class="spark-case-media__inner">
		<?php if ( $eyebrow || $title || $lede ) : ?>
			<header class="spark-case-media__heading">
				<div>
					<?php if ( $eyebrow ) : ?><p class="spark-case-media__eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
					<?php if ( $title ) : ?><h2 class="spark-case-media__title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
				</div>
				<?php if ( $lede ) : ?><p class="spark-case-media__lead"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
			</header>
		<?php endif; ?>
		<?php if ( $items ) : ?>
			<div class="spark-case-media__grid">
				<?php foreach ( $items as $item ) :
					$type        = isset( $item['type'] ) && 'video' === $item['type'] ? 'video' : 'image';
					$url         = isset( $item['url'] ) ? esc_url( $item['url'] ) : '';
					$caption     = isset( $item['caption'] ) ? (string) $item['caption'] : '';
					$ratio       = isset( $item['aspectRatio'] ) && in_array( $item['aspectRatio'], $ratios, true ) ? $item['aspectRatio'] : ( 'video' === $type ? '16/9' : '4/3' );
					$width       = isset( $item['width'] ) && '100' === $item['width'] ? '100' : '50';
					$media_type  = isset( $item['mediaType'] ) ? sanitize_mime_type( $item['mediaType'] ) : '';
					$poster      = isset( $item['posterUrl'] ) ? esc_url( $item['posterUrl'] ) : '';
					?>
					<?php if ( $url ) : ?>
						<figure class="spark-case-media__item<?php echo '100' === $width ? ' spark-case-media__item--full' : ''; ?><?php echo 'video' === $type ? ' spark-case-media__item--video' : ''; ?>" style="aspect-ratio: <?php echo esc_attr( str_replace( '/', ' / ', $ratio ) ); ?>">
							<?php if ( 'video' === $type ) : ?>
								<video controls playsinline preload="<?php echo $poster ? 'metadata' : 'auto'; ?>"<?php if ( $poster ) : ?> poster="<?php echo esc_url( $poster ); ?>"<?php else : ?> autoplay muted loop<?php endif; ?>>
									<source src="<?php echo esc_url( $url ); ?>"<?php if ( $media_type ) : ?> type="<?php echo esc_attr( $media_type ); ?>"<?php endif; ?>>
								</video>
							<?php else : ?>
								<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( isset( $item['alt'] ) ? $item['alt'] : '' ); ?>" loading="lazy" decoding="async">
							<?php endif; ?>
							<?php if ( $caption ) : ?><figcaption class="spark-case-media__caption"><?php echo esc_html( $caption ); ?></figcaption><?php endif; ?>
						</figure>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
