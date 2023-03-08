<?php
require get_template_directory() . '/inc/function-admin.php';
require get_template_directory() . '/inc/enqueue_admin_styles.php';

add_action( 'after_switch_theme', 'darctheme_setup_redirect' );
function darctheme_setup_redirect() {
    if( isset( $_GET['activated'] ) ) {
        wp_safe_redirect( admin_url('admin.php?page=darctheme_setup_wizard') );
        exit;
    }
}

function load_theme_styles() {
    wp_register_style('stylesheet', get_template_directory_uri() . '/css/theme.css', '', 1, 'all');
    wp_enqueue_style('stylesheet');
    wp_register_style('stylesheet', get_template_directory_uri() . '/style.css', '', 1, 'all');
    wp_enqueue_style('stylesheet');
}
add_action('wp_enqueue_scripts', 'load_theme_styles');

function load_theme_js() {
    wp_register_script('dg_theme_js', get_template_directory_uri() . '/js/theme.js', false, '', true);
    wp_enqueue_script('dg_theme_js');
    wp_register_script('dg_theme_transitions_js', get_template_directory_uri() . '/js/transitions.js', false, '', true);
    wp_enqueue_script('dg_theme_transitions_js');
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'load_theme_js');

//Remove html margin-top
add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );
add_theme_support('post-thumbnails');



// Register Menus
function register_theme_menus() {
    register_nav_menus(
        array(
            'main-menu' => __( 'Hauptmenu' ),
            'footer-help' => __('Hilfe Menu'),
            'footer-law' => __( 'Rechtliches Menu' )
        )
    );
}
add_action( 'init', 'register_theme_menus' );

