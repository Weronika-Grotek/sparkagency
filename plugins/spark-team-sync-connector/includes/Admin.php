<?php
namespace TeamSyncConnector;

use TeamSyncConnector\Core\Config;
use TeamSyncConnector\Core\Bulk;
use TeamSyncConnector\Core\Ui;

defined( 'ABSPATH' ) || exit;

final class Admin {
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_' . Config::key( 'save_key' ), array( __CLASS__, 'save_key' ) );
		add_action( 'admin_post_' . Config::key( 'conn_action' ), array( __CLASS__, 'conn_action' ) );
	}

	public static function menu(): void {
		add_menu_page( 'Team Sync', 'Team Sync', 'manage_options', Config::$page, array( __CLASS__, 'render' ), 'dashicons-share-alt2', 81 );
	}

	public static function render(): void {
		$tab  = sanitize_key( $_GET['tab'] ?? 'connection' );
		$tabs = array(
			'connection' => 'Połączenie',
			'sync'       => 'Synchronizacja',
			'settings'   => 'Ustawienia',
			'log'        => 'Log',
		);
		$tab = isset( $tabs[ $tab ] ) ? $tab : 'connection';
		echo '<div class="wrap"><h1>Team Sync - Connector</h1>';
		Ui::render_notices();
		Ui::tabs( $tabs, $tab );
		if ( 'sync' === $tab ) {
			Bulk::render_tab();
		} elseif ( 'settings' === $tab ) {
			Ui::render_settings();
		} elseif ( 'log' === $tab ) {
			Ui::render_log();
		} else {
			self::render_connection();
		}
		echo '</div>';
	}

	private static function render_connection(): void {
		$c = Connection::get();
		if ( Connection::is_configured() ) {
			$brands = $c['brands'] ?? array();
			?>
			<table class="form-table" role="presentation">
				<tr><th>Hub</th><td><?php echo esc_html( $c['hub_name'] ?? '' ); ?><br><code><?php echo esc_html( $c['endpoint'] ); ?></code></td></tr>
				<tr><th>ID tej strony</th><td><code><?php echo esc_html( $c['site_id'] ); ?></code></td></tr>
				<tr><th>Ostatni udany kontakt</th><td><?php echo ! empty( $c['last_ok'] ) ? esc_html( wp_date( 'Y-m-d H:i', (int) $c['last_ok'] ) ) : '<span style="color:#d63638">brak</span>'; ?></td></tr>
				<tr><th>Marki przypisane na hubie</th><td><?php echo $brands ? esc_html( implode( ', ', $brands ) ) : '<em>żadne — ta strona nie będzie otrzymywać wpisów</em>'; ?></td></tr>
			</table>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( Ui::action_url( 'conn_action', array( 'do' => 'test' ) ) ); ?>">Testuj połączenie</a>
				<a class="button" href="<?php echo esc_url( Ui::action_url( 'conn_action', array( 'do' => 'disconnect' ) ) ); ?>" onclick="return confirm('Rozłączyć z hubem?')">Rozłącz</a>
			</p>
			<hr>
			<?php
		}
		printf( '<h2>%s</h2>', Connection::is_configured() ? 'Zmień klucz' : 'Połącz z hubem' );
		Ui::form_open( 'save_key' );
		?>
		<textarea name="key" rows="4" class="large-text code" required placeholder="teamsync_..."></textarea>
		<p class="description">Klucz generuje administrator huba w Team Sync Hub &gt; Strony. Po zapisaniu wykonamy test i hub zapamięta adres tej strony:
			<code><?php echo esc_html( rest_url( Config::$rest_ns . '/' ) ); ?></code></p>
		<?php
		submit_button( 'Zapisz i połącz' );
		echo '</form>';
	}

	public static function save_key(): void {
		Ui::check( 'save_key' );
		$parsed = Connection::parse_key( (string) wp_unslash( $_POST['key'] ?? '' ) );
		if ( is_wp_error( $parsed ) ) {
			Ui::notice( 'error', esc_html( $parsed->get_error_message() ) );
			Ui::redirect( 'connection' );
		}
		Connection::save( $parsed );
		$r = Connection::ping();
		if ( is_wp_error( $r ) ) {
			Ui::notice( 'warning', 'Klucz zapisany, ale hub nie odpowiada: ' . esc_html( $r->get_error_message() ) );
		} else {
			Ui::notice( 'success', sprintf( 'Połączono z hubem "%s".', esc_html( $r['hub'] ?? '' ) ) );
		}
		Ui::redirect( 'connection' );
	}

	public static function conn_action(): void {
		Ui::check( 'conn_action' );
		$do = sanitize_key( wp_unslash( $_GET['do'] ?? '' ) );
		if ( 'disconnect' === $do ) {
			Connection::clear();
			Ui::notice( 'success', 'Rozłączono z hubem.' );
		} else {
			$r = Connection::ping();
			if ( is_wp_error( $r ) ) {
				Ui::notice( 'error', 'Test nieudany: ' . esc_html( $r->get_error_message() ) );
			} else {
				Ui::notice( 'success', sprintf( 'Połączenie działa. Hub: "%s".', esc_html( $r['hub'] ?? '' ) ) );
			}
		}
		Ui::redirect( 'connection' );
	}
}
