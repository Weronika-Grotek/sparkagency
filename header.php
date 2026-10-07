<?php wp_head(); ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="p-header">
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

		<?php if ( has_nav_menu( 'primary-menu' ) ) : ?>
			<nav class="p-header__navigation" aria-label="<?php echo esc_attr__( 'Primary menu', 'textdomain' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary-menu',
						'container'      => false,
						'menu_class'     => 'p-header__menu',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</header>
