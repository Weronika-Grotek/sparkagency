<?php
/**
 * Polityka prywatności block template.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

$headings = array();
$html     = $content;

if ( class_exists( 'DOMDocument' ) && class_exists( 'DOMXPath' ) && '' !== trim( $content ) ) {
	$previous = libxml_use_internal_errors( true );
	$document = new DOMDocument( '1.0', 'UTF-8' );
	$document->loadHTML( '<?xml encoding="utf-8" ?><div id="spark-privacy-content">' . $content . '</div>', LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED );
	$xpath    = new DOMXPath( $document );
	$nodes    = $xpath->query( '//*[@id="spark-privacy-content"]//h1 | //*[@id="spark-privacy-content"]//h2 | //*[@id="spark-privacy-content"]//h3 | //*[@id="spark-privacy-content"]//h4 | //*[@id="spark-privacy-content"]//h5 | //*[@id="spark-privacy-content"]//h6' );
	$used_ids = array();

	if ( false !== $nodes ) {
		foreach ( $nodes as $node ) {
			$title = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
			if ( '' === $title ) {
				continue;
			}

			$base = sanitize_title( remove_accents( $title ) );
			if ( '' === $base ) {
				$base = 'sekcja';
			}
			$count = isset( $used_ids[ $base ] ) ? $used_ids[ $base ] + 1 : 1;
			$used_ids[ $base ] = $count;
			$id = 1 === $count ? $base : $base . '-' . $count;
			$node->setAttribute( 'id', $id );
			$headings[] = array(
				'id'    => $id,
				'title' => $title,
			);
		}
	}

	$container = $document->getElementById( 'spark-privacy-content' );
	if ( $container ) {
		$html = '';
		foreach ( $container->childNodes as $child ) {
			$html .= $document->saveHTML( $child );
		}
	}

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
}
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-privacy-policy' ) ) ); ?> aria-label="<?php esc_attr_e( 'Polityka prywatności', 'sparkagency' ); ?>">
	<aside class="spark-privacy-policy__sidebar" aria-label="<?php esc_attr_e( 'Spis treści', 'sparkagency' ); ?>">
		<p class="spark-privacy-policy__label"><?php esc_html_e( 'Spis treści', 'sparkagency' ); ?></p>
		<nav>
			<ol class="spark-privacy-policy__toc">
				<?php foreach ( $headings as $index => $heading ) : ?>
					<li><a href="#<?php echo esc_attr( $heading['id'] ); ?>"><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?>.</span><?php echo esc_html( $heading['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
	</aside>
	<div class="spark-privacy-policy__content">
		<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered and sanitized by Gutenberg. ?>
	</div>
</section>
