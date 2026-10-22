<?php
namespace TeamSyncConnector\Core;

defined( 'ABSPATH' ) || exit;

/** Metabox ze stanem synchronizacji + "Wyślij ponownie". */
final class PostUI {
	public static function init(): void {
		add_action( 'add_meta_boxes', array( __CLASS__, 'box' ) );
		add_action( 'admin_post_' . Config::key( 'resend' ), array( __CLASS__, 'resend' ) );
	}

	public static function box(): void {
		add_meta_box( Config::key( 'box' ), 'Team Sync', array( __CLASS__, 'render' ), Config::post_type(), 'side', 'default' );
	}

	public static function render( $post ): void {
		$src = get_post_meta( $post->ID, Config::META_SOURCE, true );
		if ( is_array( $src ) && $src ) {
			printf(
				'<p>Zaimportowano z: <a href="%s" target="_blank" rel="noopener">%s</a><br>%s</p><p class="description">Wpis nie jest już synchronizowany. Zarządzasz nim lokalnie.</p>',
				esc_url( $src['permalink'] ?: $src['site_url'] ),
				esc_html( $src['site_name'] ?: $src['site_url'] ),
				esc_html( wp_date( 'Y-m-d H:i', (int) $src['imported'] ) )
			);
			return;
		}

		$sent = (int) get_post_meta( $post->ID, Config::META_SENT, true );
		if ( ! $sent ) {
			echo '<p>Wpis zostanie wysłany do sieci przy publikacji, jeśli ma przypisaną marekę.</p>';
			return;
		}

		printf( '<p>Wysłano do sieci: %s</p>', esc_html( wp_date( 'Y-m-d H:i', $sent ) ) );
		if ( 'publish' === $post->post_status ) {
			$url = wp_nonce_url(
				add_query_arg( array( 'action' => Config::key( 'resend' ), 'post' => $post->ID ), admin_url( 'admin-post.php' ) ),
				Config::key( 'resend' ) . '_' . $post->ID
			);
			printf(
				'<p><a class="button" href="%s">Wyślij ponownie</a></p><p class="description">Przydatne po dodaniu nowej strony do marki. Strony, które mają już ten wpis, pominą go.</p>',
				esc_url( $url )
			);
		}
	}

	public static function resend(): void {
		$id = absint( $_GET['post'] ?? 0 );
		check_admin_referer( Config::key( 'resend' ) . '_' . $id );
		if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Brak uprawnień.', 403 );
		}
		if ( 'publish' === get_post_status( $id ) && ! get_post_meta( $id, Config::META_SOURCE, true ) ) {
			Publisher::dispatch( $id, 0 );
		}
		wp_safe_redirect( get_edit_post_link( $id, 'url' ) );
		exit;
	}
}
