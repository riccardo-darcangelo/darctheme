<?php
/**
 * Template Name: With sidebar
 * Template Post Type: page, post
 *
 * Pages have no sidebar by default. This template opts a single page back in.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<main id="content" class="site-main" tabindex="-1">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content/content', 'page' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
