<?php
/**
 *
 * @author dev
 * @package SparkAgency
 */

require_once get_template_directory() . '/bootstrap/sparkagency-bootstrap.php';

function sparkagency_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
}
add_action( 'after_setup_theme', 'sparkagency_theme_setup' );

function my_custom_menu_setup() {
	register_nav_menus(
		array(
			'primary-menu'            => __( 'Primary Menu', 'sparkagency' ),
			'footer-menu'             => __( 'Footer Menu', 'sparkagency' ),
			'footer-information-menu' => __( 'Footer Information Menu', 'sparkagency' ),

		)
	);
}
add_action( 'after_setup_theme', 'my_custom_menu_setup' );

/**
 * Rejestruje natywnie typ wpisu Case Study.
 *
 * Definicja zastępuje konfigurację z wtyczki Custom Post Type UI.
 */
function sparkagency_register_case_study_post_type() {
	if ( post_type_exists( 'case-study' ) ) {
		return;
	}

	$labels = array(
		'name'                  => __( 'Case studies', 'sparkagency' ),
		'singular_name'         => __( 'Case study', 'sparkagency' ),
		'menu_name'             => __( 'Case studies', 'sparkagency' ),
		'name_admin_bar'        => __( 'Case study', 'sparkagency' ),
		'add_new'               => __( 'Dodaj nowe', 'sparkagency' ),
		'add_new_item'          => __( 'Dodaj case study', 'sparkagency' ),
		'new_item'              => __( 'Nowe case study', 'sparkagency' ),
		'edit_item'             => __( 'Edytuj case study', 'sparkagency' ),
		'view_item'             => __( 'Zobacz case study', 'sparkagency' ),
		'all_items'             => __( 'Wszystkie case studies', 'sparkagency' ),
		'search_items'          => __( 'Szukaj case studies', 'sparkagency' ),
		'not_found'             => __( 'Nie znaleziono case studies.', 'sparkagency' ),
		'not_found_in_trash'    => __( 'Nie znaleziono case studies w koszu.', 'sparkagency' ),
		'featured_image'        => __( 'Obrazek wyróżniający', 'sparkagency' ),
		'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'sparkagency' ),
		'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'sparkagency' ),
		'use_featured_image'    => __( 'Użyj jako obrazka wyróżniającego', 'sparkagency' ),
		'archives'              => __( 'Archiwum case studies', 'sparkagency' ),
	);

	register_post_type(
		'case-study',
		array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'case-study',
				'with_front' => false,
			),
			'query_var'          => true,
			'hierarchical'       => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'menu_icon'          => 'dashicons-portfolio',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
		)
	);
}
add_action( 'init', 'sparkagency_register_case_study_post_type' );

/** Keep the public case study archive available even when CPT UI registers the type first. */
function sparkagency_enable_case_study_post_type_archives( array $args, string $post_type ): array {
	if ( ! in_array( $post_type, array( 'case-study', 'case_study' ), true ) ) {
		return $args;
	}

	$args['public']             = true;
	$args['publicly_queryable'] = true;
	$args['has_archive']        = true;
	if ( false === ( $args['rewrite'] ?? true ) ) {
		$args['rewrite'] = array(
			'slug'       => 'case_study' === $post_type ? 'case_studies' : 'case-study',
			'with_front' => false,
		);
	}
	return $args;
}
add_filter( 'register_post_type_args', 'sparkagency_enable_case_study_post_type_archives', 99, 2 );

