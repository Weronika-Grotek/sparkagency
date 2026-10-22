<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Tryb lokalny (Laragon, Local, Valet, DDEV itp.).
 *
 * Gdy TA strona działa lokalnie, połączenia do innych lokalnych hostów:
 * - nie weryfikują certyfikatu SSL (self-signed z Laragona),
 * - mogą iść na 127.0.0.1 / .test (WP domyślnie blokuje to przy pobieraniu obrazów).
 * Na produkcji nic się nie zmienia, bo warunek dotyczy obu stron połączenia.
 *
 * Wymuszenie: define( 'TEAM_SYNC_LOCAL', true|false ) w wp-config.php.
 */
final class Local {
	public static function init(): void {
		if ( ! self::is_active() ) {
			return;
		}
		add_filter( 'http_request_args', array( __CLASS__, 'request_args' ), 20, 2 );
		add_filter( 'http_request_host_is_external', array( __CLASS__, 'host_is_external' ), 20, 3 );
	}

	public static function is_active(): bool {
		if ( defined( 'TEAM_SYNC_LOCAL' ) ) {
			return (bool) TEAM_SYNC_LOCAL;
		}
		if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
			return true;
		}
		return self::is_local_host( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	public static function is_local_host( string $host ): bool {
		$host = strtolower( trim( $host, '[]' ) );
		if ( '' === $host ) {
			return false;
		}
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return true;
		}
		foreach ( array( '.test', '.local', '.localhost', '.lan', '.lndo.site', '.ddev.site' ) as $tld ) {
			if ( substr( $host, -strlen( $tld ) ) === $tld ) {
				return true;
			}
		}
		// Adresy prywatne (np. 192.168.x.x w sieci domowej).
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return false === filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
		}
		return false;
	}

	public static function request_args( $args, $url ) {
		if ( self::is_local_host( (string) wp_parse_url( $url, PHP_URL_HOST ) ) ) {
			$args['sslverify']          = false;
			$args['reject_unsafe_urls'] = false;
		}
		return $args;
	}

	public static function host_is_external( $external, $host, $url = '' ) {
		return self::is_local_host( (string) $host ) ? true : $external;
	}
}
