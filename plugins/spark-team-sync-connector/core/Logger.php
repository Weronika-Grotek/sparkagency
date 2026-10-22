<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

final class Logger {
	const MAX = 300;

	public static function add( string $level, string $message, array $context = array() ): void {
		$key = Config::key( 'log' );
		$log = get_option( $key, array() );
		$log = is_array( $log ) ? $log : array();
		array_unshift( $log, array( 't' => time(), 'l' => $level, 'm' => $message, 'c' => $context ) );
		update_option( $key, array_slice( $log, 0, self::MAX ), false );
	}

	public static function all(): array {
		$log = get_option( Config::key( 'log' ), array() );
		return is_array( $log ) ? $log : array();
	}

	public static function clear(): void {
		delete_option( Config::key( 'log' ) );
	}
}
