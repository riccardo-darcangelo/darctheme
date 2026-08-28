<?php
/**
 * Single post.
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

			get_template_part( 'template-parts/content/content', 'single' );

			if ( basalt_get_option( 'single_show_author_box' ) ) {
				get_template_part( 'template-parts/components/author-box' );
			}

			basalt_post_navigation();

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
