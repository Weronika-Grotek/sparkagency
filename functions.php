<?php
/**
 *
 * @author dev
 * @package SparkAgency
 */

require_once get_stylesheet_directory() . '/bootstrap/dev-bootstrap.php';

/** Configure WordPress features used by the theme. */
function sparkagency_theme_setup() {
	add_theme_support( 'title-tag' );
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
			'primary-menu' => __( 'Primary Menu', 'dev-theme-tooling' ),
			'footer-menu'  => __( 'Footer Menu', 'dev-theme-tooling' ),
		)
	);
}
add_action( 'after_setup_theme', 'my_custom_menu_setup' );

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
	$script_path = '/assets/js/header.js';
	if ( is_file( get_theme_file_path( $script_path ) ) ) {
		wp_enqueue_script(
			'spark-header',
			get_theme_file_uri( $script_path ),
			array(),
			(string) filemtime( get_theme_file_path( $script_path ) ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sparkagency_enqueue_header_assets', 20 );
