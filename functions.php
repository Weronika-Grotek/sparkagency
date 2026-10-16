<?php
/**
 *
 * @author dev
 * @package SparkAgency
 */

require_once get_template_directory() . '/bootstrap/sparkagency-bootstrap.php';

/** Configure WordPress features used by the theme. */
function sparkagency_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
}
add_action( 'after_setup_theme', 'sparkagency_theme_setup' );

/** Preserve the existing menu locations and their assignments. */
function my_custom_menu_setup() {
	register_nav_menus(
		array(
			'primary-menu' => __( 'Primary Menu', 'sparkagency' ),
			'footer-menu'  => __( 'Footer Menu', 'sparkagency' ),
		)
	);
}
add_action( 'after_setup_theme', 'my_custom_menu_setup' );

/**
 * Style the Kontakt item as the primary action without duplicating the
 * WordPress-managed menu in the template.
 *
 * @param string[] $classes Existing menu-item classes.
 * @param object   $item    Current menu item.
 * @param stdClass $args    Arguments passed to wp_nav_menu().
 * @param int      $depth   Current menu depth.
 * @return string[]
 */
function sparkagency_primary_menu_contact_cta( $classes, $item, $args, $depth ) {
	if ( 0 !== $depth || empty( $args->theme_location ) || 'primary-menu' !== $args->theme_location ) {
		return $classes;
	}

	$label = sanitize_title( wp_strip_all_tags( $item->title ) );
	$path  = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );

	if ( in_array( $label, array( 'kontakt', 'contact' ), true ) || in_array( $path, array( 'kontakt', 'contact' ), true ) ) {
		$classes[] = 'menu-item-cta';
	}

	return array_unique( $classes );
}
add_filter( 'nav_menu_css_class', 'sparkagency_primary_menu_contact_cta', 10, 4 );

/** Load header assets; file modification times invalidate browser caches. */
function sparkagency_enqueue_header_assets() {
	$assets = array(
		'spark-header-tokens' => '/assets/css/header-tokens.css',
		'spark-header'        => '/assets/css/header.css',
	);
	foreach ( $assets as $handle => $relative_path ) {
		$path = get_theme_file_path( $relative_path );
		if ( is_file( $path ) ) {
			wp_enqueue_style(
				$handle,
				get_theme_file_uri( $relative_path ),
				'spark-header' === $handle ? array( 'spark-header-tokens' ) : array(),
				(string) filemtime( $path )
			);
		}
	}
	wp_enqueue_style(
		'spark-header-font',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap',
		array(),
		null
	);
	$script_path = '/header.js';
	if ( is_file( get_theme_file_path( $script_path ) ) ) {
		wp_enqueue_script(
			'spark-header',
			get_theme_file_uri( $script_path ),
			array(),
			(string) filemtime( get_theme_file_path( $script_path ) ),
			true
		);
	}
	$preferences_script_path = '/assets/js/preferences.js';
	if ( is_file( get_theme_file_path( $preferences_script_path ) ) ) {
		wp_enqueue_script(
			'spark-preferences',
			get_theme_file_uri( $preferences_script_path ),
			array( 'spark-header' ),
			(string) filemtime( get_theme_file_path( $preferences_script_path ) ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sparkagency_enqueue_header_assets', 20 );