/** Register the Hub's company/branch taxonomy and attach it to both case study types. */
function sparkagency_register_case_study_branches_taxonomy(): void {
	$case_types = array_values( array_filter( array( 'case-study', 'case_study' ), 'post_type_exists' ) );
	if ( empty( $case_types ) ) {
		return;
	}

	if ( taxonomy_exists( 'branches' ) ) {
		foreach ( $case_types as $post_type ) {
			if ( ! is_object_in_taxonomy( $post_type, 'branches' ) ) {
				register_taxonomy_for_object_type( 'branches', $post_type );
			}
		}
		return;
	}

	register_taxonomy(
		'branches',
		$case_types,
		array(
			'labels'            => array(
				'name'          => __( 'Branże', 'sparkagency' ),
				'singular_name' => __( 'Branża', 'sparkagency' ),
				'menu_name'     => __( 'Branże', 'sparkagency' ),
				'all_items'     => __( 'Wszystkie firmy', 'sparkagency' ),
				'edit_item'     => __( 'Edytuj firmę', 'sparkagency' ),
				'add_new_item'  => __( 'Dodaj firmę', 'sparkagency' ),
			),
			'public'            => true,
			'publicly_queryable' => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'rewrite'           => array(
				'slug'       => 'branches',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'sparkagency_register_case_study_branches_taxonomy', 20 );

/** Register the full case study archive as an editable Gutenberg block. */
function sparkagency_register_case_studies_archive_block(): void {
	$metadata = get_theme_file_path( '/src/blocks/case-studies-archive/block.json' );
	if ( ! is_readable( $metadata ) ) {
		return;
	}

	$script_path = '/assets/js/case-studies-archive-block.js';
	$script_file = get_theme_file_path( $script_path );
	if ( is_readable( $script_file ) ) {
		wp_register_script(
			'sparkagency-case-studies-archive-editor',
			get_theme_file_uri( $script_path ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n' ),
			(string) filemtime( $script_file ),
			true
		);
	}

	register_block_type(
		$metadata,
		array( 'render_callback' => 'sparkagency_render_case_studies_archive_block' )
	);
}
add_action( 'init', 'sparkagency_register_case_studies_archive_block', 20 );

/** Render the archive block from current case study posts and Hub branch terms. */
function sparkagency_render_case_studies_archive_block( array $attributes = array() ): string {
	$template = get_theme_file_path( '/template-parts/blocks/case-studies-archive.php' );
	if ( ! is_readable( $template ) ) {
		return '';
	}

	ob_start();
	require $template;
	return (string) ob_get_clean();
}

/** Create a Gutenberg-managed archive page once, without replacing existing page content. */
function sparkagency_ensure_case_studies_page(): void {
	$version = '2';
	if ( get_option( 'sparkagency_case_studies_page_version' ) === $version ) {
		return;
	}

	$page = get_page_by_path( 'case-studies', OBJECT, 'page' );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => __( 'Case Studies', 'sparkagency' ),
				'post_name'    => 'case-studies',
				'post_content' => '<!-- wp:sparkagency/case-studies-archive /-->',
			),
			true
		);
		if ( is_wp_error( $page_id ) ) {
			return;
		}
	} else {
		$blocks        = parse_blocks( $page->post_content );
		$archive_block = null;
		$page_blocks   = array();
		foreach ( $blocks as $block ) {
			if ( 'sparkagency/case-studies-archive' === ( $block['blockName'] ?? '' ) ) {
				$archive_block = $archive_block ?: $block;
				continue;
			}
			$page_blocks[] = $block;
		}

		if ( null === $archive_block ) {
			$archive_block = parse_blocks( '<!-- wp:sparkagency/case-studies-archive /-->' )[0];
		}
		// The archive is a long list. Keep every editable block, especially Hero,
		// before it so a Hero inserted after the starter block is still at the top.
		$page_blocks[] = $archive_block;
		$updated_content = serialize_blocks( $page_blocks );
		if ( $updated_content !== $page->post_content ) {
			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => $updated_content,
				)
			);
		}
	}

	update_option( 'sparkagency_case_studies_page_version', $version );
	flush_rewrite_rules( false );
}
add_action( 'admin_init', 'sparkagency_ensure_case_studies_page', 30 );

/** Refresh rewrite rules once after enabling the public case study archive. */
function sparkagency_refresh_case_study_archive_rewrites(): void {
	$version = '3';
	if ( get_option( 'sparkagency_case_archive_rewrite_version' ) === $version ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'sparkagency_case_archive_rewrite_version', $version );
}
add_action( 'admin_init', 'sparkagency_refresh_case_study_archive_rewrites', 20 );

