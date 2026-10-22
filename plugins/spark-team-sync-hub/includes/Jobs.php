<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Auth;
use TeamSyncHub\Core\Bulk;
use TeamSyncHub\Core\Config;
use TeamSyncHub\Core\Importer;
use TeamSyncHub\Core\Logger;
use TeamSyncHub\Core\Payload;
use TeamSyncHub\Core\Queue;

defined( 'ABSPATH' ) || exit;

final class Jobs {
	public static function handle( array $job ) {
		switch ( $job['type'] ?? '' ) {
			case 'dispatch':
				return self::dispatch( (int) ( $job['post_id'] ?? 0 ), ! empty( $job['force'] ) );
			case 'route':
				return self::route( (array) ( $job['payload'] ?? array() ), (string) ( $job['origin_site'] ?? '' ), false );
			case 'bulk':
				return Bulk::run_batch( $job );
			case 'send':
				return self::send( (string) ( $job['site'] ?? '' ), (array) ( $job['payload'] ?? array() ) );
			case 'import':
				return Importer::run_job( (array) ( $job['payload'] ?? array() ) );
		}
		return new \WP_Error( 'team_sync_job', 'Nieznany typ zadania.', array( 'permanent' => true ) );
	}

	/** Wpis opublikowany na hubie. */
	private static function dispatch( int $post_id, bool $force = false ) {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return new \WP_Error( 'team_sync_post', 'Wpis nie istnieje lub nie jest opublikowany.', array( 'permanent' => true ) );
		}
		// Kopia z connectora przechowywana na hubie: nie odsyłamy jej do strony źródłowej.
		$origin = '';
		$src    = get_post_meta( $post_id, Config::META_SOURCE, true );
		if ( is_array( $src ) && ! empty( $src['site_url'] ) ) {
			$origin = (string) Sites::find_by_url( $src['site_url'] );
		}
		return self::route( Payload::build( $post_id, $force ), $origin, true );
	}

	/**
	 * Rozsyła payload do stron przypisanych do jego marek.
	 * $origin   = ID connectora źródłowego ('' gdy wpis powstał na hubie).
	 * $from_hub = paczkę zbudował hub z własnej bazy, więc hub jej nie importuje.
	 */
	public static function route( array $payload, string $origin, bool $from_hub ) {
		$valid = Payload::validate( $payload );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// Nowe markę z connectorów pojawiają się na hubie, żeby można było je zmapować.
		Importer::ensure_terms( $payload['brands'], Config::taxonomy() );

		$queued = array();
		$failed = 0;
		foreach ( Router::targets( $payload['brands'] ) as $target ) {
			if ( $target === $origin ) {
				continue;
			}
			if ( Router::HUB === $target ) {
				if ( $from_hub ) {
					continue; // Hub ma już ten wpis.
				}
				$ok = Queue::push( array( 'type' => 'import', 'payload' => $payload ) );
			} else {
				$ok = Queue::push( array( 'type' => 'send', 'site' => $target, 'payload' => $payload ) );
			}
			if ( $ok ) {
				$queued[] = $target;
			} else {
				$failed++;
			}
		}

		if ( $failed ) {
			// Ponowienie całego routingu jest bezpieczne: odbiorcy pomijają wpisy, które już mają.
			return new \WP_Error( 'team_sync_queue', sprintf( 'Nie zakolejkowano %d wysyłek.', $failed ) );
		}

		$ctx = array(
			'uuid'    => $payload['uuid'],
			'title'   => $payload['post']['title'] ?? '',
			'from'    => '' === $origin ? 'hub' : $origin,
			'force'   => ! empty( $payload['force'] ),
			'targets' => $queued,
		);
		if ( $queued ) {
			Logger::add( 'info', sprintf( 'Rozesłano do %d stron.', count( $queued ) ), $ctx );
			Queue::kick();
		} else {
			Logger::add( 'warning', 'Brak stron docelowych dla marek tego wpisu. Sprawdź mapowanie.', $ctx + array( 'brands' => wp_list_pluck( $payload['brands'], 'slug' ) ) );
		}
		return true;
	}

	private static function send( string $site_id, array $payload ) {
		$s = Sites::get( $site_id );
		if ( ! $s || empty( $s['active'] ) ) {
			return new \WP_Error( 'team_sync_site', 'Strona docelowa nie istnieje lub jest wyłączona.', array( 'permanent' => true ) );
		}
		$ctx = array( 'uuid' => $payload['uuid'] ?? '', 'title' => $payload['post']['title'] ?? '', 'site' => $s['name'] );
		$r   = Auth::send( $s['endpoint'] . 'receive', $s['id'], $s['secret'], array( 'payload' => $payload ) );
		if ( is_wp_error( $r ) ) {
			Logger::add( 'error', sprintf( 'Wysyłka do "%s" nieudana: %s', $s['name'], $r->get_error_message() ), $ctx );
			return $r;
		}
		Sites::update( $site_id, array( 'last_seen' => time() ) );
		Logger::add( 'success', sprintf( 'Przekazano do "%s" (%s).', $s['name'], $r['status'] ?? 'ok' ), $ctx );
		return true;
	}
}
