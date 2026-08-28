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
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme should emit description and social meta tags.
 *
 * @return bool
 */
function basalt_should_output_meta(): bool {
	$enabled = (bool) basalt_get_option( 'meta_enabled' ) && ! basalt_seo_plugin_handles( 'meta' );

	/**
	 * Filter whether the theme emits meta tags.
	 *
	 * @param bool $enabled Whether to emit meta tags.
	 */
	return (bool) apply_filters( 'basalt_output_meta', $enabled );
}

/**
 * Whether the theme should emit its Schema.org graph.
 *
 * @return bool
 */
function basalt_should_output_schema(): bool {
	$enabled = (bool) basalt_get_option( 'schema_enabled' ) && ! basalt_seo_plugin_handles( 'schema' );

	/**
	 * Filter whether the theme emits structured data.
	 *
	 * @param bool $enabled Whether to emit JSON-LD.
	 */
	return (bool) apply_filters( 'basalt_output_schema', $enabled );
}

/* -------------------------------------------------------------------------
 * Meta tags
 * ---------------------------------------------------------------------- */

/**
 * Description, Open Graph and Twitter Card tags.
 *
 * @return void
 */
function basalt_meta_tags(): void {
	if ( ! basalt_should_output_meta() ) {
		return;
	}

	$description = basalt_get_meta_description();
	$title       = wp_get_document_title();
	$url         = basalt_get_current_url();
	$image       = basalt_get_share_image();

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

	$handle = (string) basalt_get_option( 'meta_twitter_site' );

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
	$tags = (array) apply_filters( 'basalt_meta_tags', $tags );

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
add_action( 'wp_head', 'basalt_meta_tags', 5 );

/**
 * The description for the current view.
 *
 * Order of preference: a hand written excerpt, the term description, the site
 * tagline. Content is only auto-trimmed as a last resort, because a truncated
 * first paragraph rarely reads like a useful snippet.
 *
 * @return string
 */
function basalt_get_meta_description(): string {
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
	return (string) apply_filters( 'basalt_meta_description', $description );
}

/**
 * The sharing image for the current view.
 *
 * @return array{url: string, width: int, height: int, alt: string}|null
 */
function basalt_get_share_image(): ?array {
	$attachment_id = 0;

	if ( is_singular() && has_post_thumbnail() ) {
		$attachment_id = (int) get_post_thumbnail_id();
	}

	if ( ! $attachment_id ) {
		$attachment_id = (int) basalt_get_option( 'meta_default_image' );
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
function basalt_get_current_url(): string {
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
function basalt_robots( $robots ) {
	if ( basalt_seo_plugin_handles( 'meta' ) ) {
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
add_filter( 'wp_robots', 'basalt_robots', 20 );

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
function basalt_archive_canonical(): void {
	if ( ! basalt_should_output_meta() || is_singular() || is_404() ) {
		return;
	}

	$url = basalt_get_current_url();

	if ( ! $url ) {
		return;
	}

	$page = (int) get_query_var( 'paged' );

	if ( $page > 1 ) {
		$url = get_pagenum_link( $page );
	}

	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'basalt_archive_canonical', 9 );

/* -------------------------------------------------------------------------
 * Structured data
 * ---------------------------------------------------------------------- */

/**
 * Print the Schema.org graph for the current view.
 *
 * One @graph with cross referenced @id values, which is what Google recommends
 * and what lets the nodes reference each other instead of repeating data.
 *
 * @return void
 */
function basalt_schema_graph(): void {
	if ( ! basalt_should_output_schema() ) {
		return;
	}

	$graph = array_values( array_filter( basalt_build_schema_graph() ) );

	if ( ! $graph ) {
		return;
	}

	$json = wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? JSON_PRETTY_PRINT : 0 )
	);

	if ( false === $json ) {
		return;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		$json // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output inside a JSON-LD script tag.
	);
}
add_action( 'wp_head', 'basalt_schema_graph', 20 );

/**
 * Assemble the graph nodes for the current view.
 *
 * @return array<int, array<string, mixed>>
 */
function basalt_build_schema_graph(): array {
	$home       = home_url( '/' );
	$website_id = $home . '#website';
	$entity_id  = $home . '#identity';
	$url        = basalt_get_current_url();
	$page_id    = $url . '#webpage';

	$graph = array(
		basalt_schema_entity_node( $entity_id ),
		basalt_schema_website_node( $website_id, $entity_id ),
		basalt_schema_webpage_node( $page_id, $url, $website_id, $entity_id ),
	);

	/*
	 * No trail on a 404. The page describes an address that does not exist, so
	 * a breadcrumb ending in "Page not found" claims a position in a hierarchy
	 * that is not there.
	 */
	$breadcrumbs = is_404() ? null : basalt_schema_breadcrumb_node( $url );

	if ( $breadcrumbs ) {
		$graph[] = $breadcrumbs;
	}

	if ( is_singular() && ! is_front_page() ) {
		$article = basalt_schema_article_node( $page_id, $entity_id );

		if ( $article ) {
			$graph[] = $article;
		}
	}

	$faq = basalt_schema_faq_node( $page_id );

	if ( $faq ) {
		$graph[] = $faq;
	}

	/**
	 * Filter the complete Schema.org graph.
	 *
	 * This is the extension point for custom post types. A child theme that
	 * registers a product or service post type adds its Product, Offer or
	 * Service node here and gets breadcrumbs, publisher and page identity for
	 * free through the existing @id references.
	 *
	 * @param array<int, array<string, mixed>> $graph   Graph nodes.
	 * @param string                           $page_id The @id of the current WebPage node.
	 */
	return (array) apply_filters( 'basalt_schema_graph', $graph, $page_id );
}

/**
 * The site's identity node: Organization, LocalBusiness or Person.
 *
 * @param string $entity_id Node @id.
 * @return array<string, mixed>
 */
function basalt_schema_entity_node( string $entity_id ): array {
	$type = (string) basalt_get_option( 'schema_entity_type' );
	$name = (string) basalt_get_option( 'schema_entity_name' );

	$node = array(
		'@type' => $type,
		'@id'   => $entity_id,
		'name'  => $name ?: get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$description = (string) get_bloginfo( 'description' );

	if ( $description ) {
		$node['description'] = $description;
	}

	$logo_id = (int) basalt_get_option( 'schema_logo' ) ?: (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$logo = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $logo ) {
			$node['logo'] = array(
				'@type'  => 'ImageObject',
				'@id'    => home_url( '/' ) . '#logo',
				'url'    => $logo[0],
				'width'  => (int) $logo[1],
				'height' => (int) $logo[2],
			);

			$node['image'] = array( '@id' => home_url( '/' ) . '#logo' );
		}
	}

	$phone = (string) basalt_get_option( 'schema_phone' );

	if ( $phone ) {
		$node['telephone'] = $phone;
	}

	$email = (string) basalt_get_option( 'schema_email' );

	if ( $email ) {
		$node['email'] = $email;
	}

	$address = array_filter(
		array(
			'streetAddress'   => (string) basalt_get_option( 'schema_street' ),
			'postalCode'      => (string) basalt_get_option( 'schema_postal_code' ),
			'addressLocality' => (string) basalt_get_option( 'schema_city' ),
			'addressRegion'   => (string) basalt_get_option( 'schema_region' ),
			'addressCountry'  => (string) basalt_get_option( 'schema_country' ),
		)
	);

	if ( $address ) {
		$node['address'] = array_merge( array( '@type' => 'PostalAddress' ), $address );
	}

	$hours = basalt_parse_lines( (string) basalt_get_option( 'schema_opening_hours' ) );

	if ( $hours && 'Person' !== $type ) {
		$node['openingHours'] = $hours;
	}

	$price_range = (string) basalt_get_option( 'schema_price_range' );

	if ( $price_range && 'Person' !== $type && 'Organization' !== $type ) {
		$node['priceRange'] = $price_range;
	}

	$profiles = basalt_parse_lines( (string) basalt_get_option( 'schema_profiles' ) );

	if ( $profiles ) {
		$node['sameAs'] = $profiles;
	}

	/**
	 * Filter the site identity node.
	 *
	 * @param array<string, mixed> $node The Organization, LocalBusiness or Person node.
	 */
	return (array) apply_filters( 'basalt_schema_entity_node', $node );
}

/**
 * The WebSite node, including the sitelinks search box action.
 *
 * @param string $website_id Node @id.
 * @param string $entity_id  Publisher @id.
 * @return array<string, mixed>
 */
function basalt_schema_website_node( string $website_id, string $entity_id ): array {
	return array(
		'@type'           => 'WebSite',
		'@id'             => $website_id,
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => $entity_id ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => array(
			array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
	);
}

/**
 * The WebPage node for the current view.
 *
 * @param string $page_id    Node @id.
 * @param string $url        Canonical URL.
 * @param string $website_id WebSite @id.
 * @param string $entity_id  Entity @id.
 * @return array<string, mixed>
 */
function basalt_schema_webpage_node( string $page_id, string $url, string $website_id, string $entity_id ): array {
	$type = 'WebPage';

	if ( is_front_page() ) {
		$type = 'WebPage';
	} elseif ( is_archive() || is_home() || is_search() ) {
		$type = 'CollectionPage';
	} elseif ( is_singular( 'post' ) ) {
		$type = 'ItemPage';
	}

	$node = array(
		'@type'      => $type,
		'@id'        => $page_id,
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'isPartOf'   => array( '@id' => $website_id ),
		'about'      => array( '@id' => $entity_id ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	$description = basalt_get_meta_description();

	if ( $description ) {
		$node['description'] = $description;
	}

	if ( is_singular() ) {
		$node['datePublished'] = get_the_date( DATE_W3C );
		$node['dateModified']  = get_the_modified_date( DATE_W3C );
	}

	$image = basalt_get_share_image();

	if ( $image ) {
		$node['primaryImageOfPage'] = array(
			'@type'  => 'ImageObject',
			'url'    => $image['url'],
			'width'  => $image['width'],
			'height' => $image['height'],
		);
	}

	// Must match the condition in basalt_build_schema_graph(), or this points
	// at a node that was never emitted.
	if ( ! is_404() && count( basalt_get_breadcrumb_items() ) > 1 ) {
		$node['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	}

	return $node;
}

/**
 * The BreadcrumbList node, built from the same data as the visible trail.
 *
 * @param string $url Current URL.
 * @return array<string, mixed>|null
 */
function basalt_schema_breadcrumb_node( string $url ): ?array {
	$items = basalt_get_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return null;
	}

	$elements = array();

	foreach ( array_values( $items ) as $index => $item ) {
		$elements[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $item['name'],
			'item'     => $item['url'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => $elements,
	);
}

/**
 * The Article node for single posts.
 *
 * Only emitted for genuinely editorial content. Pages are not articles, and
 * marking them up as such is a common cause of Search Console warnings.
 *
 * @param string $page_id   WebPage @id.
 * @param string $entity_id Publisher @id.
 * @return array<string, mixed>|null
 */
function basalt_schema_article_node( string $page_id, string $entity_id ): ?array {
	/**
	 * Filter which post types are marked up as articles.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	$article_types = (array) apply_filters( 'basalt_schema_article_post_types', array( 'post' ) );

	if ( ! is_singular( $article_types ) ) {
		return null;
	}

	$post_id = (int) get_the_ID();

	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => $page_id . '#article',
		'isPartOf'         => array( '@id' => $page_id ),
		'mainEntityOfPage' => array( '@id' => $page_id ),
		'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
		'datePublished'    => get_the_date( DATE_W3C, $post_id ),
		'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
		'publisher'        => array( '@id' => $entity_id ),
		'inLanguage'       => get_bloginfo( 'language' ),
		'wordCount'        => str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ),
	);

	$author_id = (int) get_post_field( 'post_author', $post_id );

	if ( $author_id ) {
		$node['author'] = array(
			'@type' => 'Person',
			'@id'   => get_author_posts_url( $author_id ) . '#author',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => get_author_posts_url( $author_id ),
		);
	}

	$description = basalt_get_meta_description();

	if ( $description ) {
		$node['description'] = $description;
	}

	$image = basalt_get_share_image();

	if ( $image ) {
		$node['image'] = array(
			'@type'  => 'ImageObject',
			'url'    => $image['url'],
			'width'  => $image['width'],
			'height' => $image['height'],
		);
	}

	$sections = get_the_category_list( ', ', '', $post_id );

	if ( $sections ) {
		$node['articleSection'] = wp_strip_all_tags( $sections );
	}

	$tags = get_the_tags( $post_id );

	if ( is_array( $tags ) ) {
		$node['keywords'] = wp_list_pluck( $tags, 'name' );
	}

	return $node;
}

/**
 * FAQPage node, built from the content of the current post.
 *
 * Picks up core/details blocks that carry the theme's "FAQ item" block style,
 * so an editor writes a normal accordion in the editor and the structured data
 * follows automatically. No shortcode, no custom block, nothing that breaks on
 * a theme switch.
 *
 * @param string $page_id WebPage @id.
 * @return array<string, mixed>|null
 */
function basalt_schema_faq_node( string $page_id ): ?array {
	if ( ! is_singular() ) {
		return null;
	}

	$content = (string) get_post_field( 'post_content', get_the_ID() );

	if ( ! $content || ! has_blocks( $content ) ) {
		return null;
	}

	$questions = array();

	foreach ( basalt_flatten_blocks( parse_blocks( $content ) ) as $block ) {
		if ( 'core/details' !== ( $block['blockName'] ?? '' ) ) {
			continue;
		}

		$class_name = $block['attrs']['className'] ?? '';

		if ( false === strpos( (string) $class_name, 'is-style-faq' ) ) {
			continue;
		}

		$summary = wp_strip_all_tags( (string) ( $block['attrs']['summary'] ?? '' ) );
		$answer  = trim( wp_strip_all_tags( render_block( $block ) ) );

		// The rendered details block contains the summary text as well; drop it.
		if ( $summary && str_starts_with( $answer, $summary ) ) {
			$answer = trim( substr( $answer, strlen( $summary ) ) );
		}

		if ( '' === $summary || '' === $answer ) {
			continue;
		}

		$questions[] = array(
			'@type'          => 'Question',
			'name'           => $summary,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}

	if ( ! $questions ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'@id'        => $page_id . '#faq',
		'isPartOf'   => array( '@id' => $page_id ),
		'mainEntity' => $questions,
	);
}

/**
 * Flatten a parsed block tree into a single list.
 *
 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
 * @return array<int, array<string, mixed>>
 */
function basalt_flatten_blocks( array $blocks ): array {
	$flat = array();

	foreach ( $blocks as $block ) {
		$flat[] = $block;

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$flat = array_merge( $flat, basalt_flatten_blocks( $block['innerBlocks'] ) );
		}
	}

	return $flat;
}

/**
 * Split a textarea value into a clean list of non empty lines.
 *
 * @param string $value Raw textarea value.
 * @return string[]
 */
function basalt_parse_lines( string $value ): array {
	$lines = preg_split( '/\R/', $value ) ?: array();

	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}
