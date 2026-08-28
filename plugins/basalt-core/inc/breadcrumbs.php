<?php
/**
 * Breadcrumbs.
 *
 * The trail is built as data, once. The visible breadcrumb block and the
 * BreadcrumbList JSON-LD both read from this array, so they cannot drift apart.
 * That is not a style preference: a visible trail that disagrees with the
 * markup is a structured data error Google reports.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * The breadcrumb trail for the current view.
 *
 * @return array<int, array{name: string, url: string}>
 */
function basalt_core_breadcrumb_items(): array {
	$items = array(
		array(
			'name' => __( 'Home', 'basalt-core' ),
			'url'  => home_url( '/' ),
		),
	);

	/*
	 * The front page is the root of the trail, so it has no trail. Without this
	 * a static front page falls into the is_singular() branch below and lands
	 * on itself: "Home > Home", both pointing at the same URL.
	 */
	if ( is_front_page() ) {
		return $items;
	}

	if ( is_singular() ) {
		$post_id   = (int) get_the_ID();
		$post_type = (string) get_post_type();

		if ( 'page' === $post_type ) {
			foreach ( array_reverse( (array) get_post_ancestors( $post_id ) ) as $ancestor ) {
				$items[] = array(
					'name' => get_the_title( $ancestor ),
					'url'  => (string) get_permalink( $ancestor ),
				);
			}
		} else {
			$object = get_post_type_object( $post_type );

			if ( $object && ! empty( $object->has_archive ) ) {
				$archive = get_post_type_archive_link( $post_type );

				if ( $archive ) {
					$items[] = array(
						'name' => $object->labels->name,
						'url'  => $archive,
					);
				}
			}

			$term = basalt_core_primary_term( $post_id, $post_type );

			if ( $term ) {
				$link = get_term_link( $term );

				if ( ! is_wp_error( $link ) ) {
					$items[] = array(
						'name' => $term->name,
						'url'  => $link,
					);
				}
			}
		}

		$items[] = array(
			'name' => get_the_title( $post_id ),
			'url'  => (string) get_permalink( $post_id ),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			foreach ( array_reverse( (array) get_ancestors( $term->term_id, $term->taxonomy ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, $term->taxonomy );

				if ( $ancestor instanceof WP_Term ) {
					$items[] = array(
						'name' => $ancestor->name,
						'url'  => (string) get_term_link( $ancestor ),
					);
				}
			}

			$items[] = array(
				'name' => $term->name,
				'url'  => (string) get_term_link( $term ),
			);
		}
	} elseif ( is_post_type_archive() ) {
		$items[] = array(
			'name' => post_type_archive_title( '', false ),
			'url'  => (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) ),
		);
	} elseif ( is_author() ) {
		$items[] = array(
			'name' => (string) get_the_author(),
			'url'  => get_author_posts_url( (int) get_query_var( 'author' ) ),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'name' => sprintf(
				/* translators: %s: the search term. */
				__( 'Search results for %s', 'basalt-core' ),
				get_search_query()
			),
			'url'  => (string) get_search_link(),
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'name' => __( 'Page not found', 'basalt-core' ),
			'url'  => home_url( '/' ),
		);
	} elseif ( is_home() ) {
		$blog_page = (int) get_option( 'page_for_posts' );

		if ( $blog_page ) {
			$items[] = array(
				'name' => get_the_title( $blog_page ),
				'url'  => (string) get_permalink( $blog_page ),
			);
		}
	}

	/**
	 * Filter the breadcrumb trail.
	 *
	 * The extension point for custom post types: insert the level that belongs
	 * between the archive and the entry.
	 *
	 * @param array<int, array{name: string, url: string}> $items Trail items.
	 */
	return (array) apply_filters( 'basalt_core_breadcrumb_items', $items );
}

/**
 * Best guess at the primary term of a post.
 *
 * Honours the Rank Math and Yoast primary term when one is set, so a site that
 * already curates this does not get a second opinion from us.
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type.
 * @return WP_Term|null
 */
function basalt_core_primary_term( int $post_id, string $post_type ): ?WP_Term {
	$taxonomies = get_object_taxonomies( $post_type, 'objects' );

	/**
	 * Filter the taxonomies searched for a primary term, in order.
	 *
	 * The first taxonomy that yields a term wins. A post type with several
	 * taxonomies should set this explicitly rather than rely on registration
	 * order, which changes the moment a taxonomy is added.
	 *
	 * @param WP_Taxonomy[] $taxonomies Taxonomy objects, in search order.
	 * @param string        $post_type  The post type being resolved.
	 * @param int           $post_id    The post being resolved.
	 */
	$taxonomies = (array) apply_filters( 'basalt_core_primary_term_taxonomies', $taxonomies, $post_type, $post_id );

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! $taxonomy instanceof WP_Taxonomy || ! $taxonomy->public || ! $taxonomy->hierarchical ) {
			continue;
		}

		foreach ( array( 'rank_math_primary_' . $taxonomy->name, '_yoast_wpseo_primary_' . $taxonomy->name ) as $meta_key ) {
			$primary_id = (int) get_post_meta( $post_id, $meta_key, true );

			if ( $primary_id ) {
				$term = get_term( $primary_id, $taxonomy->name );

				if ( $term instanceof WP_Term ) {
					return $term;
				}
			}
		}

		$terms = get_the_terms( $post_id, $taxonomy->name );

		if ( is_array( $terms ) && isset( $terms[0] ) && $terms[0] instanceof WP_Term ) {
			return $terms[0];
		}
	}

	return null;
}

/**
 * Render the visible breadcrumb trail.
 *
 * Returns an empty string when there is nothing to show, so the caller can skip
 * the surrounding markup rather than render an empty bar.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_core_render_breadcrumbs( array $attributes = array() ): string {
	if ( is_front_page() ) {
		return '';
	}

	// Defer to an SEO plugin that renders its own trail, to avoid two of them.
	if ( basalt_core_seo_plugin_handles( 'breadcrumbs' ) && ! empty( $attributes['deferToSeoPlugin'] ) ) {
		if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
			ob_start();
			rank_math_the_breadcrumbs();

			return (string) ob_get_clean();
		}

		if ( function_exists( 'yoast_breadcrumb' ) ) {
			return (string) yoast_breadcrumb(
				'<nav class="basalt-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'basalt-core' ) . '">',
				'</nav>',
				false
			);
		}
	}

	$items = basalt_core_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return '';
	}

	$last  = count( $items ) - 1;
	$list  = '';

	foreach ( array_values( $items ) as $index => $item ) {
		if ( $index === $last ) {
			/*
			 * The current page is not a link. aria-current tells assistive
			 * technology which entry is where the user already is.
			 */
			$list .= sprintf(
				'<li class="basalt-breadcrumbs__item"><span aria-current="page">%s</span></li>',
				esc_html( $item['name'] )
			);
			continue;
		}

		$list .= sprintf(
			'<li class="basalt-breadcrumbs__item"><a href="%1$s">%2$s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['name'] )
		);
	}

	return sprintf(
		'<nav class="basalt-breadcrumbs" aria-label="%1$s"><ol class="basalt-breadcrumbs__list">%2$s</ol></nav>',
		esc_attr__( 'Breadcrumb', 'basalt-core' ),
		$list
	);
}
