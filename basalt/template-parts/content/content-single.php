<?php
/**
 * A single post.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_taxonomies = basalt_get_entry_taxonomies();
$basalt_show_meta  = post_type_supports( (string) get_post_type(), 'author' ) || 'post' === get_post_type();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>

	<header class="entry__header">
		<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>

		<?php if ( $basalt_show_meta ) : ?>
			<div class="entry__meta">
				<?php
				basalt_posted_on();
				echo ' ';
				basalt_posted_by();

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
	</header>

	<?php basalt_post_thumbnail( 'basalt-hero', true ); ?>

	<div class="entry__content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'basalt' ) . '"><span class="page-links__label">' . esc_html__( 'Pages:', 'basalt' ) . '</span>',
				'after'    => '</nav>',
				'separator' => ' ',
			)
		);
		?>
	</div>

	<?php if ( '' !== $basalt_taxonomies ) : ?>
		<footer class="entry__footer">
			<?php echo $basalt_taxonomies; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the getter. ?>
		</footer>
	<?php endif; ?>

</article>
