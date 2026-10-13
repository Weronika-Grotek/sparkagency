<?php
/**
 * Registers compiled blocks found in build/blocks.
 *
 * @package SparkAgency
 */

declare( strict_types=1 );

namespace SparkAgency\Theme;

defined( 'ABSPATH' ) || exit;

final class BlocksRegister {
	private string $directory;
	private string $uri;

	public function __construct( string $directory, string $uri ) {
		$this->directory = untrailingslashit( $directory );
		$this->uri       = untrailingslashit( $uri );
	}

	/** Register the discovery hook. */
	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/** Register metadata and styles for each first-party block. */
	public function register_blocks(): void {
		$metadata_files = glob( $this->directory . '/build/blocks/*/block.json' );

		if ( false === $metadata_files ) {
			return;
		}

		foreach ( $metadata_files as $metadata_file ) {
			$block_directory = dirname( $metadata_file );
			$slug            = basename( $block_directory );

			$template = $this->directory . '/template-parts/blocks/' . $slug . '.php';
			$args     = is_readable( $template )
				? array( 'render_callback' => array( $this, 'render_block' ) )
				: array();

			register_block_type( $block_directory, $args );

			$this->register_block_style( $slug );
		}
	}

	/**
	 * Render a block through its matching template part.
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Saved block content.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public function render_block( array $attributes, string $content, \WP_Block $block ): string {
		$parts    = explode( '/', $block->name );
		$slug     = end( $parts );
		$template = $this->directory . '/template-parts/blocks/' . $slug . '.php';

		if ( ! is_readable( $template ) ) {
			return '';
		}

		ob_start();
		require $template;
		return (string) ob_get_clean();
	}

	/** Register a block stylesheet when the source contains one. */
	private function register_block_style( string $slug ): void {
		$relative_path = '/assets/css/blocks/spark-' . $slug . '.css';
		$absolute_path = $this->directory . $relative_path;

		if ( ! is_readable( $absolute_path ) ) {
			return;
		}

		$modified = filemtime( $absolute_path );
		$version  = false !== $modified ? (string) $modified : wp_get_theme()->get( 'Version' );

		wp_enqueue_block_style(
			'sparkagency/' . $slug,
			array(
				'handle' => 'sparkagency-' . $slug . '-style',
				'src'    => $this->uri . $relative_path,
				'ver'    => $version,
			)
		);
	}
}
