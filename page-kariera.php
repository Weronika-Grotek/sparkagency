<?php
/**
 * Editable Career page.
 *
 * Create a standard WordPress page with the slug "kariera". Its Gutenberg
 * content, including the career-listing block, is rendered here.
 *
 * @package SparkAgency
 */

get_header();
?>

<main id="site-content" class="spark-career spark-career--page" role="main">
	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'spark-career__article' ); ?>>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
