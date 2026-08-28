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
