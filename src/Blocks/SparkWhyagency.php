<?php
/**
 * Registration for the Spark Why Agency block.
 *
 * @package DevTheme
 * @author dev.
 */

namespace Dev\Theme\Blocks;

defined( 'ABSPATH' ) || exit;

final class SparkWhyagency {

	/** @var string */
	private string $directory;

	/** @var string */
	private string $uri;

	/**
	 * @param string $directory Absolute theme directory.
	 * @param string $uri Theme directory URL.
	 */
	public function __construct( string $directory, string $uri ) {
		$this->directory = untrailingslashit( $directory );
		$this->uri       = untrailingslashit( $uri );
	}

	/** Register the block and its stylesheet. */
	public function register(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'register_styles' ), 20 );
	}

	/** Register block metadata. */
	public function register_block(): void {
		register_block_type( $this->directory . '/src/Blocks/SparkWhyagency' );
	}

	/** Register the shared front-end and editor stylesheet. */
	public function register_styles(): void {
		$relative_path = '/assets/css/blocks/spark-whyagency.css';
		$absolute_path = $this->directory . $relative_path;

		if ( ! is_readable( $absolute_path ) ) {
			return;
		}

		$modified = filemtime( $absolute_path );
		$version  = false !== $modified ? (string) $modified : wp_get_theme()->get( 'Version' );

		wp_enqueue_block_style(
			'sparkagency/whyagency',
			array(
				'handle' => 'sparkagency-whyagency-style',
				'src'    => $this->uri . $relative_path,
				'ver'    => $version,
			)
		);
	}
}
