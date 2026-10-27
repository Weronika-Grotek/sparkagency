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
	}

	/** Render the Appearance → Spark Agency settings page. */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
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
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