// Add Customizer Functions
function theme_customizer_function($wp_customize) {
    // Header Panel
    $wp_customize->add_panel('header', array(
        'title' => 'Header',
        'panel' => 'header',
        'priority' => 10,
        'capability' => 'edit_theme_options',
    ));
    $wp_customize->add_section('header_logo', array(
        'title' => 'Logo',
        'description' => __('Füge dein Logo ein'),
        'priority' => 1,
        'panel' => 'header',
    ));
    $wp_customize->add_setting('header_logo_img', array(
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
        'transport'   => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'header_logo_control', array(
        'label' => 'Logo',
        'section' => 'header_logo',
        'priority' => 3,
        'settings' => 'header_logo_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));

    // Homepage Panel
    $wp_customize->add_panel('homepage', array(
        'title' => 'Homepage',
        'panel' => 'homepage',
        'priority' => 20,
        'capability' => 'edit_theme_options',
    ));

    // Hero Section
    $wp_customize->add_section('homepage_hero', array(
        'title' => 'Hero',
        'description' => __('Hero Bereich'),
        'priority' => 1,
        'panel' => 'homepage',
    ));
    $wp_customize->add_setting('homepage_hero_feature_img', array(
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
        'transport'   => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'homepage_hero_feature_img_control', array(
        'label' => 'Featured Image',
        'section' => 'homepage_hero',
        'priority' => 3,
        'settings' => 'homepage_hero_feature_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));

    // Brand Section
    $wp_customize->add_section('homepage_brand', array(
        'title' => 'Marken',
        'description' => __('Marken Bearbeiten'),
        'priority' => 2,
        'panel' => 'homepage',
    ));
    $wp_customize->add_setting('homepage_brand_gal', array(
        'default' => __('Clean & Unique'),
        'sanitize_callback' => ''
    ));
    $wp_customize->add_control('homepage_brand_gal_control', array(
        'label' => 'Small Heading',
        'section' => 'homepage_brand',
        'priority' => 1,
        'settings' => 'homepage_brand_gal',
        'type' => '',
    ));

    // About Section
    $wp_customize->add_section('homepage_about', array(
        'title' => 'Über Uns',
        'description' => __('Schreibe etwas über dich oder dein Geschäft.'),
        'priority' => 3,
        'panel' => 'homepage',
    ));
    $wp_customize->add_setting('homepage_about_title', array(
        'default' => __('Über Uns'),
        'sanitize_callback' => 'sanitize_custom_text',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('homepage_about_title_control', array(
        'label' => 'Title',
        'section' => 'homepage_about',
        'priority' => 1,
        'settings' => 'homepage_about_title',
        'type' => '',
    ));
    $wp_customize->add_setting('homepage_about_text', array(
        'default' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
        'sanitize_callback' => 'sanitize_custom_text',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('homepage_about_text_control', array(
        'default' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
        'label' => 'Text',
        'section' => 'homepage_about',
        'priority' => 2,
        'settings' => 'homepage_about_text',
        'type' => 'textarea',
    ));
    $wp_customize->add_setting('homepage_about_link_text', array(
        'default' => 'more',
        'sanitize_callback' => 'sanitize_custom_text',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('homepage_about_link_text_control', array(
        'label' => 'Button Text',
        'section' => 'homepage_about',
        'priority' => 2,
        'settings' => 'homepage_about_link_text',
    ));
    $wp_customize->add_setting('homepage_about_link', array(
        'default' => '#',
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('homepage_about_link_control', array(
        'label' => 'Link',
        'section' => 'homepage_about',
        'priority' => 2,
        'settings' => 'homepage_about_link',
    ));
    $wp_customize->add_setting('homepage_about_img', array(
        'default' => '',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'homepage_about_img_control', array(
        'label' => 'Titel Bild',
        'section' => 'homepage_about',
        'priority' => 3,
        'settings' => 'homepage_about_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));

    // Footer Panel
    $wp_customize->add_panel('footer', array(
        'title' => 'Footer',
        'panel' => 'footer',
        'priority' => 180,
        'capability' => 'edit_theme_options',
    ));

    // First Section
    $wp_customize->add_section('footer_one', array(
        'title' => 'Footer 1',
        'priority' => 1,
        'panel' => 'footer',
    ));
    $wp_customize->add_setting('footer_one_logo', array(
        'default' => '',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_one_logo_control', array(
        'label' => 'Titel Bild',
        'section' => 'footer_one',
        'priority' => 1,
        'settings' => 'footer_one_logo',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));

    // Second Section
    $wp_customize->add_section('footer_two', array(
        'title' => 'Footer 2',
        'priority' => 1,
        'panel' => 'footer',
    ));
    $wp_customize->add_setting('footer_two_social_one_img', array(
        'default' => '',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_two_social_one_img_control', array(
        'label' => 'Social 1',
        'section' => 'footer_two',
        'priority' => 1,
        'settings' => 'footer_two_social_one_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));
    $wp_customize->add_setting('footer_two_social_one_link', array(
        'default' => '#',
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('footer_two_social_one_link_control', array(
        'label' => 'Social 1 - Link',
        'section' => 'footer_two',
        'priority' => 2,
        'settings' => 'footer_two_social_one_link',
    ));

    $wp_customize->add_setting('footer_two_social_two_img', array(
        'default' => '',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_two_social_two_img_control', array(
        'label' => 'Social 2',
        'section' => 'footer_two',
        'priority' => 3,
        'settings' => 'footer_two_social_two_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));
    $wp_customize->add_setting('footer_two_social_two_link', array(
        'default' => '#',
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('footer_two_social_two_link_control', array(
        'label' => 'Social 2 - Link',
        'section' => 'footer_two',
        'priority' => 4,
        'settings' => 'footer_two_social_two_link',
    ));

    $wp_customize->add_setting('footer_two_social_three_img', array(
        'default' => '',
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_two_social_three_img_control', array(
        'label' => 'Social 3',
        'section' => 'footer_two',
        'priority' => 5,
        'settings' => 'footer_two_social_three_img',
        'button_labels' => array(// All These labels are optional
            'select' => 'Hinzufügen',
            'remove' => 'Entfernen',
            'change' => 'Ändern',
            )
    )));
    $wp_customize->add_setting('footer_two_social_three_link', array(
        'default' => '#',
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'theme_mod',
    ));
    $wp_customize->add_control('footer_two_social_three_link_control', array(
        'label' => 'Social 3 - Link',
        'section' => 'footer_two',
        'priority' => 6,
        'settings' => 'footer_two_social_three_link',
    ));
    
}
add_action('customize_register', 'theme_customizer_function');

function sanitize_custom_text($input) {
    return htmlspecialchars($input);
}

function sanitize_custom_url($input) {
    return filter_var($input, FILTER_SANITIZE_URL);
}