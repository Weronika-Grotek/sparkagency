<?php
namespace TeamSyncConnector;

use TeamSyncConnector\Core\Auth;
use TeamSyncConnector\Core\Config;

defined( 'ABSPATH' ) || exit;

final class Connection {
	private static function opt(): string {
		return Config::key( 'connection' );
	}

	public static function get(): array {
		$c = get_option( self::opt(), array() );
		return is_array( $c ) ? $c : array();
	}

	public static function save( array $c ): void {
		update_option( self::opt(), $c, false );
	}

	public static function clear(): void {
		delete_option( self::opt() );
	}

	public static function is_configured(): bool {
		$c = self::get();
		return ! empty( $c['endpoint'] ) && ! empty( $c['site_id'] ) && ! empty( $c['secret'] );
	}

	/** Hub podpisuje żądania naszym ID i sekretem - akceptujemy tylko je. */
	public static function secret_for( string $site ): ?string {
		$c = self::get();
		return ( self::is_configured() && hash_equals( (string) $c['site_id'], $site ) ) ? $c['secret'] : null;
	}

	/** @return array|\WP_Error */
	public static function parse_key( string $key ) {
		$key = trim( $key );
		$err = new \WP_Error( 'team_sync_key', 'Nieprawidłowy klucz połączenia. Skopiuj go ponownie z huba.' );
		if ( 0 !== strpos( $key, 'teamsync_' ) ) {
			return $err;
		}
		$raw  = strtr( substr( $key, 7 ), '-_', '+/' );
		$raw .= str_repeat( '=', ( 4 - strlen( $raw ) % 4 ) % 4 );
		$json = base64_decode( $raw, true );
		$d    = $json ? json_decode( $json, true ) : null;
		if ( ! is_array( $d ) || empty( $d['e'] ) || empty( $d['s'] ) || empty( $d['k'] ) ) {
			return $err;
		}
		$endpoint = esc_url_raw( (string) $d['e'] );
		$secret   = preg_replace( '/[^a-f0-9]/', '', (string) $d['k'] );
		if ( '' === $endpoint || 64 !== strlen( $secret ) ) {
			return $err;
		}
		return array(
			'endpoint'  => $endpoint,
			'site_id'   => sanitize_key( (string) $d['s'] ),
			'secret'    => $secret,
			'connected' => time(),
			'last_ok'   => 0,
		);
	}

	/** Ping do huba; hub zapisuje nasz endpoint i zwraca listę marek przypisanych do tej strony. */
	public static function ping() {
		if ( ! self::is_configured() ) {
			return new \WP_Error( 'team_sync_conn', 'Brak konfiguracji połączenia.' );
		}
		$c = self::get();
		$r = Auth::send(
			$c['endpoint'] . 'ping',
			$c['site_id'],
			$c['secret'],
			array(
				'endpoint' => rest_url( Config::$rest_ns . '/' ),
				'name'     => get_bloginfo( 'name' ),
				'url'      => home_url( '/' ),
			),
			15
		);
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$c['last_ok']  = time();
		$c['hub_name'] = sanitize_text_field( (string) ( $r['hub'] ?? '' ) );
		$c['brands'] = array_map( 'sanitize_text_field', (array) ( $r['brands'] ?? array() ) );
		self::save( $c );
		return $r;
	}
}
