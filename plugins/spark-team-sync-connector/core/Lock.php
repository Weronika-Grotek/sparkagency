<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Atomowa blokada na tabeli options. add_option() się do tego nie nadaje, bo używa
 * INSERT ... ON DUPLICATE KEY UPDATE; tutaj INSERT IGNORE zwraca 0 wierszy, gdy blokada już istnieje.
 */
final class Lock {
	public static function acquire( string $name, int $ttl = 600 ): bool {
		global $wpdb;
		$key = Config::key( 'lock_' . $name );
		$ins = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $key, (string) time() ) );
		if ( $ins ) {
			return true;
		}
		// Blokada po awarii procesu: przejmujemy ją warunkowo, tylko jeśli nikt nie zdążył przed nami.
		$since = (int) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $key ) );
		if ( $since && time() - $since > $ttl ) {
			return (bool) $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", (string) time(), $key, (string) $since ) );
		}
		return false;
	}

	public static function release( string $name ): void {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => Config::key( 'lock_' . $name ) ) );
	}
}
