<?php
/**
 * Accessibility.
 *
 * The theme's share of the work: the skip link, and making sure the target of
 * that link can actually receive focus. Everything that corrects core block
 * output lives in the Basalt Core plugin, because it is just as necessary
 * under any other theme.
 *
 * The rules that belong in CSS (focus visibility, target size, reduced motion,
 * logical properties for RTL) are in assets/css/base.css.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip link, rendered as the first focusable element on the page.
 *
 * Block themes have no header.php to put this in, so it is printed on
 * wp_body_open, which core fires before the template renders.
 *
 * @return void
 */
function basalt_skip_link(): void {
	printf(
		'<a class="skip-link screen-reader-text" href="#basalt-content">%s</a>',
		esc_html__( 'Skip to content', 'basalt' )
	);
}
add_action( 'wp_body_open', 'basalt_skip_link', 1 );

/**
 * Give the main landmark the id the skip link points at, and make it focusable.
 *
 * A skip link that moves the reading position but not focus leaves keyboard
 * users where they started: the next Tab continues from the link, not from the
 * content. tabindex="-1" on the target is what fixes that, and it is why this
 * filter exists rather than hardcoding the id in every template.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_mark_main_landmark( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/group' ) {
		return $content;
	}

	if ( ( $block['attrs']['tagName'] ?? '' ) !== 'main' ) {
		return $content;
	}

	if ( ! is_string( $content ) || false !== strpos( $content, 'id="basalt-content"' ) ) {
		return $content;
	}

	return (string) preg_replace(
		'/(<main\b)/',
		'$1 id="basalt-content" tabindex="-1"',
		$content,
		1
	);
}
add_filter( 'render_block', 'basalt_mark_main_landmark', 10, 2 );

/**
 * Give the posts page a level one heading.
 *
 * core/query-title with type "archive" renders nothing on the posts page,
 * because a blog index is not an archive in the sense the block means. The
 * result is a page with no h1 at all, which fails an accessibility audit and
 * costs the most important heading on what is often the second most visited
 * page of a site.
 *
 * The title of the page assigned under Settings > Reading is the honest answer,
 * with the site tagline as a fallback for a site that has not assigned one.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_blog_heading( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/query-title' ) {
		return $content;
	}

	if ( ! is_home() || is_front_page() || '' !== trim( (string) $content ) ) {
		return $content;
	}

	$blog_page = (int) get_option( 'page_for_posts' );
	$title     = $blog_page ? get_the_title( $blog_page ) : '';

	if ( '' === trim( $title ) ) {
		$title = (string) get_bloginfo( 'name' );
	}

	$level = (int) ( $block['attrs']['level'] ?? 1 );
	$align = $block['attrs']['textAlign'] ?? '';

	return sprintf(
		'<h%1$d class="wp-block-query-title%2$s">%3$s</h%1$d>',
		max( 1, min( 6, $level ) ),
		$align ? ' has-text-align-' . esc_attr( $align ) : '',
		esc_html( $title )
	);
}
add_filter( 'render_block', 'basalt_blog_heading', 10, 2 );
