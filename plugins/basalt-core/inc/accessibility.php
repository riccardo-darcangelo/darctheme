<?php
/**
 * Accessibility corrections for core blocks.
 *
 * These belong in a plugin rather than a theme for two reasons. They fix core
 * output, so they are just as necessary under any other theme; and a site that
 * changes theme should not silently lose them.
 *
 * Everything here is a landmark naming problem. WCAG does not forbid two
 * navigation regions on a page, but it does require that a user can tell them
 * apart. Core renders core/navigation as an unnamed <nav> and core/search as an
 * unnamed role="search", so a page with a header menu and a footer menu gives a
 * screen reader user two entries called "navigation" and no way to choose.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Give the core navigation block an accessible name.
 *
 * The name comes from the menu the block renders, so it matches what the site
 * owner already called it and needs no extra configuration. An explicit
 * aria-label on the block always wins.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_core_name_navigation_block( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/navigation' ) {
		return $content;
	}

	$label = '';
	$ref   = (int) ( $block['attrs']['ref'] ?? 0 );

	if ( $ref ) {
		$menu = get_post( $ref );

		if ( $menu instanceof WP_Post ) {
			$label = $menu->post_title;
		}
	}

	if ( '' === trim( $label ) ) {
		$label = __( 'Menu', 'basalt-core' );
	}

	return basalt_core_label_tag( $content, 'NAV', $label );
}
add_filter( 'render_block', 'basalt_core_name_navigation_block', 10, 2 );

/**
 * Give the core search block an accessible name.
 *
 * Reuses the block's own label text, so renaming the visible label renames the
 * landmark with it.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_core_name_search_block( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/search' ) {
		return $content;
	}

	$label = trim( wp_strip_all_tags( (string) ( $block['attrs']['label'] ?? '' ) ) );

	if ( '' === $label ) {
		$label = __( 'Search', 'basalt-core' );
	}

	return basalt_core_label_tag( $content, 'FORM', $label );
}
add_filter( 'render_block', 'basalt_core_name_search_block', 10, 2 );

/**
 * Put an aria-label on the first tag of a given kind, if it has none.
 *
 * Uses WP_HTML_Tag_Processor rather than a regular expression. That is not
 * fastidiousness: the first version of this checked whether the block markup
 * contained the string "aria-label=" anywhere and bailed if it did. Core's
 * icon-only search button carries its own aria-label, so the check matched the
 * button and the form was never named. The tag processor asks the question that
 * was actually meant, which is whether *this element* has the attribute.
 *
 * @param string $content Block markup.
 * @param string $tag     Tag name to label, uppercase.
 * @param string $label   The accessible name.
 * @return string
 */
function basalt_core_label_tag( $content, string $tag, string $label ): string {
	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return (string) $content;
	}

	$html = new WP_HTML_Tag_Processor( $content );

	if ( ! $html->next_tag( array( 'tag_name' => $tag ) ) ) {
		return $content;
	}

	if ( null !== $html->get_attribute( 'aria-label' ) || null !== $html->get_attribute( 'aria-labelledby' ) ) {
		return $content;
	}

	$html->set_attribute( 'aria-label', $label );

	return $html->get_updated_html();
}

/**
 * Name the two landmarks core renders without one.
 *
 * core/post-template and core/comment-template produce list regions that some
 * screen readers announce; naming them costs nothing and removes ambiguity on
 * archives that show more than one loop.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_core_name_query_block( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/query' ) {
		return $content;
	}

	if ( ! is_string( $content ) || false === strpos( $content, '<nav' ) ) {
		return $content;
	}

	// The pagination inside a query is a navigation region; name it.
	return (string) str_replace(
		'<nav class="wp-block-query-pagination',
		'<nav aria-label="' . esc_attr__( 'Pagination', 'basalt-core' ) . '" class="wp-block-query-pagination',
		$content
	);
}
add_filter( 'render_block', 'basalt_core_name_query_block', 10, 2 );

/**
 * Warn the editor when an image is published without alternative text.
 *
 * Not a front end change: a notice in the editor, where the person who can
 * still fix it is standing. Decorative images are marked as such with an empty
 * alt attribute, which this respects.
 *
 * @return void
 */
function basalt_core_editor_assets(): void {
	wp_enqueue_style(
		'basalt-core-editor',
		BASALT_CORE_URL . 'assets/editor.css',
		array(),
		BASALT_CORE_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'basalt_core_editor_assets' );
