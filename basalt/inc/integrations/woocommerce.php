<?php
/**
 * WooCommerce support.
 *
 * Declaring support is what stops WooCommerce from falling back to its own
 * bundled layout, which ignores the theme's containers. Everything here is
 * inert on a site without WooCommerce.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare theme support for WooCommerce and its gallery features.
 *
 * @return void
 */
function basalt_woocommerce_setup(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 400,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'basalt_woocommerce_setup' );

/**
 * Load the WooCommerce stylesheet only on shop views.
 *
 * @return void
 */
function basalt_woocommerce_assets(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		return;
	}

	wp_enqueue_style(
		'basalt-woocommerce',
		BASALT_URI . 'assets/css/woocommerce.css',
		array( 'basalt-components' ),
		basalt_asset_version( 'assets/css/woocommerce.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'basalt_woocommerce_assets', 20 );

/**
 * Replace WooCommerce's own wrappers with the theme's containers.
 *
 * @return void
 */
function basalt_woocommerce_wrappers(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	add_action(
		'woocommerce_before_main_content',
		static function (): void {
			echo '<div class="container"><main id="content" class="site-main woocommerce-main" tabindex="-1">';
		},
		10
	);

	add_action(
		'woocommerce_after_main_content',
		static function (): void {
			echo '</main></div>';
		},
		10
	);

	// The theme renders breadcrumbs in header.php; WooCommerce's own would duplicate them.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'init', 'basalt_woocommerce_wrappers' );

/**
 * Hide the theme's page title on shop views, WooCommerce prints its own.
 *
 * @param bool $show Whether to show the title.
 * @return bool
 */
function basalt_woocommerce_hide_page_title( $show ) {
	return class_exists( 'WooCommerce' ) && is_woocommerce() ? false : (bool) $show;
}
add_filter( 'basalt_show_page_header', 'basalt_woocommerce_hide_page_title' );
