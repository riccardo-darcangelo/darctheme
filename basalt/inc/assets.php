<?php
/**
 * Asset loading.
 *
 * The stylesheets are plain CSS organised with @layer, so there is no build
 * step and buyers can edit a file and see the result. Order matters only for
 * the @layer statement in base.css; everything else is resolved by the cascade
 * layers, which is why the dependency chain below is explicit.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache buster for a theme asset.
 *
 * Uses the file modification time while debugging so local edits show up
 * immediately, and the theme version in production so the value is stable
 * across servers and CDN nodes.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function basalt_asset_version( string $relative_path ): string {
	$debugging = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );

	if ( $debugging ) {
		$file = BASALT_DIR . ltrim( $relative_path, '/' );

		if ( is_readable( $file ) ) {
			return (string) filemtime( $file );
		}
	}

	return BASALT_VERSION;
}

/**
 * Front end styles and scripts.
 *
 * @return void
 */
function basalt_enqueue_assets(): void {
	// Base layer: cascade layer order, reset, element defaults, typography.
	wp_enqueue_style(
		'basalt-base',
		BASALT_URI . 'assets/css/base.css',
		array(),
		basalt_asset_version( 'assets/css/base.css' )
	);

	wp_enqueue_style(
		'basalt-layout',
		BASALT_URI . 'assets/css/layout.css',
		array( 'basalt-base' ),
		basalt_asset_version( 'assets/css/layout.css' )
	);

	wp_enqueue_style(
		'basalt-components',
		BASALT_URI . 'assets/css/components.css',
		array( 'basalt-layout' ),
		basalt_asset_version( 'assets/css/components.css' )
	);

	wp_enqueue_style(
		'basalt-blocks',
		BASALT_URI . 'assets/css/blocks.css',
		array( 'basalt-components' ),
		basalt_asset_version( 'assets/css/blocks.css' )
	);

	// Only loaded where comments are actually rendered.
	if ( is_singular() && ( comments_open() || get_comments_number() ) ) {
		wp_enqueue_style(
			'basalt-comments',
			BASALT_URI . 'assets/css/comments.css',
			array( 'basalt-components' ),
			basalt_asset_version( 'assets/css/comments.css' )
		);
	}

	wp_enqueue_style(
		'basalt-print',
		BASALT_URI . 'assets/css/print.css',
		array( 'basalt-base' ),
		basalt_asset_version( 'assets/css/print.css' ),
		'print'
	);

	/*
	 * A child theme's style.css is enqueued last, so anything it defines wins
	 * on source order.
	 *
	 * With no child theme active this is skipped. The parent's style.css holds
	 * the theme header and nothing else, so loading it would cost a
	 * render-blocking round trip for a file that is pure comment. Buyers who
	 * want custom CSS have a child theme or Additional CSS; the file header
	 * says as much.
	 */
	if ( get_template_directory() !== get_stylesheet_directory() ) {
		$child_stylesheet = get_stylesheet_directory() . '/style.css';

		wp_enqueue_style(
			'basalt-child-style',
			get_stylesheet_uri(),
			array( 'basalt-blocks' ),
			// The child's own timestamp, not the parent's.
			is_readable( $child_stylesheet ) ? (string) filemtime( $child_stylesheet ) : BASALT_VERSION
		);
	}

	// Navigation: menu toggle, submenu handling, focus management.
	wp_enqueue_script(
		'basalt-navigation',
		BASALT_URI . 'assets/js/navigation.js',
		array(),
		basalt_asset_version( 'assets/js/navigation.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Progressive enhancements: sticky header state, back to top, reveal.
	wp_enqueue_script(
		'basalt-interactions',
		BASALT_URI . 'assets/js/interactions.js',
		array(),
		basalt_asset_version( 'assets/js/interactions.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'basalt-navigation',
		'basaltNavStrings',
		array(
			'openMenu'     => __( 'Open menu', 'basalt' ),
			'closeMenu'    => __( 'Close menu', 'basalt' ),
			'openSubmenu'  => __( 'Open submenu', 'basalt' ),
			'closeSubmenu' => __( 'Close submenu', 'basalt' ),
		)
	);

	if ( is_singular() && comments_open() && (bool) get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'basalt_enqueue_assets' );

/**
 * Editor assets.
 *
 * add_editor_style() in setup.php handles the iframed post editor. This adds
 * the same variables to the block editor chrome. The site editor is not part
 * of this: WordPress offers it only to block themes.
 *
 * @return void
 */
function basalt_enqueue_editor_assets(): void {
	wp_enqueue_style(
		'basalt-editor',
		BASALT_URI . 'assets/css/editor.css',
		array(),
		basalt_asset_version( 'assets/css/editor.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'basalt_enqueue_editor_assets' );

/**
 * Mark the theme stylesheets as safe to load with logical properties in RTL.
 *
 * Basalt uses CSS logical properties (margin-inline, padding-block, inset-*)
 * throughout, so no separate rtl.css is needed. Telling WordPress that the
 * stylesheets are already RTL aware prevents it from looking for -rtl files.
 *
 * @return void
 */
function basalt_declare_rtl_support(): void {
	foreach ( array( 'basalt-base', 'basalt-layout', 'basalt-components', 'basalt-blocks' ) as $handle ) {
		wp_style_add_data( $handle, 'rtl', 'no-conflict' );
	}
}
add_action( 'wp_enqueue_scripts', 'basalt_declare_rtl_support', 20 );

/**
 * Preload the featured image of the current singular view.
 *
 * WordPress sets fetchpriority="high" on the first large image it finds in the
 * content, but the featured image is usually rendered by the template above the
 * content, so core cannot see it in time. Preloading it improves LCP measurably
 * on post and product style layouts.
 *
 * @return void
 */
function basalt_preload_featured_image(): void {
	if ( ! is_singular() || ! has_post_thumbnail() ) {
		return;
	}

	$id = (int) get_post_thumbnail_id();

	$src = wp_get_attachment_image_src( $id, 'basalt-hero' );

	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( $id, 'basalt-hero' );
	$sizes  = wp_get_attachment_image_sizes( $id, 'basalt-hero' );

	printf(
		'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
		esc_url( $src[0] ),
		$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
		$sizes ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
	);
}
add_action( 'wp_head', 'basalt_preload_featured_image', 2 );
