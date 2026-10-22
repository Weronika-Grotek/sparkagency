<?php
namespace TeamSyncHub\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Tworzy lokalną kopię zespół z payloadu. Po imporcie wpis żyje własnym życiem:
 * nie jest już synchronizowany ani wysyłany dalej.
 */
final class Importer {
	/** Blokuje Publisher w trakcie importu. */
	public static $running = false;

	private static $cache = array();

	public static function find_by_uuid( string $uuid ): int {
		$ids = get_posts(
			array(
				'post_type'        => Config::post_type(),
				'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
				'meta_key'         => Config::META_UUID,
				'meta_value'       => $uuid,
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	/** Wpis sprzed wtyczki (bez UUID) o tym samym slugu - ręcznie skopiowany wcześniej zespół. */
	private static function find_legacy_by_slug( string $slug ): int {
		if ( '' === $slug ) {
			return 0;
		}
		$ids = get_posts(
			array(
				'post_type'        => Config::post_type(),
				'name'             => $slug,
				'post_status'      => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				'meta_query'       => array( array( 'key' => Config::META_UUID, 'compare' => 'NOT EXISTS' ) ),
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	/** Uruchamiane z kolejki - import + log. */
	public static function run_job( array $payload ) {
		$r   = self::import( $payload );
		$ctx = array(
			'uuid'  => $payload['uuid'] ?? '',
			'title' => $payload['post']['title'] ?? '',
			'from'  => $payload['origin']['site_name'] ?? '',
		);
		if ( is_wp_error( $r ) ) {
			if ( 'team_sync_locked' !== $r->get_error_code() ) {
				Logger::add( 'error', 'Import nieudany: ' . $r->get_error_message(), $ctx );
			}
			return $r;
		}
		$ctx['post_id'] = $r['post_id'];
		$messages       = array(
			'created' => array( 'success', 'Opublikowano zaimportowany wpis.' ),
			'updated' => array( 'success', 'Nadpisano istniejącą kopię (wymuszona synchronizacja).' ),
			'linked'  => array( 'info', 'Znaleziono wpis o tym samym slugu sprzed wtyczki - połączono go z siecią, treść bez zmian.' ),
			'exists'  => array( 'info', 'Wpis już istnieje na tej stronie - pominięto.' ),
			'trashed' => array( 'info', 'Wpis jest w koszu na tej stronie - pominięto.' ),
			'local'   => array( 'info', 'Na tej stronie to wpis lokalny, nie kopia - nie nadpisano.' ),
		);
		$msg = $messages[ $r['status'] ] ?? array( 'info', $r['status'] );
		Logger::add( $msg[0], $msg[1], $ctx );
		return true;
	}

	/** @return array{status:string,post_id:int}|\WP_Error */
	public static function import( $p ) {
		$valid = Payload::validate( $p );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$pt  = Config::post_type();
		$tax = Config::taxonomy();
		if ( ! post_type_exists( $pt ) ) {
			return new \WP_Error( 'team_sync_pt', sprintf( 'Typ treści "%s" nie istnieje na tej stronie.', $pt ) );
		}
		if ( ! taxonomy_exists( $tax ) ) {
			return new \WP_Error( 'team_sync_tax', sprintf( 'Taksonomia "%s" nie istnieje na tej stronie.', $tax ) );
		}

		$uuid = $p['uuid'];
		$lock = 'import_' . md5( $uuid );
		if ( ! Lock::acquire( $lock ) ) {
			return new \WP_Error( 'team_sync_locked', 'Import tego wpisu już trwa.', array( 'quiet' => true ) );
		}

		self::$running = true;
		self::$cache   = array();
		try {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$force    = ! empty( $p['force'] );
			$existing = self::find_by_uuid( $uuid );
			if ( $existing ) {
				if ( get_post_meta( $existing, Config::META_INCOMPLETE, true ) ) {
					wp_delete_post( $existing, true ); // Pozostałość po przerwanym imporcie.
					return self::create( $p, $pt, $tax );
				}
				if ( 'trash' === get_post_status( $existing ) ) {
					return array( 'status' => 'trashed', 'post_id' => $existing );
				}
				if ( ! $force ) {
					return array( 'status' => 'exists', 'post_id' => $existing );
				}
				// Nadpisujemy tylko kopie z sieci, nigdy wpisy powstałe na tej stronie.
				if ( ! get_post_meta( $existing, Config::META_SOURCE, true ) ) {
					return array( 'status' => 'local', 'post_id' => $existing );
				}
				return self::update( $existing, $p, $tax );
			}

			$legacy = self::find_legacy_by_slug( sanitize_title( (string) ( $p['post']['slug'] ?? '' ) ) );
			if ( $legacy ) {
				update_post_meta( $legacy, Config::META_UUID, $uuid );
				return array( 'status' => 'linked', 'post_id' => $legacy );
			}

			return self::create( $p, $pt, $tax );
		} finally {
			self::$running = false;
			Lock::release( $lock );
		}
	}

	private static function source_meta( array $p ): array {
		$origin = ( isset( $p['origin'] ) && is_array( $p['origin'] ) ) ? $p['origin'] : array();
		$author = ( isset( $p['author'] ) && is_array( $p['author'] ) ) ? $p['author'] : array();
		return array(
			'site_url'  => esc_url_raw( (string) ( $origin['site_url'] ?? '' ) ),
			'site_name' => sanitize_text_field( (string) ( $origin['site_name'] ?? '' ) ),
			'post_id'   => absint( $origin['post_id'] ?? 0 ),
			'permalink' => esc_url_raw( (string) ( $origin['permalink'] ?? '' ) ),
			'author'    => array(
				'login' => sanitize_user( (string) ( $author['login'] ?? '' ) ),
				'email' => sanitize_email( (string) ( $author['email'] ?? '' ) ),
				'name'  => sanitize_text_field( (string) ( $author['name'] ?? '' ) ),
			),
			'imported'  => time(),
		);
	}

	/**
	 * Autor kopii. Tryb "match": użytkownik o tym samym e-mailu, potem loginie co autor oryginału.
	 * W pozostałych przypadkach domyślny autor z ustawień.
	 */
	public static function resolve_author( array $author ): int {
		if ( 'match' === Config::get( 'author_mode' ) ) {
			$user = null;
			if ( ! empty( $author['email'] ) ) {
				$user = get_user_by( 'email', $author['email'] );
			}
			if ( ! $user && ! empty( $author['login'] ) ) {
				$user = get_user_by( 'login', $author['login'] );
			}
			if ( $user && user_can( $user, 'edit_posts' ) && ! in_array( strtolower( $user->user_login ), Config::excluded_logins(), true ) ) {
				return (int) $user->ID;
			}
		}
		return Config::author_id();
	}

	private static function create( array $p, string $pt, string $tax ) {
		$post   = $p['post'];
		$source = self::source_meta( $p );

		// Szkic - publikujemy dopiero, gdy obrazy są na miejscu.
		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => $pt,
					'post_status'  => 'draft',
					'post_title'   => (string) ( $post['title'] ?? '' ),
					'post_excerpt' => (string) ( $post['excerpt'] ?? '' ),
					'post_name'    => sanitize_title( (string) ( $post['slug'] ?? '' ) ),
					'post_author'  => self::resolve_author( $source['author'] ),
					'meta_input'   => array(
						Config::META_UUID       => $p['uuid'],
						Config::META_SOURCE     => $source,
						Config::META_INCOMPLETE => 1,
					),
				)
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$content = self::apply_content_and_media( $post_id, $p, $tax );

		$update   = array(
			'ID'           => $post_id,
			'post_content' => $content,
			'post_status'  => 'publish',
		);
		$date_gmt = (string) ( $post['date_gmt'] ?? '' );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date_gmt )
			&& '0000-00-00 00:00:00' !== $date_gmt
			&& strtotime( $date_gmt . ' UTC' ) <= time() ) {
			$update['post_date_gmt'] = $date_gmt;
			$update['post_date']     = get_date_from_gmt( $date_gmt );
			$update['edit_date']     = true;
		}
		$res = wp_update_post( wp_slash( $update ), true );
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		delete_post_meta( $post_id, Config::META_INCOMPLETE );
		do_action( 'team_sync_imported', $post_id, $p );
		return array( 'status' => 'created', 'post_id' => (int) $post_id );
	}

	/** Wymuszona synchronizacja: treść, tytuł, zajawka, markę, obrazy, meta i autor ze źródła. Slug, data i status zostają. */
	private static function update( int $post_id, array $p, string $tax ) {
		$post   = $p['post'];
		$source = self::source_meta( $p );

		$content = self::apply_content_and_media( $post_id, $p, $tax );
		$res     = wp_update_post(
			wp_slash(
				array(
					'ID'           => $post_id,
					'post_title'   => (string) ( $post['title'] ?? '' ),
					'post_excerpt' => (string) ( $post['excerpt'] ?? '' ),
					'post_content' => $content,
					'post_author'  => self::resolve_author( $source['author'] ),
				)
			),
			true
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$old              = get_post_meta( $post_id, Config::META_SOURCE, true );
		$source['imported'] = is_array( $old ) && ! empty( $old['imported'] ) ? $old['imported'] : time();
		$source['updated']  = time();
		update_post_meta( $post_id, Config::META_SOURCE, $source );
		do_action( 'team_sync_updated', $post_id, $p );
		return array( 'status' => 'updated', 'post_id' => $post_id );
	}

	/** Markę, miniatura, obrazy z treści i meta. Zwraca treść z podmienionymi URL-ami. */
	private static function apply_content_and_media( int $post_id, array $p, string $tax ): string {
		$term_ids = self::ensure_terms( $p['brands'], $tax );
		wp_set_object_terms( $post_id, $term_ids, $tax, false );

		$hosts = self::allowed_hosts( (string) ( $p['origin']['site_url'] ?? '' ), (string) ( $p['assets_url'] ?? '' ) );

		if ( ! empty( $p['featured'] ) && is_array( $p['featured'] ) ) {
			$thumb = self::sideload( $p['featured'], $post_id, $hosts );
			if ( $thumb ) {
				set_post_thumbnail( $post_id, $thumb );
			}
		} elseif ( ! empty( $p['force'] ) ) {
			delete_post_thumbnail( $post_id );
		}

		$content = (string) ( $p['post']['content'] ?? '' );
		if ( ! empty( $p['images'] ) && is_array( $p['images'] ) ) {
			$map = array();
			foreach ( $p['images'] as $old_id => $img ) {
				if ( ! is_array( $img ) || empty( $img['url'] ) ) {
					continue;
				}
				$new_id = self::sideload( $img, $post_id, $hosts );
				if ( ! $new_id ) {
					continue;
				}
				$map[ (int) $old_id ] = $new_id;
				$content              = self::replace_urls( $content, (string) $img['url'], (string) wp_get_attachment_url( $new_id ) );
			}
			$content = self::remap_ids( $content, $map );
		}

		$allowed = Config::meta_keys();
		if ( $allowed && ! empty( $p['meta'] ) && is_array( $p['meta'] ) ) {
			foreach ( $p['meta'] as $k => $v ) {
				if ( in_array( (string) $k, $allowed, true ) ) {
					update_post_meta( $post_id, (string) $k, wp_slash( $v ) );
				}
			}
		}
		return $content;
	}

	/** Zwraca ID termów dla listy {slug,name}; brakujące tworzy. */
	public static function ensure_terms( array $branches, string $tax ): array {
		$ids = array();
		foreach ( $branches as $b ) {
			if ( ! is_array( $b ) ) {
				continue;
			}
			$slug = sanitize_title( (string) ( $b['slug'] ?? '' ) );
			if ( '' === $slug ) {
				continue;
			}
			$term = get_term_by( 'slug', $slug, $tax );
			if ( $term ) {
				$ids[] = (int) $term->term_id;
				continue;
			}
			$name = sanitize_text_field( (string) ( $b['name'] ?? '' ) );
			$r    = wp_insert_term( '' !== $name ? $name : $slug, $tax, array( 'slug' => $slug ) );
			if ( ! is_wp_error( $r ) ) {
				$ids[] = (int) $r['term_id'];
			} elseif ( $r->get_error_data( 'term_exists' ) ) {
				$ids[] = (int) $r->get_error_data( 'term_exists' );
			}
		}
		return array_values( array_unique( $ids ) );
	}

	private static function allowed_hosts( string ...$urls ): array {
		$hosts = array();
		foreach ( $urls as $u ) {
			$h = strtolower( (string) wp_parse_url( $u, PHP_URL_HOST ) );
			if ( $h ) {
				$hosts[] = $h;
				$hosts[] = 0 === strpos( $h, 'www.' ) ? substr( $h, 4 ) : 'www.' . $h;
			}
		}
		$hosts = (array) apply_filters( 'team_sync_allowed_image_hosts', array_unique( $hosts ), $urls[0] ?? '' );
		return array_map( 'strtolower', $hosts );
	}

	private static function sideload( array $img, int $post_id, array $hosts ): int {
		$url = esc_url_raw( (string) ( $img['url'] ?? '' ) );
		if ( '' === $url ) {
			return 0;
		}
		if ( isset( self::$cache[ $url ] ) ) {
			return self::$cache[ $url ];
		}

		// Ten sam plik pobrany wcześniej (np. przy poprzedniej synchronizacji) - używamy ponownie.
		$found = get_posts(
			array(
				'post_type'        => 'attachment',
				'post_status'      => 'inherit',
				'meta_key'         => '_team_sync_source_url',
				'meta_value'       => $url,
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);
		if ( $found ) {
			return self::$cache[ $url ] = (int) $found[0];
		}

		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		if ( ! in_array( $host, $hosts, true ) ) {
			Logger::add( 'warning', 'Pominięto obraz spoza strony źródłowej.', array( 'url' => $url ) );
			return self::$cache[ $url ] = 0;
		}

		$title = isset( $img['title'] ) ? sanitize_text_field( (string) $img['title'] ) : null;
		$id    = media_sideload_image( $url, $post_id, $title, 'id' );
		if ( is_wp_error( $id ) ) {
			Logger::add( 'warning', 'Nie udało się pobrać obrazu: ' . $id->get_error_message(), array( 'url' => $url ) );
			return self::$cache[ $url ] = 0;
		}

		if ( ! empty( $img['alt'] ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( (string) $img['alt'] ) );
		}
		if ( ! empty( $img['caption'] ) ) {
			wp_update_post( wp_slash( array( 'ID' => $id, 'post_excerpt' => wp_kses_post( (string) $img['caption'] ) ) ) );
		}
		update_post_meta( $id, '_team_sync_source_url', $url );

		return self::$cache[ $url ] = (int) $id;
	}

	/**
	 * Podmienia w treści każdy wariant obrazu (oryginał, -scaled, -WxH) na nowy URL.
	 */
	public static function replace_urls( string $content, string $old_url, string $new_url ): string {
		if ( '' === $new_url ) {
			return $content;
		}
		$path = (string) wp_parse_url( $old_url, PHP_URL_PATH );
		$info = pathinfo( $path );
		if ( empty( $info['filename'] ) || empty( $info['extension'] ) ) {
			return $content;
		}
		$base    = preg_replace( '/-scaled$/', '', $info['filename'] );
		$dir_url = substr( $old_url, 0, strrpos( $old_url, '/' ) + 1 );
		$dir_url = preg_replace( '#^https?:#i', '', $dir_url );

		$pattern = '#(?:https?:)?' . preg_quote( $dir_url . $base, '#' )
			. '(?:-scaled)?(?:-\d+x\d+)?\.' . preg_quote( $info['extension'], '#' ) . '#i';

		$out = preg_replace( $pattern, $new_url, $content );
		return null === $out ? $content : $out;
	}

	/** Przepina ID załączników: klasa wp-image-ID oraz atrybuty bloków image/cover/media-text. */
	public static function remap_ids( string $content, array $map ): string {
		if ( ! $map ) {
			return $content;
		}
		$content = preg_replace_callback(
			'/\bwp-image-(\d+)\b/',
			function ( $m ) use ( $map ) {
				$old = (int) $m[1];
				return isset( $map[ $old ] ) ? 'wp-image-' . $map[ $old ] : $m[0];
			},
			$content
		);

		$out = preg_replace_callback(
			'/<!--\s+wp:(image|cover|media-text)\s+(\{.*?\})\s+(\/?)-->/s',
			function ( $m ) use ( $map ) {
				$attrs = json_decode( $m[2], true );
				if ( ! is_array( $attrs ) ) {
					return $m[0];
				}
				$changed = false;
				foreach ( array( 'id', 'mediaId' ) as $k ) {
					if ( isset( $attrs[ $k ] ) && isset( $map[ (int) $attrs[ $k ] ] ) ) {
						$attrs[ $k ] = $map[ (int) $attrs[ $k ] ];
						$changed     = true;
					}
				}
				if ( ! $changed ) {
					return $m[0];
				}
				return '<!-- wp:' . $m[1] . ' ' . serialize_block_attributes( $attrs ) . ' ' . $m[3] . '-->';
			},
			$content
		);
		return null === $out ? $content : $out;
	}
}
