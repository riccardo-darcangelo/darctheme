<?php
/**
 * Template tags used by the templates in template-parts/.
 *
 * Every tag echoes escaped output and is safe to call unconditionally.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Publication and update dates for the current post.
 *
 * Emits both dates as <time> elements. Search engines read the machine
 * readable datetime attribute, so it must carry the ISO 8601 value even when
 * the visible text uses the site's date format.
 *
 * @return void
 */
function basalt_posted_on(): void {
	$published = get_the_date( DATE_W3C );
	$modified  = get_the_modified_date( DATE_W3C );

	printf(
		'<time class="entry__date" datetime="%1$s">%2$s</time>',
		esc_attr( $published ),
		esc_html( get_the_date() )
	);

	// Only show the update date when it is a meaningful distance from publication.
	if ( strtotime( $modified ) > strtotime( $published ) + DAY_IN_SECONDS ) {
		printf(
			' <time class="entry__date entry__date--modified" datetime="%1$s">%2$s</time>',
			esc_attr( $modified ),
			esc_html(
				sprintf(
					/* translators: %s: last update date. */
					__( 'Updated %s', 'basalt' ),
					get_the_modified_date()
				)
			)
		);
	}
}

/**
 * Post author with a link to the author archive.
 *
 * @return void
 */
function basalt_posted_by(): void {
	printf(
		'<span class="entry__author">%1$s <a class="entry__author-link" href="%2$s" rel="author">%3$s</a></span>',
		esc_html__( 'By', 'basalt' ),
		esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);
}

/**
 * Estimated reading time in minutes.
 *
 * @param int|WP_Post|null $post Optional. Post to measure.
 * @return int
 */
function basalt_reading_time( $post = null ): int {
	$content = get_post_field( 'post_content', $post );
	$words   = str_word_count( wp_strip_all_tags( (string) $content ) );

	/**
	 * Filter the words-per-minute rate used for reading time.
	 *
	 * @param int $wpm Words per minute.
	 */
	$wpm = (int) apply_filters( 'basalt_words_per_minute', 220 );

	return max( 1, (int) ceil( $words / max( 1, $wpm ) ) );
}

/**
 * Category and tag list for the current post.
 *
 * @return void
 */
function basalt_entry_taxonomies(): void {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	$categories = get_the_category_list( '', '', get_the_ID() );
	$tags       = get_the_tag_list( '', '', '', get_the_ID() );

	if ( ! $categories && ! $tags ) {
		return;
	}

	echo '<div class="entry__taxonomies">';

	if ( $categories ) {
		printf(
			'<div class="entry__terms entry__terms--category"><span class="entry__terms-label">%1$s</span> %2$s</div>',
			esc_html__( 'Categories', 'basalt' ),
			wp_kses_post( $categories )
		);
	}

	if ( $tags && ! is_wp_error( $tags ) ) {
		printf(
			'<div class="entry__terms entry__terms--tag"><span class="entry__terms-label">%1$s</span> %2$s</div>',
			esc_html__( 'Tags', 'basalt' ),
			wp_kses_post( $tags )
		);
	}

	echo '</div>';
}

/**
 * Featured image for the current entry.
 *
 * On archives the image is wrapped in the permalink and hidden from the
 * accessibility tree, because the heading right below it is the real link.
 *
 * @param string $size          Image size slug.
 * @param bool   $is_lcp_target Whether this image is likely the largest paint.
 * @return void
 */
function basalt_post_thumbnail( string $size = 'basalt-card', bool $is_lcp_target = false ): void {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	$attr = array(
		'class'    => 'entry__image',
		'decoding' => 'async',
	);

	if ( $is_lcp_target ) {
		// Never lazy load the element that decides the LCP score.
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	}

	if ( is_singular() ) {
		echo '<figure class="entry__media">';
		the_post_thumbnail( $size, $attr );

		$caption = get_the_post_thumbnail_caption();

		if ( $caption ) {
			printf( '<figcaption class="entry__media-caption">%s</figcaption>', wp_kses_post( $caption ) );
		}

		echo '</figure>';

		return;
	}

	$attr['alt']         = '';
	$attr['aria-hidden'] = 'true';

	printf( '<a class="entry__media" href="%s" tabindex="-1" aria-hidden="true">', esc_url( get_permalink() ) );
	the_post_thumbnail( $size, $attr );
	echo '</a>';
}

