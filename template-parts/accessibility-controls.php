<?php
/**
 * Shared appearance and accessibility controls.
 *
 * The desktop dock and the mobile-header controls both open this single panel.
 * Keeping this markup outside header.php lets any future template reuse it.
 *
 * @package SparkAgency
 */

$spark_accessibility_options = array(
	'contrast' => __( 'Wysoki kontrast', 'dev-theme-tooling' ),
	'links'    => __( 'Podkreślone linki', 'dev-theme-tooling' ),
	'spacing'  => __( 'Większe odstępy w tekście', 'dev-theme-tooling' ),
	'motion'   => __( 'Zatrzymaj animacje', 'dev-theme-tooling' ),
	'focus'    => __( 'Wyraźny fokus klawiatury', 'dev-theme-tooling' ),
);
?>

<aside class="p-preferences-dock" aria-label="<?php esc_attr_e( 'Szybkie ustawienia', 'dev-theme-tooling' ); ?>" data-preferences-dock hidden>
	<button class="p-preferences-dock__button" type="button" data-theme-toggle data-light-label="<?php esc_attr_e( 'Włącz jasny motyw', 'dev-theme-tooling' ); ?>" data-dark-label="<?php esc_attr_e( 'Włącz ciemny motyw', 'dev-theme-tooling' ); ?>">
		<svg class="p-preferences-dock__sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.4 1.4M17.6 17.6 19 19M5 19l1.4-1.4M17.6 6.4 19 5"/></svg>
		<svg class="p-preferences-dock__moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
		<span class="p-preferences-dock__label" data-theme-label><?php esc_html_e( 'Włącz jasny motyw', 'dev-theme-tooling' ); ?></span>
	</button>
	<button class="p-preferences-dock__button" type="button" data-accessibility-open aria-haspopup="dialog" aria-controls="spark-accessibility" aria-expanded="false" aria-label="<?php esc_attr_e( 'Otwórz ustawienia dostępności', 'dev-theme-tooling' ); ?>">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1m-8-5 3-3 5.5 3-2.36 3.5M4.24 14.5a5 5 0 0 0 6.88 6M13.76 17.5a5 5 0 0 0-6.88-6"/></svg>
	</button>
</aside>

<dialog class="p-preferences-panel" id="spark-accessibility" aria-labelledby="spark-accessibility-title" aria-describedby="spark-accessibility-description">
	<div class="p-preferences-panel__heading">
		<div>
			<p class="p-preferences-panel__eyebrow"><?php esc_html_e( 'Komfort przeglądania', 'dev-theme-tooling' ); ?></p>
			<h2 id="spark-accessibility-title"><?php esc_html_e( 'Dostępność', 'dev-theme-tooling' ); ?></h2>
		</div>
		<button type="button" class="p-preferences-panel__close" data-accessibility-close aria-label="<?php esc_attr_e( 'Zamknij ustawienia dostępności', 'dev-theme-tooling' ); ?>">×</button>
	</div>
	<p id="spark-accessibility-description" class="p-preferences-panel__description"><?php esc_html_e( 'Dopasuj wygląd strony do swoich potrzeb. Wybór zostanie zapamiętany tylko w tej przeglądarce.', 'dev-theme-tooling' ); ?></p>
	<div class="p-preferences-panel__list">
		<div class="p-preferences-panel__row">
			<span id="spark-theme-label"><?php esc_html_e( 'Motyw', 'dev-theme-tooling' ); ?></span>
			<div class="p-preferences-panel__segment" role="group" aria-labelledby="spark-theme-label">
				<button type="button" data-theme-set="light" aria-pressed="false"><?php esc_html_e( 'Jasny', 'dev-theme-tooling' ); ?></button>
				<button type="button" data-theme-set="dark" aria-pressed="false"><?php esc_html_e( 'Ciemny', 'dev-theme-tooling' ); ?></button>
			</div>
		</div>
		<div class="p-preferences-panel__row">
			<span id="spark-text-label"><?php esc_html_e( 'Rozmiar tekstu', 'dev-theme-tooling' ); ?></span>
			<div class="p-preferences-panel__segment" role="group" aria-labelledby="spark-text-label">
				<?php foreach ( array( '100', '125', '150' ) as $spark_text_size ) : ?>
					<button type="button" data-text-set="<?php echo esc_attr( $spark_text_size ); ?>" aria-pressed="<?php echo '100' === $spark_text_size ? 'true' : 'false'; ?>"><?php echo esc_html( $spark_text_size . '%' ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<?php foreach ( $spark_accessibility_options as $spark_option => $spark_label ) : ?>
			<button class="p-preferences-panel__row p-preferences-panel__switch" type="button" role="switch" aria-checked="false" data-accessibility-option="<?php echo esc_attr( $spark_option ); ?>">
				<span><?php echo esc_html( $spark_label ); ?></span><span class="p-preferences-panel__indicator" aria-hidden="true"></span>
			</button>
		<?php endforeach; ?>
	</div>
	<button class="p-preferences-panel__reset" type="button" data-accessibility-reset><?php esc_html_e( 'Przywróć ustawienia domyślne', 'dev-theme-tooling' ); ?></button>
</dialog>
