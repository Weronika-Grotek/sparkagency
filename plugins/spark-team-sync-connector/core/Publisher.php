<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wykrywa pierwszą publikację zespół z przypisaną marką i wrzuca zadanie "dispatch" do kolejki.
 * Wpisy zaimportowane z sieci nigdy nie są wysyłane dalej.
 */
final class Publisher {
	public static function init(): void {
		// Odpala się po zapisaniu termów i meta (także z edytora blokowego).
		add_action( 'wp_after_insert_post', array( __CLASS__, 'on_save' ), 20, 2 );
		// Wpisy zaplanowane (future -> publish przez cron).
		add_action( 'transition_post_status', array( __CLASS__, 'on_transition' ), 20, 3 );
	}

	public static function on_save( $post_id, $post ): void {
		self::maybe( $post );
	}

	public static function on_transition( $new, $old, $post ): void {
		if ( 'publish' === $new && 'future' === $old ) {
			self::maybe( $post );
		}
	}

	private static function maybe( $post ): void {
		$post = get_post( $post );
		if ( ! $post || $post->post_type !== Config::post_type() || 'publish' !== $post->post_status ) {
			return;
		}
		if ( Importer::$running || wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
			return;
		}
		if ( get_post_meta( $post->ID, Config::META_SOURCE, true ) || get_post_meta( $post->ID, Config::META_SENT, true ) ) {
			return;
		}

		$terms = wp_get_object_terms( $post->ID, Config::taxonomy(), array( 'fields' => 'ids' ) );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return; // Bez marki nie wysyłamy - wyślemy, gdy marka zostanie dodana.
		}
		if ( ! apply_filters( 'team_sync_should_send', true, $post ) ) {
			return;
		}

		self::dispatch( $post->ID, (int) Config::get( 'dispatch_delay' ) );
	}

	/**
	 * @param bool $force Odbiorcy nadpiszą istniejące kopie.
	 * @param bool $quiet Bez wpisu w logu (masowa synchronizacja loguje zbiorczo).
	 */
	public static function dispatch( int $post_id, int $delay = 0, bool $force = false, bool $quiet = false ): bool {
		Payload::uuid( $post_id );
		// Opóźnienie daje czas na zapis meta boxów (np. ACF) po zapisie z edytora blokowego.
		if ( ! Queue::push( array( 'type' => 'dispatch', 'post_id' => $post_id, 'force' => $force ), $delay ) ) {
			return false; // META_SENT nie ustawiony - wpis zostanie wysłany przy następnym zapisie.
		}
		update_post_meta( $post_id, Config::META_SENT, time() );
		if ( 0 === $delay && ! $quiet ) {
			Queue::kick();
		}
		if ( ! $quiet ) {
			Logger::add( 'info', $force ? 'Wpis zakolejkowany do wymuszonej synchronizacji.' : 'Wpis zakolejkowany do wysłania.', array( 'post_id' => $post_id, 'title' => get_the_title( $post_id ) ) );
		}
		return true;
	}
}