/**
 * Accessible numbered pagination for archives.
 *
 * @return void
 */
function basalt_pagination(): void {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => '<span aria-hidden="true">&larr;</span> <span>' . esc_html__( 'Previous', 'basalt' ) . '</span>',
			'next_text' => '<span>' . esc_html__( 'Next', 'basalt' ) . '</span> <span aria-hidden="true">&rarr;</span>',
		)
	);

	if ( empty( $links ) ) {
		return;
	}

	printf( '<nav class="pagination" aria-label="%s"><ul class="pagination__list">', esc_attr__( 'Posts pagination', 'basalt' ) );

	foreach ( $links as $link ) {
		printf( '<li class="pagination__item">%s</li>', wp_kses_post( $link ) );
	}

	echo '</ul></nav>';
}

/**
 * Previous and next links on singular views.
 *
 * @return void
 */
function basalt_post_navigation(): void {
	$previous = get_previous_post();
	$next     = get_next_post();

	if ( ! $previous && ! $next ) {
		return;
	}

	printf( '<nav class="post-nav" aria-label="%s"><ul class="post-nav__list">', esc_attr__( 'Continue reading', 'basalt' ) );

	if ( $previous ) {
		printf(
			'<li class="post-nav__item post-nav__item--prev"><a href="%1$s" rel="prev"><span class="post-nav__label">%2$s</span><span class="post-nav__title">%3$s</span></a></li>',
			esc_url( get_permalink( $previous ) ),
			esc_html__( 'Previous post', 'basalt' ),
			esc_html( get_the_title( $previous ) )
		);
	}

	if ( $next ) {
		printf(
			'<li class="post-nav__item post-nav__item--next"><a href="%1$s" rel="next"><span class="post-nav__label">%2$s</span><span class="post-nav__title">%3$s</span></a></li>',
			esc_url( get_permalink( $next ) ),
			esc_html__( 'Next post', 'basalt' ),
			esc_html( get_the_title( $next ) )
		);
	}

	echo '</ul></nav>';
}

/**
 * The site logo, or the site title when no logo is set.
 *
 * The homepage renders the branding inside an H1 only when no other H1 exists
 * on the page; every other view uses a plain div so the content keeps the
 * single H1 that search engines expect.
 *
 * @return void
 */
function basalt_site_branding(): void {
	$is_home_without_content_heading = is_front_page() && ! is_singular();
	$tag                             = $is_home_without_content_heading ? 'h1' : 'div';

	echo '<div class="site-branding">';

	if ( has_custom_logo() ) {
		the_custom_logo();
	}

	printf( '<%1$s class="site-branding__title%2$s">', esc_attr( $tag ), has_custom_logo() ? ' screen-reader-text' : '' );
	printf(
		'<a class="site-branding__link" href="%1$s" rel="home">%2$s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
	printf( '</%s>', esc_attr( $tag ) );

	$description = get_bloginfo( 'description', 'display' );

	if ( $description && ! has_custom_logo() ) {
		printf( '<p class="site-branding__description">%s</p>', esc_html( $description ) );
	}

	echo '</div>';
}

/**
 * Breadcrumb trail.
 *
 * Defers to Rank Math, Yoast or SEOPress when one of them is active, so the
 * page never carries two competing BreadcrumbList markups. Otherwise renders
 * the theme's own trail; the matching JSON-LD is emitted by inc/seo.php.
 *
 * @return void
 */
function basalt_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		rank_math_the_breadcrumbs();
		return;
	}

	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'basalt' ) . '">', '</nav>' );
		return;
	}

	$items = basalt_get_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return;
	}

	printf( '<nav class="breadcrumbs" aria-label="%s"><ol class="breadcrumbs__list">', esc_attr__( 'Breadcrumb', 'basalt' ) );

	$last = count( $items ) - 1;

	foreach ( $items as $index => $item ) {
		if ( $index === $last ) {
			printf(
				'<li class="breadcrumbs__item"><span aria-current="page">%s</span></li>',
				esc_html( $item['name'] )
			);
			continue;
		}

		printf(
			'<li class="breadcrumbs__item"><a href="%1$s">%2$s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['name'] )
		);
	}

	echo '</ol></nav>';
}

