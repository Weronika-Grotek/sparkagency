<?php
namespace TeamSyncHub;

use TeamSyncHub\Core\Config;

defined( 'ABSPATH' ) || exit;

/** Mapowanie marka -> strony docelowe (term meta na hubie). */
final class Router {
	const META = 'team_sync_targets';
	const HUB  = 'hub';

	public static function get_targets_for_term( int $term_id ): array {
		$m = get_term_meta( $term_id, self::META, true );
		return is_array( $m ) ? array_values( array_filter( array_map( 'strval', $m ) ) ) : array();
	}

	public static function set_targets_for_term( int $term_id, array $targets ): void {
		$valid   = array_merge( array( self::HUB ), array_keys( Sites::all() ) );
		$targets = array_values( array_intersect( array_unique( array_map( 'sanitize_key', $targets ) ), $valid ) );
		if ( $targets ) {
			update_term_meta( $term_id, self::META, $targets );
		} else {
			delete_term_meta( $term_id, self::META );
		}
	}

	/** Suma stron docelowych dla listy marek {slug,name}; tylko aktywne strony. */
	public static function targets( array $branches ): array {
		$tax = Config::taxonomy();
		$out = array();
		foreach ( $branches as $b ) {
			$slug = sanitize_title( (string) ( $b['slug'] ?? '' ) );
			$term = $slug ? get_term_by( 'slug', $slug, $tax ) : null;
			if ( $term ) {
				$out = array_merge( $out, self::get_targets_for_term( (int) $term->term_id ) );
			}
		}
		return array_values(
			array_filter(
				array_unique( $out ),
				function ( $id ) {
					if ( self::HUB === $id ) {
						return true;
					}
					$s = Sites::get( $id );
					return $s && ! empty( $s['active'] );
				}
			)
		);
	}

	public static function branches_for_site( string $site_id ): array {
		$terms = get_terms(
			array(
				'taxonomy'   => Config::taxonomy(),
				'hide_empty' => false,
				'meta_key'   => self::META,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}
		$names = array();
		foreach ( $terms as $t ) {
			if ( in_array( $site_id, self::get_targets_for_term( (int) $t->term_id ), true ) ) {
				$names[] = $t->name;
			}
		}
		return $names;
	}

	public static function remove_site( string $site_id ): void {
		$terms = get_terms(
			array(
				'taxonomy'   => Config::taxonomy(),
				'hide_empty' => false,
				'meta_key'   => self::META,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return;
		}
		foreach ( $terms as $t ) {
			$targets = self::get_targets_for_term( (int) $t->term_id );
			if ( in_array( $site_id, $targets, true ) ) {
				self::set_targets_for_term( (int) $t->term_id, array_diff( $targets, array( $site_id ) ) );
			}
		}
	}
}
