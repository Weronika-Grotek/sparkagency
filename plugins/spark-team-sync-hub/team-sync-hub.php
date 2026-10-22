<?php
/**
 * Plugin Name:       Team Sync - Hub
 * Description:       Centralny hub sieci zespoły. Przyjmuje wpisy z connectorów i rozsyła je do stron przypisanych do marek.
 * Version:           1.1.1
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Text Domain:       team-sync-hub
 */

namespace TeamSyncHub;

use TeamSyncHub\Core\Config;
use TeamSyncHub\Core\Queue;
use TeamSyncHub\Core\Publisher;
use TeamSyncHub\Core\PostUI;
use TeamSyncHub\Core\Ui;
use TeamSyncHub\Core\Bulk;
use TeamSyncHub\Core\Local;

defined( 'ABSPATH' ) || exit;

define( 'TEAM_SYNC_HUB_FILE', __FILE__ );

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
		'prefix'      => 'team_sync_hub',
		'rest_ns'     => 'team-sync-hub/v1',
		'page'        => 'team-sync-hub',
		'role'        => 'hub',
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
	TermUI::init();
}

add_action(
	'admin_notices',
	function () {
		if ( defined( 'TEAM_SYNC_CONNECTOR_FILE' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>Team Sync: Hub i Connector są aktywne na tej samej stronie. Zostaw tylko jeden.</p></div>';
		}
	}
);
