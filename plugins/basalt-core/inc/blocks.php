<?php
/**
 * Blocks provided by the plugin.
 *
 * Dynamic, server rendered blocks are how a block theme keeps exact control
 * over markup. The breadcrumb trail has to emit a nav landmark, an ordered list
 * and aria-current on the last item, and it has to come from the same data as
 * the BreadcrumbList JSON-LD. A pattern built from core blocks cannot promise
 * any of that; a render callback can.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the plugin's blocks.
 *
 * @return void
 */
function basalt_core_register_blocks(): void {
	register_block_type(
		BASALT_CORE_DIR . 'blocks/breadcrumbs',
		array( 'render_callback' => 'basalt_core_breadcrumbs_block' )
	);

	/*
	 * Opening hours: today's status and a table, from the same setting the
	 * LocalBusiness node reads. A pattern of paragraphs would have to be kept
	 * in step by hand, and the first time the hours change it would not be.
	 */
	register_block_type(
		BASALT_CORE_DIR . 'blocks/opening-hours',
		array( 'render_callback' => 'basalt_core_opening_hours_block' )
	);

	/*
	 * The three maintenance texts. A block rather than words typed into the
	 * template, so the page can be updated from the settings screen while the
	 * site is down and the editor is the last thing anybody wants to open.
	 */
	register_block_type(
		BASALT_CORE_DIR . 'blocks/maintenance-text',
		array( 'render_callback' => 'basalt_core_maintenance_block' )
	);

	/*
	 * One question under a page that explains something. Server rendered
	 * because the answer has to be counted somewhere, and a form post is the
	 * only way to do that without a script and without a cookie.
	 */
	register_block_type(
		BASALT_CORE_DIR . 'blocks/feedback',
		array( 'render_callback' => 'basalt_core_feedback_block' )
	);
}
add_action( 'init', 'basalt_core_register_blocks' );

/**
 * Render callback for the breadcrumbs block.
 *
 * Returns an empty string on the front page and anywhere else the trail would
 * be a single entry, so the block leaves no empty wrapper behind.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_core_breadcrumbs_block( $attributes ): string {
	$markup = basalt_core_render_breadcrumbs( (array) $attributes );

	if ( '' === $markup ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes( array( 'class' => 'basalt-breadcrumbs-block' ) ),
		$markup
	);
}
