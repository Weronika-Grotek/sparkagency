<?php
require_once get_stylesheet_directory() . '/bootstrap/dev-bootstrap.php';

/**
 * Configure core WordPress features used by the theme shell.
 */
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


function my_custom_menu_setup() {
    register_nav_menus( array(
        'primary-menu' => __( 'Primary Menu', 'textdomain' ),
        'footer-menu'  => __( 'Footer Menu', 'textdomain' ),
    ) );
}
add_action( 'after_setup_theme', 'my_custom_menu_setup' );
