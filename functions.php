<?php
/**
 *
 * @author dev
 * @package SparkAgency
 */

require_once get_template_directory() . '/bootstrap/sparkagency-bootstrap.php';

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

function my_custom_menu_setup() {
	register_nav_menus(
		array(
			'primary-menu'            => __( 'Primary Menu', 'sparkagency' ),
			'footer-menu'             => __( 'Footer Menu', 'sparkagency' ),
			'footer-information-menu' => __( 'Footer Information Menu', 'sparkagency' ),

		)
	);
}
add_action( 'after_setup_theme', 'my_custom_menu_setup' );

/**
 * Rejestruje natywnie typ wpisu Case Study.
 *
 * Definicja zastępuje konfigurację z wtyczki Custom Post Type UI.
 */
function sparkagency_register_case_study_post_type() {
	if ( post_type_exists( 'case-study' ) ) {
		return;
	}

	$labels = array(
		'name'                  => __( 'Case studies', 'sparkagency' ),
		'singular_name'         => __( 'Case study', 'sparkagency' ),
		'menu_name'             => __( 'Case studies', 'sparkagency' ),
		'name_admin_bar'        => __( 'Case study', 'sparkagency' ),
		'add_new'               => __( 'Dodaj nowe', 'sparkagency' ),
		'add_new_item'          => __( 'Dodaj case study', 'sparkagency' ),
		'new_item'              => __( 'Nowe case study', 'sparkagency' ),
		'edit_item'             => __( 'Edytuj case study', 'sparkagency' ),
		'view_item'             => __( 'Zobacz case study', 'sparkagency' ),
		'all_items'             => __( 'Wszystkie case studies', 'sparkagency' ),
		'search_items'          => __( 'Szukaj case studies', 'sparkagency' ),
		'not_found'             => __( 'Nie znaleziono case studies.', 'sparkagency' ),
		'not_found_in_trash'    => __( 'Nie znaleziono case studies w koszu.', 'sparkagency' ),
		'featured_image'        => __( 'Obrazek wyróżniający', 'sparkagency' ),
		'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'sparkagency' ),
		'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'sparkagency' ),
		'use_featured_image'    => __( 'Użyj jako obrazka wyróżniającego', 'sparkagency' ),
		'archives'              => __( 'Archiwum case studies', 'sparkagency' ),
	);

	register_post_type(
		'case-study',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'case-study',
				'with_front' => false,
			),
			'query_var'          => true,
			'hierarchical'       => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'menu_icon'          => 'dashicons-portfolio',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
		)
	);
}
add_action( 'init', 'sparkagency_register_case_study_post_type' );

/**
 * Registers team content and the brand taxonomy used to route it in Team Sync.
 */
function sparkagency_register_team_post_type() {
	if ( ! post_type_exists( 'team' ) ) {
		$labels = array(
			'name'                  => __( 'Zespoły', 'sparkagency' ),
			'singular_name'         => __( 'Zespół', 'sparkagency' ),
			'menu_name'             => __( 'Zespoły', 'sparkagency' ),
			'name_admin_bar'        => __( 'Zespół', 'sparkagency' ),
			'add_new'               => __( 'Dodaj nowy', 'sparkagency' ),
			'add_new_item'          => __( 'Dodaj zespół', 'sparkagency' ),
			'new_item'              => __( 'Nowy zespół', 'sparkagency' ),
			'edit_item'             => __( 'Edytuj zespół', 'sparkagency' ),
			'view_item'             => __( 'Zobacz zespół', 'sparkagency' ),
			'all_items'             => __( 'Wszystkie zespoły', 'sparkagency' ),
			'search_items'          => __( 'Szukaj zespołów', 'sparkagency' ),
			'not_found'             => __( 'Nie znaleziono zespołów.', 'sparkagency' ),
			'not_found_in_trash'    => __( 'Nie znaleziono zespołów w koszu.', 'sparkagency' ),
			'featured_image'        => __( 'Obrazek wyróżniający', 'sparkagency' ),
			'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'sparkagency' ),
			'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'sparkagency' ),
			'use_featured_image'    => __( 'Użyj jako obrazka wyróżniającego', 'sparkagency' ),
			'archives'              => __( 'Archiwum zespołów', 'sparkagency' ),
		);

		register_post_type(
			'team',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_admin_bar'  => true,
				'show_in_rest'       => true,
				'has_archive'        => true,
				'rewrite'            => array(
					'slug'       => 'team',
					'with_front' => false,
				),
				'query_var'          => true,
				'hierarchical'       => false,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				'menu_icon'          => 'dashicons-groups',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
			)
		);
	}

	if ( ! taxonomy_exists( 'team-brand' ) ) {
		register_taxonomy(
			'team-brand',
			array( 'team' ),
			array(
				'labels' => array(
					'name'          => __( 'Marki zespołu', 'sparkagency' ),
					'singular_name' => __( 'Marka zespołu', 'sparkagency' ),
					'menu_name'     => __( 'Marki zespołu', 'sparkagency' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'hierarchical' => false,
				'rewrite'      => array(
					'slug'       => 'team-brand',
					'with_front' => false,
				),
			)
		);
	}
}
add_action( 'init', 'sparkagency_register_team_post_type', 5 );

/**
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
