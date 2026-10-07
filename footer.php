<footer class="p-footer">
	<div class="p-footer__inner">
		<p class="p-footer__copyright">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
		</p>

		<?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
			<nav class="p-footer__navigation" aria-label="<?php echo esc_attr__( 'Footer menu', 'textdomain' ); ?>">
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

<?php wp_footer(); ?>
</body>
</html>
