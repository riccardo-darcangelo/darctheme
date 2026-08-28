<?php
/**
 * One search result.
 *
 * Search results are a list, not a gallery: the post type matters more than the
 * image, because the visitor is scanning for a specific thing.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_post_type = get_post_type_object( get_post_type() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--result' ); ?>>

	<div class="entry__body">
		<?php if ( $basalt_post_type instanceof WP_Post_Type ) : ?>
			<p class="entry__kind"><?php echo esc_html( $basalt_post_type->labels->singular_name ); ?></p>
		<?php endif; ?>

		<?php
		the_title(
			sprintf(
				'<h2 class="entry__title"><a class="entry__link" href="%s">',
				esc_url( get_permalink() )
			),
			'</a></h2>'
		);
		?>

		<div class="entry__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<p class="entry__url"><?php echo esc_html( urldecode( (string) get_permalink() ) ); ?></p>
	</div>

</article>
