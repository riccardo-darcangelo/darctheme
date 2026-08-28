<?php
/**
 * Shown when a query returns nothing.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="no-results">

	<?php if ( is_search() ) : ?>

		<h2 class="no-results__title"><?php esc_html_e( 'Nothing matched that search', 'basalt' ); ?></h2>
		<p><?php esc_html_e( 'Try fewer words, or a different spelling.', 'basalt' ); ?></p>
		<?php get_search_form(); ?>

	<?php elseif ( current_user_can( 'publish_posts' ) && is_home() ) : ?>

		<h2 class="no-results__title"><?php esc_html_e( 'Nothing published yet', 'basalt' ); ?></h2>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">
				<?php esc_html_e( 'Write the first post', 'basalt' ); ?>
			</a>
		</p>

	<?php else : ?>

		<h2 class="no-results__title"><?php esc_html_e( 'Nothing here yet', 'basalt' ); ?></h2>
		<p><?php esc_html_e( 'There is no content in this section at the moment. A search might help.', 'basalt' ); ?></p>
		<?php get_search_form(); ?>

	<?php endif; ?>

</section>
