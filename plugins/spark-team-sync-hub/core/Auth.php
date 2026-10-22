<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Podpisy HMAC-SHA256: podpisujemy "timestamp\nnonce\nbody" wspólnym sekretem strony.
 * Okno czasowe 5 min + jednorazowy nonce chronią przed powtórzeniem żądania.
 */
final class Auth {
	const SKEW = 300;

	public static function generate_secret(): string {
		return bin2hex( random_bytes( 32 ) );
	}

	public static function generate_site_id(): string {
		return 'site_' . bin2hex( random_bytes( 6 ) );
	}

	public static function signature( string $secret, string $ts, string $nonce, string $body ): string {
		return hash_hmac( 'sha256', $ts . "\n" . $nonce . "\n" . $body, $secret );
	}

	/**
	 * Wysyła podpisany POST. Zwraca tablicę z odpowiedzi albo WP_Error.
	 * Błędy 4xx (poza 408/429) są oznaczane jako trwałe - kolejka nie ponawia.
	 */
	public static function send( string $url, string $site_id, string $secret, array $data, int $timeout = 20 ) {
		$body  = wp_json_encode( $data );
		$ts    = (string) time();
		$nonce = bin2hex( random_bytes( 12 ) );

		$res = wp_remote_post(
			$url,
			array(
				'timeout' => $timeout,
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Accept'         => 'application/json',
					'X-Team-Sync-Site'      => $site_id,
					'X-Team-Sync-Timestamp' => $ts,
					'X-Team-Sync-Nonce'     => $nonce,
					'X-Team-Sync-Signature' => self::signature( $secret, $ts, $nonce, $body ),
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		$raw  = (string) wp_remote_retrieve_body( $res );
		$json = json_decode( $raw, true );

		if ( $code < 200 || $code >= 300 ) {
			$msg = ( is_array( $json ) && isset( $json['message'] ) ) ? $json['message'] : substr( wp_strip_all_tags( $raw ), 0, 200 );
			return new \WP_Error(
				'team_sync_http',
				sprintf( 'HTTP %d: %s', $code, $msg ),
				array(
					'status'    => $code,
					'permanent' => $code >= 400 && $code < 500 && ! in_array( $code, array( 408, 429 ), true ),
				)
			);
		}

		return is_array( $json ) ? $json : array();
	}

	/**
	 * @param callable $secret_for fn(string $site_id): ?string
	 * @return true|\WP_Error
	 */
	public static function verify( \WP_REST_Request $r, callable $secret_for ) {
		$site  = (string) $r->get_header( 'x-team-sync-site' );
		$ts    = (string) $r->get_header( 'x-team-sync-timestamp' );
		$nonce = (string) $r->get_header( 'x-team-sync-nonce' );
		$sig   = (string) $r->get_header( 'x-team-sync-signature' );

		if ( '' === $site || '' === $ts || '' === $nonce || '' === $sig ) {
			return new \WP_Error( 'team_sync_auth', 'Brak nagłówków podpisu.', array( 'status' => 401 ) );
		}
		if ( ! ctype_digit( $ts ) || abs( time() - (int) $ts ) > self::SKEW ) {
			return new \WP_Error( 'team_sync_clock', 'Znacznik czasu poza oknem 5 min - sprawdź zegar serwera.', array( 'status' => 401 ) );
		}

		$secret = call_user_func( $secret_for, $site );
		if ( ! $secret ) {
			return new \WP_Error( 'team_sync_site', 'Nieznana lub wyłączona strona.', array( 'status' => 401 ) );
		}

		$expected = self::signature( $secret, $ts, $nonce, (string) $r->get_body() );
		if ( ! hash_equals( $expected, $sig ) ) {
			return new \WP_Error( 'team_sync_sig', 'Nieprawidłowy podpis (zły klucz?).', array( 'status' => 401 ) );
		}

		$nonce_key = Config::key( 'n_' . md5( $site . '|' . $nonce ) );
		if ( get_transient( $nonce_key ) ) {
			return new \WP_Error( 'team_sync_replay', 'Żądanie zostało już użyte.', array( 'status' => 401 ) );
		}
		set_transient( $nonce_key, 1, self::SKEW * 2 );

		return true;
	}
}
