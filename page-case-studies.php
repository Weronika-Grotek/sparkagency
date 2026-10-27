<?php
/** Editable Gutenberg landing page for the Case Studies archive. */
get_header();
?>
<main id="site-content" role="main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
