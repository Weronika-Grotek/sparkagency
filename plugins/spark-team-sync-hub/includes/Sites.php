<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Auth;
use TeamSyncHub\Core\Config;

defined( 'ABSPATH' ) || exit;

/** Rejestr connectorów: ID, URL, endpoint REST, sekret. */
final class Sites {
	private static function opt(): string {
		return Config::key( 'sites' );
	}

	public static function all(): array {
		$s = get_option( self::opt(), array() );
		return is_array( $s ) ? $s : array();
	}

	public static function get( string $id ): ?array {
		$all = self::all();
		return $all[ $id ] ?? null;
	}

	private static function save_all( array $sites ): void {
		update_option( self::opt(), $sites, false );
	}

	public static function add( string $name, string $url ): array {
		$url  = untrailingslashit( $url );
		$id   = Auth::generate_site_id();
		$site = array(
			'id'        => $id,
			'name'      => $name,
			'url'       => $url,
			// Domyślny endpoint; connector nadpisze go prawdziwym rest_url() przy pierwszym połączeniu.
			'endpoint'  => trailingslashit( $url ) . 'wp-json/team-sync/v1/',
			'secret'    => Auth::generate_secret(),
			'active'    => true,
			'created'   => time(),
			'last_seen' => 0,
		);
		$all        = self::all();
		$all[ $id ] = $site;
		self::save_all( $all );
		return $site;
	}

	public static function update( string $id, array $fields ): void {
		$all = self::all();
		if ( ! isset( $all[ $id ] ) ) {
			return;
		}
		$all[ $id ] = array_merge( $all[ $id ], $fields );
		self::save_all( $all );
	}

	public static function delete( string $id ): void {
		$all = self::all();
		unset( $all[ $id ] );
		self::save_all( $all );
		Router::remove_site( $id );
	}

	/** ID connectora po adresie strony (porównanie bez schematu, www i końcowego ukośnika). */
	public static function find_by_url( string $url ): ?string {
		$norm = function ( $u ) {
			$u = strtolower( preg_replace( '#^https?://(www\.)?#i', '', trim( (string) $u ) ) );
			return untrailingslashit( $u );
		};
		$needle = $norm( $url );
		foreach ( self::all() as $id => $s ) {
			if ( $norm( $s['url'] ) === $needle ) {
				return $id;
			}
		}
		return null;
	}

	public static function secret_for( string $id ): ?string {
		$s = self::get( $id );
		return ( $s && ! empty( $s['active'] ) ) ? $s['secret'] : null;
	}

	/** Klucz do wklejenia w connectorze: endpoint huba + ID strony + sekret. */
	public static function connection_key( array $site ): string {
		$data = array(
			'e' => rest_url( Config::$rest_ns . '/' ),
			's' => $site['id'],
			'k' => $site['secret'],
		);
		return 'teamsync_' . rtrim( strtr( base64_encode( wp_json_encode( $data, JSON_UNESCAPED_SLASHES ) ), '+/', '-_' ), '=' );
	}
}