/**
 * Build the breadcrumb trail for the current view.
 *
 * Returned as data rather than markup so the same trail feeds both the visible
 * breadcrumb and the BreadcrumbList JSON-LD.
 *
 * @return array<int, array{name: string, url: string}>
 */
function basalt_get_breadcrumb_items(): array {
	$items = array(
		array(
			'name' => __( 'Home', 'basalt' ),
			'url'  => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$post_id   = get_the_ID();
		$post_type = get_post_type();

		if ( 'page' === $post_type ) {
			foreach ( array_reverse( (array) get_post_ancestors( $post_id ) ) as $ancestor ) {
				$items[] = array(
					'name' => get_the_title( $ancestor ),
					'url'  => (string) get_permalink( $ancestor ),
				);
			}
		} else {
			$post_type_object = get_post_type_object( $post_type );

			if ( $post_type_object && ! empty( $post_type_object->has_archive ) ) {
				$archive = get_post_type_archive_link( $post_type );

				if ( $archive ) {
					$items[] = array(
						'name' => $post_type_object->labels->name,
						'url'  => $archive,
					);
				}
			}

			$primary_term = basalt_get_primary_term( $post_id, $post_type );

			if ( $primary_term ) {
				$items[] = array(
					'name' => $primary_term->name,
					'url'  => (string) get_term_link( $primary_term ),
				);
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
				/* translators: %s: search query. */
				__( 'Search results for %s', 'basalt' ),
				get_search_query()
			),
			'url'  => (string) get_search_link(),
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'name' => __( 'Page not found', 'basalt' ),
			'url'  => home_url( '/' ),
		);
	} elseif ( is_home() && ! is_front_page() ) {
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
	 * Custom post types added by a child theme or a plugin can insert their own
	 * levels here, for example a product category between archive and product.
	 *
	 * @param array<int, array{name: string, url: string}> $items Trail items.
	 */
	return (array) apply_filters( 'basalt_breadcrumb_items', $items );
}

/**
 * Best guess at the primary term of a post.
 *
 * Uses the Rank Math or Yoast primary term when one is set, otherwise the
 * first term of the post type's first hierarchical taxonomy.
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type.
 * @return WP_Term|null
 */
function basalt_get_primary_term( int $post_id, string $post_type ): ?WP_Term {
	$taxonomies = get_object_taxonomies( $post_type, 'objects' );

	/**
	 * Filter the taxonomies searched for a primary term, in order.
	 *
	 * The first taxonomy that yields a term wins, so reordering this decides
	 * which term appears in the breadcrumb. A post type with several
	 * taxonomies should set this explicitly rather than rely on registration
	 * order, which changes the moment a taxonomy is added.
	 *
	 * @param WP_Taxonomy[] $taxonomies Taxonomy objects, in search order.
	 * @param string        $post_type  The post type being resolved.
	 * @param int           $post_id    The post being resolved.
	 */
	$taxonomies = (array) apply_filters( 'basalt_primary_term_taxonomies', $taxonomies, $post_type, $post_id );

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! $taxonomy instanceof WP_Taxonomy ) {
			continue;
		}

		if ( ! $taxonomy->public || ! $taxonomy->hierarchical ) {
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
