<?php
/**
 * The fallback template.
 *
 * WordPress falls back to this file whenever no more specific template matches,
 * so it has to render a complete, correct page on its own.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<main id="content" class="site-main" tabindex="-1">

		<?php if ( have_posts() ) : ?>

			<?php get_template_part( 'template-parts/components/page-header' ); ?>

			<div class="entry-list entry-list--<?php echo esc_attr( (string) basalt_get_option( 'archive_layout' ) ); ?>">
				<?php
				$basalt_index = 0;

				while ( have_posts() ) :
					the_post();

					get_template_part(
						'template-parts/content/content',
						get_post_type(),
						array( 'is_first' => 0 === $basalt_index++ )
					);
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
