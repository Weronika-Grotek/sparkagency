<?php
/**
 * Team section. The sparkAGENCY section also lists people from team blocks
 * on published pages of the other companies.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

// Order and labels must match src/blocks/team/index.jsx.
$brands = array(
	'agency' => 'sparkAGENCY',
	'health' => 'sparkHEALTH',
	'exp'    => 'sparkEXP',
	'warsaw' => 'sparkWARSAW',
	'works'  => 'sparkWORKS',
	'studio' => 'sparkSTUDIO',
);

$text        = static function ( $value ): string {
	return is_string( $value ) ? trim( $value ) : '';
};
$valid_brand = static function ( $brand, string $fallback ) use ( $brands ): string {
	return is_string( $brand ) && isset( $brands[ $brand ] ) ? $brand : $fallback;
};
// A person working for several companies is matched by name.
$person_key = static function ( string $name ): string {
	return mb_strtolower( (string) preg_replace( '/\s+/u', ' ', trim( $name ) ) );
};
$resolve    = static function ( $person, string $source_brand ) use ( $text, $valid_brand, $person_key ): array {
	$person = is_array( $person ) ? $person : array();
	$name   = $text( $person['name'] ?? '' );
	return array(
		'key'   => $person_key( $name ),
		'name'  => $name,
		'brand' => $valid_brand( $person['brand'] ?? '', $source_brand ),
		'role'  => $text( $person['role'] ?? '' ),
		'bio'   => $text( $person['bio'] ?? '' ),
		'image_id'  => absint( $person['imageId'] ?? 0 ),
		'image_url' => esc_url_raw( $text( $person['imageUrl'] ?? '' ) ),
		'image_alt' => $text( $person['imageAlt'] ?? '' ),
	);
};
$initials   = static function ( string $name ): string {
	$words = preg_split( '/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! $words ) {
		return '-';
	}
	$letters = '';
	foreach ( array_slice( $words, 0, 2 ) as $word ) {
		$letters .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
	}
	return $letters;
};

$section_brand = $valid_brand( $attributes['brand'] ?? '', 'agency' );
$eyebrow       = $text( $attributes['eyebrow'] ?? '' );
$title         = $text( $attributes['title'] ?? '' );
$lede          = $text( $attributes['lede'] ?? '' );
$overrides     = is_array( $attributes['overrides'] ?? null ) ? $attributes['overrides'] : array();
$people        = array();

foreach ( is_array( $attributes['people'] ?? null ) ? $attributes['people'] : array() as $person ) {
	$item = $resolve( $person, $section_brand );
	if ( '' === $item['key'] ) {
		continue;
	}
	if ( ! isset( $people[ $item['key'] ] ) ) {
		$people[ $item['key'] ] = $item;
	}
}

if ( 'agency' === $section_brand ) {
	$collect = static function ( array $blocks ) use ( &$collect, &$people, $resolve, $valid_brand ): void {
		foreach ( $blocks as $inner ) {
			if ( 'sparkagency/team' === ( $inner['blockName'] ?? '' ) ) {
				$source_brand = $valid_brand( $inner['attrs']['brand'] ?? '', 'agency' );
				if ( 'agency' !== $source_brand && is_array( $inner['attrs']['people'] ?? null ) ) {
					foreach ( $inner['attrs']['people'] as $person ) {
						$item = $resolve( $person, $source_brand );
						if ( '' !== $item['key'] && ! isset( $people[ $item['key'] ] ) ) {
							$people[ $item['key'] ] = $item;
						}
					}
				}
			}
			$collect( $inner['innerBlocks'] ?? array() );
		}
	};

	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'suppress_filters' => false,
		)
	);
	foreach ( $pages as $page ) {
		if ( has_block( 'sparkagency/team', $page ) ) {
			$collect( parse_blocks( $page->post_content ) );
		}
	}
}

$cards = array();
foreach ( $people as $key => $person ) {
	$override = is_array( $overrides[ $key ] ?? null ) ? $overrides[ $key ] : array();
	if ( ! empty( $override['hidden'] ) ) {
		continue;
	}
	$role    = $text( $override['role'] ?? '' );
	$bio     = $text( $override['bio'] ?? '' );
	$image_id  = absint( $override['imageId'] ?? $person['image_id'] );
	$image_url = esc_url_raw( $text( $override['imageUrl'] ?? '' ) );
	$image_alt = $text( $override['imageAlt'] ?? '' );
	$cards[] = array(
		'name'  => $person['name'],
		'brand' => $valid_brand( $override['brand'] ?? '', $person['brand'] ),
		'role'  => '' !== $role ? $role : $person['role'],
		'bio'   => '' !== $bio ? $bio : $person['bio'],
		'image_id'  => $image_id,
		'image_url' => '' !== $image_url ? $image_url : $person['image_url'],
		'image_alt' => '' !== $image_alt ? $image_alt : $person['image_alt'],
	);
}

if ( ! $cards && '' === $title ) {
	return;
}

$heading_id = wp_unique_id( 'spark-team-title-' );
$wrapper    = array( 'class' => 'spark-team is-brand-' . $section_brand );
if ( '' !== $title ) {
	$wrapper['aria-labelledby'] = $heading_id;
}
if ( '' !== $text( $attributes['anchor'] ?? '' ) ) {
	$wrapper['id'] = $text( $attributes['anchor'] );
}
?>
<section <?php echo get_block_wrapper_attributes( $wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes wrapper attributes. ?>>
	<div class="spark-team__inner">
		<div class="spark-team__header">
			<span class="spark-team__label"><?php echo esc_html( $eyebrow ); ?></span>
			<div>
				<?php if ( '' !== $title ) : ?>
					<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $lede ) : ?>
					<p class="spark-team__lede"><?php echo esc_html( $lede ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $cards ) : ?>
			<div class="spark-team__grid">
				<?php foreach ( $cards as $card ) : ?>
					<article class="spark-team__person has-brand-<?php echo esc_attr( $card['brand'] ); ?>">
						<div class="spark-team__avatar<?php echo $card['image_id'] || $card['image_url'] ? ' has-image' : ''; ?>">
							<?php if ( $card['image_id'] ) : ?>
								<?php echo wp_get_attachment_image( $card['image_id'], 'medium_large', false, array( 'alt' => $card['image_alt'] ?: $card['name'], 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes image markup. ?>
							<?php elseif ( $card['image_url'] ) : ?>
								<img src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['image_alt'] ?: $card['name'] ); ?>" loading="lazy" />
							<?php else : ?>
								<span aria-hidden="true"><?php echo esc_html( $initials( $card['name'] ) ); ?></span>
							<?php endif; ?>
							<em aria-hidden="true"><?php echo esc_html( $brands[ $card['brand'] ] ); ?></em>
						</div>
						<div>
							<h3><?php echo esc_html( $card['name'] ); ?></h3>
							<?php if ( '' !== $card['role'] ) : ?>
								<p class="spark-team__role"><?php echo esc_html( $card['role'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $card['bio'] ) : ?>
								<p class="spark-team__bio"><?php echo esc_html( $card['bio'] ); ?></p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
