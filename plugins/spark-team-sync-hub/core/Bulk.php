<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Wymuszona synchronizacja: masowa wysyłka istniejących zespoły (zakładka "Synchronizacja"
 * i akcja masowa na liście wpisów) oraz zmiana autora zaimportowanych kopii.
 */
final class Bulk {
	const BATCH = 25;

	public static function init(): void {
		add_action( 'admin_post_' . Config::key( 'bulk_sync' ), array( __CLASS__, 'handle_form' ) );
		add_action( 'admin_post_' . Config::key( 'reassign_author' ), array( __CLASS__, 'handle_reassign' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_list_actions' ) );
		add_action( 'admin_notices', array( __CLASS__, 'list_notice' ) );
	}

	/** Na hubie można rozsyłać także kopie przechowywane na hubie (np. żeby zasilić nową stronę). */
	private static function can_include_imported(): bool {
		return 'hub' === Config::$role;
	}

	/* ---------- Kolejka ---------- */

	public static function start( bool $force, int $term_id, bool $include_imported ): void {
		Queue::push(
			array(
				'type'             => 'bulk',
				'page'             => 1,
				'force'            => $force,
				'term'             => $term_id,
				'include_imported' => $include_imported && self::can_include_imported(),
				'queued'           => 0,
			)
		);
		Queue::kick();
	}

	private static function query_args( int $term_id, bool $include_imported ): array {
		$args = array(
			'post_type'        => Config::post_type(),
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'suppress_filters' => true,
			'tax_query'        => array(
				$term_id
					? array( 'taxonomy' => Config::taxonomy(), 'terms' => array( $term_id ) )
					: array( 'taxonomy' => Config::taxonomy(), 'operator' => 'EXISTS' ),
			),
		);
		if ( ! $include_imported ) {
			$args['meta_query'] = array( array( 'key' => Config::META_SOURCE, 'compare' => 'NOT EXISTS' ) );
		}
		return $args;
	}

	public static function count( int $term_id, bool $include_imported ): int {
		$q = new \WP_Query( self::query_args( $term_id, $include_imported ) + array( 'posts_per_page' => 1 ) );
		return (int) $q->found_posts;
	}

	/** Jedna paczka 25 wpisów; kolejna paczka trafia do kolejki jako nowe zadanie. */
	public static function run_batch( array $job ) {
		$page  = max( 1, (int) ( $job['page'] ?? 1 ) );
		$force = ! empty( $job['force'] );
		$ids   = get_posts(
			self::query_args( (int) ( $job['term'] ?? 0 ), ! empty( $job['include_imported'] ) )
			+ array( 'posts_per_page' => self::BATCH, 'paged' => $page, 'no_found_rows' => true )
		);

		$failed = 0;
		foreach ( $ids as $id ) {
			if ( ! Publisher::dispatch( (int) $id, 0, $force, true ) ) {
				$failed++;
			}
		}
		if ( $failed ) {
			// Paczka zostanie powtórzona; wpisy już wysłane odbiorcy pominą.
			return new \WP_Error( 'team_sync_queue', sprintf( 'Nie zakolejkowano %d wpisów z paczki.', $failed ) );
		}
		$queued = (int) ( $job['queued'] ?? 0 ) + count( $ids );

		if ( count( $ids ) === self::BATCH ) {
			$next = Queue::push(
				array(
					'type'             => 'bulk',
					'page'             => $page + 1,
					'force'            => $force,
					'term'             => (int) ( $job['term'] ?? 0 ),
					'include_imported' => ! empty( $job['include_imported'] ),
					'queued'           => $queued,
				)
			);
			if ( ! $next ) {
				return new \WP_Error( 'team_sync_queue', 'Nie zakolejkowano kolejnej paczki.' );
			}
		} else {
			Logger::add(
				'success',
				sprintf( 'Wymuszona synchronizacja: zakolejkowano %d wpisów (%s).', $queued, $force ? 'nadpisywanie kopii' : 'tylko brakujące' ),
				array( 'term' => (int) ( $job['term'] ?? 0 ) )
			);
		}
		Queue::kick();
		return true;
	}

	/* ---------- Zakładka "Synchronizacja" ---------- */

	public static function render_tab(): void {
		$tax   = Config::taxonomy();
		$terms = taxonomy_exists( $tax ) ? get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) ) : array();
		$terms = is_wp_error( $terms ) ? array() : $terms;
		$own   = self::count( 0, false );
		?>
		<h2>Wymuszona synchronizacja</h2>
		<p>Wysyła ponownie opublikowane zespoły z przypisaną marką (<?php echo (int) $own; ?> wpisów utworzonych na tej stronie).
			Wpisy sprzed instalacji wtyczki też zostaną wysłane. Jeśli na stronie docelowej istnieje już wpis o tym samym slugu, zostanie z nim połączony zamiast tworzyć duplikat.</p>
		<?php Ui::form_open( 'bulk_sync' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">Marka</th>
				<td>
					<select name="term">
						<option value="0">Wszystkie marki</option>
						<?php foreach ( $terms as $t ) : ?>
							<option value="<?php echo (int) $t->term_id; ?>"><?php echo esc_html( $t->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">Tryb</th>
				<td>
					<label style="display:block;margin-bottom:6px"><input type="radio" name="mode" value="missing" checked> <strong>Tylko brakujące</strong> - strony, które mają już wpis, pominą go. Bezpieczne.</label>
					<label style="display:block"><input type="radio" name="mode" value="force"> <strong>Nadpisz kopie</strong> - tytuł, treść, zajawka, markę, obrazy, pola meta i autor zostaną zastąpione wersją źródłową. Zmiany wprowadzone na stronach docelowych przepadną. Wpisy powstałe lokalnie na stronach docelowych nigdy nie są nadpisywane.</label>
				</td>
			</tr>
			<?php if ( self::can_include_imported() ) : ?>
			<tr>
				<th scope="row">Kopie na hubie</th>
				<td><label><input type="checkbox" name="include_imported" value="1"> Rozsyłaj także wpisy zaimportowane na hub z connectorów (<?php echo (int) ( self::count( 0, true ) - $own ); ?>)</label>
					<p class="description">Przydatne przy podłączaniu nowej strony: dostanie całe archiwum z huba. Strona źródłowa wpisu zostanie pominięta.</p></td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
		submit_button( 'Uruchom synchronizację', 'primary', 'submit', true, array( 'onclick' => "return document.querySelector('input[name=mode]:checked').value!=='force'||confirm('Nadpisać istniejące kopie na stronach docelowych?')" ) );
		echo '</form>';
		echo '<p class="description">Postęp i wyniki w zakładce Log. Pojedyncze wpisy możesz też zaznaczyć na liście zespoły i wybrać akcję masową "Team Sync".</p>';

		echo '<hr><h2>Autor zaimportowanych wpisów</h2>';
		echo '<p>Zmienia autora wszystkich kopii, które ta strona dostała z sieci. Nie dotyka wpisów utworzonych tutaj. Nowe importy dostają autora według ustawienia w zakładce Ustawienia.</p>';
		Ui::form_open( 'reassign_author' );
		wp_dropdown_users(
			array(
				'name'     => 'user',
				'selected' => Config::author_id(),
				'exclude'  => array_filter( array_map( function ( $l ) { $u = get_user_by( 'login', $l ); return $u ? $u->ID : 0; }, Config::excluded_logins() ) ),
				'role__in' => array( 'administrator', 'editor', 'author' ),
			)
		);
		echo ' <label><input type="checkbox" name="match_first" value="1" checked> najpierw dopasuj autora oryginału po e-mailu / loginie</label> ';
		submit_button( 'Zmień autora', 'secondary', 'submit', false );
		echo '</form>';
	}

	public static function handle_form(): void {
		Ui::check( 'bulk_sync' );
		$force   = 'force' === sanitize_key( wp_unslash( $_POST['mode'] ?? '' ) );
		$term_id = absint( $_POST['term'] ?? 0 );
		$incl    = ! empty( $_POST['include_imported'] );
		$n       = self::count( $term_id, $incl && self::can_include_imported() );
		self::start( $force, $term_id, $incl );
		Ui::notice( 'success', sprintf( 'Uruchomiono synchronizację %d wpisów w tle. Postęp w zakładce Log.', $n ) );
		Ui::redirect( 'sync' );
	}

	public static function handle_reassign(): void {
		Ui::check( 'reassign_author' );
		$fallback = absint( $_POST['user'] ?? 0 );
		if ( ! $fallback || ! get_userdata( $fallback ) ) {
			Ui::notice( 'error', 'Wybierz użytkownika.' );
			Ui::redirect( 'sync' );
		}
		$match = ! empty( $_POST['match_first'] );
		$ids   = get_posts(
			array(
				'post_type'        => Config::post_type(),
				'post_status'      => 'any',
				'meta_key'         => Config::META_SOURCE,
				'fields'           => 'ids',
				'posts_per_page'   => -1,
				'suppress_filters' => true,
			)
		);
		$n = 0;
		foreach ( $ids as $id ) {
			$author = $fallback;
			if ( $match ) {
				$src = get_post_meta( $id, Config::META_SOURCE, true );
				$a   = ( is_array( $src ) && ! empty( $src['author'] ) ) ? $src['author'] : array();
				$u   = ! empty( $a['email'] ) ? get_user_by( 'email', $a['email'] ) : null;
				$u   = $u ?: ( ! empty( $a['login'] ) ? get_user_by( 'login', $a['login'] ) : null );
				if ( $u && user_can( $u, 'edit_posts' ) && ! in_array( strtolower( $u->user_login ), Config::excluded_logins(), true ) ) {
					$author = (int) $u->ID;
				}
			}
			if ( (int) get_post_field( 'post_author', $id ) !== $author ) {
				global $wpdb;
				// Bezpośrednio w bazie: bez wyzwalania hooków zapisu i bez zmiany daty modyfikacji.
				$wpdb->update( $wpdb->posts, array( 'post_author' => $author ), array( 'ID' => $id ) );
				clean_post_cache( $id );
				$n++;
			}
		}
		Logger::add( 'success', sprintf( 'Zmieniono autora %d zaimportowanych wpisów.', $n ) );
		Ui::notice( 'success', sprintf( 'Zmieniono autora %d z %d zaimportowanych wpisów.', $n, count( $ids ) ) );
		Ui::redirect( 'sync' );
	}

	/* ---------- Akcja masowa na liście wpisów ---------- */

	public static function register_list_actions(): void {
		$pt = Config::post_type();
		add_filter( "bulk_actions-edit-{$pt}", array( __CLASS__, 'list_actions' ) );
		add_filter( "handle_bulk_actions-edit-{$pt}", array( __CLASS__, 'handle_list_action' ), 10, 3 );
	}

	public static function list_actions( $actions ) {
		$actions['team_sync_missing'] = 'Team Sync: wyślij do brakujących stron';
		$actions['team_sync_force']   = 'Team Sync: wyślij i nadpisz kopie';
		return $actions;
	}

	public static function handle_list_action( $redirect, $action, $ids ) {
		if ( ! in_array( $action, array( 'team_sync_missing', 'team_sync_force' ), true ) ) {
			return $redirect;
		}
		$force = 'team_sync_force' === $action;
		$ok    = 0;
		$skip  = 0;
		foreach ( (array) $ids as $id ) {
			$id       = (int) $id;
			$imported = (bool) get_post_meta( $id, Config::META_SOURCE, true );
			$terms    = wp_get_object_terms( $id, Config::taxonomy(), array( 'fields' => 'ids' ) );
			if ( ! current_user_can( 'edit_post', $id ) || 'publish' !== get_post_status( $id )
				|| is_wp_error( $terms ) || ! $terms || ( $imported && ! self::can_include_imported() ) ) {
				$skip++;
				continue;
			}
			Publisher::dispatch( $id, 0, $force, true );
			$ok++;
		}
		if ( $ok ) {
			Logger::add( 'info', sprintf( 'Akcja masowa: zakolejkowano %d wpisów (%s).', $ok, $force ? 'nadpisywanie kopii' : 'tylko brakujące' ) );
			Queue::kick();
		}
		return add_query_arg( array( 'team_sync_ok' => $ok, 'team_sync_skip' => $skip ), $redirect );
	}

	public static function list_notice(): void {
		if ( ! isset( $_GET['team_sync_ok'] ) ) {
			return;
		}
		$ok   = absint( $_GET['team_sync_ok'] );
		$skip = absint( $_GET['team_sync_skip'] ?? 0 );
		printf(
			'<div class="notice notice-success is-dismissible"><p>Team Sync: zakolejkowano %d wpisów.%s</p></div>',
			$ok,
			$skip ? esc_html( sprintf( ' Pominięto %d (nieopublikowane, bez marki albo kopie z innej strony).', $skip ) ) : ''
		);
	}
}
