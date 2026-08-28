<?php
/**
 * Filters that shape what WordPress hands to the templates.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace the excerpt ellipsis with a typographic one, without a read more link.
 *
 * A link inside the excerpt would sit next to the post title link and give
 * screen reader users two targets for the same destination, one of them called
 * "read more".
 *
 * @param string $more Default more string.
 * @return string
 */
function basalt_excerpt_more( $more ) {
	return is_admin() ? $more : '&hellip;';
}
add_filter( 'excerpt_more', 'basalt_excerpt_more' );

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
		return single_term_title( '', false );
	}

	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}

	if ( is_author() ) {
		return (string) get_the_author();
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'basalt_archive_title' );

/**
 * Give every attachment image a decoding hint.
 *
 * @param array<string, string> $attr Image attributes.
 * @return array<string, string>
 */
function basalt_image_attributes( $attr ) {
	$attr = (array) $attr;

	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'basalt_image_attributes' );

/**
 * Let a table from the classic editor scroll rather than overflow the page.
 *
 * The core table block brings its own scroll container. A table pasted into a
 * classic editor post does not, and a wide one is the most common cause of
 * horizontal page overflow on phones, which is both a layout bug and a WCAG
 * 1.4.10 reflow failure.
 *
 * @param string $content Post content.
 * @return string
 */
function basalt_wrap_overflowing_tables( $content ) {
	if ( is_admin() || empty( $content ) || false === stripos( $content, '<table' ) ) {
		return $content;
	}

	$label = esc_attr__( 'Table, scrollable', 'basalt' );

	/*
	 * The alternation is what makes this safe. The first branch matches a whole
	 * core table block and returns it untouched; because the regex engine tries
	 * it first, a table inside such a figure can never reach the second branch.
	 *
	 * An earlier version tested the class on the <table> tag itself. The core
	 * block puts its class on the surrounding <figure> and leaves the table
	 * bare, so every core table was wrapped a second time and inherited the
	 * 32rem minimum width meant for legacy tables. On a phone that turned a
	 * three row table into a horizontally scrolling one.
	 */
	return (string) preg_replace_callback(
		'#(<figure[^>]*class="[^"]*wp-block-table[^"]*"[^>]*>.*?</figure>)|(<table\b.*?</table>)#is',
		static function ( array $matches ) use ( $label ): string {
			if ( ! empty( $matches[1] ) ) {
				return $matches[1];
			}

			return sprintf(
				'<div class="table-scroll" tabindex="0" role="region" aria-label="%s">%s</div>',
				$label,
				$matches[2]
			);
		},
		$content
	);
}
add_filter( 'the_content', 'basalt_wrap_overflowing_tables', 20 );

/**
 * Add layout state to the body element.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function basalt_body_classes( $classes ) {
	$classes = (array) $classes;

	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-featured-image';
	}

	return $classes;
}
add_filter( 'body_class', 'basalt_body_classes' );
