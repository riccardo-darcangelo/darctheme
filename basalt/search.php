<?php
/**
 * Search results.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<main id="content" class="site-main" tabindex="-1">

		<header class="page-header">
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: the search term. */
					esc_html__( 'Search results for %s', 'basalt' ),
					'<span class="page-header__query">' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>

			<p class="page-header__meta">
				<?php
				printf(
					esc_html(
						/* translators: %d: number of results. */
						_n( '%d result', '%d results', (int) $GLOBALS['wp_query']->found_posts, 'basalt' )
					),
					(int) $GLOBALS['wp_query']->found_posts
				);
				?>
			</p>

			<div class="page-header__search">
				<?php get_search_form(); ?>
			</div>
		</header>

		<?php if ( have_posts() ) : ?>

			<div class="entry-list entry-list--list">
				<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content/content', 'search' );
				endwhile;
				?>
			</div>

			<?php basalt_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

		<?php endif; ?>

	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
