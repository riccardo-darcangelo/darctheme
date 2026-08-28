<?php
/**
 * Search engine output: meta tags and Schema.org structured data.
 *
 * Design rule for this module: never compete with an SEO plugin. Every output
 * checks whether a plugin already owns that responsibility and stays silent if
 * it does. A page with two canonical tags or two Article nodes is worse than a
 * page with none.
 *
 * The theme is fully usable without any SEO plugin, which is the point: a small
 * business site gets correct titles, descriptions, social cards, breadcrumbs and
 * structured data out of the box.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme should emit description and social meta tags.
 *
 * @return bool
 */
function basalt_core_should_output_meta(): bool {
	$enabled = (bool) basalt_core_get( 'meta_enabled' ) && ! basalt_core_seo_plugin_handles( 'meta' );

	/**
	 * Filter whether the theme emits meta tags.
	 *
	 * @param bool $enabled Whether to emit meta tags.
	 */
	return (bool) apply_filters( 'basalt_core_output_meta', $enabled );
}

/**
 * Whether the theme should emit its Schema.org graph.
 *
 * @return bool
 */
function basalt_core_should_output_schema(): bool {
	$enabled = (bool) basalt_core_get( 'schema_enabled' ) && ! basalt_core_seo_plugin_handles( 'schema' );

	/**
	 * Filter whether the theme emits structured data.
	 *
	 * @param bool $enabled Whether to emit JSON-LD.
	 */
	return (bool) apply_filters( 'basalt_core_output_schema', $enabled );
}

/* -------------------------------------------------------------------------
 * Meta tags
 * ---------------------------------------------------------------------- */

/**
 * Description, Open Graph and Twitter Card tags.
 *
 * @return void
 */
function basalt_core_meta_tags(): void {
	if ( ! basalt_core_should_output_meta() ) {
		return;
	}

	$description = basalt_core_meta_description();
	$title       = wp_get_document_title();
	$url         = basalt_core_current_url();
	$image       = basalt_core_share_image();

	$tags = array();

	if ( $description ) {
		$tags[] = array( 'name', 'description', $description );
		$tags[] = array( 'property', 'og:description', $description );
		$tags[] = array( 'name', 'twitter:description', $description );
	}

	$tags[] = array( 'property', 'og:title', $title );
	$tags[] = array( 'name', 'twitter:title', $title );
	$tags[] = array( 'property', 'og:type', is_singular( 'post' ) ? 'article' : 'website' );
	$tags[] = array( 'property', 'og:url', $url );
	$tags[] = array( 'property', 'og:site_name', get_bloginfo( 'name' ) );
	$tags[] = array( 'property', 'og:locale', get_locale() );

	if ( $image ) {
		$tags[] = array( 'property', 'og:image', $image['url'] );
		$tags[] = array( 'name', 'twitter:image', $image['url'] );

		if ( ! empty( $image['width'] ) && ! empty( $image['height'] ) ) {
			$tags[] = array( 'property', 'og:image:width', (string) $image['width'] );
			$tags[] = array( 'property', 'og:image:height', (string) $image['height'] );
		}

		if ( ! empty( $image['alt'] ) ) {
			$tags[] = array( 'property', 'og:image:alt', $image['alt'] );
		}

		$tags[] = array( 'name', 'twitter:card', 'summary_large_image' );
	} else {
		$tags[] = array( 'name', 'twitter:card', 'summary' );
	}

	$handle = (string) basalt_core_get( 'meta_twitter_site' );

	if ( $handle ) {
		$tags[] = array( 'name', 'twitter:site', '@' . $handle );
	}

	if ( is_singular( 'post' ) ) {
		$tags[] = array( 'property', 'article:published_time', get_the_date( DATE_W3C ) );
		$tags[] = array( 'property', 'article:modified_time', get_the_modified_date( DATE_W3C ) );
	}

	/**
	 * Filter the meta tags before they are printed.
	 *
	 * @param array<int, array{0: string, 1: string, 2: string}> $tags Attribute, name, content.
	 */
	$tags = (array) apply_filters( 'basalt_core_meta_tags', $tags );

	echo "\n<!-- Basalt meta -->\n";

	foreach ( $tags as $tag ) {
		if ( ! is_array( $tag ) || 3 !== count( $tag ) || '' === trim( (string) $tag[2] ) ) {
			continue;
		}

		printf(
			'<meta %1$s="%2$s" content="%3$s">' . "\n",
			esc_attr( $tag[0] ),
			esc_attr( $tag[1] ),
			esc_attr( $tag[2] )
		);
	}
}
add_action( 'wp_head', 'basalt_core_meta_tags', 5 );

