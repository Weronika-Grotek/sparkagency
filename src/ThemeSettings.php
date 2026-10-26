<?php
/**
 * Theme-wide settings managed from the WordPress administration panel.
 *
 * @package SparkAgency
 */

namespace SparkAgency\Theme;

defined( 'ABSPATH' ) || exit;

final class ThemeSettings {

	/** Option that controls the full-page navigation animation. */
	private const PAGE_TRANSITIONS_OPTION = 'sparkagency_enable_page_transitions';
	private const CASE_ARCHIVE_OPTION = 'sparkagency_case_archive';

	/** Register the administration hooks. */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Determine whether the page transition is enabled for visitors.
	 */
	public static function page_transitions_enabled(): bool {
		return (bool) get_option( self::PAGE_TRANSITIONS_OPTION, false );
	}

	/** Add the theme settings page under Appearance. */
	public function add_settings_page(): void {
		add_theme_page(
			__( 'Ustawienia Spark Agency', 'sparkagency' ),
			__( 'Spark Agency', 'sparkagency' ),
			'edit_theme_options',
			'sparkagency-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/** Register and sanitize the theme options. */
	public function register_settings(): void {
		register_setting(
			'sparkagency_settings',
			self::PAGE_TRANSITIONS_OPTION,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => static fn( $value ): bool => ! empty( $value ),
			)
		);
		register_setting(
			'sparkagency_settings',
			self::CASE_ARCHIVE_OPTION,
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_case_archive_settings' ),
			)
		);
	}

	/** Sanitize editable copy for the case study archive. */
	public function sanitize_case_archive_settings( $value ): array {
		$value = is_array( $value ) ? $value : array();
		return array(
			'eyebrow' => sanitize_text_field( $value['eyebrow'] ?? '' ),
			'title'   => sanitize_text_field( $value['title'] ?? '' ),
			'lead'    => sanitize_textarea_field( $value['lead'] ?? '' ),
			'subtitle' => wp_kses_post( $value['subtitle'] ?? '' ),
		);
	}

	/** Render the Appearance → Spark Agency settings page. */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$case_archive = get_option( self::CASE_ARCHIVE_OPTION, array() );
		$case_archive = is_array( $case_archive ) ? $case_archive : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Ustawienia Spark Agency', 'sparkagency' ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( 'sparkagency_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Animacja między stronami', 'sparkagency' ); ?></th>
						<td>
							<label for="sparkagency-enable-page-transitions">
								<input type="hidden" name="<?php echo esc_attr( self::PAGE_TRANSITIONS_OPTION ); ?>" value="0" />
								<input
									id="sparkagency-enable-page-transitions"
									name="<?php echo esc_attr( self::PAGE_TRANSITIONS_OPTION ); ?>"
									type="checkbox"
									value="1"
									<?php checked( self::page_transitions_enabled() ); ?>
								/>
								<?php esc_html_e( 'Włącz animację podczas przechodzenia między podstronami.', 'sparkagency' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Domyślnie wyłączona. To ustawienie obowiązuje wszystkich odwiedzających stronę.', 'sparkagency' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Archiwum case studies', 'sparkagency' ); ?></th>
						<td>
							<p><label for="sparkagency-case-archive-eyebrow"><?php esc_html_e( 'Nadtytuł', 'sparkagency' ); ?></label><br /><input class="regular-text" id="sparkagency-case-archive-eyebrow" name="<?php echo esc_attr( self::CASE_ARCHIVE_OPTION ); ?>[eyebrow]" type="text" value="<?php echo esc_attr( $case_archive['eyebrow'] ?? 'Nasza praca' ); ?>" /></p>
							<p><label for="sparkagency-case-archive-title"><?php esc_html_e( 'Tytuł', 'sparkagency' ); ?></label><br /><input class="regular-text" id="sparkagency-case-archive-title" name="<?php echo esc_attr( self::CASE_ARCHIVE_OPTION ); ?>[title]" type="text" value="<?php echo esc_attr( $case_archive['title'] ?? 'Case studies' ); ?>" /></p>
							<p><label for="sparkagency-case-archive-lead"><?php esc_html_e( 'Główny opis', 'sparkagency' ); ?></label><br /><textarea class="large-text" rows="2" id="sparkagency-case-archive-lead" name="<?php echo esc_attr( self::CASE_ARCHIVE_OPTION ); ?>[lead]"><?php echo esc_textarea( $case_archive['lead'] ?? 'Poznaj projekty, w których strategia i kreatywność przekładają się na konkretne rezultaty.' ); ?></textarea></p>
							<p><label for="sparkagency-case-archive-subtitle"><?php esc_html_e( 'Dodatkowy opis', 'sparkagency' ); ?></label><br /><textarea class="large-text" rows="3" id="sparkagency-case-archive-subtitle" name="<?php echo esc_attr( self::CASE_ARCHIVE_OPTION ); ?>[subtitle]"><?php echo esc_textarea( $case_archive['subtitle'] ?? '' ); ?></textarea></p>
							<p class="description"><?php esc_html_e( 'Te teksty wyświetlają się nad listą realizacji.', 'sparkagency' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
