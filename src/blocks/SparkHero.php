<?php
/**
 * Registration for the Spark hero block.
 *
 * @package DevTheme
 * @author dev.
 */

namespace Dev\Theme\Blocks;

defined( 'ABSPATH' ) || exit;

final class SparkHero {

	/**
	 * Absolute path to the theme directory.
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
	 * Set the theme directory.
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
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'register_styles' ), 20 );
	}

	/**
	 * Register the block from its metadata.
	 */
	public function register_block(): void {
		register_block_type( $this->directory . '/src/Blocks/SparkHero' );
	}

	/**
	 * Register the stylesheet for the front end and block editor.
	 */
	public function register_styles(): void {
		$relative_path = '/assets/css/blocks/spark-hero.css';
		$absolute_path = $this->directory . $relative_path;

		if ( ! is_readable( $absolute_path ) ) {
			return;
		}

		$modified = filemtime( $absolute_path );
		$version  = false !== $modified ? (string) $modified : wp_get_theme()->get( 'Version' );

		wp_enqueue_block_style(
			'sparkagency/hero',
			array(
				'handle' => 'sparkagency-hero-style',
				'src'    => $this->uri . $relative_path,
				'ver'    => $version,
			)
		);
	}
}