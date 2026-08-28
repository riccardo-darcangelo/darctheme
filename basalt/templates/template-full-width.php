<?php
/**
 * Template Name: Full width
 * Template Post Type: page, post
 *
 * Content spans the wide layout width and the sidebar is suppressed. The title
 * is still rendered, so the page keeps its H1.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container container--wide">
	<main id="content" class="site-main site-main--full" tabindex="-1">
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
</div>

<?php
get_footer();
