<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Prosta trwała kolejka na WP-Cron. Zadanie = opcja (autoload=no) + zdarzenie cron z jej ID.
 * Ponowienia: 1 min, 5 min, 15 min, 1 h, 3 h.
 */
final class Queue {
	const MAX_ATTEMPTS = 6;

	private static $backoff = array( 60, 300, 900, 3600, 10800 );

	public static function hook(): string {
		return Config::key( 'job' );
	}

	public static function init(): void {
		add_action( self::hook(), array( __CLASS__, 'run' ) );
		// Zamiatacz: wykonuje zadania, których zdarzenie cron zginęło (np. błąd zapisu harmonogramu).
		add_filter( 'cron_schedules', array( __CLASS__, 'schedules' ) );
		add_action( Config::key( 'sweep' ), array( __CLASS__, 'sweep' ) );
		if ( ! wp_next_scheduled( Config::key( 'sweep' ) ) ) {
			wp_schedule_event( time() + 300, 'team_sync_5min', Config::key( 'sweep' ) );
		}
	}

	public static function schedules( $s ) {
		$s['team_sync_5min'] = array( 'interval' => 300, 'display' => 'Team Sync: co 5 min' );
		return $s;
	}

	public static function sweep(): void {
		foreach ( self::ids() as $id ) {
			$job = get_option( self::opt( $id ) );
			if ( is_array( $job ) && (int) ( $job['next'] ?? 0 ) < time() - 120 ) {
				self::run( $id );
			}
		}
	}

	private static function opt( string $id ): string {
		return Config::key( 'job_' . $id );
	}

	/** Zwraca ID zadania albo '' gdy nie udało się go zapisać (wołający powinien zgłosić błąd). */
	public static function push( array $job, int $delay = 0 ): string {
		$id              = str_replace( '-', '', wp_generate_uuid4() );
		$job['attempts'] = 0;
		$job['created']  = time();
		$job['next']     = time() + max( 0, $delay );
		if ( ! add_option( self::opt( $id ), $job, '', 'no' ) ) {
			Logger::add( 'error', 'Nie udało się zapisać zadania w kolejce.', array( 'type' => $job['type'] ?? '' ) );
			return '';
		}
		wp_schedule_single_event( $job['next'], self::hook(), array( $id ) );
		return $id;
	}

	/** Natychmiastowe odpalenie wp-cron w tle (nieblokujące). */
	public static function kick(): void {
		if ( wp_doing_cron() || ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
			return;
		}
		spawn_cron();
	}

	public static function run( $id ): void {
		$id = (string) $id;
		if ( ! is_callable( Config::$job_handler ) || ! get_option( self::opt( $id ) ) ) {
			return;
		}

		// Atomowe zajęcie zadania: cron i ręczne przetwarzanie nie wykonają go dwa razy.
		if ( ! Lock::acquire( 'job_' . $id ) ) {
			return;
		}
		try {
			self::execute( $id );
		} finally {
			Lock::release( 'job_' . $id );
		}
	}

	private static function execute( string $id ): void {
		wp_cache_delete( self::opt( $id ), 'options' );
		$job = get_option( self::opt( $id ) );
		if ( ! is_array( $job ) ) {
			return; // Wykonane przez inny proces w międzyczasie.
		}

		$res = call_user_func( Config::$job_handler, $job );
		if ( ! is_wp_error( $res ) ) {
			delete_option( self::opt( $id ) );
			return;
		}

		$job['attempts']   = (int) $job['attempts'] + 1;
		$job['last_error'] = $res->get_error_message();
		$data              = $res->get_error_data();
		$permanent         = is_array( $data ) && ! empty( $data['permanent'] );
		$quiet             = is_array( $data ) && ! empty( $data['quiet'] );
		$ctx               = array_filter(
			array(
				'type'    => $job['type'] ?? '',
				'post_id' => $job['post_id'] ?? 0,
				'uuid'    => $job['payload']['uuid'] ?? '',
				'site'    => $job['site'] ?? '',
			)
		);

		if ( $permanent || $job['attempts'] >= self::MAX_ATTEMPTS ) {
			delete_option( self::opt( $id ) );
			Logger::add( 'error', sprintf( 'Zadanie porzucone po %d próbach: %s', $job['attempts'], $job['last_error'] ), $ctx );
			return;
		}

		$wait        = self::$backoff[ min( $job['attempts'] - 1, count( self::$backoff ) - 1 ) ];
		$job['next'] = time() + $wait;
		update_option( self::opt( $id ), $job, false );
		wp_schedule_single_event( $job['next'], self::hook(), array( $id ) );
		if ( ! $quiet ) {
			Logger::add( 'warning', sprintf( 'Ponowienie za %d s (próba %d): %s', $wait, $job['attempts'], $job['last_error'] ), $ctx );
		}
	}

	public static function ids(): array {
		global $wpdb;
		$prefix = Config::key( 'job_' );
		$names  = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id ASC", $wpdb->esc_like( $prefix ) . '%' ) );
		return array_map(
			function ( $n ) use ( $prefix ) {
				return substr( $n, strlen( $prefix ) );
			},
			$names
		);
	}

	public static function pending(): int {
		return count( self::ids() );
	}

	/** Ręczne przetworzenie kolejki z panelu (gdy cron na hostingu nie działa). */
	public static function run_now( int $limit = 20 ): int {
		$n = 0;
		foreach ( array_slice( self::ids(), 0, $limit ) as $id ) {
			self::run( $id );
			$n++;
		}
		return $n;
	}
}
