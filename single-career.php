<?php
/**
 * Single career offer template.
 *
 * @package SparkAgency
 */

get_header();
?>

<main id="site-content" class="spark-career spark-career--single" role="main">
	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		$career_content = get_the_content();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'spark-career__article' ); ?>>

			<?php the_content(); ?>

		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
