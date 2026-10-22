<?php
/**
 * Plugin Name:       Team Sync - Connector
 * Description:       Łączy stronę z hubem zespoły: wysyła nowe wpisy do huba i publikuje wpisy otrzymane z sieci.
 * Version:           1.1.1
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Text Domain:       team-sync-connector
 */

namespace TeamSyncConnector;

use TeamSyncConnector\Core\Config;
use TeamSyncConnector\Core\Queue;
use TeamSyncConnector\Core\Publisher;
use TeamSyncConnector\Core\PostUI;
use TeamSyncConnector\Core\Ui;
use TeamSyncConnector\Core\Bulk;
use TeamSyncConnector\Core\Local;

defined( 'ABSPATH' ) || exit;

define( 'TEAM_SYNC_CONNECTOR_FILE', __FILE__ );

spl_autoload_register(
	function ( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}
		$rel  = substr( $class, strlen( __NAMESPACE__ ) + 1 );
		$path = 0 === strpos( $rel, 'Core\\' )
			? __DIR__ . '/core/' . substr( $rel, 5 ) . '.php'
			: __DIR__ . '/includes/' . $rel . '.php';
		if ( is_file( $path ) ) {
			require $path;
		}
	}
);

Config::boot(
	array(
		'prefix'      => 'team_sync_connector',
		'rest_ns'     => 'team-sync/v1',
		'page'        => 'team-sync',
		'role'        => 'connector',
		'job_handler' => array( Jobs::class, 'handle' ),
	)
);

Local::init();
Queue::init();
Publisher::init();
Rest::init();

if ( is_admin() ) {
	Ui::init();
	PostUI::init();
	Bulk::init();
	Admin::init();
}
