<?php
/**
 * Elementor: survival, not integration.
 *
 * Elementor is on roughly a third of all WordPress sites, so a buyer may well
 * install it on top of Basalt. It is deliberately not integrated:
 *
 * - Elementor is not a plugin a theme supports, it is a plugin that replaces
 *   the theme. Registering its theme locations would hand templates/ over to
 *   Elementor Pro's theme builder, and Basalt would be a container for someone
 *   else's markup with none of the accessibility work reachable.
 * - A default Elementor page ships several hundred kilobytes of JavaScript
 *   before the first theme asset. That is the opposite of what this theme is
 *   for, and a customer who wants it is better served by a theme built around
 *   it.
 *
 * What the theme owes an Elementor page is that it renders correctly and gets
 * out of the way. That is one thing: width. Everything else already works,
 * because Elementor brings its own layout, its own typography and its own
 * colours.
 *
 * Those colours are Elementor's, not the theme's, and its untouched default
 * kit fails contrast on a white background. The theme does not overwrite them:
 * a customer who has chosen brand colours in Elementor would lose them without
 * being asked. Setting the global colours is the first step of the Elementor
 * section in the documentation instead.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a post was built with Elementor rather than with blocks.
 *
 * Read from post meta rather than through Elementor's API: the meta is what
 * Elementor itself checks first, it is set before the API is available on some
 * requests, and it means this file loads without Elementor present.
 *
 * @param int|null $post_id Post ID, defaults to the current post.
 * @return bool
 */
function basalt_is_built_with_elementor( ?int $post_id = null ): bool {
	if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
		return false;
	}

	$post_id = $post_id ?? (int) get_the_ID();

	return $post_id > 0 && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

/**
 * Give Elementor's root element the full width of the content area.
 *
 * The post content block is a constrained layout, so every direct child is
 * clamped to the theme's content width. That is right for prose and wrong for
 * a builder: an Elementor section set to full width would come out as a narrow
 * column, and the person who built it would reasonably conclude the theme is
 * broken.
 *
 * alignfull is the block layout system's own way of saying "this one is not
 * clamped", so the theme's content and wide sizes stay in charge of everything
 * else and Elementor decides the width of its own sections, which is what it
 * is for.
 *
 * @param string               $content Rendered block markup.
 * @param array<string, mixed> $block   Parsed block.
 * @return string
 */
function basalt_elementor_full_width( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/post-content' ) {
		return $content;
	}

	if ( ! is_string( $content ) || ! basalt_is_built_with_elementor() ) {
		return $content;
	}

	$tags = new WP_HTML_Tag_Processor( $content );

	while ( $tags->next_tag( array( 'tag_name' => 'DIV', 'class_name' => 'elementor' ) ) ) {
		$tags->add_class( 'alignfull' );
	}

	return $tags->get_updated_html();
}
add_filter( 'render_block', 'basalt_elementor_full_width', 10, 2 );
