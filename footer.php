<footer class="p-footer">
	<div class="p-footer__inner">
		<p class="p-footer__copyright">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		</p>

		<?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
			<nav class="p-footer__navigation" aria-label="<?php echo esc_attr__( 'Footer menu', 'sparkagency' ); ?>">
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
</footer>

<button class="p-scroll-top" type="button" aria-label="<?php esc_attr_e( 'Wróć na górę strony', 'sparkagency' ); ?>" data-scroll-top tabindex="-1">
	<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
