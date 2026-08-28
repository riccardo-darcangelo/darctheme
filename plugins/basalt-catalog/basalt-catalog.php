<?php
/**
 * Plugin Name: Basalt Catalog
 * Description: Registers a catalog post type with filterable taxonomies and technical specification fields. Example of the data layer that belongs in a plugin rather than in the theme.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Riccardo D'Arcangelo
 * License: GPL-2.0-or-later
 * Text Domain: basalt-catalog
 *
 * Why this is a plugin and not part of the theme
 * ----------------------------------------------
 * Post types, taxonomies and custom fields are content structure. If the theme
 * registered them, switching theme would make every catalog entry disappear
 * from the admin, the URLs would 404, and the customer would conclude their
 * data was deleted. Envato rejects themes for this, and it is the right call
 * regardless of where the theme is sold.
 *
 * The theme renders this data when the plugin is active and is completely
 * unaffected when it is not.
 *
 * Adapting it for a project: change BASALT_CATALOG_POST_TYPE and the labels and
 * rewrite slug in basalt_catalog_register(), then the entries in
 * basalt_catalog_taxonomies() and basalt_catalog_specs(). Everything else
 * follows from those three.
 *
 * Decide the names before entering content. Renaming a post type afterwards
 * leaves the existing posts with the old post_type value in the database, and
 * they disappear from the admin.
 *
 * @package BasaltCatalog
 */

defined( 'ABSPATH' ) || exit;

const BASALT_CATALOG_VERSION   = '1.0.0';
const BASALT_CATALOG_POST_TYPE = 'catalog_item';

/**
 * The taxonomies attached to the catalog post type.
 *
 * Each one becomes a filterable archive, which is where the long tail of search
 * traffic comes from: a page for "hoists up to 200 kg" ranks for a query no
 * single product page can serve.
 *
 * @return array<string, array{plural: string, singular: string, slug: string, hierarchical: bool}>
 */
function basalt_catalog_taxonomies(): array {
	/**
	 * Filter the catalog taxonomies.
	 *
	 * @param array<string, array<string, mixed>> $taxonomies Taxonomy configuration.
	 */
	return (array) apply_filters(
		'basalt_catalog_taxonomies',
		array(
			'catalog_capacity' => array(
				'plural'       => __( 'Load capacities', 'basalt-catalog' ),
				'singular'     => __( 'Load capacity', 'basalt-catalog' ),
				'slug'         => 'capacity',
				'hierarchical' => true,
			),
			'catalog_use_case' => array(
				'plural'       => __( 'Applications', 'basalt-catalog' ),
				'singular'     => __( 'Application', 'basalt-catalog' ),
				'slug'         => 'application',
				'hierarchical' => true,
			),
		)
	);
}

/**
 * The specification fields shown on a catalog entry.
 *
 * Registered through register_post_meta with show_in_rest, so they are editable
 * in the block editor sidebar and readable through the REST API without any
 * additional plugin.
 *
 * @return array<string, array{label: string, unit: string, type: string}>
 */
function basalt_catalog_specs(): array {
	/**
	 * Filter the specification fields.
	 *
	 * @param array<string, array<string, string>> $specs Specification fields.
	 */
	return (array) apply_filters(
		'basalt_catalog_specs',
		array(
			'capacity'   => array(
				'label' => __( 'Load capacity', 'basalt-catalog' ),
				'unit'  => 'kg',
				'type'  => 'number',
			),
			'max_height' => array(
				'label' => __( 'Maximum height', 'basalt-catalog' ),
				'unit'  => 'm',
				'type'  => 'number',
			),
			'speed'      => array(
				'label' => __( 'Lifting speed', 'basalt-catalog' ),
				'unit'  => 'm/min',
				'type'  => 'number',
			),
			'power'      => array(
				'label' => __( 'Power supply', 'basalt-catalog' ),
				'unit'  => '',
				'type'  => 'string',
			),
			'weight'     => array(
				'label' => __( 'Weight', 'basalt-catalog' ),
				'unit'  => 'kg',
				'type'  => 'number',
			),
		)
	);
}

/**
 * Register the post type, its taxonomies and its meta.
 *
 * @return void
 */
