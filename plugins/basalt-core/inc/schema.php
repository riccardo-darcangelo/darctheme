<?php
/**
 * Schema.org structured data.
 *
 * One @graph with cross referenced @id values, which is what Google
 * recommends: nodes reference each other instead of repeating the publisher
 * on every page.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the Schema.org graph for the current view.
 *
 * One @graph with cross referenced @id values, which is what Google recommends
 * and what lets the nodes reference each other instead of repeating data.
 *
 * @return void
 */
function basalt_core_schema_graph(): void {
	if ( ! basalt_core_should_output_schema() ) {
		return;
	}

	$graph = array_values( array_filter( basalt_core_build_schema_graph() ) );

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
add_action( 'wp_head', 'basalt_core_schema_graph', 20 );

/**
 * Assemble the graph nodes for the current view.
 *
 * @return array<int, array<string, mixed>>
 */
function basalt_core_build_schema_graph(): array {
	$home       = home_url( '/' );
	$website_id = $home . '#website';
	$entity_id  = $home . '#identity';
	$url        = basalt_core_current_url();
	$page_id    = $url . '#webpage';

	$graph = array(
		basalt_core_schema_entity_node( $entity_id ),
		basalt_core_schema_website_node( $website_id, $entity_id ),
		basalt_core_schema_webpage_node( $page_id, $url, $website_id, $entity_id ),
	);

	/*
	 * No trail on a 404. The page describes an address that does not exist, so
	 * a breadcrumb ending in "Page not found" claims a position in a hierarchy
	 * that is not there.
	 */
	$breadcrumbs = is_404() ? null : basalt_core_schema_breadcrumb_node( $url );

	if ( $breadcrumbs ) {
		$graph[] = $breadcrumbs;
	}

	if ( is_singular() && ! is_front_page() ) {
		$article = basalt_core_schema_article_node( $page_id, $entity_id );

		if ( $article ) {
			$graph[] = $article;
		}
	}

	$faq = basalt_core_schema_faq_node( $page_id );

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
	return (array) apply_filters( 'basalt_core_schema_graph', $graph, $page_id );
}

/**
 * The site's identity node: Organization, LocalBusiness or Person.
 *
 * @param string $entity_id Node @id.
 * @return array<string, mixed>
 */
function basalt_core_schema_entity_node( string $entity_id ): array {
	$type = (string) basalt_core_get( 'entity_type' );
	$name = (string) basalt_core_get( 'entity_name' );

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

	$logo_id = (int) basalt_core_get( 'logo' ) ?: (int) get_theme_mod( 'custom_logo' );

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

	$phone = (string) basalt_core_get( 'phone' );

	if ( $phone ) {
		$node['telephone'] = $phone;
	}

	$email = (string) basalt_core_get( 'email' );

	if ( $email ) {
		$node['email'] = $email;
	}

	$address = array_filter(
		array(
			'streetAddress'   => (string) basalt_core_get( 'street' ),
			'postalCode'      => (string) basalt_core_get( 'postal_code' ),
			'addressLocality' => (string) basalt_core_get( 'city' ),
			'addressRegion'   => (string) basalt_core_get( 'region' ),
			'addressCountry'  => (string) basalt_core_get( 'country' ),
		)
	);

	if ( $address ) {
		$node['address'] = array_merge( array( '@type' => 'PostalAddress' ), $address );
	}

	$hours = basalt_core_parse_lines( (string) basalt_core_get( 'opening_hours' ) );

	if ( $hours && 'Person' !== $type ) {
		$node['openingHours'] = $hours;
	}

	$price_range = (string) basalt_core_get( 'price_range' );

	if ( $price_range && 'Person' !== $type && 'Organization' !== $type ) {
		$node['priceRange'] = $price_range;
	}

	$profiles = basalt_core_parse_lines( (string) basalt_core_get( 'profiles' ) );

	if ( $profiles ) {
		$node['sameAs'] = $profiles;
	}

	/**
	 * Filter the site identity node.
	 *
	 * @param array<string, mixed> $node The Organization, LocalBusiness or Person node.
	 */
	return (array) apply_filters( 'basalt_core_schema_entity_node', $node );
}

/**
 * The WebSite node, including the sitelinks search box action.
 *
 * @param string $website_id Node @id.
 * @param string $entity_id  Publisher @id.
 * @return array<string, mixed>
 */
function basalt_core_schema_website_node( string $website_id, string $entity_id ): array {
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
function basalt_core_schema_webpage_node( string $page_id, string $url, string $website_id, string $entity_id ): array {
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

	$description = basalt_core_meta_description();

	if ( $description ) {
		$node['description'] = $description;
	}

	if ( is_singular() ) {
		$node['datePublished'] = get_the_date( DATE_W3C );
		$node['dateModified']  = get_the_modified_date( DATE_W3C );
	}

	$image = basalt_core_share_image();

	if ( $image ) {
		$node['primaryImageOfPage'] = array(
			'@type'  => 'ImageObject',
			'url'    => $image['url'],
			'width'  => $image['width'],
			'height' => $image['height'],
		);
	}

	// Must match the condition in basalt_core_build_schema_graph(), or this points
	// at a node that was never emitted.
	if ( ! is_404() && count( basalt_core_breadcrumb_items() ) > 1 ) {
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
function basalt_core_schema_breadcrumb_node( string $url ): ?array {
	$items = basalt_core_breadcrumb_items();

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
function basalt_core_schema_article_node( string $page_id, string $entity_id ): ?array {
	/**
	 * Filter which post types are marked up as articles.
	 *
	 * @param string[] $post_types Post type slugs.
	 */
	$article_types = (array) apply_filters( 'basalt_core_schema_article_post_types', array( 'post' ) );

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

	$description = basalt_core_meta_description();

	if ( $description ) {
		$node['description'] = $description;
	}

	$image = basalt_core_share_image();

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
function basalt_core_schema_faq_node( string $page_id ): ?array {
	if ( ! is_singular() ) {
		return null;
	}

	$content = (string) get_post_field( 'post_content', get_the_ID() );

	if ( ! $content || ! has_blocks( $content ) ) {
		return null;
	}

	$questions = array();

	foreach ( basalt_core_flatten_blocks( parse_blocks( $content ) ) as $block ) {
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
function basalt_core_flatten_blocks( array $blocks ): array {
	$flat = array();

	foreach ( $blocks as $block ) {
		$flat[] = $block;

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$flat = array_merge( $flat, basalt_core_flatten_blocks( $block['innerBlocks'] ) );
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
function basalt_core_parse_lines( string $value ): array {
	$lines = preg_split( '/\R/', $value ) ?: array();

	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}
