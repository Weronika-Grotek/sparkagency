


<?php
/**
 * Nagłówek motywu Spark.
 *
 * @package SparkAgency
 */

$spark_page_transitions_enabled = \SparkAgency\Theme\ThemeSettings::page_transitions_enabled();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
	<script>
		(function () {
			try {
				var preferences = JSON.parse(localStorage.getItem('spark-preferences') || '{}');
				var root = document.documentElement;
				// The public site starts in dark mode. A saved choice is applied before
				// styles are painted, preventing a light/dark flash on navigation.
				var theme = preferences.theme === 'light' || preferences.theme === 'dark'
					? preferences.theme
					: 'dark';
				root.dataset.theme = theme;
				root.style.colorScheme = theme;
				if (preferences.motion === true) root.setAttribute('data-spark-motion', '');
			} catch (error) {}
		}());
	</script>
	<?php if ( $spark_page_transitions_enabled ) : ?>
		<script>
			(function () {
				try {
					var startedAt = Number(sessionStorage.getItem('spark-page-transition'));
					var isFresh = startedAt && Date.now() - startedAt < 10000;
					var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

					sessionStorage.removeItem('spark-page-transition');
					if (isFresh && !reduceMotion && !document.documentElement.hasAttribute('data-spark-motion')) {
						document.documentElement.setAttribute('data-spark-page-enter', '');
					}
				} catch (error) {}
			}());
		</script>
	<?php endif; ?>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="p-cursor-dot" data-cursor-dot aria-hidden="true"></div>
<div class="p-cursor-ring" data-cursor-ring aria-hidden="true"></div>

<?php if ( $spark_page_transitions_enabled ) : ?>
	<div class="p-page-transition" data-page-transition aria-hidden="true"></div>
<?php endif; ?>

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
		<button class="p-header__toggle" type="button" aria-expanded="false" aria-controls="spark-primary-navigation" data-menu-toggle data-open-label="<?php esc_attr_e( 'MENU', 'sparkagency' ); ?>" data-close-label="<?php esc_attr_e( 'ZAMKNIJ', 'sparkagency' ); ?>" hidden>
			<?php esc_html_e( 'MENU', 'sparkagency' ); ?>
		</button>
		<nav class="p-header__navigation" id="spark-primary-navigation" aria-label="<?php esc_attr_e( 'Główna nawigacja', 'sparkagency' ); ?>">
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
			<div class="p-header__tools" role="group" aria-label="<?php esc_attr_e( 'Wygląd i dostępność', 'sparkagency' ); ?>" hidden>
				<button class="p-header__tool" type="button" data-theme-toggle data-light-label="<?php esc_attr_e( 'Jasny motyw', 'sparkagency' ); ?>" data-dark-label="<?php esc_attr_e( 'Ciemny motyw', 'sparkagency' ); ?>">
					<svg class="p-header__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.4 1.4M17.6 17.6 19 19M5 19l1.4-1.4M17.6 6.4 19 5"/></svg>
					<svg class="p-header__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
					<span data-theme-label><?php esc_html_e( 'Jasny motyw', 'sparkagency' ); ?></span>
				</button>
				<button class="p-header__tool" type="button" data-accessibility-open aria-haspopup="dialog" aria-controls="spark-accessibility" aria-expanded="false">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1m-8-5 3-3 5.5 3-2.36 3.5M4.24 14.5a5 5 0 0 0 6.88 6M13.76 17.5a5 5 0 0 0-6.88-6"/></svg>
					<span><?php esc_html_e( 'Dostępność', 'sparkagency' ); ?></span>
				</button>
			</div>
		</nav>
	</div>
	<span class="p-header__progress" data-scroll-progress aria-hidden="true"></span>
</header>

<?php get_template_part( 'template-parts/accessibility-controls' ); ?>

