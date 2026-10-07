<?php
/**
 * Front-end theme styles.
 *
 * @package DevTheme
 * @author dev.
 */

namespace Dev\Theme;

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
			'dev-theme-main',
			$this->uri . $relative_path,
			array(),
			$version
		);
	}
}
