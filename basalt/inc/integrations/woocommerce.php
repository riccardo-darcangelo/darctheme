<?php
/**
 * WooCommerce support.
 *
 * Basalt is a block theme, so the shop pages are block templates, not PHP.
 * WooCommerce ships its own set and the theme overrides the ones it wants to
 * control: the product archive, the single product and product search. Cart,
 * checkout and the order confirmation are left to WooCommerce, because those
 * change between releases and a theme that copies them inherits the
 * maintenance without gaining anything a customer can see.
 *
 * Everything here is inert on a site without WooCommerce.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether WooCommerce is active.
 *
 * @return bool
 */
function basalt_has_woocommerce(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Declare theme support for WooCommerce and its gallery features.
 *
 * @return void
 */
function basalt_woocommerce_setup(): void {
	if ( ! basalt_has_woocommerce() ) {
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
	if ( ! basalt_has_woocommerce() ) {
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
 * Stop WooCommerce opening a second main landmark.
 *
 * Wherever WooCommerce still renders through its classic templates, and the
 * Classic Template block is one of those places, it calls
 * woocommerce_output_content_wrapper. That prints its own
 * <main class="site-main">. In a block theme the surrounding template has
 * already opened a main, so the page ends up with two, which is a WCAG failure
 * and leaves screen reader users guessing which one holds the content.
 *
 * The wrapper is replaced rather than removed: WooCommerce's own stylesheet and
 * a good deal of third party CSS expect an element between the layout and the
 * shop content. A div carries that without claiming to be a landmark.
 *
 * @return void
 */
function basalt_woocommerce_wrappers(): void {
	if ( ! basalt_has_woocommerce() || ! wp_is_block_theme() ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	add_action(
		'woocommerce_before_main_content',
		static function (): void {
			echo '<div class="woocommerce-main">';
		},
		10
	);

	add_action(
		'woocommerce_after_main_content',
		static function (): void {
			echo '</div>';
		},
		10
	);

	/*
	 * The theme templates below render the breadcrumb block, which also feeds
	 * the BreadcrumbList structured data. WooCommerce's own breadcrumb would sit
	 * next to it saying the same thing.
	 */
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'init', 'basalt_woocommerce_wrappers' );
