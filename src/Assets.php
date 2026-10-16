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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_case_study_fireworks' ), 25 );
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

	/**
	 * Reuse the Hero block's fireworks controller for a native single case
	 * study template. The script is registered automatically with the block.
	 */
	public function enqueue_case_study_fireworks(): void {
		if ( ! is_singular( array( 'case-study', 'case_study' ) ) ) {
			return;
		}

		wp_enqueue_script( 'sparkagency-hero-view-script' );
	}
}
