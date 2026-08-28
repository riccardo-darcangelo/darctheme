<?php
/**
 * Basalt Child.
 *
 * The parent theme already enqueues this child's style.css last, so there is no
 * enqueue boilerplate to write. What is here is the pattern a real project
 * follows: presentation and structured data in the child theme, content
 * structure in a plugin.
 *
 * @package BasaltChild
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/schema.php';

/**
 * Text domain for the child theme's own strings.
 *
 * @return void
 */
function basalt_child_setup(): void {
	load_child_theme_textdomain( 'basalt-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'basalt_child_setup' );

/**
 * Whether the catalog plugin is providing the data layer.
 *
 * Everything the child theme adds is guarded by this, so activating the child
 * theme without the plugin degrades to a plain Basalt site instead of a fatal
 * error.
 *
 * @return bool
 */
function basalt_child_has_catalog(): bool {
	return defined( 'BASALT_CATALOG_POST_TYPE' ) && post_type_exists( BASALT_CATALOG_POST_TYPE );
}

/**
 * Show the technical data below the content of a catalog entry.
 *
 * Rendered as a description list rather than a table: it is a set of label and
 * value pairs, not tabular data, and a description list stays readable on a
 * phone without any layout tricks.
 *
 * @param string $content Post content.
 * @return string
 */
function basalt_child_append_specs( $content ) {
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || ! basalt_child_has_catalog() ) {
		return $content;
	}

	if ( get_post_type() !== BASALT_CATALOG_POST_TYPE ) {
		return $content;
	}

	$specs = basalt_catalog_get_specs();

	if ( ! $specs ) {
		return $content;
	}

	$rows = '';

	foreach ( $specs as $spec ) {
		$rows .= sprintf(
			'<div class="catalog-specs__row"><dt class="catalog-specs__label">%1$s</dt><dd class="catalog-specs__value">%2$s</dd></div>',
			esc_html( $spec['label'] ),
			esc_html( $spec['value'] )
		);
	}

	$markup = sprintf(
		'<section class="catalog-specs" aria-labelledby="catalog-specs-title"><h2 id="catalog-specs-title">%1$s</h2><dl>%2$s</dl></section>',
		esc_html__( 'Technical data', 'basalt-child' ),
		$rows
	);

	return $content . $markup;
}
add_filter( 'the_content', 'basalt_child_append_specs', 15 );

/**
 * Show the taxonomy terms of a catalog entry above the technical data.
 *
 * The terms link to their archives, which is what makes the category pages
 * reachable for both visitors and crawlers.
 *
 * @return void
 */
function basalt_child_render_terms(): void {
	if ( ! basalt_child_has_catalog() || ! is_singular( BASALT_CATALOG_POST_TYPE ) ) {
		return;
	}

	foreach ( array_keys( basalt_catalog_taxonomies() ) as $taxonomy ) {
		$terms = get_the_terms( get_the_ID(), $taxonomy );

		if ( ! is_array( $terms ) || ! $terms ) {
			continue;
		}

		$object = get_taxonomy( $taxonomy );

		printf(
			'<ul class="catalog-terms" aria-label="%s">',
			esc_attr( $object ? $object->labels->name : $taxonomy )
		);

		foreach ( $terms as $term ) {
			$link = get_term_link( $term );

			if ( is_wp_error( $link ) ) {
				continue;
			}

			printf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( $link ),
				esc_html( $term->name )
			);
		}

		echo '</ul>';
	}
}
add_action( 'basalt_after_header', 'basalt_child_render_terms' );

/**
 * Order catalog archives by menu order, then by title.
 *
 * A catalog has a sensible manual order; publication date is meaningless for it.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function basalt_child_order_catalog_archive( $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! basalt_child_has_catalog() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( BASALT_CATALOG_POST_TYPE ) && ! $query->is_tax( array_keys( basalt_catalog_taxonomies() ) ) ) {
		return;
	}

	$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	$query->set( 'posts_per_page', 24 );
}
add_action( 'pre_get_posts', 'basalt_child_order_catalog_archive' );

/**
 * Use a card grid for catalog archives regardless of the blog setting.
 *
 * @param string $layout Configured layout.
 * @return string
 */
function basalt_child_archive_layout( $layout ) {
	if ( basalt_child_has_catalog() && ( is_post_type_archive( BASALT_CATALOG_POST_TYPE ) || is_tax( array_keys( basalt_catalog_taxonomies() ) ) ) ) {
		return 'grid';
	}

	return $layout;
}
add_filter( 'theme_mod_archive_layout', 'basalt_child_archive_layout' );
