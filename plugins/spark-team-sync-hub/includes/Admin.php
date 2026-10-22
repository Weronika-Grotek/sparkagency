<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Auth;
use TeamSyncHub\Core\Config;
use TeamSyncHub\Core\Bulk;
use TeamSyncHub\Core\Ui;

defined( 'ABSPATH' ) || exit;

final class Admin {
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_' . Config::key( 'add_site' ), array( __CLASS__, 'add_site' ) );
		add_action( 'admin_post_' . Config::key( 'site_action' ), array( __CLASS__, 'site_action' ) );
		add_action( 'admin_post_' . Config::key( 'save_mapping' ), array( __CLASS__, 'save_mapping' ) );
	}

	public static function menu(): void {
		add_menu_page( 'Team Sync - Hub', 'Team Sync Hub', 'manage_options', Config::$page, array( __CLASS__, 'render' ), 'dashicons-networking', 81 );
	}

	public static function render(): void {
		$tab  = sanitize_key( $_GET['tab'] ?? 'sites' );
		$tabs = array(
			'sites'    => 'Strony',
			'mapping'  => 'Mapowanie marek',
			'sync'     => 'Synchronizacja',
			'settings' => 'Ustawienia',
			'log'      => 'Log',
		);
		$tab = isset( $tabs[ $tab ] ) ? $tab : 'sites';
		echo '<div class="wrap"><h1>Team Sync - Hub</h1>';
		Ui::render_notices();
		Ui::tabs( $tabs, $tab );
		switch ( $tab ) {
			case 'mapping':
				self::render_mapping();
				break;
			case 'sync':
				Bulk::render_tab();
				break;
			case 'settings':
				Ui::render_settings();
				break;
			case 'log':
				Ui::render_log();
				break;
			default:
				self::render_sites();
		}
		echo '</div>';
	}

	private static function render_sites(): void {
		echo '<h2>Dodaj stronę</h2>';
		Ui::form_open( 'add_site' );
		?>
		<div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
			<p><label>Nazwa<br><input type="text" name="name" required class="regular-text" placeholder="Klient X"></label></p>
			<p><label>Adres strony<br><input type="url" name="url" required class="regular-text" placeholder="https://klient-x.pl"></label></p>
			<p><?php submit_button( 'Dodaj i wygeneruj klucz', 'primary', 'submit', false ); ?></p>
		</div>
		</form>
		<?php
		$sites = Sites::all();
		echo '<h2>Podłączone strony</h2>';
		if ( ! $sites ) {
			echo '<p>Brak stron. Dodaj pierwszą powyżej, a wygenerowany klucz wklej w connectorze.</p>';
			return;
		}
		?>
		<table class="widefat striped">
			<thead><tr><th>Strona</th><th>Endpoint</th><th>Markę</th><th>Ostatni kontakt</th><th style="width:22%">Klucz połączenia</th><th>Akcje</th></tr></thead>
			<tbody>
			<?php foreach ( $sites as $s ) : ?>
				<?php $branches = Router::branches_for_site( $s['id'] ); ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $s['name'] ); ?></strong>
						<?php echo empty( $s['active'] ) ? ' <span style="color:#d63638">(wyłączona)</span>' : ''; ?><br>
						<a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $s['url'] ); ?></a><br>
						<code><?php echo esc_html( $s['id'] ); ?></code>
					</td>
					<td><code style="font-size:11px;word-break:break-all"><?php echo esc_html( $s['endpoint'] ); ?></code></td>
					<td><?php echo $branches ? esc_html( implode( ', ', $branches ) ) : '<em>brak</em>'; ?></td>
					<td><?php echo $s['last_seen'] ? esc_html( wp_date( 'Y-m-d H:i', (int) $s['last_seen'] ) ) : 'nigdy'; ?></td>
					<td>
						<details><summary>Pokaż klucz</summary>
							<textarea readonly rows="4" class="large-text code" style="font-size:11px" onclick="this.select()"><?php echo esc_textarea( Sites::connection_key( $s ) ); ?></textarea>
						</details>
					</td>
					<td>
						<?php
						self::link( $s, 'test', 'Testuj' );
						echo ' | ';
						self::link( $s, 'toggle', empty( $s['active'] ) ? 'Włącz' : 'Wyłącz' );
						echo '<br>';
						self::link( $s, 'regen', 'Nowy klucz', 'Stary klucz przestanie działać. Kontynuować?' );
						echo ' | ';
						self::link( $s, 'delete', 'Usuń', 'Usunąć stronę i jej mapowania?', true );
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	private static function link( array $s, string $do, string $label, string $confirm = '', bool $danger = false ): void {
		printf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( Ui::action_url( 'site_action', array( 'do' => $do, 'site' => $s['id'] ) ) ),
			$confirm ? ' onclick="return confirm(\'' . esc_js( $confirm ) . '\')"' : '',
			$danger ? ' style="color:#d63638"' : '',
			esc_html( $label )
		);
	}

	private static function render_mapping(): void {
		$tax = Config::taxonomy();
		if ( ! taxonomy_exists( $tax ) ) {
			printf( '<p>Taksonomia "%s" nie istnieje. Ustaw ją w zakładce Ustawienia.</p>', esc_html( $tax ) );
			return;
		}
		$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
		$sites = Sites::all();
		if ( is_wp_error( $terms ) || ! $terms ) {
			echo '<p>Brak marek.</p>';
			return;
		}
		echo '<p>Zaznacz, które strony dostają zespoły z danej marki. Wpis z kilkoma markami trafia do sumy stron. Strona źródłowa nigdy nie dostaje własnego wpisu z powrotem.</p>';
		Ui::form_open( 'save_mapping' );
		echo '<div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th>Marka</th><th style="text-align:center">Hub</th>';
		foreach ( $sites as $s ) {
			printf( '<th style="text-align:center">%s</th>', esc_html( $s['name'] ) );
		}
		echo '</tr></thead><tbody>';
		foreach ( $terms as $t ) {
			$sel = Router::get_targets_for_term( (int) $t->term_id );
			printf( '<tr><td><strong>%s</strong> <code>%s</code><input type="hidden" name="terms[]" value="%d"></td>', esc_html( $t->name ), esc_html( $t->slug ), (int) $t->term_id );
			foreach ( array_merge( array( Router::HUB ), array_keys( $sites ) ) as $id ) {
				printf(
					'<td style="text-align:center"><input type="checkbox" name="map[%d][]" value="%s" %s></td>',
					(int) $t->term_id,
					esc_attr( $id ),
					checked( in_array( $id, $sel, true ), true, false )
				);
			}
			echo '</tr>';
		}
		echo '</tbody></table></div>';
		submit_button( 'Zapisz mapowanie' );
		echo '</form>';
	}

	public static function add_site(): void {
		Ui::check( 'add_site' );
		$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$url  = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
		if ( '' === $name || '' === $url ) {
			Ui::notice( 'error', 'Podaj nazwę i poprawny adres strony.' );
			Ui::redirect( 'sites' );
		}
		$site = Sites::add( $name, $url );
		Ui::notice( 'success', sprintf( 'Dodano "%s". Skopiuj klucz z tabeli i wklej go w connectorze (Team Sync > Połączenie), potem przypisz markę w zakładce Mapowanie.', esc_html( $site['name'] ) ) );
		Ui::redirect( 'sites' );
	}

	public static function site_action(): void {
		Ui::check( 'site_action' );
		$id = sanitize_key( wp_unslash( $_GET['site'] ?? '' ) );
		$do = sanitize_key( wp_unslash( $_GET['do'] ?? '' ) );
		$s  = Sites::get( $id );
		if ( ! $s ) {
			Ui::notice( 'error', 'Nie znaleziono strony.' );
			Ui::redirect( 'sites' );
		}
		switch ( $do ) {
			case 'test':
				$r = Auth::send( $s['endpoint'] . 'ping', $s['id'], $s['secret'], array( 'hub' => get_bloginfo( 'name' ) ), 15 );
				if ( is_wp_error( $r ) ) {
					Ui::notice( 'error', sprintf( 'Test "%s" nieudany: %s', esc_html( $s['name'] ), esc_html( $r->get_error_message() ) ) );
				} else {
					Sites::update( $id, array( 'last_seen' => time() ) );
					Ui::notice( 'success', sprintf( 'Połączenie z "%s" działa.', esc_html( $s['name'] ) ) );
				}
				break;
			case 'regen':
				Sites::update( $id, array( 'secret' => Auth::generate_secret() ) );
				Ui::notice( 'warning', sprintf( 'Nowy klucz dla "%s". Wklej go w connectorze - stary już nie działa.', esc_html( $s['name'] ) ) );
				break;
			case 'toggle':
				Sites::update( $id, array( 'active' => empty( $s['active'] ) ) );
				Ui::notice( 'success', 'Zmieniono status strony.' );
				break;
			case 'delete':
				Sites::delete( $id );
				Ui::notice( 'success', sprintf( 'Usunięto "%s".', esc_html( $s['name'] ) ) );
				break;
		}
		Ui::redirect( 'sites' );
	}

	public static function save_mapping(): void {
		Ui::check( 'save_mapping' );
		$terms = isset( $_POST['terms'] ) ? array_map( 'absint', (array) $_POST['terms'] ) : array();
		$map   = isset( $_POST['map'] ) ? (array) wp_unslash( $_POST['map'] ) : array();
		foreach ( $terms as $tid ) {
			Router::set_targets_for_term( $tid, isset( $map[ $tid ] ) ? (array) $map[ $tid ] : array() );
		}
		Ui::notice( 'success', 'Zapisano mapowanie marek.' );
		Ui::redirect( 'mapping' );
	}
}
