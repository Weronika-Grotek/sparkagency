<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/** Wspólne elementy panelu: powiadomienia, zakładki, ustawienia, log. */
final class Ui {
	public static function init(): void {
		add_action( 'admin_post_' . Config::key( 'save_settings' ), array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_' . Config::key( 'clear_log' ), array( __CLASS__, 'clear_log' ) );
		add_action( 'admin_post_' . Config::key( 'run_queue' ), array( __CLASS__, 'run_queue' ) );
	}

	public static function notice( string $type, string $msg ): void {
		$k   = Config::key( 'notice_' . get_current_user_id() );
		$all = get_transient( $k );
		$all = is_array( $all ) ? $all : array();
		$all[] = array( $type, $msg );
		set_transient( $k, $all, 120 );
	}

	public static function render_notices(): void {
		$k   = Config::key( 'notice_' . get_current_user_id() );
		$all = get_transient( $k );
		if ( ! is_array( $all ) ) {
			return;
		}
		delete_transient( $k );
		foreach ( $all as $n ) {
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $n[0] ), wp_kses_post( $n[1] ) );
		}
	}

	public static function page_url( string $tab ): string {
		return add_query_arg( array( 'page' => Config::$page, 'tab' => $tab ), admin_url( 'admin.php' ) );
	}

	public static function redirect( string $tab ): void {
		wp_safe_redirect( self::page_url( $tab ) );
		exit;
	}

	public static function action_url( string $action, array $args = array() ): string {
		return wp_nonce_url(
			add_query_arg( array_merge( array( 'action' => Config::key( $action ) ), $args ), admin_url( 'admin-post.php' ) ),
			Config::key( $action )
		);
	}

	public static function check( string $action ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Brak uprawnień.', 403 );
		}
		check_admin_referer( Config::key( $action ) );
	}

	public static function form_open( string $action ): void {
		printf( '<form method="post" action="%s">', esc_url( admin_url( 'admin-post.php' ) ) );
		printf( '<input type="hidden" name="action" value="%s">', esc_attr( Config::key( $action ) ) );
		wp_nonce_field( Config::key( $action ) );
	}

	public static function tabs( array $tabs, string $current ): void {
		echo '<nav class="nav-tab-wrapper" style="margin-bottom:16px">';
		foreach ( $tabs as $slug => $label ) {
			printf(
				'<a href="%s" class="nav-tab%s">%s</a>',
				esc_url( self::page_url( $slug ) ),
				$slug === $current ? ' nav-tab-active' : '',
				esc_html( $label )
			);
		}
		echo '</nav>';
	}

	public static function render_settings(): void {
		$s     = Config::settings();
		$pts   = get_post_types( array( 'show_ui' => true ), 'objects' );
		$taxes = get_taxonomies( array( 'show_ui' => true ), 'objects' );
		self::form_open( 'save_settings' );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="cs-pt">Typ treści</label></th>
				<td>
					<select id="cs-pt" name="post_type">
						<?php foreach ( $pts as $pt ) : ?>
							<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $s['post_type'], $pt->name ); ?>><?php echo esc_html( $pt->labels->name . ' (' . $pt->name . ')' ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( ! post_type_exists( $s['post_type'] ) ) : ?>
						<p style="color:#d63638">Zapisany typ "<?php echo esc_html( $s['post_type'] ); ?>" nie istnieje na tej stronie.</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cs-tax">Taksonomia marek</label></th>
				<td>
					<select id="cs-tax" name="taxonomy">
						<?php foreach ( $taxes as $tx ) : ?>
							<option value="<?php echo esc_attr( $tx->name ); ?>" <?php selected( $s['taxonomy'], $tx->name ); ?>><?php echo esc_html( $tx->labels->name . ' (' . $tx->name . ')' ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">Markę łączone są po slugu - slug musi być identyczny na wszystkich stronach.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cs-meta">Pola meta do synchronizacji</label></th>
				<td>
					<input id="cs-meta" type="text" class="large-text code" name="meta_keys" value="<?php echo esc_attr( $s['meta_keys'] ); ?>" placeholder="client_name, project_year, _yoast_wpseo_metadesc">
					<p class="description">Klucze po przecinku. Działa w obie strony jako biała lista: wysyłamy tylko te pola i przyjmujemy tylko te pola.</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Autor importowanych wpisów</th>
				<td>
					<label style="display:block;margin-bottom:4px"><input type="radio" name="author_mode" value="match" <?php checked( $s['author_mode'], 'match' ); ?>> Autor oryginału, jeśli na tej stronie istnieje konto z tym samym e-mailem lub loginem; w przeciwnym razie użytkownik poniżej</label>
					<label style="display:block;margin-bottom:8px"><input type="radio" name="author_mode" value="fixed" <?php checked( $s['author_mode'], 'fixed' ); ?>> Zawsze użytkownik poniżej</label>
					<?php
					wp_dropdown_users(
						array(
							'name'              => 'author',
							'id'                => 'cs-author',
							'selected'          => (int) $s['author'],
							'show_option_none'  => '- pierwszy administrator -',
							'option_none_value' => 0,
							'role__in'          => array( 'administrator', 'editor', 'author' ),
						)
					);
					?>
					<p style="margin-top:10px"><label>Nigdy nie przypisuj kont (loginy po przecinku):<br>
						<input type="text" class="regular-text" name="author_exclude" value="<?php echo esc_attr( $s['author_exclude'] ); ?>" placeholder="agencja, serwis"></label></p>
					<p class="description">Konto agencji istnieje zwykle na każdej stronie z tym samym loginem, więc bez wykluczenia dopasowanie przypisałoby mu wpis. Istniejące kopie poprawisz w zakładce Synchronizacja.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cs-delay">Opóźnienie wysyłki (s)</label></th>
				<td>
					<input id="cs-delay" type="number" min="0" max="600" name="dispatch_delay" value="<?php echo esc_attr( (string) $s['dispatch_delay'] ); ?>" class="small-text">
					<p class="description">Czas od publikacji do wysyłki. Pozwala edytorowi blokowemu zapisać meta boxy (np. ACF) zanim zbudujemy paczkę.</p>
				</td>
			</tr>
		</table>
		<p><strong>Tryb lokalny:</strong> <?php echo Local::is_active() ? 'aktywny - połączenia do hostów .test / localhost / 127.0.0.1 nie weryfikują SSL' : 'wyłączony'; ?>.
			<span class="description">Wymuszenie: <code>define( 'TEAM_SYNC_LOCAL', true );</code> w wp-config.php.</span></p>
		<?php
		submit_button( 'Zapisz ustawienia' );
		echo '</form>';
	}

	public static function save_settings(): void {
		self::check( 'save_settings' );
		$keys = explode( ',', (string) wp_unslash( $_POST['meta_keys'] ?? '' ) );
		$keys = array_filter(
			array_map(
				function ( $k ) {
					return preg_replace( '/[^A-Za-z0-9_\-]/', '', trim( $k ) );
				},
				$keys
			)
		);
		$settings = array(
			'post_type'      => sanitize_key( wp_unslash( $_POST['post_type'] ?? '' ) ),
			'taxonomy'       => sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) ),
			'meta_keys'      => implode( ', ', array_unique( $keys ) ),
			'author'         => absint( $_POST['author'] ?? 0 ),
			'author_exclude' => implode( ', ', array_filter( array_map( 'sanitize_user', array_map( 'trim', explode( ',', (string) wp_unslash( $_POST['author_exclude'] ?? '' ) ) ) ) ) ),
			'author_mode'    => 'fixed' === sanitize_key( wp_unslash( $_POST['author_mode'] ?? '' ) ) ? 'fixed' : 'match',
			'dispatch_delay' => min( 600, absint( $_POST['dispatch_delay'] ?? 20 ) ),
		);
		update_option( Config::key( 'settings' ), $settings, false );
		self::notice( 'success', 'Zapisano ustawienia.' );
		self::redirect( 'settings' );
	}

	public static function render_log(): void {
		$colors = array( 'success' => '#00a32a', 'info' => '#2271b1', 'warning' => '#dba617', 'error' => '#d63638' );
		printf(
			'<p>Zadania w kolejce: <strong>%d</strong> &nbsp; <a class="button" href="%s">Przetwórz kolejkę teraz</a> <a class="button" href="%s" onclick="return confirm(\'Wyczyścić log?\')">Wyczyść log</a></p>',
			(int) Queue::pending(),
			esc_url( self::action_url( 'run_queue' ) ),
			esc_url( self::action_url( 'clear_log' ) )
		);
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			echo '<p class="description">DISABLE_WP_CRON jest włączone - upewnij się, że systemowy cron wywołuje wp-cron.php co minutę.</p>';
		}

		$log = Logger::all();
		if ( ! $log ) {
			echo '<p>Log jest pusty.</p>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr><th style="width:140px">Czas</th><th style="width:80px">Poziom</th><th>Komunikat</th><th>Szczegóły</th></tr></thead><tbody>';
		foreach ( $log as $e ) {
			printf(
				'<tr><td>%s</td><td><strong style="color:%s">%s</strong></td><td>%s</td><td>%s</td></tr>',
				esc_html( wp_date( 'Y-m-d H:i:s', (int) $e['t'] ) ),
				esc_attr( $colors[ $e['l'] ] ?? '#50575e' ),
				esc_html( $e['l'] ),
				esc_html( $e['m'] ),
				$e['c'] ? '<code style="font-size:11px;word-break:break-all">' . esc_html( wp_json_encode( $e['c'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) . '</code>' : ''
			);
		}
		echo '</tbody></table>';
	}

	public static function clear_log(): void {
		self::check( 'clear_log' );
		Logger::clear();
		self::notice( 'success', 'Wyczyszczono log.' );
		self::redirect( 'log' );
	}

	public static function run_queue(): void {
		self::check( 'run_queue' );
		$n = Queue::run_now();
		self::notice( 'success', sprintf( 'Przetworzono zadań: %d. Wyniki w logu.', $n ) );
		self::redirect( 'log' );
	}
}