function basalt_catalog_register(): void {
	register_post_type(
		BASALT_CATALOG_POST_TYPE,
		array(
			'labels'              => array(
				'name'          => __( 'Catalog', 'basalt-catalog' ),
				'singular_name' => __( 'Catalog item', 'basalt-catalog' ),
				'add_new_item'  => __( 'Add catalog item', 'basalt-catalog' ),
				'edit_item'     => __( 'Edit catalog item', 'basalt-catalog' ),
				'search_items'  => __( 'Search catalog', 'basalt-catalog' ),
				'not_found'     => __( 'No catalog items yet', 'basalt-catalog' ),
			),
			/*
			 * A description is worth setting: the theme uses it as the meta
			 * description of the archive page.
			 */
			'description'         => __( 'Products and equipment presented with their technical data.', 'basalt-catalog' ),
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-archive',
			'menu_position'       => 22,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
			'rewrite'             => array(
				'slug'       => 'catalog',
				'with_front' => false,
			),
			'exclude_from_search' => false,
			'hierarchical'        => false,
		)
	);

	foreach ( basalt_catalog_taxonomies() as $taxonomy => $config ) {
		register_taxonomy(
			$taxonomy,
			BASALT_CATALOG_POST_TYPE,
			array(
				'labels'            => array(
					'name'          => $config['plural'],
					'singular_name' => $config['singular'],
				),
				'public'            => true,
				'hierarchical'      => (bool) $config['hierarchical'],
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => $config['slug'],
					'with_front' => false,
				),
			)
		);
	}

	foreach ( basalt_catalog_specs() as $key => $spec ) {
		register_post_meta(
			BASALT_CATALOG_POST_TYPE,
			'_catalog_' . $key,
			array(
				'type'         => 'number' === $spec['type'] ? 'number' : 'string',
				'description'  => $spec['label'],
				'single'       => true,
				'show_in_rest' => true,
				/*
				 * Wrapped in a closure rather than passed as 'floatval'.
				 * register_post_meta registers the sanitize callback as a filter
				 * with four accepted arguments, and PHP throws an
				 * ArgumentCountError when an internal function is handed more
				 * arguments than it declares. WordPress functions such as
				 * sanitize_text_field are userland and silently ignore the
				 * extras, which is why the mistake is easy to miss.
				 */
				'sanitize_callback' => 'number' === $spec['type']
					? static fn( $value ): float => (float) $value
					: 'sanitize_text_field',
				/*
				 * Scoped to the post being edited, not a blanket edit_posts.
				 * The callback is handed the object id, and the per-post
				 * capability is what decides whether this user may edit this
				 * entry: a contributor may edit their own drafts and nothing
				 * else, and the meta has to follow the same rule as the post it
				 * belongs to. A blanket check would let any contributor write
				 * specification values onto somebody else's published entry
				 * through the REST API.
				 */
				'auth_callback'     => static function ( $allowed, $meta_key, $object_id ): bool {
					return current_user_can( 'edit_post', (int) $object_id );
				},
			)
		);
	}
}
add_action( 'init', 'basalt_catalog_register' );

/**
 * The specification values of one entry, ready to render.
 *
 * @param int|null $post_id Post ID, defaults to the current post.
 * @return array<int, array{label: string, value: string}>
 */
function basalt_catalog_get_specs( ?int $post_id = null ): array {
	$post_id = $post_id ?? (int) get_the_ID();
	$values  = array();

	foreach ( basalt_catalog_specs() as $key => $spec ) {
		$value = get_post_meta( $post_id, '_catalog_' . $key, true );

		if ( '' === $value || null === $value ) {
			continue;
		}

		$values[] = array(
			'label' => $spec['label'],
			'value' => trim( $value . ' ' . $spec['unit'] ),
		);
	}

	return $values;
}

/**
 * Flush rewrite rules once on activation so the archive URL works immediately.
 *
 * Registering the post type first is required; without it the rules being
 * flushed do not yet contain the new ones.
 *
 * @return void
 */
function basalt_catalog_activate(): void {
	basalt_catalog_register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'basalt_catalog_activate' );

/**
 * Clean up the rewrite rules on deactivation.
 *
 * @return void
 */
function basalt_catalog_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'basalt_catalog_deactivate' );

/**
 * Load translations.
 *
 * @return void
 */
function basalt_catalog_load_textdomain(): void {
	load_plugin_textdomain( 'basalt-catalog', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'basalt_catalog_load_textdomain' );
