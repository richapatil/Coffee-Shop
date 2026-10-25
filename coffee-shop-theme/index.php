<?php
/**
 * Fallback template for every other page (for example a page you add in wp-admin).
 * Every WordPress theme must have an index.php.
 */

get_header();
?>

<main class="section">
	<div class="container container-narrow">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article>
					<h1 class="section-title"><?php the_title(); ?></h1>
					<?php the_content(); ?>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<h1 class="section-title">Page not found</h1>
			<p class="section-text"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the home page</a></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
