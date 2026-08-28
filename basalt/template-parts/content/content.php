<?php
/**
 * One entry in an archive listing.
 *
 * The whole card is not a link. A single link on the heading keeps the
 * accessible name meaningful and avoids the nested interactive elements that
 * a clickable card produces.
 *
 * @package Basalt
 *
 * @var array{is_first?: bool} $args Passed by get_template_part().
 */

defined( 'ABSPATH' ) || exit;

$basalt_is_first = ! empty( $args['is_first'] );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--card' ); ?>>

	<?php basalt_post_thumbnail( 'basalt-card', $basalt_is_first ); ?>

	<div class="entry__body">

		<?php if ( basalt_get_option( 'archive_show_meta' ) ) : ?>
			<div class="entry__meta">
				<?php
				basalt_posted_on();

				if ( basalt_get_option( 'single_show_reading_time' ) ) {
					printf(
						' <span class="entry__reading-time">%s</span>',
						esc_html(
							sprintf(
								/* translators: %d: reading time in minutes. */
								_n( '%d min read', '%d min read', basalt_reading_time(), 'basalt' ),
								basalt_reading_time()
							)
						)
					);
				}
				?>
			</div>
		<?php endif; ?>

		<?php
		the_title(
			sprintf(
				'<h2 class="entry__title"><a class="entry__link" href="%s" rel="bookmark">',
				esc_url( get_permalink() )
			),
			'</a></h2>'
		);
		?>

		<?php if ( basalt_get_option( 'archive_show_excerpt' ) ) : ?>
			<div class="entry__excerpt">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>

	</div>
</article>
