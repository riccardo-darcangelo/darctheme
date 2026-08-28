<?php
/**
 * Structured data for the catalog post type.
 *
 * This is the whole point of the parent theme's basalt_schema_graph filter: the
 * child adds one node, and the existing graph supplies the publisher, the page
 * identity and the breadcrumb trail through @id references. No duplicated
 * Organization node, no second BreadcrumbList.
 *
 * Which type to use is a real decision, not a formality:
 *
 * - Product with an Offer is for something that can be bought at a stated
 *   price. Google shows price and availability, and expects them to be true.
 * - Product without an Offer is for something presented but not sold online.
 *   Valid, and it still feeds the knowledge graph.
 * - Service is for work performed rather than an object handed over. Renting
 *   equipment out sits closer to Service than to Product.
 *
 * Claiming an Offer with a price that does not exist on the page earns a manual
 * action, so the default here is Product without Offer.
 *
 * @package BasaltChild
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a Product node for single catalog entries.
 *
 * @param array<int, array<string, mixed>> $graph   The graph nodes.
 * @param string                           $page_id The @id of the current WebPage node.
 * @return array<int, array<string, mixed>>
 */
function basalt_child_schema_catalog_item( $graph, $page_id ) {
	if ( ! basalt_child_has_catalog() || ! is_singular( BASALT_CATALOG_POST_TYPE ) ) {
		return $graph;
	}

	$post_id = (int) get_the_ID();

	$node = array(
		'@type'            => 'Product',
		'@id'              => $page_id . '#product',
		'mainEntityOfPage' => array( '@id' => $page_id ),
		'name'             => wp_strip_all_tags( get_the_title( $post_id ) ),
		'url'              => (string) get_permalink( $post_id ),
	);

	$description = has_excerpt( $post_id )
		? get_the_excerpt( $post_id )
		: wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) ), 40, '' );

	if ( $description ) {
		$node['description'] = $description;
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$image = wp_get_attachment_image_src( (int) get_post_thumbnail_id( $post_id ), 'full' );

		if ( $image ) {
			$node['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $image[0],
				'width'  => (int) $image[1],
				'height' => (int) $image[2],
			);
		}
	}

	/*
	 * The specification fields become additionalProperty entries. Google reads
	 * them, and unlike a free text table they survive a redesign.
	 */
	$specs = basalt_catalog_get_specs( $post_id );

	if ( $specs ) {
		$node['additionalProperty'] = array_map(
			static fn( array $spec ): array => array(
				'@type' => 'PropertyValue',
				'name'  => $spec['label'],
				'value' => $spec['value'],
			),
			$specs
		);
	}

	// The taxonomy terms describe the category the item belongs to.
	$categories = array();

	foreach ( array_keys( basalt_catalog_taxonomies() ) as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_array( $terms ) ) {
			$categories = array_merge( $categories, wp_list_pluck( $terms, 'name' ) );
		}
	}

	if ( $categories ) {
		$node['category'] = implode( ', ', $categories );
	}

	/**
	 * Filter the catalog Product node before it enters the graph.
	 *
	 * Add an Offer here once the site actually publishes prices:
	 *
	 *     $node['offers'] = array(
	 *         '@type'         => 'Offer',
	 *         'price'         => '199.00',
	 *         'priceCurrency' => 'EUR',
	 *         'availability'  => 'https://schema.org/InStock',
	 *         'url'           => get_permalink(),
	 *     );
	 *
	 * @param array<string, mixed> $node    The Product node.
	 * @param int                  $post_id The catalog item.
	 */
	$graph[] = apply_filters( 'basalt_child_schema_catalog_node', $node, $post_id );

	return $graph;
}
add_filter( 'basalt_schema_graph', 'basalt_child_schema_catalog_item', 10, 2 );

/**
 * Mark catalog archives as an ItemList.
 *
 * A category page that lists its members as an ItemList is eligible for the
 * carousel treatment in search results, and it tells the crawler that the page
 * is a listing rather than an article.
 *
 * @param array<int, array<string, mixed>> $graph   The graph nodes.
 * @param string                           $page_id The @id of the current WebPage node.
 * @return array<int, array<string, mixed>>
 */
function basalt_child_schema_catalog_archive( $graph, $page_id ) {
	if ( ! basalt_child_has_catalog() ) {
		return $graph;
	}

	$is_catalog_archive = is_post_type_archive( BASALT_CATALOG_POST_TYPE )
		|| is_tax( array_keys( basalt_catalog_taxonomies() ) );

	if ( ! $is_catalog_archive || ! have_posts() ) {
		return $graph;
	}

	$elements = array();
	$position = 1;

	foreach ( $GLOBALS['wp_query']->posts as $post ) {
		$elements[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'url'      => (string) get_permalink( $post ),
			'name'     => wp_strip_all_tags( get_the_title( $post ) ),
		);
	}

	$graph[] = array(
		'@type'           => 'ItemList',
		'@id'             => $page_id . '#itemlist',
		'itemListElement' => $elements,
		'numberOfItems'   => count( $elements ),
	);

	return $graph;
}
add_filter( 'basalt_schema_graph', 'basalt_child_schema_catalog_archive', 10, 2 );

/**
 * Choose which taxonomy supplies the breadcrumb term for a catalog entry.
 *
 * The parent theme walks the post type's hierarchical taxonomies in
 * registration order and takes the first term it finds. For the catalog that
 * would be the load capacity, but "Home > Catalog > Facade work > Model X"
 * reads better than "Home > Catalog > Up to 200 kg > Model X", and it matches
 * how visitors narrow down a choice.
 *
 * Setting this explicitly also means adding another taxonomy later cannot
 * silently reshuffle every breadcrumb on the site.
 *
 * Both the visible breadcrumb and the BreadcrumbList JSON-LD are built from
 * this one list, so they cannot drift apart.
 *
 * @param WP_Taxonomy[] $taxonomies Taxonomy objects, in search order.
 * @param string        $post_type  The post type being resolved.
 * @return WP_Taxonomy[]
 */
function basalt_child_primary_term_order( $taxonomies, $post_type ) {
	if ( ! basalt_child_has_catalog() || BASALT_CATALOG_POST_TYPE !== $post_type ) {
		return $taxonomies;
	}

	$preferred = get_taxonomy( 'catalog_use_case' );

	if ( ! $preferred ) {
		return $taxonomies;
	}

	$rest = array_filter(
		(array) $taxonomies,
		static fn( $taxonomy ): bool => $taxonomy instanceof WP_Taxonomy && 'catalog_use_case' !== $taxonomy->name
	);

	return array_merge( array( $preferred ), array_values( $rest ) );
}
add_filter( 'basalt_primary_term_taxonomies', 'basalt_child_primary_term_order', 10, 2 );
