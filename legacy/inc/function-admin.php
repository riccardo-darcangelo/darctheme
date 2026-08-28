<?php

/*
* @package darctheme
*
*   ==================
*       Admin Page
*   ==================
*/

function darctheme_add_admin_page() {

    // Generate Admin Page
    add_menu_page('Darc Theme Options', 'DARC Theme', 'manage_options', 'darctheme', 'darcdesign_create_admin_page', '', 59);

    // Generate Admin Subpages - Common Settings
    add_submenu_page('darctheme', 'Darc Theme Options', 'Allgemein', 'manage_options', 'darctheme', 'darcdesign_create_admin_page');

    // Generate Admin Subpages - Setup Wizard
    add_submenu_page('darctheme', 'Darctheme Setup Wizard', 'Setup Wizard', 'manage_options', 'darctheme_setup_wizard', 'darctheme_setup_wizard');
}
add_action('admin_menu', 'darctheme_add_admin_page');

function darcdesign_create_admin_page() {
    require_once (get_template_directory() . '/inc/templates/darctheme-admin.php');
}

function darctheme_setup_wizard() {
    require_once (get_template_directory() . '/inc/templates/darctheme-setup-wizard.php');
}