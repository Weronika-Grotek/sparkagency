<?php
/**
 * Front-end template for the Needs Generator block.
 *
 * Companies are reusable destinations. Answers in the first question point
 * to a company; the company controls the dot colour and final summary.
 *
 * @package SparkAgency
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
$old_titles = array( 'Dla której firmy szukasz wsparcia?', 'Jaka informacja jest dla Ciebie najważniejsza?', 'Jakiego zakresu potrzebujesz?' );
$new_titles = array( 'Co chcesz zrobić?', 'Skala zadania', 'Czego potrzebujesz?' );
foreach ( $titles as $index => $title ) {
	if ( $old_titles[ $index ] === $title ) $titles[ $index ] = $new_titles[ $index ];
}

// Migrate the previous company-first data structure when an old block renders.
$raw_companies = $items( $attributes['companies'] ?? array() );
$legacy_ids    = array();
if ( empty( $raw_companies ) ) {
	$results       = $items( $attributes['results'] ?? array() );
	$groups        = $items( $attributes['serviceGroups'] ?? array() );
	$first_options = $items( $steps[0]['options'] ?? array() );
	$find          = static function ( array $list, string $id ): array {
		foreach ( $list as $item ) {
			if ( '' !== $id && ( $item['id'] ?? '' ) === $id ) return $item;
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
			if ( '' !== $result_id ) $legacy_ids[ $result_id ] = $id;
			$raw_companies[] = array( 'id' => $id, 'label' => $option['companyLabel'] ?? $option['label'] ?? '', 'brand' => $option['brand'] ?? 'agency', 'description' => $result['description'] ?? '', 'imageUrl' => $result['imageUrl'] ?? '', 'imageAlt' => $result['imageAlt'] ?? '', 'url' => ( $result['contactUrl'] ?? '' ) ?: ( $option['nextUrl'] ?? '' ), 'scopes' => (array) ( $group['categories'] ?? array() ) );
		}
	} else {
		foreach ( $groups as $group ) {
			$raw_companies[] = array( 'id' => ( $group['id'] ?? '' ) ?: $slug( $group['title'] ?? '' ), 'label' => $group['title'] ?? '', 'brand' => $group['id'] ?? 'agency', 'scopes' => (array) ( $group['categories'] ?? array() ) );
		}
	}
}

$companies     = array();
$legacy_scopes = array();
foreach ( $raw_companies as $company ) {
	$label = $text( $company['label'] ?? '' );
	$id    = sanitize_key( (string) ( $company['id'] ?? '' ) ) ?: $slug( $label );
	if ( '' === $label || '' === $id ) continue;
	$brand = sanitize_key( (string) ( $company['brand'] ?? '' ) );
	$brand = in_array( $brand, $brands, true ) ? $brand : 'agency';
	$highlight_brand = sanitize_key( (string) ( $company['highlightBrand'] ?? '' ) );
	$companies[] = array(
		'id' => $id, 'label' => $label, 'brand' => $brand,
		'highlightText' => $text( $company['highlightText'] ?? '' ),
		'highlightBrand' => in_array( $highlight_brand, $brands, true ) ? $highlight_brand : $brand,
		'highlightBold' => ! isset( $company['highlightBold'] ) || (bool) $company['highlightBold'],
		'description' => sanitize_textarea_field( (string) ( $company['description'] ?? '' ) ),
		'imageUrl' => esc_url_raw( (string) ( $company['imageUrl'] ?? '' ) ), 'imageAlt' => $text( $company['imageAlt'] ?? '' ),
		'url' => esc_url_raw( (string) ( $company['url'] ?? '' ) ),
	);
	$legacy_scopes[ $id ] = (array) ( $company['scopes'] ?? array() );
}
$company_ids = wp_list_pluck( $companies, 'id' );

$actions = array();
$raw_actions = $items( $steps[0]['options'] ?? array() );
if ( empty( $raw_actions ) ) {
	foreach ( $companies as $company ) $actions[] = array( 'label' => $company['label'], 'value' => '', 'companyId' => $company['id'] );
} else {
	foreach ( $raw_actions as $action ) {
		$label = $text( $action['actionLabel'] ?? $action['label'] ?? '' );
		if ( '' === $label ) continue;
		$company_id = sanitize_key( (string) ( $action['companyId'] ?? $action['serviceGroupId'] ?? $action['resultId'] ?? '' ) );
		$actions[] = array( 'label' => $label, 'value' => $text( $action['value'] ?? '' ), 'companyId' => in_array( $company_id, $company_ids, true ) ? $company_id : '' );
	}
}

$question_options = static function ( $source ) use ( $items, $text, $legacy_ids ): array {
	$options = array();
	foreach ( $items( $source ) as $option ) {
		$label = $text( $option['label'] ?? '' );
		if ( '' === $label ) continue;
		$visible_for = sanitize_key( (string) ( $option['visibleFor'] ?? $option['visibleForCompany'] ?? '' ) );
		$legacy_filter = sanitize_key( (string) ( $option['visibleForResultId'] ?? '' ) );
		if ( '' === $visible_for && '' !== $legacy_filter ) $visible_for = $legacy_ids[ $legacy_filter ] ?? $legacy_filter;
		$options[] = array( 'label' => $label, 'value' => $text( $option['value'] ?? '' ) ?: $label, 'visibleFor' => $visible_for );
	}
	return $options;
};
$information = $question_options( $steps[1]['options'] ?? array() );
$scope       = $question_options( $steps[2]['options'] ?? array() );
if ( empty( $scope ) ) {
	foreach ( $legacy_scopes as $company_id => $company_scopes ) {
		foreach ( $company_scopes as $item ) {
			$label = $text( is_array( $item ) ? ( $item['label'] ?? '' ) : $item );
			if ( '' !== $label ) $scope[] = array( 'label' => $label, 'value' => $text( is_array( $item ) ? ( $item['value'] ?? '' ) : '' ) ?: $label, 'visibleFor' => $company_id );
		}
	}
}

$config = array(
	'titles' => $titles, 'companies' => $companies, 'actions' => $actions, 'information' => $information, 'scope' => $scope,
	'finalUrl' => esc_url_raw( (string) ( $attributes['finalUrl'] ?? '' ) ),
	'finalButtonLabel' => $text( $attributes['finalButtonLabel'] ?? '' ) ?: __( 'Przejdź dalej', 'sparkagency' ),
);
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'spark-needs-generator' ) ) ); ?>>
	<div class="spark-needs-generator__header">
		<?php if ( ! empty( $attributes['eyebrow'] ) ) : ?><p class="spark-needs-generator__eyebrow"><?php echo esc_html( $attributes['eyebrow'] ); ?></p><?php endif; ?>
		<div><?php if ( ! empty( $attributes['title'] ) ) : ?><h2><?php echo wp_kses_post( $attributes['title'] ); ?></h2><?php endif; ?><?php if ( ! empty( $attributes['lede'] ) ) : ?><p class="spark-needs-generator__lede"><?php echo wp_kses_post( $attributes['lede'] ); ?></p><?php endif; ?></div>
	</div>
	<?php if ( empty( $actions ) ) : ?>
		<p><?php esc_html_e( 'Generator wymaga co najmniej jednej odpowiedzi w kroku 1.', 'sparkagency' ); ?></p>
	<?php else : ?>
		<div class="spark-needs-generator__finder" data-spark-needs-generator data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
		<ol class="spark-needs-generator__steps" data-needs-steps></ol><div class="spark-needs-generator__bar" aria-hidden="true"><span data-needs-progress></span></div><div class="spark-needs-generator__stage" data-needs-stage aria-live="polite"></div><div class="spark-needs-generator__footer"><button class="spark-needs-generator__back" type="button" data-needs-back hidden><?php esc_html_e( '← Wstecz', 'sparkagency' ); ?></button><span data-needs-count></span></div>
		</div>
	<?php endif; ?>
</section>
