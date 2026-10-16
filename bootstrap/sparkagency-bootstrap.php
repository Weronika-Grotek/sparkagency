<?php
/**
 * Bootstrap the standalone Spark Agency theme.
 *
 * @package SparkAgency
 */

defined( 'ABSPATH' ) || exit;

$sparkagency_directory  = dirname( __DIR__ );
$sparkagency_autoloader = $sparkagency_directory . '/vendor/autoload.php';

if ( ! is_readable( $sparkagency_autoloader ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Theme autoloader is missing. Run composer install in the active Spark Agency theme directory.', 'sparkagency' ) . '</p></div>';
			}
		}
	);
	return;
}

require_once $sparkagency_autoloader;

$sparkagency_uri = get_template_directory_uri();
$sparkagency_components = array(
	new \SparkAgency\Theme\Assets( $sparkagency_directory, $sparkagency_uri ),
	new \SparkAgency\Theme\BlocksRegister( $sparkagency_directory, $sparkagency_uri ),
	new \SparkAgency\Theme\ThemeSettings(),
);

foreach ( $sparkagency_components as $sparkagency_component ) {
	$sparkagency_component->register();
}

unset( $sparkagency_directory, $sparkagency_autoloader, $sparkagency_uri, $sparkagency_components, $sparkagency_component );
