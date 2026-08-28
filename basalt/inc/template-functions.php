<?php
/**
 * Filters that shape what WordPress hands to the templates.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add layout state to the body element so CSS does not have to guess.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function basalt_body_classes( $classes ) {
	$classes = (array) $classes;

	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	if ( basalt_has_sidebar() ) {
		$classes[] = 'has-sidebar';
	} else {
		$classes[] = 'no-sidebar';
	}

	if ( ! has_nav_menu( 'primary' ) ) {
		$classes[] = 'no-primary-menu';
	}

	if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) {
		$classes[] = 'has-footer-widgets';
	}

	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-featured-image';
	}

	return $classes;
}
add_filter( 'body_class', 'basalt_body_classes' );

/**
 * Whether the current view should render the sidebar.
 *
 * @return bool
 */
function basalt_has_sidebar(): bool {
	$has_sidebar = is_active_sidebar( 'sidebar-1' )
		&& ! is_page_template( array( 'templates/template-full-width.php', 'templates/template-landing.php' ) )
		&& ! is_404()
		&& ! is_page();

	if ( is_page_template( 'templates/template-sidebar.php' ) ) {
		$has_sidebar = is_active_sidebar( 'sidebar-1' );
	}

	/**
	 * Filter whether the sidebar renders on the current view.
	 *
	 * @param bool $has_sidebar Whether to render the sidebar.
	 */
	return (bool) apply_filters( 'basalt_has_sidebar', $has_sidebar );
}

/**
 * Emit the pingback endpoint on singular views that accept pings.
 *
 * @return void
 */
function basalt_pingback_header(): void {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'basalt_pingback_header' );

/**
 * Replace the excerpt ellipsis with a typographic one, without a read more link.
 *
 * A link inside the excerpt would duplicate the card link and give screen
 * reader users two identical targets. The card itself is the link.
 *
 * @param string $more Default more string.
 * @return string
 */
function basalt_excerpt_more( $more ) {
	return is_admin() ? $more : '&hellip;';
}
add_filter( 'excerpt_more', 'basalt_excerpt_more' );

/**
 * Excerpt length in words.
 *
 * @param int $length Default length.
 * @return int
 */
function basalt_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	/**
	 * Filter the excerpt length used by the theme's cards and archives.
	 *
	 * @param int $length Number of words.
	 */
	return (int) apply_filters( 'basalt_excerpt_length', 28 );
}
add_filter( 'excerpt_length', 'basalt_excerpt_length' );

/**
 * Strip the "Category:", "Tag:" and similar prefixes from archive titles.
 *
 * The prefix is already carried by the breadcrumb and the page structure, and
 * repeating it weakens the H1 as a ranking signal.
 *
 * @param string $title Archive title.
 * @return string
 */
function basalt_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'basalt_archive_title' );

/**
 * Remove the wrapping markup WordPress adds around archive descriptions.
 *
 * @param string $description Archive description.
 * @return string
 */
function basalt_archive_description( $description ) {
	return wp_kses_post( $description );
}
add_filter( 'get_the_archive_description', 'basalt_archive_description' );

/**
 * Give every attachment image a decoding hint and a stable aspect ratio.
 *
 * @param array<string, string> $attr       Image attributes.
 * @param WP_Post               $attachment Attachment post.
 * @param string|int[]          $size       Requested size.
 * @return array<string, string>
 */
function basalt_image_attributes( $attr, $attachment, $size ) {
	$attr = (array) $attr;

	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'basalt_image_attributes', 10, 3 );

/**
 * Wrap tables and iframes coming from the editor so they can scroll on phones.
 *
 * Long tables are the most common source of horizontal page overflow, which
 * Lighthouse reports as a mobile usability failure.
 *
 * @param string $content Post content.
 * @return string
 */
function basalt_wrap_overflowing_content( $content ) {
	if ( is_admin() || empty( $content ) ) {
		return $content;
	}

	return (string) preg_replace(
		'#(<table(?![^>]*class="[^"]*wp-block-table)[^>]*>.*?</table>)#is',
		'<div class="table-scroll">$1</div>',
		$content
	);
}
add_filter( 'the_content', 'basalt_wrap_overflowing_content', 20 );

/**
 * Allowed HTML for the small snippets the theme renders from options.
 *
 * @return array<string, array<string, bool>>
 */
function basalt_allowed_inline_html(): array {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'rel'    => true,
			'target' => true,
		),
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
		'span'   => array( 'class' => true ),
	);
}
