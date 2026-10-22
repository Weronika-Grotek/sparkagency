<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

final class Payload {
	const VERSION = 1;

	public static function uuid( int $post_id ): string {
		$uuid = (string) get_post_meta( $post_id, Config::META_UUID, true );
		if ( ! $uuid ) {
			$uuid = wp_generate_uuid4();
			update_post_meta( $post_id, Config::META_UUID, $uuid );
		}
		return $uuid;
	}

	public static function build( int $post_id, bool $force = false ): array {
		$post   = get_post( $post_id );
		$source = get_post_meta( $post_id, Config::META_SOURCE, true );
		$source = is_array( $source ) ? $source : array();

		// Kopia przechowywana na hubie zachowuje dane oryginału (strona źródłowa i autor).
		if ( $source ) {
			$origin = array(
				'site_url'  => $source['site_url'] ?? '',
				'site_name' => $source['site_name'] ?? '',
				'post_id'   => $source['post_id'] ?? 0,
				'permalink' => $source['permalink'] ?? '',
			);
			$author = $source['author'] ?? array();
		} else {
			$origin = array(
				'site_url'  => home_url( '/' ),
				'site_name' => get_bloginfo( 'name' ),
				'post_id'   => $post_id,
				'permalink' => get_permalink( $post_id ),
			);
			$user   = get_userdata( (int) $post->post_author );
			$author = $user ? array(
				'login' => $user->user_login,
				'email' => $user->user_email,
				'name'  => $user->display_name,
			) : array();
		}

		$branches = array();
		$terms    = wp_get_object_terms( $post_id, Config::taxonomy() );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$branches[] = array( 'slug' => $t->slug, 'name' => $t->name );
			}
		}

		$featured = null;
		$thumb    = (int) get_post_thumbnail_id( $post_id );
		if ( $thumb ) {
			$featured = self::attachment( $thumb );
		}

		// Obrazy z biblioteki mediów osadzone w treści (klasa wp-image-ID: image, cover, media-text).
		$images = array();
		if ( preg_match_all( '/\bwp-image-(\d+)\b/', $post->post_content, $m ) ) {
			foreach ( array_unique( $m[1] ) as $id ) {
				$a = self::attachment( (int) $id );
				if ( $a ) {
					$images[ (string) $id ] = $a;
				}
			}
		}

		$meta = array();
		foreach ( Config::meta_keys() as $k ) {
			if ( metadata_exists( 'post', $post_id, $k ) ) {
				$meta[ $k ] = get_post_meta( $post_id, $k, true );
			}
		}

		return array(
			'v'        => self::VERSION,
			'uuid'     => self::uuid( $post_id ),
			'origin'     => $origin,
			'author'     => $author,
			'assets_url' => home_url( '/' ),
			'force'      => $force,
			'post'     => array(
				'title'    => $post->post_title,
				'content'  => $post->post_content,
				'excerpt'  => $post->post_excerpt,
				'slug'     => $post->post_name,
				'date_gmt' => $post->post_date_gmt,
			),
			'brands' => $branches,
			'featured' => $featured,
			'images'   => $images,
			'meta'     => $meta,
		);
	}

	private static function attachment( int $id ): ?array {
		$url = wp_get_attachment_url( $id );
		if ( ! $url ) {
			return null;
		}
		return array(
			'id'      => $id,
			'url'     => $url,
			'alt'     => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			'title'   => get_the_title( $id ),
			'caption' => (string) wp_get_attachment_caption( $id ),
		);
	}

	/** @return true|\WP_Error */
	public static function validate( $p ) {
		$bad = function ( string $msg ) {
			return new \WP_Error( 'team_sync_payload', $msg, array( 'permanent' => true, 'status' => 400 ) );
		};
		if ( ! is_array( $p ) ) {
			return $bad( 'Brak danych wpisu.' );
		}
		if ( empty( $p['uuid'] ) || ! is_string( $p['uuid'] ) || ! wp_is_uuid( $p['uuid'] ) ) {
			return $bad( 'Nieprawidłowy UUID.' );
		}
		if ( empty( $p['post'] ) || ! is_array( $p['post'] ) || ( empty( $p['post']['title'] ) && empty( $p['post']['content'] ) ) ) {
			return $bad( 'Wpis bez tytułu i treści.' );
		}
		if ( ! isset( $p['brands'] ) || ! is_array( $p['brands'] ) ) {
			return $bad( 'Brak marek.' );
		}
		return true;
	}
}
