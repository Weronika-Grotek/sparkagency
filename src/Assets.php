<?php
/**
 * Front-end theme styles.
 *
 * @package SparkAgency
 */

namespace SparkAgency\Theme;

defined( 'ABSPATH' ) || exit;

final class Assets {

	/**
	 * Absolute path to the active theme.
	 *
	 * @var string
	 */
	private string $directory;

	/**
	 * Public URL of the active theme.
	 *
	 * @var string
	 */
	private string $uri;

	/**
	 * Set the asset locations.
	 *
	 * @param string $directory Absolute theme directory.
	 * @param string $uri       Theme directory URL.
	 */
	public function __construct( string $directory, string $uri ) {
		$this->directory = untrailingslashit( $directory );
		$this->uri       = untrailingslashit( $uri );
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_case_study_archive_assets' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_native_hero_animation' ), 25 );
	}

	/**
	 * Load the compiled stylesheet with modification-time cache busting.
	 */
	public function enqueue_styles(): void {
		$relative_path = '/assets/css/main.css';
		$absolute_path = $this->directory . $relative_path;

		if ( ! is_readable( $absolute_path ) ) {
			return;
		}

		$modified = filemtime( $absolute_path );
		$version  = false !== $modified ? (string) $modified : wp_get_theme()->get( 'Version' );

		wp_enqueue_style(
			'sparkagency-main',
			$this->uri . $relative_path,
			array(),
			$version
		);
	}

	/** Load archive-specific presentation and filters on the case study archive. */
	public function enqueue_case_study_archive_assets(): void {
		if ( ! is_post_type_archive( array( 'case-study', 'case_study' ) ) ) {
			return;
		}

		$style_path = '/assets/css/case-study-archive.css';
		$style_file = $this->directory . $style_path;
		if ( is_readable( $style_file ) ) {
			wp_enqueue_style( 'sparkagency-case-study-archive', $this->uri . $style_path, array( 'sparkagency-main' ), (string) filemtime( $style_file ) );
		}

		$script_path = '/assets/js/case-study-archive.js';
		$script_file = $this->directory . $script_path;
		if ( is_readable( $script_file ) ) {
			wp_enqueue_script( 'sparkagency-case-study-archive', $this->uri . $script_path, array(), (string) filemtime( $script_file ), true );
		}
	}

	/**
	 * Reuse the Hero block controller in native single templates.
	 *
	 * The script is registered automatically with the Hero block.
	 */
	public function enqueue_native_hero_animation(): void {
		if ( ! is_singular( array( 'case-study', 'case_study', 'career' ) ) ) {
			return;
		}

		$script = is_singular( 'career' ) ? 'sparkagency-hero-second-view-script' : 'sparkagency-hero-view-script';
		wp_enqueue_script( $script );
	}
}
