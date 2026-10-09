


<?php
/**
 * Nagłówek motywu Spark.
 *
 * @author dev
 * @package SparkAgency
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="p-header" data-spark-header>
	<div class="p-header__inner">
		<div class="p-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="p-header__site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
		<button class="p-header__toggle" type="button" aria-expanded="false" aria-controls="spark-primary-navigation" data-menu-toggle data-open-label="<?php esc_attr_e( 'MENU', 'dev-theme-tooling' ); ?>" data-close-label="<?php esc_attr_e( 'ZAMKNIJ', 'dev-theme-tooling' ); ?>" hidden>
			<?php esc_html_e( 'MENU', 'dev-theme-tooling' ); ?>
		</button>
		<nav class="p-header__navigation" id="spark-primary-navigation" aria-label="<?php esc_attr_e( 'Główna nawigacja', 'dev-theme-tooling' ); ?>">
			<?php
			if ( has_nav_menu( 'primary-menu' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary-menu',
						'container'      => false,
						'menu_class'     => 'p-header__menu',
						'menu_id'        => 'spark-primary-menu',
						'fallback_cb'    => false,
					)
				);
			}
			?>
			<div class="p-header__tools" role="group" aria-label="<?php esc_attr_e( 'Wygląd i dostępność', 'dev-theme-tooling' ); ?>" hidden>
				<button class="p-header__tool" type="button" data-theme-toggle data-light-label="<?php esc_attr_e( 'Jasny motyw', 'dev-theme-tooling' ); ?>" data-dark-label="<?php esc_attr_e( 'Ciemny motyw', 'dev-theme-tooling' ); ?>">
					<svg class="p-header__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.4 1.4M17.6 17.6 19 19M5 19l1.4-1.4M17.6 6.4 19 5"/></svg>
					<svg class="p-header__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
					<span data-theme-label><?php esc_html_e( 'Jasny motyw', 'dev-theme-tooling' ); ?></span>
				</button>
				<button class="p-header__tool" type="button" data-accessibility-open aria-haspopup="dialog" aria-controls="spark-accessibility" aria-expanded="false">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1m-8-5 3-3 5.5 3-2.36 3.5M4.24 14.5a5 5 0 0 0 6.88 6M13.76 17.5a5 5 0 0 0-6.88-6"/></svg>
					<span><?php esc_html_e( 'Dostępność', 'dev-theme-tooling' ); ?></span>
				</button>
			</div>
		</nav>
	</div>
	<span class="p-header__progress" data-scroll-progress aria-hidden="true"></span>
</header>

<dialog class="p-header-a11y" id="spark-accessibility" aria-labelledby="spark-accessibility-title" aria-describedby="spark-accessibility-description">
	<div class="p-header-a11y__heading">
		<h2 id="spark-accessibility-title"><?php esc_html_e( 'Dostępność', 'dev-theme-tooling' ); ?></h2>
		<button type="button" class="p-header-a11y__close" data-accessibility-close aria-label="<?php esc_attr_e( 'Zamknij ustawienia dostępności', 'dev-theme-tooling' ); ?>">×</button>
	</div>
	<p id="spark-accessibility-description"><?php esc_html_e( 'Dopasuj wygląd strony do swoich potrzeb. Ustawienia są zapamiętywane w tej przeglądarce.', 'dev-theme-tooling' ); ?></p>
	<div class="p-header-a11y__row">
		<span id="spark-theme-label"><?php esc_html_e( 'Motyw', 'dev-theme-tooling' ); ?></span>
		<div class="p-header-a11y__segment" role="group" aria-labelledby="spark-theme-label">
			<button type="button" data-theme-set="light" aria-pressed="false"><?php esc_html_e( 'Jasny', 'dev-theme-tooling' ); ?></button>
			<button type="button" data-theme-set="dark" aria-pressed="false"><?php esc_html_e( 'Ciemny', 'dev-theme-tooling' ); ?></button>
		</div>
	</div>
	<div class="p-header-a11y__row">
		<span id="spark-text-label"><?php esc_html_e( 'Rozmiar tekstu', 'dev-theme-tooling' ); ?></span>
		<div class="p-header-a11y__segment" role="group" aria-labelledby="spark-text-label">
			<?php foreach ( array( '100', '125', '150' ) as $spark_text_size ) : ?>
				<button type="button" data-text-set="<?php echo esc_attr( $spark_text_size ); ?>" aria-pressed="<?php echo '100' === $spark_text_size ? 'true' : 'false'; ?>"><?php echo esc_html( $spark_text_size . '%' ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	$spark_accessibility_options = array(
		'contrast' => __( 'Wysoki kontrast', 'dev-theme-tooling' ),
		'links'    => __( 'Podkreślone linki', 'dev-theme-tooling' ),
		'spacing'  => __( 'Większe odstępy w tekście', 'dev-theme-tooling' ),
		'motion'   => __( 'Zatrzymaj animacje', 'dev-theme-tooling' ),
		'focus'    => __( 'Wyraźny fokus klawiatury', 'dev-theme-tooling' ),
	);
	foreach ( $spark_accessibility_options as $spark_option => $spark_label ) :
		?>
		<button class="p-header-a11y__row p-header-a11y__switch" type="button" role="switch" aria-checked="false" data-accessibility-option="<?php echo esc_attr( $spark_option ); ?>">
			<span><?php echo esc_html( $spark_label ); ?></span><span class="p-header-a11y__indicator" aria-hidden="true"></span>
		</button>
	<?php endforeach; ?>
	<button class="p-header-a11y__reset" type="button" data-accessibility-reset><?php esc_html_e( 'Przywróć ustawienia domyślne', 'dev-theme-tooling' ); ?></button>
</dialog>

