<?php
namespace TeamSyncConnector;

use TeamSyncConnector\Core\Auth;
use TeamSyncConnector\Core\Bulk;
use TeamSyncConnector\Core\Config;
use TeamSyncConnector\Core\Importer;
use TeamSyncConnector\Core\Logger;
use TeamSyncConnector\Core\Payload;

defined( 'ABSPATH' ) || exit;

final class Jobs {
	public static function handle( array $job ) {
		switch ( $job['type'] ?? '' ) {
			case 'dispatch':
				return self::dispatch( (int) ( $job['post_id'] ?? 0 ), ! empty( $job['force'] ) );
			case 'bulk':
				return Bulk::run_batch( $job );
			case 'import':
				return Importer::run_job( (array) ( $job['payload'] ?? array() ) );
		}
		return new \WP_Error( 'team_sync_job', 'Nieznany typ zadania.', array( 'permanent' => true ) );
	}

	/** Lokalnie opublikowany wpis idzie do huba; hub decyduje, gdzie trafi dalej. */
	private static function dispatch( int $post_id, bool $force = false ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return new \WP_Error( 'team_sync_post', 'Wpis nie istnieje lub nie jest opublikowany.', array( 'permanent' => true ) );
		}
		if ( get_post_meta( $post_id, Config::META_SOURCE, true ) ) {
			return new \WP_Error( 'team_sync_post', 'To kopia z sieci - connector wysyła tylko własne wpisy.', array( 'permanent' => true ) );
		}
		if ( ! Connection::is_configured() ) {
			return new \WP_Error( 'team_sync_conn', 'Brak połączenia z hubem.' );
		}
		$c       = Connection::get();
		$payload = Payload::build( $post_id, $force );
		$ctx     = array( 'uuid' => $payload['uuid'], 'title' => $post->post_title );
		$r       = Auth::send( $c['endpoint'] . 'push', $c['site_id'], $c['secret'], array( 'payload' => $payload ) );
		if ( is_wp_error( $r ) ) {
			Logger::add( 'error', 'Wysyłka do huba nieudana: ' . $r->get_error_message(), $ctx );
			return $r;
		}
		Logger::add( 'success', 'Wysłano do huba.', $ctx );
		return true;
	}
}
