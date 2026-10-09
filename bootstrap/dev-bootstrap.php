<?php
/**
 * Initialize theme classes through Composer.
 *
 * @package DevTheme
 * @author dev.
 */

defined( 'ABSPATH' ) || exit;

$dev_theme_directory = dirname( __DIR__ );
$dev_theme_autoloader = $dev_theme_directory . '/vendor/autoload.php';

if ( ! is_readable( $dev_theme_autoloader ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'edit_theme_options' ) ) {
				return;
			}

			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Theme autoloader is missing. Run composer install in the active theme directory.', 'dev-theme-tooling' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $dev_theme_autoloader;

$dev_theme_assets = new \Dev\Theme\Assets(
	$dev_theme_directory,
	get_stylesheet_directory_uri()
);
$dev_theme_assets->register();

$dev_theme_spark_hero = new \Dev\Theme\Blocks\SparkHero(
	$dev_theme_directory,
	get_stylesheet_directory_uri()
);
$dev_theme_spark_hero->register();

$dev_theme_spark_slider = new \Dev\Theme\Blocks\SparkSlider(
	$dev_theme_directory,
	get_stylesheet_directory_uri()
);
$dev_theme_spark_slider->register();

$dev_theme_spark_group = new \Dev\Theme\Blocks\SparkGroup(
	$dev_theme_directory,
	get_stylesheet_directory_uri()
);
$dev_theme_spark_group->register();

$dev_theme_spark_oneteam = new \Dev\Theme\Blocks\SparkOneteam(
	$dev_theme_directory,
	get_stylesheet_directory_uri()
);
$dev_theme_spark_oneteam->register();

unset( $dev_theme_directory, $dev_theme_autoloader, $dev_theme_assets, $dev_theme_spark_hero, $dev_theme_spark_slider, $dev_theme_spark_group, $dev_theme_spark_oneteam );
