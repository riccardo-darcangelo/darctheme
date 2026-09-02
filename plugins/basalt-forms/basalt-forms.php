<?php
/**
 * Plugin Name: Basalt Forms
 * Description: One accessible form block for contact and appointment requests. Server side validation with the error at the field, a honeypot, a rate limit, delivery by email and a copy of every submission in the admin. No JavaScript, no external service, nothing to configure before it works.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Riccardo D'Arcangelo
 * License: GPL-2.0-or-later
 * Text Domain: basalt-forms
 *
 * Why a plugin of its own
 * -----------------------
 * A contact form is the one interactive element almost every small business
 * site has, and it is what puts the site in scope of the accessibility law.
 * The popular form plugins load JavaScript on every page, render labels as
 * placeholders and put the errors in a list far from the field. This one does
 * the opposite, and does nothing else.
 *
 * The block's labels are attributes, so a site edits them in its own language
 * in the editor. Only the validation messages are translated strings.
 *
 * @package BasaltForms
 */

defined( 'ABSPATH' ) || exit;

define( 'BASALT_FORMS_VERSION', '1.0.0' );
define( 'BASALT_FORMS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BASALT_FORMS_URL', plugin_dir_url( __FILE__ ) );

require_once BASALT_FORMS_DIR . 'inc/entries.php';
require_once BASALT_FORMS_DIR . 'inc/handler.php';
require_once BASALT_FORMS_DIR . 'inc/render.php';

/**
 * Register the block.
 *
 * @return void
 */
function basalt_forms_register_block(): void {
	register_block_type(
		BASALT_FORMS_DIR . 'blocks/form',
		array( 'render_callback' => 'basalt_forms_render' )
	);
}
add_action( 'init', 'basalt_forms_register_block' );

/**
 * Load translations.
 *
 * @return void
 */
function basalt_forms_load_textdomain(): void {
	load_plugin_textdomain( 'basalt-forms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'basalt_forms_load_textdomain' );
