<?php
/**
 * Submissions are kept, not only mailed.
 *
 * wp_mail fails silently on more shared hosts than anyone admits, and the
 * person who finds out is the customer who never got a reply. Every submission
 * is therefore also stored as a private post type the site owner can read
 * under Form entries. Nothing is created from the front end except through the
 * handler, and nobody can create one by hand.
 *
 * @package BasaltForms
 */

defined( 'ABSPATH' ) || exit;

const BASALT_FORMS_ENTRY_TYPE = 'basalt_form_entry';

/**
 * Register the entry post type.
 *
 * @return void
 */
function basalt_forms_register_entries(): void {
	register_post_type(
		BASALT_FORMS_ENTRY_TYPE,
		array(
			'labels'          => array(
				'name'          => __( 'Form entries', 'basalt-forms' ),
				'singular_name' => __( 'Form entry', 'basalt-forms' ),
				'search_items'  => __( 'Search entries', 'basalt-forms' ),
				'not_found'     => __( 'No entries yet', 'basalt-forms' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => array(
				// Entries come from the front end only.
				'create_posts' => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'basalt_forms_register_entries' );

/**
 * Store a submission.
 *
 * @param string                $form_id Form identifier.
 * @param array<string, string> $values  Labelled values, label to value.
 * @param string                $source  URL the form was on.
 * @return int Post ID, or 0 on failure.
 */
function basalt_forms_store_entry( string $form_id, array $values, string $source ): int {
	$lines = array();

	foreach ( $values as $label => $value ) {
		$lines[] = $label . ': ' . $value;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => BASALT_FORMS_ENTRY_TYPE,
			'post_status'  => 'private',
			'post_title'   => sprintf(
				/* translators: 1: sender name, 2: date */
				__( '%1$s, %2$s', 'basalt-forms' ),
				$values[ array_key_first( $values ) ] ?? __( 'Unknown', 'basalt-forms' ),
				wp_date( (string) get_option( 'date_format' ) . ' ' . (string) get_option( 'time_format' ) )
			),
			'post_content' => implode( "\n", $lines ),
			'meta_input'   => array(
				'_basalt_form_id'     => $form_id,
				'_basalt_form_source' => esc_url_raw( $source ),
			),
		),
		true
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}
