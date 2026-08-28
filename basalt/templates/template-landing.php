<?php
/**
 * Template Name: Landing page
 * Template Post Type: page
 *
 * No page title, no featured image, no breadcrumb bar and no sidebar: the
 * content is built entirely from patterns, and the first pattern carries the
 * H1. Use this for campaign and product pages.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

// A bar above a full bleed hero pushes the LCP element below the fold.
add_filter( 'basalt_show_breadcrumbs', '__return_false' );

get_header();
?>

<main id="content" class="site-main site-main--landing" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();

		get_template_part( 'template-parts/content/content', 'page' );
	endwhile;
	?>
</main>

<?php
get_footer();