/**
 * The description for the current view.
 *
 * Order of preference: a hand written excerpt, the term description, the site
 * tagline. Content is only auto-trimmed as a last resort, because a truncated
 * first paragraph rarely reads like a useful snippet.
 *
 * @return string
 */
function basalt_core_meta_description(): string {
	$description = '';

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$description = $post->post_excerpt;
			} else {
				$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '' );
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && $term->description ) {
			$description = $term->description;
		}
	} elseif ( is_author() ) {
		$description = (string) get_the_author_meta( 'description', (int) get_query_var( 'author' ) );
	} elseif ( is_post_type_archive() ) {
		$post_type = get_queried_object();

		if ( $post_type instanceof WP_Post_Type && $post_type->description ) {
			$description = $post_type->description;
		}
	}

	if ( '' === trim( (string) $description ) ) {
		$description = (string) get_bloginfo( 'description' );
	}

	$description = wp_strip_all_tags( strip_shortcodes( (string) $description ), true );

	// Around 155 characters is what Google renders before truncating.
	if ( mb_strlen( $description ) > 160 ) {
		$description = rtrim( mb_substr( $description, 0, 157 ), " \t\n\r\0\x0B,.;:" ) . '…';
	}

	/**
	 * Filter the meta description.
	 *
	 * @param string $description The description.
	 */
	return (string) apply_filters( 'basalt_core_meta_description', $description );
}

/**
 * The sharing image for the current view.
 *
 * @return array{url: string, width: int, height: int, alt: string}|null
 */
function basalt_core_share_image(): ?array {
	$attachment_id = 0;

	if ( is_singular() && has_post_thumbnail() ) {
		$attachment_id = (int) get_post_thumbnail_id();
	}

	if ( ! $attachment_id ) {
		$attachment_id = (int) basalt_core_get( 'meta_default_image' );
	}

	if ( ! $attachment_id ) {
		$attachment_id = (int) get_theme_mod( 'custom_logo' );
	}

	if ( ! $attachment_id ) {
		return null;
	}

	$source = wp_get_attachment_image_src( $attachment_id, 'full' );

	if ( ! $source ) {
		return null;
	}

	return array(
		'url'    => (string) $source[0],
		'width'  => (int) $source[1],
		'height' => (int) $source[2],
		'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Absolute URL of the current request, without tracking parameters.
 *
 * @return string
 */
function basalt_core_current_url(): string {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_home() ) {
		$blog_page = (int) get_option( 'page_for_posts' );

		if ( $blog_page ) {
			return (string) get_permalink( $blog_page );
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}

	if ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( (string) get_query_var( 'post_type' ) );

		if ( $link ) {
			return $link;
		}
	}

	if ( is_author() ) {
		return get_author_posts_url( (int) get_query_var( 'author' ) );
	}

	if ( is_search() ) {
		return (string) get_search_link();
	}

	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}

/**
 * Robots directives.
 *
 * Search results and paginated comment views carry no ranking value and dilute
 * the crawl budget, so they are excluded. Everything else is left to WordPress
 * and to the site owner's own settings.
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function basalt_core_robots( $robots ) {
	if ( basalt_core_seo_plugin_handles( 'meta' ) ) {
		return $robots;
	}

	$robots = (array) $robots;

	if ( is_search() || is_404() || ( is_singular() && (int) get_query_var( 'cpage' ) > 0 ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	if ( empty( $robots['noindex'] ) ) {
		// Let Google build rich previews and use large image thumbnails.
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = '-1';
		$robots['max-video-preview'] = '-1';
	} else {
		/*
		 * Core adds max-image-preview at priority 10, before this filter runs.
		 * Preview directives on a noindex page describe a result that will
		 * never be shown, so they are removed rather than left contradicting
		 * each other in the same tag.
		 */
		unset( $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'basalt_core_robots', 20 );

/**
 * Self-referencing canonical for views WordPress does not cover.
 *
 * Core's rel_canonical() only runs on singular views. Archives, the posts page
 * and taxonomy terms get nothing, so any URL that reaches them with a tracking
 * parameter appended looks like a separate page to a crawler. Paginated
 * archives point at their own page rather than at page one, which is what
 * Google asks for since rel=prev/next was retired.
 *
 * @return void
 */
function basalt_core_archive_canonical(): void {
	if ( ! basalt_core_should_output_meta() || is_singular() || is_404() ) {
		return;
	}

	$url = basalt_core_current_url();

	if ( ! $url ) {
		return;
	}

	$page = (int) get_query_var( 'paged' );

	if ( $page > 1 ) {
		$url = get_pagenum_link( $page );
	}

	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'basalt_core_archive_canonical', 9 );
