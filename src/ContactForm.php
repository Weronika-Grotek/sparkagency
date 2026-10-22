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
	private const COMPANY_RECIPIENTS_OPTION = 'sparkagency_contact_form_company_recipients';
	private const EXTRA_COMPANY_ROWS        = 3;
	/** Topic keys of the contact forms and company IDs of the Needs Generator. */
	private const COMPANIES = array(
		'warsaw' => 'Spark Warsaw',
		'health' => 'Spark Health',
		'exp'    => 'Spark EXP',
		'studio' => 'Spark Studio',
		'works'  => 'Spark Works',
		'advice' => 'Nie wiem, doradźcie',
	);
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
		register_setting(
			'sparkagency_contact_form_settings',
			self::COMPANY_RECIPIENTS_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_company_recipients' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Keep valid key => "a@x.pl, b@y.pl" pairs. Rows come as list items with
	 * key and emails because extra companies can be added by key.
	 *
	 * The first save of a missing option runs this twice (update_option, then
	 * add_option), the second time with the already stored key => emails shape.
	 */
	public function sanitize_company_recipients( $value ): array {
		$clean = array();
		foreach ( is_array( $value ) ? $value : array() as $index => $row ) {
			if ( is_string( $row ) && is_string( $index ) ) {
				$row = array( 'key' => $index, 'emails' => $row );
			}
			$key = is_array( $row ) ? sanitize_key( (string) ( $row['key'] ?? '' ) ) : '';
			if ( '' === $key ) {
				continue;
			}
			$emails  = $this->parse_emails( (string) ( $row['emails'] ?? '' ) );
			$invalid = array_filter( array_map( 'trim', explode( ',', (string) ( $row['emails'] ?? '' ) ) ), static fn( string $item ): bool => '' !== $item && ! is_email( sanitize_email( $item ) ) );
			if ( ! empty( $invalid ) ) {
				/* translators: %s: invalid email addresses. */
				add_settings_error( self::COMPANY_RECIPIENTS_OPTION, 'invalid-company-recipient-' . $key, sprintf( __( 'Pominięto niepoprawne adresy: %s', 'sparkagency' ), implode( ', ', array_map( 'sanitize_text_field', $invalid ) ) ) );
			}
			if ( ! empty( $emails ) ) {
				$clean[ $key ] = implode( ', ', $emails );
			}
		}
		return $clean;
	}

	/** Split a comma-separated list into unique valid addresses. */
	private function parse_emails( string $list ): array {
		$emails = array();
		foreach ( explode( ',', $list ) as $email ) {
			$email = sanitize_email( trim( $email ) );
			if ( is_email( $email ) ) {
				$emails[] = strtolower( $email );
			}
		}
		return array_values( array_unique( $emails ) );
	}

	/** Copy recipients for the given company keys, without the main recipient. */
	private function copy_recipients( array $keys, string $recipient ): array {
		$stored = get_option( self::COMPANY_RECIPIENTS_OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$copies = array();
		foreach ( array_unique( $keys ) as $key ) {
			if ( isset( $stored[ $key ] ) ) {
				$copies = array_merge( $copies, $this->parse_emails( (string) $stored[ $key ] ) );
			}
		}
		return array_values( array_diff( array_unique( $copies ), array( strtolower( $recipient ) ) ) );
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
			<p><?php esc_html_e( 'Wiadomości z formularza skróconego i pełnego trafiają na adres odbiorcy, a kopie na adresy przypisane do firm.', 'sparkagency' ); ?></p>
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
				<h2><?php esc_html_e( 'Kopie wiadomości dla firm', 'sparkagency' ); ?></h2>
				<p><?php esc_html_e( 'Adresy w kopii (CC), gdy zapytanie dotyczy firmy: zaznaczono jej temat w formularzu albo wybrano ją w generatorze potrzeb. Kilka adresów oddziel przecinkami. Klucz musi być zgodny z wartością tematu w bloku formularza i identyfikatorem firmy w generatorze.', 'sparkagency' ); ?></p>
				<?php settings_errors( self::COMPANY_RECIPIENTS_OPTION ); ?>
				<table class="widefat striped" role="presentation" style="max-width:56rem">
					<thead><tr><th><?php esc_html_e( 'Firma', 'sparkagency' ); ?></th><th><?php esc_html_e( 'Klucz', 'sparkagency' ); ?></th><th><?php esc_html_e( 'Adresy w kopii', 'sparkagency' ); ?></th></tr></thead>
					<tbody>
					<?php
					$company_recipients = get_option( self::COMPANY_RECIPIENTS_OPTION, array() );
					$company_recipients = is_array( $company_recipients ) ? $company_recipients : array();
					$rows               = array();
					foreach ( self::COMPANIES as $key => $label ) {
						$rows[] = array( 'key' => $key, 'label' => $label, 'emails' => $company_recipients[ $key ] ?? '', 'fixed' => true );
					}
					foreach ( array_diff_key( $company_recipients, self::COMPANIES ) as $key => $emails ) {
						$rows[] = array( 'key' => $key, 'label' => '', 'emails' => $emails, 'fixed' => false );
					}
					for ( $extra = 0; $extra < self::EXTRA_COMPANY_ROWS; $extra++ ) {
						$rows[] = array( 'key' => '', 'label' => '', 'emails' => '', 'fixed' => false );
					}
					foreach ( $rows as $index => $row ) :
						$name = self::COMPANY_RECIPIENTS_OPTION . '[' . $index . ']';
						?>
						<tr>
							<td><?php echo '' !== $row['label'] ? esc_html( $row['label'] ) : esc_html__( 'Inna firma', 'sparkagency' ); ?></td>
							<td>
								<?php if ( $row['fixed'] ) : ?>
									<code><?php echo esc_html( $row['key'] ); ?></code><input type="hidden" name="<?php echo esc_attr( $name ); ?>[key]" value="<?php echo esc_attr( $row['key'] ); ?>" />
								<?php else : ?>
									<input class="small-text" style="width:8rem" name="<?php echo esc_attr( $name ); ?>[key]" value="<?php echo esc_attr( $row['key'] ); ?>" placeholder="np. nowa-firma" />
								<?php endif; ?>
							</td>
							<td><input class="large-text" name="<?php echo esc_attr( $name ); ?>[emails]" value="<?php echo esc_attr( $row['emails'] ); ?>" placeholder="zespol@firma.pl, szef@firma.pl" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
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
		$keys    = array_filter( array_map( 'sanitize_key', explode( ',', (string) wp_unslash( $_POST['spark_contact_topic_keys'] ?? '' ) ) ) );
		$keys[]  = sanitize_key( (string) wp_unslash( $_POST['spark_need_company'] ?? '' ) );
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
		$body    = implode( "\n", array_merge( array(
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
		), $this->needs_lines() ) );
		$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
		foreach ( $this->copy_recipients( array_slice( array_filter( $keys ), 0, 20 ), $recipient ) as $copy ) {
			$headers[] = 'Cc: ' . $copy;
		}
		$sent    = wp_mail( $recipient, $subject, $body, $headers, is_string( $attachment ) ? array( $attachment ) : array() );
		$this->remove_attachment( $attachment );
		$this->redirect( $sent ? 'success' : 'mail' );
	}

	/**
	 * Mail lines with the Needs Generator answers (hidden spark_need_* fields).
	 * They come from the page URL, possibly from another site on this theme, so
	 * values are trimmed instead of rejecting the whole message.
	 */
	private function needs_lines(): array {
		$fields = array(
			'company'     => __( 'Firma:', 'sparkagency' ),
			'information' => __( 'Informacja:', 'sparkagency' ),
			'scope'       => __( 'Zakres:', 'sparkagency' ),
		);
		$lines  = array();
		foreach ( $fields as $key => $heading ) {
			$value = $this->trim_to( sanitize_text_field( wp_unslash( $_POST[ 'spark_need_' . $key ] ?? '' ) ), 100 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in submit().
			if ( '' === $value ) {
				continue;
			}
			$label   = $this->trim_to( sanitize_text_field( wp_unslash( $_POST[ 'spark_need_' . $key . '_label' ] ?? '' ) ), 150 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in submit().
			$lines[] = $heading . ' ' . ( '' !== $label && $label !== $value ? $label . ' [' . $value . ']' : $value );
		}
		if ( empty( $lines ) ) {
			return array();
		}
		return array_merge( array( '', __( 'Generator potrzeb (dane z adresu strony):', 'sparkagency' ) ), $lines );
	}

	/** Cut a string to a character limit, multibyte-safe when available. */
	private function trim_to( string $value, int $limit ): string {
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
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