/**
 * Keep older CPT UI case_study entries available in the same public archive.
 * The theme previously shipped templates for both the underscore and hyphen
 * post type names, and existing sites can still have records under either.
 */
function sparkagency_include_legacy_case_studies_in_archive( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( array( 'case-study', 'case_study' ) ) ) {
		return;
	}

	$case_types = array_values( array_filter( array( 'case_study', 'case-study' ), 'post_type_exists' ) );
	if ( ! empty( $case_types ) ) {
		$query->set( 'post_type', $case_types );
	}
}
add_action( 'pre_get_posts', 'sparkagency_include_legacy_case_studies_in_archive' );

/** Point case study menu links to the editable archive page. */
function sparkagency_fix_case_studies_menu_link( array $items ): array {
	$archive_page = get_page_by_path( 'case-studies', OBJECT, 'page' );
	$archive_url  = $archive_page && 'publish' === $archive_page->post_status ? get_permalink( $archive_page ) : false;
	$case_types = array_values( array_filter( array( 'case_study', 'case-study' ), 'post_type_exists' ) );
	if ( ! $archive_url && empty( $case_types ) ) {
		return $items;
	}

	$target_type = $case_types[0] ?? '';
	foreach ( $case_types as $candidate ) {
		$counts = wp_count_posts( $candidate );
		if ( $counts && (int) $counts->publish > 0 ) {
			$target_type = $candidate;
			break;
		}
	}
	$archive_url = $archive_url ?: ( '' !== $target_type ? get_post_type_archive_link( $target_type ) : false );
	if ( ! $archive_url ) {
		foreach ( $case_types as $candidate ) {
			$archive_url = get_post_type_archive_link( $candidate );
			if ( $archive_url ) {
				break;
			}
		}
	}
	if ( ! $archive_url ) {
		return $items;
	}

	foreach ( $items as $item ) {
		$title = strtolower( trim( wp_strip_all_tags( $item->title ) ) );
		$url   = strtolower( (string) $item->url );
		if ( in_array( $title, array( 'case studies', 'case study', 'realizacje', 'nasze realizacje' ), true ) || preg_match( '#/(case[-_]stud(?:y|ies)|realizacje)/?$#', $url ) ) {
			$item->url = $archive_url;
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'sparkagency_fix_case_studies_menu_link' );

/** Add archive card note fields to both current and legacy case study posts. */
function sparkagency_add_case_study_note_meta_boxes(): void {
	foreach ( array( 'case-study', 'case_study' ) as $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			add_meta_box(
				'sparkagency-case-study-card-notes',
				__( 'Dopiski na karcie archiwum', 'sparkagency' ),
				'sparkagency_render_case_study_note_meta_box',
				$post_type,
				'side',
				'default'
			);
		}
	}
}
add_action( 'add_meta_boxes', 'sparkagency_add_case_study_note_meta_boxes' );

/** Render the two optional note fields shown at the end of archive cards. */
function sparkagency_render_case_study_note_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'sparkagency_save_case_study_card_notes', 'sparkagency_case_study_card_notes_nonce' );
	$accent_note = (string) get_post_meta( $post->ID, 'case_study_card_note_accent', true );
	$plain_note  = (string) get_post_meta( $post->ID, 'case_study_card_note_plain', true );
	?>
	<p>
		<label for="case_study_card_note_accent"><strong><?php esc_html_e( 'Dopisek w kolorze kafelka', 'sparkagency' ); ?></strong></label>
		<textarea class="widefat" id="case_study_card_note_accent" name="case_study_card_note_accent" rows="2"><?php echo esc_textarea( $accent_note ); ?></textarea>
	</p>
	<p>
		<label for="case_study_card_note_plain"><strong><?php esc_html_e( 'Zwykły dopisek', 'sparkagency' ); ?></strong></label>
		<textarea class="widefat" id="case_study_card_note_plain" name="case_study_card_note_plain" rows="2"><?php echo esc_textarea( $plain_note ); ?></textarea>
	</p>
	<?php
}

