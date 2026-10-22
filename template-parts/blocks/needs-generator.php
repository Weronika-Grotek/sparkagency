<?php
/**
 * Front-end template for the Needs Generator block.
 *
 * Step 1 picks a company, step 2 is a shared informational question and
 * step 3 lists the scopes of the chosen company. The migration of the older
 * attribute model mirrors readModel() in src/blocks/needs-generator/model.js.
 *
 * @package SparkAgency
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$brands = array( 'agency', 'exp', 'warsaw', 'health', 'works', 'studio' );
$text   = static fn( $value ): string => is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
$items  = static fn( $value ): array => is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
$slug   = static fn( $value ): string => sanitize_key( sanitize_title( (string) $value ) );

$steps  = $items( $attributes['steps'] ?? array() );
$titles = array();
foreach ( array( 0, 1, 2 ) as $index ) {
	$titles[] = $text( $steps[ $index ]['title'] ?? '' );
}

$scope_data = static function ( $scope ) use ( $text ): array {
	return is_array( $scope )
		? array( 'label' => $text( $scope['label'] ?? '' ), 'value' => $text( $scope['value'] ?? '' ) )
		: array( 'label' => $text( $scope ), 'value' => '' );
};

$raw_companies = $items( $attributes['companies'] ?? array() );
$legacy_ids    = array();
if ( empty( $raw_companies ) ) {
	$results       = $items( $attributes['results'] ?? array() );
	$groups        = $items( $attributes['serviceGroups'] ?? array() );
	$first_options = $items( $steps[0]['options'] ?? array() );
	$find          = static function ( array $list, string $id ): array {
		foreach ( $list as $item ) {
			if ( '' !== $id && ( $item['id'] ?? '' ) === $id ) {
				return $item;
			}
		}
		return array();
	};

	if ( ! empty( $first_options ) ) {
		foreach ( $first_options as $option ) {
			$result_id = (string) ( $option['resultId'] ?? '' );
			$group_id  = (string) ( $option['serviceGroupId'] ?? '' );
			$id        = $group_id ?: ( $result_id ?: $slug( $option['label'] ?? '' ) );
			$result    = $find( $results, $result_id );
			$group     = $find( $groups, $group_id ?: $result_id );
			if ( '' !== $result_id ) {
				$legacy_ids[ $result_id ] = $id;
			}
			$raw_companies[] = array(
				'id'          => $id,
				'label'       => $option['label'] ?? '',
				'brand'       => $option['brand'] ?? 'agency',
				'description' => $result['description'] ?? '',
				'imageUrl'    => $result['imageUrl'] ?? '',
				'imageAlt'    => $result['imageAlt'] ?? '',
				'url'         => ( $result['contactUrl'] ?? '' ) ?: ( $option['nextUrl'] ?? '' ),
				'scopes'      => (array) ( $group['categories'] ?? array() ),
			);
		}
	} else {
		foreach ( $groups as $group ) {
			$raw_companies[] = array(
				'id'     => ( $group['id'] ?? '' ) ?: $slug( $group['title'] ?? '' ),
				'label'  => $group['title'] ?? '',
				'brand'  => $group['id'] ?? 'agency',
				'scopes' => (array) ( $group['categories'] ?? array() ),
			);
		}
	}
}

$companies = array();
foreach ( $raw_companies as $company ) {
	$label = $text( $company['label'] ?? '' );
	$id    = sanitize_key( (string) ( $company['id'] ?? '' ) ) ?: $slug( $label );
	if ( '' === $label || '' === $id ) {
		continue;
	}
	$scopes = array();
	foreach ( (array) ( $company['scopes'] ?? array() ) as $scope ) {
		$scope = $scope_data( $scope );
		if ( '' === $scope['label'] ) {
			continue;
		}
		$scope['value'] = '' !== $scope['value'] ? $scope['value'] : $scope['label'];
		$scopes[]       = $scope;
	}
	$brand       = sanitize_key( (string) ( $company['brand'] ?? '' ) );
	$companies[] = array(
		'id'          => $id,
		'label'       => $label,
		'brand'       => in_array( $brand, $brands, true ) ? $brand : 'agency',
		'description' => sanitize_textarea_field( (string) ( $company['description'] ?? '' ) ),
		'imageUrl'    => esc_url_raw( (string) ( $company['imageUrl'] ?? '' ) ),
		'imageAlt'    => $text( $company['imageAlt'] ?? '' ),
		'url'         => esc_url_raw( (string) ( $company['url'] ?? '' ) ),
		'scopes'      => $scopes,
	);
}

$information = array();
foreach ( $items( $steps[1]['options'] ?? array() ) as $option ) {
	$label = $text( $option['label'] ?? '' );
	if ( '' === $label ) {
		continue;
	}
	$visible_for   = sanitize_key( (string) ( $option['visibleFor'] ?? '' ) );
	$legacy_filter = sanitize_key( (string) ( $option['visibleForResultId'] ?? '' ) );
	if ( '' === $visible_for && '' !== $legacy_filter ) {
		$visible_for = $legacy_ids[ $legacy_filter ] ?? $legacy_filter;
	}
	$value         = $text( $option['value'] ?? '' );
	$information[] = array(
		'label'      => $label,
		'value'      => '' !== $value ? $value : $label,
		'visibleFor' => $visible_for,
	);
}

$config = array(
	'titles'           => $titles,
	'companies'        => $companies,
	'information'      => $information,
	'finalUrl'         => esc_url_raw( (string) ( $attributes['finalUrl'] ?? '' ) ),
	'finalButtonLabel' => $text( $attributes['finalButtonLabel'] ?? '' ) ?: __( 'Przejdź dalej', 'sparkagency' ),
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
	<?php if ( empty( $companies ) ) : ?>
		<p><?php esc_html_e( 'Generator wymaga co najmniej jednej firmy.', 'sparkagency' ); ?></p>
	<?php else : ?>
		<div class="spark-needs-generator__finder" data-spark-needs-generator data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<ol class="spark-needs-generator__steps" data-needs-steps></ol>
			<div class="spark-needs-generator__bar" aria-hidden="true"><span data-needs-progress></span></div>
			<div class="spark-needs-generator__stage" data-needs-stage aria-live="polite"></div>
			<div class="spark-needs-generator__footer"><button class="spark-needs-generator__back" type="button" data-needs-back><?php esc_html_e( '← Wstecz', 'sparkagency' ); ?></button><span data-needs-count></span></div>
		</div>
	<?php endif; ?>
</section>
