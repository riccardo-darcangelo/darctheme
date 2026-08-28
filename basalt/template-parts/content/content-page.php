<?php
/**
 * A single page.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter whether the page header with the title is rendered.
 *
 * Landing pages usually open with a pattern that contains its own H1, so the
 * template title would produce a second one.
 *
 * @param bool $show Whether to show the page header.
 */
$basalt_show_header = (bool) apply_filters( 'basalt_show_page_header', ! is_page_template( 'templates/template-landing.php' ) );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>

	<?php if ( $basalt_show_header ) : ?>
		<header class="entry__header">
			<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
		</header>
	<?php endif; ?>

	<?php
	if ( ! is_page_template( 'templates/template-landing.php' ) ) {
		basalt_post_thumbnail( 'basalt-hero', true );
	}
	?>

	<div class="entry__content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before'    => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'basalt' ) . '"><span class="page-links__label">' . esc_html__( 'Pages:', 'basalt' ) . '</span>',
				'after'     => '</nav>',
				'separator' => ' ',
			)
		);
		?>
	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry__footer">
			<?php
			edit_post_link(
				sprintf(
					/* translators: %s: page title. */
					esc_html__( 'Edit %s', 'basalt' ),
					'<span class="screen-reader-text">' . esc_html( get_the_title() ) . '</span>'
				),
				'<span class="entry__edit-link">',
				'</span>'
			);
			?>
		</footer>
	<?php endif; ?>

</article>
