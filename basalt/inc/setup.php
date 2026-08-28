<?php
/**
 * Theme setup.
 *
 * A block theme needs far fewer declarations than a classic one: WordPress
 * infers most support from the presence of templates/ and theme.json. What is
 * left are the things theme.json cannot express.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme features.
 *
 * @return void
 */
function basalt_setup(): void {
	/*
	 * Translations. A child theme's languages/ directory wins over the parent,
	 * so a translator can override a single string without forking the file.
	 */
	load_theme_textdomain( 'basalt', BASALT_DIR . 'languages' );

	/*
	 * These four are implied for block themes, but declaring them keeps the
	 * theme honest if it is ever loaded on a site where a plugin has removed
	 * one of them.
	 */
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	// Editor stylesheet, so the editor matches the front end.
	add_editor_style( 'assets/css/editor.css' );

	/*
	 * Image sizes.
	 *
	 * Cropped, so a card never shifts layout while its image loads: the aspect
	 * ratio is fixed and the block emits width and height.
	 */
	add_image_size( 'basalt-card', 800, 500, true );
	add_image_size( 'basalt-hero', 1920, 900, true );
	add_image_size( 'basalt-thumb', 160, 160, true );
}
add_action( 'after_setup_theme', 'basalt_setup' );

/**
 * Expose the custom image sizes in the editor's size picker.
 *
 * @param array<string, string> $sizes Size name keyed by size slug.
 * @return array<string, string>
 */
function basalt_custom_image_size_names( $sizes ) {
	return array_merge(
		(array) $sizes,
		array(
			'basalt-card'  => __( 'Card', 'basalt' ),
			'basalt-hero'  => __( 'Hero', 'basalt' ),
			'basalt-thumb' => __( 'Square thumbnail', 'basalt' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'basalt_custom_image_size_names' );
