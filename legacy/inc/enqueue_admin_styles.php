<?php

/*
* @package darctheme
*
*   ==================
*       Admin Page Enqueue Styles
*   ==================
*/

function darctheme_load_admin_scripts( $hook ) {

    if( $hook !== 'darc-theme_page_darctheme_setup_wizard') {
        return;
    }
    wp_register_style('darctheme_admin', get_template_directory_uri() . '/inc/css/darctheme.admin.css', array(), '1.0.0', 'all');
    wp_enqueue_style('darctheme_admin');
}
add_action('admin_enqueue_scripts', 'darctheme_load_admin_scripts');