<?php
/**
 * Asset loading.
 *
 * Plain CSS, no build step, so a buyer can edit a file and see the result. The
 * dependency chain below is what establishes the cascade order: base, layout,
 * components, blocks, then a child theme's style.css.
 *
 * Cascade layers are deliberately not used; see the note at the top of
 * assets/css/base.css for why they lose every collision with the CSS that
 * theme.json generates.
 *
 * The theme ships no JavaScript. The block editor supplies what the navigation
 * overlay needs, and it is loaded only on pages that actually use one.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache buster for a theme asset.
 *
 * The file modification time while debugging, so local edits show up at once;
 * the theme version in production, so the value is stable across servers and
 * CDN nodes.
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
 * The theme's stylesheets, in cascade order.
 *
 * @return array<string, array{deps: string[], media?: string, conditional?: callable}>
 */
function basalt_stylesheets(): array {
	return array(
		'base'       => array( 'deps' => array() ),
		'layout'     => array( 'deps' => array( 'basalt-base' ) ),
		'components' => array( 'deps' => array( 'basalt-layout' ) ),
		'blocks'     => array( 'deps' => array( 'basalt-components' ) ),
		'comments'   => array(
			'deps'        => array( 'basalt-components' ),
			// Only where comments actually render.
			'conditional' => static fn(): bool => is_singular() && ( comments_open() || get_comments_number() > 0 ),
		),
		'print'      => array(
			'deps'  => array( 'basalt-base' ),
			'media' => 'print',
		),
	);
}

/**
 * Enqueue the front end styles.
 *
 * @return void
 */
function basalt_enqueue_assets(): void {
	foreach ( basalt_stylesheets() as $slug => $config ) {
		if ( isset( $config['conditional'] ) && ! ( $config['conditional'] )() ) {
			continue;
		}

		$path = 'assets/css/' . $slug . '.css';

		wp_enqueue_style(
			'basalt-' . $slug,
			BASALT_URI . $path,
			$config['deps'],
			basalt_asset_version( $path ),
			$config['media'] ?? 'all'
		);
	}

	/*
	 * A child theme's style.css is enqueued last, so anything it defines wins
	 * on source order.
	 *
	 * With no child theme active this is skipped: the parent's style.css holds
	 * the theme header and nothing else, so loading it would cost a
	 * render-blocking round trip for a file that is pure comment.
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
}
add_action( 'wp_enqueue_scripts', 'basalt_enqueue_assets' );

/**
 * Mark the stylesheets as RTL aware.
 *
 * Basalt uses CSS logical properties throughout, so no separate rtl.css is
 * needed. Telling WordPress that prevents it from looking for -rtl files.
 *
 * @return void
 */
function basalt_declare_rtl_support(): void {
	foreach ( array_keys( basalt_stylesheets() ) as $slug ) {
		wp_style_add_data( 'basalt-' . $slug, 'rtl', 'no-conflict' );
	}
}
add_action( 'wp_enqueue_scripts', 'basalt_declare_rtl_support', 20 );

/**
 * Preload the featured image of the current singular view.
 *
 * WordPress sets fetchpriority="high" on the first large image it finds in the
 * content, but a template that renders the featured image above the content
 * defeats that heuristic. Preloading it is measurable on LCP for post and
 * product style layouts.
 *
 * @return void
 */
function basalt_preload_featured_image(): void {
	if ( ! is_singular() || ! has_post_thumbnail() ) {
		return;
	}

	$src = wp_get_attachment_image_src( (int) get_post_thumbnail_id(), 'basalt-hero' );

	if ( ! $src ) {
		return;
	}

	$id     = (int) get_post_thumbnail_id();
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
