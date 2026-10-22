<?php
namespace TeamSyncConnector;

use TeamSyncConnector\Core\Auth;
use TeamSyncConnector\Core\Config;
use TeamSyncConnector\Core\Importer;
use TeamSyncConnector\Core\Payload;
use TeamSyncConnector\Core\Queue;

defined( 'ABSPATH' ) || exit;

final class Rest {
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes(): void {
		register_rest_route(
			Config::$rest_ns,
			'/receive',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'receive' ),
				'permission_callback' => array( __CLASS__, 'auth' ),
			)
		);
		register_rest_route(
			Config::$rest_ns,
			'/ping',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'ping' ),
				'permission_callback' => array( __CLASS__, 'auth' ),
			)
		);
	}

	public static function auth( \WP_REST_Request $r ) {
		return Auth::verify( $r, array( Connection::class, 'secret_for' ) );
	}

	/** Hub przysyła nowy wpis. Import (z pobieraniem obrazów) idzie do kolejki. */
	public static function receive( \WP_REST_Request $r ) {
		$data  = $r->get_json_params();
		$p     = is_array( $data ) ? ( $data['payload'] ?? null ) : null;
		$valid = Payload::validate( $p );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}
		if ( empty( $p['force'] ) && Importer::find_by_uuid( $p['uuid'] ) ) {
			return new \WP_REST_Response( array( 'status' => 'exists' ), 200 );
		}
		if ( ! Queue::push( array( 'type' => 'import', 'payload' => $p ) ) ) {
			return new \WP_Error( 'team_sync_queue', 'Strona nie zapisała zadania, ponów.', array( 'status' => 503 ) );
		}
		Queue::kick();
		return new \WP_REST_Response( array( 'status' => 'queued' ), 202 );
	}

	public static function ping() {
		return array(
			'ok'       => true,
			'name'     => get_bloginfo( 'name' ),
			'endpoint' => rest_url( Config::$rest_ns . '/' ),
		);
	}
}
