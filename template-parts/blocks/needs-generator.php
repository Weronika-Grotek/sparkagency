<?php
/**
 * Front-end template for the Needs Generator block.
 *
 * @package SparkAgency
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$sanitize_identifier = static function ( $value, string $fallback ): string {
	$value = sanitize_key( (string) $value );
	return '' !== $value ? $value : $fallback;
};

$steps = array();
foreach ( (array) ( $attributes['steps'] ?? array() ) as $step_index => $step ) {
	$options = array();
	foreach ( (array) ( $step['options'] ?? array() ) as $option_index => $option ) {
		$label = sanitize_text_field( $option['label'] ?? '' );
		if ( '' === $label ) {
			continue;
		}
		$options[] = array(
			'label'     => $label,
			'value'     => sanitize_text_field( $option['value'] ?? $label ),
			'resultId'  => $sanitize_identifier( $option['resultId'] ?? '', '' ),
			'nextUrl'   => esc_url_raw( $option['nextUrl'] ?? '' ),
			'brand'     => $sanitize_identifier( $option['brand'] ?? '', 'agency' ),
			'position'  => $option_index,
		);
	}

	if ( empty( $options ) ) {
		continue;
	}

	$steps[] = array(
		'title'      => sanitize_text_field( $step['title'] ?? '' ),
		'param'      => $sanitize_identifier( $step['param'] ?? '', 'answer_' . ( $step_index + 1 ) ),
		'markerMode' => 'brand' === ( $step['markerMode'] ?? '' ) ? 'brand' : 'letters',
		'options'    => $options,
	);
}

$results = array();
foreach ( (array) ( $attributes['results'] ?? array() ) as $result_index => $result ) {
	$id = $sanitize_identifier( $result['id'] ?? '', 'result_' . ( $result_index + 1 ) );
	$results[] = array(
		'id'          => $id,
		'title'       => sanitize_text_field( $result['title'] ?? '' ),
		'description' => wp_strip_all_tags( $result['description'] ?? '' ),
		'contactUrl'  => esc_url_raw( $result['contactUrl'] ?? '' ),
		'imageUrl'    => esc_url_raw( $result['imageUrl'] ?? '' ),
		'imageAlt'    => sanitize_text_field( $result['imageAlt'] ?? '' ),
	);
}

$config = array(
	'steps'            => $steps,
	'results'          => $results,
	'finalUrl'         => esc_url_raw( $attributes['finalUrl'] ?? '' ),
	'finalButtonLabel' => sanitize_text_field( $attributes['finalButtonLabel'] ?? __( 'Przejdź dalej', 'sparkagency' ) ),
);
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-needs-generator' ) ) ); ?>>
	<div class="spark-needs-generator__header">
		<?php if ( ! empty( $attributes['eyebrow'] ) ) : ?><p class="spark-needs-generator__eyebrow"><?php echo esc_html( $attributes['eyebrow'] ); ?></p><?php endif; ?>
		<div>
			<?php if ( ! empty( $attributes['title'] ) ) : ?><h2><?php echo wp_kses_post( $attributes['title'] ); ?></h2><?php endif; ?>
			<?php if ( ! empty( $attributes['lede'] ) ) : ?><p class="spark-needs-generator__lede"><?php echo wp_kses_post( $attributes['lede'] ); ?></p><?php endif; ?>
		</div>
	</div>
	<?php if ( empty( $steps ) ) : ?>
		<p><?php esc_html_e( 'Generator wymaga co najmniej jednego pytania z odpowiedziami.', 'sparkagency' ); ?></p>
	<?php else : ?>
		<div class="spark-needs-generator__finder" data-spark-needs-generator data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<ol class="spark-needs-generator__steps" data-needs-steps></ol>
			<div class="spark-needs-generator__bar" aria-hidden="true"><span data-needs-progress></span></div>
			<div class="spark-needs-generator__stage" data-needs-stage aria-live="polite"></div>
			<div class="spark-needs-generator__footer"><button class="spark-needs-generator__back" type="button" data-needs-back><?php esc_html_e( '← Wstecz', 'sparkagency' ); ?></button><span data-needs-count></span></div>
		</div>
	<?php endif; ?>
</section>
