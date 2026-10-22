<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Auth;
use TeamSyncHub\Core\Config;
use TeamSyncHub\Core\Logger;
use TeamSyncHub\Core\Payload;
use TeamSyncHub\Core\Queue;

defined( 'ABSPATH' ) || exit;

final class Rest {
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes(): void {
		register_rest_route(
			Config::$rest_ns,
			'/push',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'push' ),
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
		return Auth::verify( $r, array( Sites::class, 'secret_for' ) );
	}

	/** Connector zgłasza nowy wpis. Routing idzie do kolejki, odpowiadamy od razu. */
	public static function push( \WP_REST_Request $r ) {
		$site = sanitize_key( (string) $r->get_header( 'x-team-sync-site' ) );
		$data = $r->get_json_params();
		$p    = is_array( $data ) ? ( $data['payload'] ?? null ) : null;

		$valid = Payload::validate( $p );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		Sites::update( $site, array( 'last_seen' => time() ) );
		if ( ! Queue::push( array( 'type' => 'route', 'payload' => $p, 'origin_site' => $site ) ) ) {
			return new \WP_Error( 'team_sync_queue', 'Hub nie zapisał zadania, ponów.', array( 'status' => 503 ) );
		}
		Queue::kick();
		Logger::add( 'info', 'Odebrano wpis z connectora.', array( 'site' => $site, 'uuid' => $p['uuid'], 'title' => $p['post']['title'] ?? '' ) );

		return new \WP_REST_Response( array( 'status' => 'queued', 'uuid' => $p['uuid'] ), 202 );
	}

	/** Test połączenia z connectora; przy okazji zapisujemy jego prawdziwy endpoint REST. */
	public static function ping( \WP_REST_Request $r ) {
		$site = sanitize_key( (string) $r->get_header( 'x-team-sync-site' ) );
		$data = (array) $r->get_json_params();

		$fields   = array( 'last_seen' => time() );
		$endpoint = esc_url_raw( (string) ( $data['endpoint'] ?? '' ) );
		if ( $endpoint && in_array( wp_parse_url( $endpoint, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) {
			$fields['endpoint'] = trailingslashit( $endpoint );
		}
		Sites::update( $site, $fields );
		$s = Sites::get( $site );

		return array(
			'ok'       => true,
			'hub'      => get_bloginfo( 'name' ),
			'site'     => $s['name'] ?? '',
			'brands' => Router::branches_for_site( $site ),
		);
	}
}
