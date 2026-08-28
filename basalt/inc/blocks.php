<?php
/**
 * Block editor integration: style variations, pattern categories, editor hints.
 *
 * Block patterns themselves live in patterns/ and are registered automatically
 * by WordPress, one PHP file per pattern.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register block style variations.
 *
 * Each style has a matching rule in assets/css/blocks.css. Styles are preferred
 * over custom blocks: they survive a theme switch as plain core blocks and add
 * nothing to the editor's JavaScript payload.
 *
 * @return void
 */
function basalt_register_block_styles(): void {
	$styles = array(
		'core/group'     => array(
			array(
				'name'  => 'card',
				'label' => __( 'Card', 'basalt' ),
			),
			array(
				'name'  => 'bordered',
				'label' => __( 'Bordered', 'basalt' ),
			),
			array(
				'name'  => 'section',
				'label' => __( 'Section', 'basalt' ),
			),
		),
		'core/image'     => array(
			array(
				'name'  => 'rounded-lg',
				'label' => __( 'Rounded', 'basalt' ),
			),
			array(
				'name'  => 'frame',
				'label' => __( 'Framed', 'basalt' ),
			),
		),
		'core/list'      => array(
			array(
				'name'  => 'checklist',
				'label' => __( 'Checklist', 'basalt' ),
			),
			array(
				'name'  => 'plain',
				'label' => __( 'No markers', 'basalt' ),
			),
		),
		'core/heading'   => array(
			array(
				'name'  => 'eyebrow',
				'label' => __( 'Eyebrow', 'basalt' ),
			),
			array(
				'name'  => 'underlined',
				'label' => __( 'Underlined', 'basalt' ),
			),
		),
		'core/separator' => array(
			array(
				'name'  => 'thick',
				'label' => __( 'Thick', 'basalt' ),
			),
		),
		'core/quote'     => array(
			array(
				'name'  => 'testimonial',
				'label' => __( 'Testimonial', 'basalt' ),
			),
		),
		'core/columns'   => array(
			array(
				'name'  => 'tight',
				'label' => __( 'Tight gap', 'basalt' ),
			),
		),
		'core/table'     => array(
			array(
				'name'  => 'specs',
				'label' => __( 'Specifications', 'basalt' ),
			),
		),
		'core/details'   => array(
			array(
				'name'  => 'faq',
				'label' => __( 'FAQ item', 'basalt' ),
			),
		),
	);

	foreach ( $styles as $block => $variations ) {
		foreach ( $variations as $variation ) {
			register_block_style( $block, $variation );
		}
	}
}
add_action( 'init', 'basalt_register_block_styles' );

/**
 * Register the theme's pattern categories.
 *
 * @return void
 */
function basalt_register_pattern_categories(): void {
	$categories = array(
		'basalt-hero'     => array(
			'label'       => __( 'Basalt: hero', 'basalt' ),
			'description' => __( 'Opening sections for landing and home pages.', 'basalt' ),
		),
		'basalt-content'  => array(
			'label'       => __( 'Basalt: content', 'basalt' ),
			'description' => __( 'Text, media and feature sections.', 'basalt' ),
		),
		'basalt-catalog'  => array(
			'label'       => __( 'Basalt: catalog', 'basalt' ),
			'description' => __( 'Grids and cards for products, services or projects.', 'basalt' ),
		),
		'basalt-cta'      => array(
			'label'       => __( 'Basalt: call to action', 'basalt' ),
			'description' => __( 'Conversion sections and contact prompts.', 'basalt' ),
		),
		'basalt-faq'      => array(
			'label'       => __( 'Basalt: FAQ', 'basalt' ),
			'description' => __( 'Question and answer sections that emit FAQ structured data.', 'basalt' ),
		),
		'basalt-page'     => array(
			'label'       => __( 'Basalt: full pages', 'basalt' ),
			'description' => __( 'Complete page layouts to start from.', 'basalt' ),
		),
	);

	foreach ( $categories as $slug => $args ) {
		register_block_pattern_category( $slug, $args );
	}
}
add_action( 'init', 'basalt_register_pattern_categories', 9 );

/**
 * Stop the editor from fetching the remote pattern directory.
 *
 * The request to wordpress.org runs on every editor load, is slow on hosts
 * without outbound HTTP, and fills the inserter with patterns that do not use
 * the theme's design tokens.
 *
 * @return bool
 */
function basalt_disable_remote_patterns(): bool {
	/**
	 * Filter whether remote block patterns are loaded.
	 *
	 * @param bool $load Whether to load remote patterns.
	 */
	return (bool) apply_filters( 'basalt_load_remote_block_patterns', false );
}
add_filter( 'should_load_remote_block_patterns', 'basalt_disable_remote_patterns' );

/**
 * Give the editor the same content width as the front end.
 *
 * @return void
 */
function basalt_editor_content_width(): void {
	$css = sprintf(
		':root { --basalt-editor-content-width: %s; }',
		esc_attr( (string) apply_filters( 'basalt_editor_content_width', '44rem' ) )
	);

	wp_add_inline_style( 'basalt-editor', $css );
}
add_action( 'enqueue_block_editor_assets', 'basalt_editor_content_width', 20 );
