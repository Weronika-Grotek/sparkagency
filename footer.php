<?php
$sparkagency_footer_brands = array(
	array( 'label' => 'sparkAGENCY', 'path' => '/', 'color' => 'agency' ),
	array( 'label' => 'sparkWORKS', 'path' => '/spark-works/', 'color' => 'works' ),
	array( 'label' => 'sparkEXP', 'path' => '/spark-exp/', 'color' => 'exp' ),
	array( 'label' => 'sparkWARSAW', 'path' => '/spark-warsaw/', 'color' => 'warsaw' ),
	array( 'label' => 'sparkHEALTH', 'path' => '/spark-health/', 'color' => 'health' ),
	array( 'label' => 'sparkSTUDIO', 'path' => '/spark-studio/', 'color' => 'studio' ),
);
?>

<footer class="p-footer">
	<div class="p-footer__inner">
		<div class="p-footer__grid">
			<div class="p-footer__company">
				<a class="p-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">spark<span>AGENCY</span></a>
				<p><?php esc_html_e( 'ul. Cybernetyki 19, 02-677 Warszawa', 'sparkagency' ); ?></p>
			</div>

			<div class="p-footer__column">
				<h2><?php esc_html_e( 'Strona', 'sparkagency' ); ?></h2>
				<?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'Menu w stopce', 'sparkagency' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer-menu',
								'container'      => false,
								'menu_class'     => 'p-footer__menu',
								'fallback_cb'    => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>

			<div class="p-footer__column">
				<h2><?php esc_html_e( 'Grupa', 'sparkagency' ); ?></h2>
				<ul class="p-footer__brands">
					<?php foreach ( $sparkagency_footer_brands as $sparkagency_footer_brand ) : ?>
						<li>
							<a href="<?php echo esc_url( home_url( $sparkagency_footer_brand['path'] ) ); ?>">
								<?php if ( ! empty( $sparkagency_footer_brand['color'] ) ) : ?>
									<i class="p-footer__brand-dot p-footer__brand-dot--<?php echo esc_attr( $sparkagency_footer_brand['color'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>
								<?php echo esc_html( $sparkagency_footer_brand['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="p-footer__column">
				<h2><?php esc_html_e( 'Informacje', 'sparkagency' ); ?></h2>
				<?php if ( has_nav_menu( 'footer-information-menu' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'Informacje w stopce', 'sparkagency' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer-information-menu',
								'container'      => false,
								'menu_class'     => 'p-footer__menu',
								'fallback_cb'    => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>

		<div class="p-footer__bottom">
			<span><?php echo esc_html( sprintf( __( '© %s Spark Warsaw Sp. z o.o.', 'sparkagency' ), wp_date( 'Y' ) ) ); ?></span>
			<span><?php esc_html_e( 'Warszawa', 'sparkagency' ); ?></span>
		</div>
		<p class="p-footer__legal"><?php esc_html_e( 'Spark Warsaw Sp. z o.o., ul. Cybernetyki 19, 02-677 Warszawa · Sąd Rejonowy dla m.st. Warszawy, XII Wydział Gospodarczy KRS, nr KRS 0000861526 · REGON 387157339 · NIP PL5252837823 · Kapitał zakładowy: 1 720 000,00 PLN', 'sparkagency' ); ?></p>
	</div>
</footer>

<button class="p-scroll-top" type="button" aria-label="<?php esc_attr_e( 'Wróć na górę strony', 'sparkagency' ); ?>" data-scroll-top tabindex="-1">
	<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
