<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Config;

defined( 'ABSPATH' ) || exit;

/** Wybór stron docelowych bezpośrednio na ekranie edycji marki. */
final class TermUI {
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function register(): void {
		$tax = Config::taxonomy();
		add_action( "{$tax}_add_form_fields", array( __CLASS__, 'add_fields' ) );
		add_action( "{$tax}_edit_form_fields", array( __CLASS__, 'edit_fields' ) );
		add_action( "created_{$tax}", array( __CLASS__, 'save' ) );
		add_action( "edited_{$tax}", array( __CLASS__, 'save' ) );
		add_filter( "manage_edit-{$tax}_columns", array( __CLASS__, 'columns' ) );
		add_filter( "manage_{$tax}_custom_column", array( __CLASS__, 'column' ), 10, 3 );
	}

	private static function checkboxes( array $selected ): void {
		wp_nonce_field( Config::key( 'term' ), 'team_sync_term_nonce' );
		echo '<input type="hidden" name="team_sync_targets_present" value="1">';
		$opts = array( Router::HUB => 'Hub (ta strona)' );
		foreach ( Sites::all() as $id => $s ) {
			$opts[ $id ] = $s['name'] . ( empty( $s['active'] ) ? ' (wyłączona)' : '' );
		}
		foreach ( $opts as $id => $label ) {
			printf(
				'<label style="display:block;margin:3px 0"><input type="checkbox" name="team_sync_targets[]" value="%s" %s> %s</label>',
				esc_attr( $id ),
				checked( in_array( $id, $selected, true ), true, false ),
				esc_html( $label )
			);
		}
	}

	public static function add_fields(): void {
		echo '<div class="form-field"><label>Strony docelowe (Team Sync)</label>';
		self::checkboxes( array() );
		echo '<p>Zespoły z tą marką trafią na zaznaczone strony.</p></div>';
	}

	public static function edit_fields( $term ): void {
		echo '<tr class="form-field"><th scope="row">Strony docelowe (Team Sync)</th><td>';
		self::checkboxes( Router::get_targets_for_term( (int) $term->term_id ) );
		echo '<p class="description">Zespoły z tą marką trafią na zaznaczone strony.</p></td></tr>';
	}

	public static function save( $term_id ): void {
		if ( empty( $_POST['team_sync_targets_present'] ) ) {
			return;
		}
		if ( ! isset( $_POST['team_sync_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['team_sync_term_nonce'] ) ), Config::key( 'term' ) ) ) {
			return;
		}
		$tax = get_taxonomy( Config::taxonomy() );
		if ( ! $tax || ! current_user_can( $tax->cap->edit_terms ) ) {
			return;
		}
		$targets = isset( $_POST['team_sync_targets'] ) ? (array) wp_unslash( $_POST['team_sync_targets'] ) : array();
		Router::set_targets_for_term( (int) $term_id, $targets );
	}

	public static function columns( $cols ) {
		$cols['cs_sync'] = 'Strony docelowe';
		return $cols;
	}

	public static function column( $out, $col, $term_id ) {
		if ( 'cs_sync' !== $col ) {
			return $out;
		}
		$names = array();
		foreach ( Router::get_targets_for_term( (int) $term_id ) as $id ) {
			if ( Router::HUB === $id ) {
				$names[] = 'Hub';
				continue;
			}
			$s = Sites::get( $id );
			if ( $s ) {
				$names[] = $s['name'];
			}
		}
		return $names ? esc_html( implode( ', ', $names ) ) : '-';
	}
}