/** Save archive card notes with the case study post. */
function sparkagency_save_case_study_card_notes( int $post_id, WP_Post $post ): void {
	if ( ! in_array( $post->post_type, array( 'case-study', 'case_study' ), true ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$nonce = isset( $_POST['sparkagency_case_study_card_notes_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['sparkagency_case_study_card_notes_nonce'] ) )
		: '';
	if ( ! wp_verify_nonce( $nonce, 'sparkagency_save_case_study_card_notes' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( 'case_study_card_note_accent', 'case_study_card_note_plain' ) as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $field );
		} else {
			update_post_meta( $post_id, $field, $value );
		}
	}
}
add_action( 'save_post', 'sparkagency_save_case_study_card_notes', 10, 2 );

/** Registers the career offers post type. */
function sparkagency_register_career_post_type() {
	if ( ! post_type_exists( 'career' ) ) {
		$labels = array(
			'name'                  => __( 'Oferty pracy', 'sparkagency' ),
			'singular_name'         => __( 'Oferta pracy', 'sparkagency' ),
			'menu_name'             => __( 'Kariera', 'sparkagency' ),
			'name_admin_bar'        => __( 'Oferta pracy', 'sparkagency' ),
			'add_new'               => __( 'Dodaj ofertę', 'sparkagency' ),
			'add_new_item'          => __( 'Dodaj ofertę pracy', 'sparkagency' ),
			'new_item'              => __( 'Nowa oferta pracy', 'sparkagency' ),
			'edit_item'             => __( 'Edytuj ofertę pracy', 'sparkagency' ),
			'view_item'             => __( 'Zobacz ofertę pracy', 'sparkagency' ),
			'all_items'             => __( 'Wszystkie oferty', 'sparkagency' ),
			'search_items'          => __( 'Szukaj ofert pracy', 'sparkagency' ),
			'not_found'             => __( 'Nie znaleziono ofert pracy.', 'sparkagency' ),
			'not_found_in_trash'    => __( 'Nie znaleziono ofert pracy w koszu.', 'sparkagency' ),
			'featured_image'        => __( 'Obrazek wyróżniający', 'sparkagency' ),
			'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'sparkagency' ),
			'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'sparkagency' ),
			'use_featured_image'    => __( 'Użyj jako obrazka wyróżniającego', 'sparkagency' ),
			'archives'              => __( 'Archiwum ofert pracy', 'sparkagency' ),
		);

		register_post_type(
			'career',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_admin_bar'  => true,
				'show_in_rest'       => true,
				// The public /kariera/ address is an ordinary WordPress Page so that
				// editors can build and change its content in Gutenberg. Job entries
				// remain available below it, e.g. /kariera/front-end-developer/.
				'has_archive'        => false,
				'rewrite'            => array(
					'slug'       => 'kariera',
					'with_front' => false,
				),
				'query_var'          => true,
				'hierarchical'       => false,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				'menu_icon'          => 'dashicons-businessperson',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'template'           => array(
					array( 'sparkagency/career-single-header', array() ),
					array(
						'core/group',
						array( 'className' => 'spark-career__content-wrap' ),
						array(
							array(
								'core/group',
								array( 'className' => 'spark-career__content' ),
								array(
									array( 'sparkagency/requirement', array() ),
									array( 'core/paragraph', array( 'placeholder' => __( 'Dodaj opis stanowiska.', 'sparkagency' ) ) ),
								),
							),
							array( 'sparkagency/career-single-apply', array() ),
						),
					),
				),
			)
		);
	}

}
add_action( 'init', 'sparkagency_register_career_post_type', 5 );

/** Options shown when editors create a career offer. */
function sparkagency_career_options(): array {
	return array(
		'company' => array(
			'agency' => __( 'sparkAGENCY', 'sparkagency' ),
			'works'  => __( 'sparkWORKS', 'sparkagency' ),
			'exp'    => __( 'sparkEXP', 'sparkagency' ),
			'warsaw' => __( 'sparkWARSAW', 'sparkagency' ),
			'health' => __( 'sparkHEALTH', 'sparkagency' ),
			'studio' => __( 'sparkSTUDIO', 'sparkagency' ),
		),
		'contract' => array(
			'b2b'            => __( 'B2B', 'sparkagency' ),
			'zlecenie'       => __( 'Umowa zlecenie', 'sparkagency' ),
			'umowa-o-prace'  => __( 'Umowa o pracę', 'sparkagency' ),
		),
		'work_mode' => array(
			'zdalnie'     => __( 'Zdalnie', 'sparkagency' ),
			'hybrydowo'   => __( 'Hybrydowo', 'sparkagency' ),
			'stacjonarnie' => __( 'Stacjonarnie', 'sparkagency' ),
		),
		'status' => array(
			'otwarta'    => __( 'Otwarta rekrutacja', 'sparkagency' ),
			'planowana'  => __( 'Planowana rekrutacja', 'sparkagency' ),
			'zamknieta'  => __( 'Zamknięta rekrutacja', 'sparkagency' ),
		),
	);
}

/** Returns the selected company for a job card. */
function sparkagency_get_career_company( int $post_id ): ?array {
	$options = sparkagency_career_options()['company'];
	$company = sanitize_key( (string) get_post_meta( $post_id, 'career_company', true ) );

	if ( ! isset( $options[ $company ] ) ) {
		return null;
	}

	return array(
		'slug' => $company,
		'name' => $options[ $company ],
	);
}

/** Returns a selected option label, retaining existing hand-entered data safely. */
function sparkagency_get_career_option_label( int $post_id, string $field, string $group ): string {
	$options = sparkagency_career_options();
	$value   = (string) get_post_meta( $post_id, $field, true );
	$key     = sanitize_key( $value );

	if ( isset( $options[ $group ][ $key ] ) ) {
		return $options[ $group ][ $key ];
	}

	$legacy_statuses = array(
		'otwarta-rekrutacja'   => 'otwarta',
		'planowana-rekrutacja' => 'planowana',
		'zamknieta-rekrutacja' => 'zamknieta',
	);
	if ( 'status' === $group && isset( $legacy_statuses[ $key ] ) ) {
		return $options['status'][ $legacy_statuses[ $key ] ];
	}

	return $value;
}

/** Returns only the filled labels displayed below a job title. */
function sparkagency_get_career_details( int $post_id ): array {
	$details = array(
		sparkagency_get_career_option_label( $post_id, 'career_contract', 'contract' ),
		sparkagency_get_career_option_label( $post_id, 'career_work_mode', 'work_mode' ),
		(string) get_post_meta( $post_id, 'career_experience', true ),
	);

	return array_values( array_filter( $details ) );
}

/** Adds the concise career-card fields to the job editor. */
function sparkagency_add_career_meta_box() {
	add_meta_box(
		'sparkagency-career-details',
		__( 'Szczegóły oferty', 'sparkagency' ),
		'sparkagency_render_career_meta_box',
		'career',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_career', 'sparkagency_add_career_meta_box' );

/** Renders job card metadata fields. */
function sparkagency_render_career_meta_box( WP_Post $post ) {
	$options = sparkagency_career_options();
	$values  = array(
		'career_company'   => sanitize_key( (string) get_post_meta( $post->ID, 'career_company', true ) ),
		'career_contract'  => sanitize_key( (string) get_post_meta( $post->ID, 'career_contract', true ) ),
		'career_work_mode' => sanitize_key( (string) get_post_meta( $post->ID, 'career_work_mode', true ) ),
		'career_status'    => sanitize_key( (string) get_post_meta( $post->ID, 'career_status', true ) ),
	);

	wp_nonce_field( 'sparkagency_save_career_details', 'sparkagency_career_details_nonce' );
	?>
	<p><?php esc_html_e( 'Na karcie pojawią się wyłącznie pola, które zostały uzupełnione. Zajawkę wpisz w polu „Zajawka” w panelu ustawień wpisu.', 'sparkagency' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
			<?php foreach ( array( 'career_company' => 'company', 'career_contract' => 'contract', 'career_work_mode' => 'work_mode', 'career_status' => 'status' ) as $field => $group ) : ?>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( array( 'career_company' => __( 'Firma rekrutująca', 'sparkagency' ), 'career_contract' => __( 'Rodzaj umowy', 'sparkagency' ), 'career_work_mode' => __( 'Typ pracy', 'sparkagency' ), 'career_status' => __( 'Status rekrutacji', 'sparkagency' ) )[ $field ] ); ?></label></th>
					<td><select id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>"><option value=""><?php esc_html_e( '— Wybierz —', 'sparkagency' ); ?></option><?php foreach ( $options[ $group ] as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>"<?php selected( $values[ $field ], $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<th scope="row"><label for="career_experience"><?php esc_html_e( 'Wymaganie', 'sparkagency' ); ?></label></th>
				<td><input class="regular-text" type="text" id="career_experience" name="career_experience" value="<?php echo esc_attr( get_post_meta( $post->ID, 'career_experience', true ) ); ?>" placeholder="<?php esc_attr_e( 'np. Min. 1 rok doświadczenia', 'sparkagency' ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="career_email"><?php esc_html_e( 'E-mail do aplikacji', 'sparkagency' ); ?></label></th>
				<td><input class="regular-text" type="email" id="career_email" name="career_email" value="<?php echo esc_attr( get_post_meta( $post->ID, 'career_email', true ) ); ?>" placeholder="<?php esc_attr_e( 'np. kariera@sparkagency.pl', 'sparkagency' ); ?>"></td>
			</tr>
		</tbody>
	</table>
	<?php
}

/** Saves career card metadata. */
function sparkagency_save_career_meta( int $post_id ) {
	if ( ! isset( $_POST['sparkagency_career_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sparkagency_career_details_nonce'] ) ), 'sparkagency_save_career_details' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$options = sparkagency_career_options();
	foreach ( array( 'career_company' => 'company', 'career_contract' => 'contract', 'career_work_mode' => 'work_mode', 'career_status' => 'status' ) as $field => $group ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_key( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( isset( $options[ $group ][ $value ] ) ) {
			update_post_meta( $post_id, $field, $value );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	$requirement = isset( $_POST['career_experience'] ) ? sanitize_text_field( wp_unslash( $_POST['career_experience'] ) ) : '';
	$email       = isset( $_POST['career_email'] ) ? sanitize_email( wp_unslash( $_POST['career_email'] ) ) : '';

	if ( '' !== $requirement ) {
		update_post_meta( $post_id, 'career_experience', $requirement );
	} else {
		delete_post_meta( $post_id, 'career_experience' );
	}

	if ( '' !== $email ) {
		update_post_meta( $post_id, 'career_email', $email );
	} else {
		delete_post_meta( $post_id, 'career_email' );
	}
}
add_action( 'save_post_career', 'sparkagency_save_career_meta' );

/**
 * Registers team content and the brand taxonomy used to route it in Team Sync.
 */
function sparkagency_register_team_post_type() {
	if ( ! post_type_exists( 'team' ) ) {
		$labels = array(
			'name'                  => __( 'Zespoły', 'sparkagency' ),
			'singular_name'         => __( 'Zespół', 'sparkagency' ),
			'menu_name'             => __( 'Zespoły', 'sparkagency' ),
			'name_admin_bar'        => __( 'Zespół', 'sparkagency' ),
			'add_new'               => __( 'Dodaj nowy', 'sparkagency' ),
			'add_new_item'          => __( 'Dodaj zespół', 'sparkagency' ),
			'new_item'              => __( 'Nowy zespół', 'sparkagency' ),
			'edit_item'             => __( 'Edytuj zespół', 'sparkagency' ),
			'view_item'             => __( 'Zobacz zespół', 'sparkagency' ),
			'all_items'             => __( 'Wszystkie zespoły', 'sparkagency' ),
			'search_items'          => __( 'Szukaj zespołów', 'sparkagency' ),
			'not_found'             => __( 'Nie znaleziono zespołów.', 'sparkagency' ),
			'not_found_in_trash'    => __( 'Nie znaleziono zespołów w koszu.', 'sparkagency' ),
			'featured_image'        => __( 'Obrazek wyróżniający', 'sparkagency' ),
			'set_featured_image'    => __( 'Ustaw obrazek wyróżniający', 'sparkagency' ),
			'remove_featured_image' => __( 'Usuń obrazek wyróżniający', 'sparkagency' ),
			'use_featured_image'    => __( 'Użyj jako obrazka wyróżniającego', 'sparkagency' ),
			'archives'              => __( 'Archiwum zespołów', 'sparkagency' ),
		);

		register_post_type(
			'team',
			array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_in_admin_bar'  => true,
				'show_in_rest'       => true,
				'has_archive'        => true,
				'rewrite'            => array(
					'slug'       => 'team',
					'with_front' => false,
				),
				'query_var'          => true,
				'hierarchical'       => false,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				'menu_icon'          => 'dashicons-groups',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
			)
		);
	}

	if ( ! taxonomy_exists( 'team-brand' ) ) {
		register_taxonomy(
			'team-brand',
			array( 'team' ),
			array(
				'labels' => array(
					'name'          => __( 'Marki zespołu', 'sparkagency' ),
					'singular_name' => __( 'Marka zespołu', 'sparkagency' ),
					'menu_name'     => __( 'Marki zespołu', 'sparkagency' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'hierarchical' => false,
				'rewrite'      => array(
					'slug'       => 'team-brand',
					'with_front' => false,
				),
			)
		);
	}
}
add_action( 'init', 'sparkagency_register_team_post_type', 5 );

/**
 *
 * @param string[] $classes Existing menu-item classes.
 * @param object   $item    Current menu item.
 * @param stdClass $args    Arguments passed to wp_nav_menu().
 * @param int      $depth   Current menu depth.
 * @return string[]
 */
function sparkagency_primary_menu_contact_cta( $classes, $item, $args, $depth ) {
	if ( 0 !== $depth || empty( $args->theme_location ) || 'primary-menu' !== $args->theme_location ) {
		return $classes;
	}

	$label = sanitize_title( wp_strip_all_tags( $item->title ) );
	$path  = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );

	if ( in_array( $label, array( 'kontakt', 'contact' ), true ) || in_array( $path, array( 'kontakt', 'contact' ), true ) ) {
		$classes[] = 'menu-item-cta';
	}

	return array_unique( $classes );
}
add_filter( 'nav_menu_css_class', 'sparkagency_primary_menu_contact_cta', 10, 4 );

function sparkagency_enqueue_header_assets() {
	$assets = array(
		'spark-header-tokens' => '/assets/css/header-tokens.css',
		'spark-header'        => '/assets/css/header.css',
	);
	foreach ( $assets as $handle => $relative_path ) {
		$path = get_theme_file_path( $relative_path );
		if ( is_file( $path ) ) {
			wp_enqueue_style(
				$handle,
				get_theme_file_uri( $relative_path ),
				'spark-header' === $handle ? array( 'spark-header-tokens' ) : array(),
				(string) filemtime( $path )
			);
		}
	}
	wp_enqueue_style(
		'spark-header-font',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap',
		array(),
		null
	);
	$script_path = '/header.js';
	if ( is_file( get_theme_file_path( $script_path ) ) ) {
		wp_enqueue_script(
			'spark-header',
			get_theme_file_uri( $script_path ),
			array(),
			(string) filemtime( get_theme_file_path( $script_path ) ),
			true
		);
	}
	$preferences_script_path = '/assets/js/preferences.js';
	if ( is_file( get_theme_file_path( $preferences_script_path ) ) ) {
		wp_enqueue_script(
			'spark-preferences',
			get_theme_file_uri( $preferences_script_path ),
			array( 'spark-header' ),
			(string) filemtime( get_theme_file_path( $preferences_script_path ) ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sparkagency_enqueue_header_assets', 20 );
