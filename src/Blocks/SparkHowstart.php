<?php
/** Registration for the Spark How Start block. @package DevTheme */
namespace Dev\Theme\Blocks;
defined( 'ABSPATH' ) || exit;

final class SparkHowstart {
	private string $directory;
	private string $uri;
	public function __construct( string $directory, string $uri ) {
		$this->directory = untrailingslashit( $directory );
		$this->uri = untrailingslashit( $uri );
	}
	public function register(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'register_styles' ), 20 );
	}
	public function register_block(): void { register_block_type( $this->directory . '/src/Blocks/SparkHowstart' ); }
	public function register_styles(): void {
		$relative_path = '/assets/css/blocks/spark-howstart.css';
		$absolute_path = $this->directory . $relative_path;
		if ( ! is_readable( $absolute_path ) ) { return; }
		$modified = filemtime( $absolute_path );
		wp_enqueue_block_style( 'sparkagency/howstart', array(
			'handle' => 'sparkagency-howstart-style',
			'src' => $this->uri . $relative_path,
			'ver' => false !== $modified ? (string) $modified : wp_get_theme()->get( 'Version' ),
		) );
	}
}
