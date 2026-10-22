<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wspólna konfiguracja. Wartości prefix / rest_ns / page ustawia plik główny wtyczki.
 */
final class Config {
	const META_UUID       = '_team_sync_uuid';
	const META_SOURCE     = '_team_sync_source';
	const META_SENT       = '_team_sync_sent';
	const META_INCOMPLETE = '_team_sync_incomplete';

	public static $prefix      = '';
	public static $rest_ns     = '';
	public static $page        = '';
	public static $role        = '';
	public static $job_handler = null;

	public static function boot( array $args ): void {
		foreach ( $args as $k => $v ) {
			if ( property_exists( __CLASS__, $k ) ) {
				self::${$k} = $v;
			}
		}
	}

	public static function key( string $suffix ): string {
		return self::$prefix . '_' . $suffix;
	}

	public static function settings(): array {
		$defaults = array(
			'post_type'      => 'team',
			'taxonomy'       => 'team-brand',
			'meta_keys'      => '',
			'author'         => 0,
			'author_mode'    => 'match',
			'author_exclude' => '',
			'dispatch_delay' => 20,
		);
		$saved = get_option( self::key( 'settings' ), array() );
		return array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}

	public static function get( string $k ) {
		$s = self::settings();
		return $s[ $k ] ?? null;
	}

	public static function post_type(): string {
		return (string) self::get( 'post_type' );
	}

	public static function taxonomy(): string {
		return (string) self::get( 'taxonomy' );
	}

	/** Klucze meta, które wysyłamy i przyjmujemy (biała lista). */
	public static function meta_keys(): array {
		$keys = array_map( 'trim', explode( ',', (string) self::get( 'meta_keys' ) ) );
		return array_values( array_filter( $keys ) );
	}

	/** Loginy, którym nigdy nie przypisujemy importowanych wpisów (np. konto agencji). */
	public static function excluded_logins(): array {
		$l = array_map( 'trim', explode( ',', strtolower( (string) self::get( 'author_exclude' ) ) ) );
		return array_values( array_filter( $l ) );
	}

	public static function author_id(): int {
		$id = (int) self::get( 'author' );
		if ( $id && get_userdata( $id ) ) {
			return $id;
		}
		// Brak wyboru: pierwszy administrator spoza listy wykluczonych.
		$excluded = self::excluded_logins();
		foreach ( get_users( array( 'role' => 'administrator', 'orderby' => 'ID', 'number' => 50 ) ) as $u ) {
			if ( ! in_array( strtolower( $u->user_login ), $excluded, true ) ) {
				return (int) $u->ID;
			}
		}
		return 0;
	}
}
