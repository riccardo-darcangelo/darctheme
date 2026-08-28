<?php
/**
 * Theme setup: supports, menus, image sizes, widget areas.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme features.
 *
 * Everything that is purely visual configuration lives in theme.json.
 * This function only declares capabilities that theme.json cannot express.
 *
 * @return void
 */
function basalt_setup(): void {
	/*
	 * Translations. A child theme's languages/ directory wins over the parent,
	 * so translators can override single strings without forking the .po file.
	 */
	load_theme_textdomain( 'basalt', BASALT_DIR . 'languages' );

	// Let WordPress own the document title. Never hardcode <title>.
	add_theme_support( 'title-tag' );

	// Feed links for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// Featured images for posts, pages and every custom post type added later.
	add_theme_support( 'post-thumbnails' );

	// Modern, valid markup for the templates WordPress renders on our behalf.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	// Logo. Flexible so it adapts to whatever the site owner uploads.
	add_theme_support(
		'custom-logo',
		array(
			'height'               => 96,
			'width'                => 320,
			'flex-height'          => true,
			'flex-width'           => true,
			'unlink-homepage-logo' => false,
			'header-text'          => array( 'site-title', 'site-description' ),
		)
	);

	// Editor and block features.
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-units' );
	add_theme_support( 'link-color' );
	add_theme_support( 'appearance-tools' );

	/*
	 * Deliberately not enabled: block-template-parts.
	 *
	 * It would expose a second, editable source of header and footer markup
	 * next to header.php and footer.php. Two sources for the same region is the
	 * fastest way to lose the heading order and landmark structure the SEO and
	 * accessibility work depends on. The hybrid part of this theme is the
	 * theme.json design system, the patterns and the block styles.
	 */

	// Selective refresh keeps the customizer preview from reloading the page.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Post formats used by the blog templates.
	add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio' ) );

	// Editor stylesheet, so the editor matches the front end.
	add_editor_style( 'assets/css/editor.css' );

	/*
	 * Content width. Kept in sync with the contentSize in theme.json so that
	 * oEmbeds and legacy [caption] shortcodes size themselves correctly.
	 */
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 704; // 44rem at a 16px root.
	}

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'basalt' ),
			'footer'  => __( 'Footer menu', 'basalt' ),
			'social'  => __( 'Social links', 'basalt' ),
			'legal'   => __( 'Legal menu', 'basalt' ),
		)
	);

	/*
	 * Image sizes.
	 *
	 * Cropped sizes exist so cards never shift layout while images load; the
	 * aspect ratio is fixed and the templates emit width and height attributes.
	 */
	add_image_size( 'basalt-card', 800, 500, true );
	add_image_size( 'basalt-card-2x', 1600, 1000, true );
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

/**
 * Register widget areas.
 *
 * @return void
 */
function basalt_widgets_init(): void {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'basalt' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Shown next to posts and on pages using the "With sidebar" template.', 'basalt' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title">',
			'after_title'   => '</h2>',
		)
	);

	for ( $column = 1; $column <= 4; $column++ ) {
		register_sidebar(
			array(
				'name'          => sprintf(
					/* translators: %d: footer column number. */
					__( 'Footer column %d', 'basalt' ),
					$column
				),
				'id'            => 'footer-' . $column,
				'description'   => __( 'Empty columns are skipped, so the footer grid adapts to how many you fill.', 'basalt' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget__title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'basalt_widgets_init' );

/**
 * Set the content width for full width and landing templates.
 *
 * @return void
 */
function basalt_adjust_content_width(): void {
	if ( is_page_template( array( 'templates/template-full-width.php', 'templates/template-landing.php' ) ) ) {
		$GLOBALS['content_width'] = 1280;
	}
}
add_action( 'template_redirect', 'basalt_adjust_content_width' );
