<?php
/**
 * Handles contact-form delivery and its administration setting.
 *
 * @package SparkAgency
 */

declare( strict_types=1 );

namespace SparkAgency\Theme;

defined( 'ABSPATH' ) || exit;

final class ContactForm {
	private const RECIPIENT_OPTION = 'sparkagency_contact_form_recipient';
	private const MAX_FILE_SIZE    = 10485760;

	/** Register public submission and administration hooks. */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_sparkagency_submit_contact_form', array( $this, 'submit' ) );
		add_action( 'admin_post_nopriv_sparkagency_submit_contact_form', array( $this, 'submit' ) );
	}

	/** Add Appearance → Formularz kontaktowy. */
	public function add_settings_page(): void {
		add_theme_page(
			__( 'Formularz kontaktowy', 'sparkagency' ),
			__( 'Formularz kontaktowy', 'sparkagency' ),
			'edit_theme_options',
			'sparkagency-contact-form',
			array( $this, 'render_settings_page' )
		);
	}

	/** Register the recipient address option. */
	public function register_settings(): void {
		register_setting(
			'sparkagency_contact_form_settings',
			self::RECIPIENT_OPTION,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_recipient' ),
				'default'           => '',
			)
		);
	}

	/** Validate a saved recipient email address. */
	public function sanitize_recipient( $value ): string {
		$raw   = is_string( $value ) ? trim( $value ) : '';
		$email = sanitize_email( $raw );
		if ( '' === $raw ) {
			return '';
		}
		if ( ! is_email( $email ) ) {
			add_settings_error( self::RECIPIENT_OPTION, 'invalid-recipient', __( 'Podaj poprawny adres e-mail odbiorcy.', 'sparkagency' ) );
			return self::recipient_email();
		}
		return $email;
	}

	/** Resolve the configured recipient, with the site administrator as fallback. */
	public static function recipient_email(): string {
		$recipient = sanitize_email( (string) get_option( self::RECIPIENT_OPTION, '' ) );
		if ( is_email( $recipient ) ) {
			return $recipient;
		}
		$admin_email = sanitize_email( (string) get_option( 'admin_email', '' ) );
		return is_email( $admin_email ) ? $admin_email : '';
	}

	/** Render the dedicated recipient setting screen. */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$stored = (string) get_option( self::RECIPIENT_OPTION, '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Formularz kontaktowy', 'sparkagency' ); ?></h1>
			<p><?php esc_html_e( 'Wiadomości z bloku „Formularz kontaktowy” będą wysyłane na wskazany adres.', 'sparkagency' ); ?></p>
			<form action="options.php" method="post">
				<?php settings_fields( 'sparkagency_contact_form_settings' ); ?>
				<?php settings_errors( self::RECIPIENT_OPTION ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sparkagency-contact-form-recipient"><?php esc_html_e( 'Adres odbiorcy', 'sparkagency' ); ?></label></th>
						<td>
							<input class="regular-text" id="sparkagency-contact-form-recipient" name="<?php echo esc_attr( self::RECIPIENT_OPTION ); ?>" type="email" value="<?php echo esc_attr( $stored ); ?>" placeholder="kontakt@twojafirma.pl" />
							<p class="description"><?php esc_html_e( 'Pozostaw puste, aby użyć adresu administratora WordPressa. Zmiana nie wpływa na teksty formularza edytowane w bloku.', 'sparkagency' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** Process a public form submission. */
	public function submit(): void {
		if ( ! isset( $_POST['spark_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spark_contact_nonce'] ) ), 'sparkagency_contact_form' ) ) {
			$this->redirect( 'invalid' );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['spark_contact_name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['spark_contact_email'] ?? '' ) );
		$company = sanitize_text_field( wp_unslash( $_POST['spark_contact_company'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['spark_contact_message'] ?? '' ) );
		$topics  = sanitize_text_field( wp_unslash( $_POST['spark_contact_topics'] ?? '' ) );
		$phone   = sanitize_text_field( wp_unslash( $_POST['spark_contact_phone'] ?? '' ) );
		$budget  = sanitize_text_field( wp_unslash( $_POST['spark_contact_budget'] ?? '' ) );
		$start   = sanitize_text_field( wp_unslash( $_POST['spark_contact_start'] ?? '' ) );
		$source  = sanitize_text_field( wp_unslash( $_POST['spark_contact_source'] ?? '' ) );
		$consent = isset( $_POST['spark_contact_consent'] ) && '1' === $_POST['spark_contact_consent'];

		if ( ! $this->is_valid_name( $name ) || ! is_email( $email ) || $this->string_length( $company ) > 50 || '' === trim( $message ) || $this->string_length( $message ) > 300 || $this->string_length( $topics ) > 300 || $this->string_length( $phone ) > 50 || $this->string_length( $budget ) > 100 || $this->string_length( $start ) > 100 || $this->string_length( $source ) > 100 || ! $consent ) {
			$this->redirect( 'invalid' );
		}

		$attachment = $this->handle_attachment();
		if ( is_wp_error( $attachment ) ) {
			$this->redirect( 'upload' );
		}

		$recipient = self::recipient_email();
		if ( '' === $recipient ) {
			$this->remove_attachment( $attachment );
			$this->redirect( 'mail' );
		}

		$subject = sprintf( __( 'Nowe zapytanie ze strony: %s', 'sparkagency' ), $name );
		$body    = implode( "\n", array(
			__( 'Nowe zapytanie z formularza kontaktowego', 'sparkagency' ),
			'',
			__( 'Imię i nazwisko:', 'sparkagency' ) . ' ' . $name,
			__( 'E-mail:', 'sparkagency' ) . ' ' . $email,
			__( 'Firma:', 'sparkagency' ) . ' ' . ( '' !== $company ? $company : '—' ),
			__( 'Tematy:', 'sparkagency' ) . ' ' . ( '' !== $topics ? $topics : '—' ),
			__( 'Telefon:', 'sparkagency' ) . ' ' . ( '' !== $phone ? $phone : '—' ),
			__( 'Budżet:', 'sparkagency' ) . ' ' . ( '' !== $budget ? $budget : '—' ),
			__( 'Planowany start:', 'sparkagency' ) . ' ' . ( '' !== $start ? $start : '—' ),
			__( 'Źródło:', 'sparkagency' ) . ' ' . ( '' !== $source ? $source : '—' ),
			'',
			__( 'Wiadomość:', 'sparkagency' ),
			$message,
		) );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
		$sent    = wp_mail( $recipient, $subject, $body, $headers, is_string( $attachment ) ? array( $attachment ) : array() );
		$this->remove_attachment( $attachment );
		$this->redirect( $sent ? 'success' : 'mail' );
	}

	/** Validate a real name with Unicode letters and common separators only. */
	private function is_valid_name( string $name ): bool {
		return '' !== $name && $this->string_length( $name ) <= 45 && 1 === preg_match( "/^[\\p{L}\\p{M}]+(?:[ '\\-]+[\\p{L}\\p{M}]+)*$/u", $name );
	}

	/** Count multibyte characters when the server supports mbstring. */
	private function string_length( string $value ): int {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
	}

	/** Store an allowed temporary upload so wp_mail can attach it. */
	private function handle_attachment() {
		if ( empty( $_FILES['spark_contact_attachment'] ) || UPLOAD_ERR_NO_FILE === (int) $_FILES['spark_contact_attachment']['error'] ) {
			return null;
		}
		$file = $_FILES['spark_contact_attachment'];
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) $file['error'] || self::MAX_FILE_SIZE < (int) $file['size'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new \WP_Error( 'invalid_upload' );
		}
		$allowed = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' );
		$type    = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
		if ( empty( $type['ext'] ) || empty( $type['type'] ) ) {
			return new \WP_Error( 'invalid_type' );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$uploaded = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => $allowed ) );
		return empty( $uploaded['error'] ) && ! empty( $uploaded['file'] ) ? $uploaded['file'] : new \WP_Error( 'upload_failed' );
	}

	/** Remove the short-lived file used by wp_mail. */
	private function remove_attachment( $attachment ): void {
		if ( is_string( $attachment ) && is_file( $attachment ) ) {
			wp_delete_file( $attachment );
		}
	}

	/** Redirect to the originating page with a safe result code. */
	private function redirect( string $status ): void {
		$referer = wp_get_referer() ?: home_url( '/' );
		$url     = wp_validate_redirect( $referer, home_url( '/' ) );
		$url     = remove_query_arg( 'spark_contact_status', $url );
		$url     = add_query_arg( 'spark_contact_status', $status, $url );
		$anchor  = sanitize_title( sanitize_text_field( wp_unslash( $_POST['spark_contact_anchor'] ?? '' ) ) );
		wp_safe_redirect( $url . ( '' !== $anchor ? '#' . $anchor : '' ) );
		exit;
	}
}
