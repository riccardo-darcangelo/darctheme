<?php
/**
 * Plugin Name: Basalt Shop
 * Description: WooCommerce for a shop with a door. Marks products as available in store only, in which case the cart button becomes an appointment button and the structured data says so. Settings under WooCommerce, Settings, Products, In store.
 * Version: 1.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Author: Riccardo D'Arcangelo
 * License: GPL-2.0-or-later
 * Text Domain: basalt-shop
 *
 * Why this exists
 * ---------------
 * A shop that also has a counter sells some things online and some only after
 * a fitting, a tasting or a look. WooCommerce knows "purchasable" or not, and
 * for a product that is not, it shows nothing. This plugin turns that nothing
 * into the thing the visitor should do instead: book an appointment, call, or
 * come by. It touches the product page, the product grid and the Offer node,
 * and nothing else.
 *
 * @package BasaltShop
 */

defined( 'ABSPATH' ) || exit;

define( 'BASALT_SHOP_VERSION', '1.1.0' );

const BASALT_SHOP_META = '_basalt_in_store_only';

/**
 * Whether WooCommerce is active. Everything below is inert without it.
 *
 * @return bool
 */
function basalt_shop_active(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * A setting, with its default.
 *
 * @param string $key Setting key without prefix.
 * @return string
 */
function basalt_shop_setting( string $key ): string {
	$defaults = array(
		'appointment_url'   => '',
		'appointment_label' => __( 'Book a fitting', 'basalt-shop' ),
		'in_store_text'     => __( 'This one we only hand over after a fitting. We measure you, bring two or three sizes into the cubicle, and you decide in front of the mirror.', 'basalt-shop' ),
		'grid_label'        => __( 'In store only', 'basalt-shop' ),
		'hide_price'        => 'no',
	);

	$value = get_option( 'basalt_shop_' . $key, null );

	return null === $value || '' === $value ? (string) ( $defaults[ $key ] ?? '' ) : (string) $value;
}

/**
 * Whether a product is sold in store only.
 *
 * @param int|WC_Product|null $product Product or id, defaults to the current one.
 * @return bool
 */
function basalt_shop_in_store_only( $product = null ): bool {
	if ( ! basalt_shop_active() ) {
		return false;
	}

	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( $product ?: get_the_ID() );
	}

	if ( ! $product ) {
		return false;
	}

	// A variation inherits the flag from its parent.
	if ( $product->is_type( 'variation' ) ) {
		$product = wc_get_product( $product->get_parent_id() ) ?: $product;
	}

	return 'yes' === $product->get_meta( BASALT_SHOP_META, true );
}

/* -------------------------------------------------------------------------
 * Product editor
 * ---------------------------------------------------------------------- */

/**
 * The checkbox on the General tab of the product data box.
 *
 * @return void
 */
function basalt_shop_product_field(): void {
	woocommerce_wp_checkbox(
		array(
			'id'          => BASALT_SHOP_META,
			'label'       => __( 'In store only', 'basalt-shop' ),
			'description' => __( 'Show the product, but replace the cart button with the appointment button. The Offer in the structured data is marked InStoreOnly.', 'basalt-shop' ),
		)
	);
}
add_action( 'woocommerce_product_options_general_product_data', 'basalt_shop_product_field' );

/**
 * Save the checkbox.
 *
 * WooCommerce has already verified its own nonce and capability by the time
 * this runs.
 *
 * @param WC_Product $product The product being saved.
 * @return void
 */
function basalt_shop_save_product_field( $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified by WooCommerce before this hook.
	$product->update_meta_data( BASALT_SHOP_META, isset( $_POST[ BASALT_SHOP_META ] ) ? 'yes' : 'no' );
}
add_action( 'woocommerce_admin_process_product_object', 'basalt_shop_save_product_field' );

/* -------------------------------------------------------------------------
 * Front end
 * ---------------------------------------------------------------------- */

/**
 * An in store product cannot be bought online.
 *
 * This is the one switch that makes WooCommerce itself hide its cart form,
 * refuse the product in the cart and leave it out of the mini cart.
 *
 * @param bool       $purchasable Whether the product can be bought.
 * @param WC_Product $product     The product.
 * @return bool
 */
function basalt_shop_not_purchasable( $purchasable, $product ) {
	return basalt_shop_in_store_only( $product ) ? false : $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'basalt_shop_not_purchasable', 10, 2 );
add_filter( 'woocommerce_variation_is_purchasable', 'basalt_shop_not_purchasable', 10, 2 );

/**
 * Hide the price when the setting says so.
 *
 * @param string     $html    Price markup.
 * @param WC_Product $product The product.
 * @return string
 */
function basalt_shop_price_html( $html, $product ) {
	if ( 'yes' === basalt_shop_setting( 'hide_price' ) && basalt_shop_in_store_only( $product ) ) {
		return '';
	}

	return $html;
}
add_filter( 'woocommerce_get_price_html', 'basalt_shop_price_html', 20, 2 );

/**
 * The appointment call to action.
 *
 * @param bool $compact True inside a product grid: label and button only.
 * @return string
 */
function basalt_shop_cta( bool $compact = false ): string {
	$url   = basalt_shop_setting( 'appointment_url' );
	$label = basalt_shop_setting( 'appointment_label' );

	$button = $url
		? sprintf(
			'<div class="wp-block-button%3$s"><a class="wp-block-button__link wp-element-button" href="%1$s">%2$s</a></div>',
			esc_url( $url ),
			esc_html( $label ),
			$compact ? ' is-style-outline' : ''
		)
		: '';

	if ( $compact ) {
		return sprintf(
			'<div class="basalt-shop-cta basalt-shop-cta--compact"><p class="basalt-shop-cta__label">%1$s</p><div class="wp-block-buttons">%2$s</div></div>',
			esc_html( basalt_shop_setting( 'grid_label' ) ),
			$button
		);
	}

	return sprintf(
		'<div class="basalt-shop-cta"><p class="basalt-shop-cta__label"><strong>%1$s</strong></p><p class="basalt-shop-cta__text">%2$s</p><div class="wp-block-buttons">%3$s</div></div>',
		esc_html( basalt_shop_setting( 'grid_label' ) ),
		esc_html( basalt_shop_setting( 'in_store_text' ) ),
		$button
	);
}

/**
 * Replace the cart blocks of an in store product with the call to action.
 *
 * @param string   $content  Rendered block.
 * @param array    $block    Parsed block.
 * @param WP_Block $instance Block instance, carrying the context.
 * @return string
 */
function basalt_shop_render_block( $content, $block, $instance ) {
	static $targets = array(
		'woocommerce/add-to-cart-form'         => false,
		'woocommerce/add-to-cart-with-options' => false,
		'woocommerce/product-button'           => true,
	);

	$name = (string) ( $block['blockName'] ?? '' );

	if ( ! array_key_exists( $name, $targets ) || ! basalt_shop_active() ) {
		return $content;
	}

	$post_id = (int) ( $instance->context['postId'] ?? get_the_ID() );

	if ( ! basalt_shop_in_store_only( $post_id ) ) {
		return $content;
	}

	return basalt_shop_cta( $targets[ $name ] );
}
add_filter( 'render_block', 'basalt_shop_render_block', 10, 3 );

/**
 * A class on the product, so a theme can style the grid card.
 *
 * @param string[]   $classes Classes.
 * @param WC_Product $product The product.
 * @return string[]
 */
function basalt_shop_post_class( $classes, $product ) {
	if ( basalt_shop_in_store_only( $product ) ) {
		$classes[] = 'basalt-in-store-only';
	}

	return $classes;
}
add_filter( 'woocommerce_post_class', 'basalt_shop_post_class', 10, 2 );

/**
 * Tell search engines the truth about the offer.
 *
 * @param array<string, mixed> $offer   Offer node.
 * @param WC_Product           $product The product.
 * @return array<string, mixed>
 */
function basalt_shop_offer_availability( $offer, $product ) {
	if ( basalt_shop_in_store_only( $product ) ) {
		$offer['availability'] = 'https://schema.org/InStoreOnly';
	}

	return $offer;
}
add_filter( 'woocommerce_structured_data_product_offer', 'basalt_shop_offer_availability', 10, 2 );

/**
 * Minimal styles, printed only on WooCommerce views.
 *
 * @return void
 */
function basalt_shop_styles(): void {
	if ( ! basalt_shop_active() || ! ( is_woocommerce() || is_front_page() ) ) {
		return;
	}

	wp_register_style( 'basalt-shop', false, array(), BASALT_SHOP_VERSION );
	wp_enqueue_style( 'basalt-shop' );
	wp_add_inline_style(
		'basalt-shop',
		'.basalt-shop-cta{display:grid;gap:.5rem}.basalt-shop-cta__label,.basalt-shop-cta__text{margin:0}.basalt-shop-cta--compact .basalt-shop-cta__label{font-size:.9375rem}'
	);
}
add_action( 'wp_enqueue_scripts', 'basalt_shop_styles' );

/* -------------------------------------------------------------------------
 * Settings, as a section of WooCommerce, Settings, Products
 * ---------------------------------------------------------------------- */

/**
 * Add the section.
 *
 * @param array<string, string> $sections Sections.
 * @return array<string, string>
 */
function basalt_shop_settings_section( $sections ) {
	$sections['basalt_shop'] = __( 'In store', 'basalt-shop' );

	return $sections;
}
add_filter( 'woocommerce_get_sections_products', 'basalt_shop_settings_section' );

/**
 * The fields of the section.
 *
 * @param array<int, array<string, mixed>> $settings Settings.
 * @param string                           $section  Current section.
 * @return array<int, array<string, mixed>>
 */
function basalt_shop_settings_fields( $settings, $section ) {
	if ( 'basalt_shop' !== $section ) {
		return $settings;
	}

	return array(
		array(
			'title' => __( 'Products sold in store only', 'basalt-shop' ),
			'type'  => 'title',
			'desc'  => __( 'Mark a product as "In store only" on its General tab. These settings decide what visitors see instead of the cart button.', 'basalt-shop' ),
			'id'    => 'basalt_shop_options',
		),
		array(
			'title'    => __( 'Appointment page', 'basalt-shop' ),
			'desc'     => __( 'Where the button leads. Leave empty to show the text without a button.', 'basalt-shop' ),
			'id'       => 'basalt_shop_appointment_url',
			'type'     => 'url',
			'css'      => 'min-width:24rem',
			'desc_tip' => false,
		),
		array(
			'title'   => __( 'Button label', 'basalt-shop' ),
			'id'      => 'basalt_shop_appointment_label',
			'type'    => 'text',
			'default' => __( 'Book a fitting', 'basalt-shop' ),
		),
		array(
			'title'   => __( 'Label in the product grid', 'basalt-shop' ),
			'id'      => 'basalt_shop_grid_label',
			'type'    => 'text',
			'default' => __( 'In store only', 'basalt-shop' ),
		),
		array(
			'title'   => __( 'Text on the product page', 'basalt-shop' ),
			'id'      => 'basalt_shop_in_store_text',
			'type'    => 'textarea',
			'css'     => 'min-width:24rem;min-height:5rem',
			'default' => __( 'This one we only hand over after a fitting. We measure you, bring two or three sizes into the cubicle, and you decide in front of the mirror.', 'basalt-shop' ),
		),
		array(
			'title'   => __( 'Hide the price', 'basalt-shop' ),
			'desc'    => __( 'Do not show a price for products sold in store only.', 'basalt-shop' ),
			'id'      => 'basalt_shop_hide_price',
			'type'    => 'checkbox',
			'default' => 'no',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'basalt_shop_options',
		),
	);
}
add_filter( 'woocommerce_get_settings_products', 'basalt_shop_settings_fields', 10, 2 );

/**
 * Load translations.
 *
 * @return void
 */
function basalt_shop_load_textdomain(): void {
	load_plugin_textdomain( 'basalt-shop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'basalt_shop_load_textdomain' );

/* -------------------------------------------------------------------------
 * Block: attribute range
 * ---------------------------------------------------------------------- */

/**
 * Register the plugin's blocks.
 *
 * @return void
 */
function basalt_shop_register_blocks(): void {
	if ( ! basalt_shop_active() ) {
		return;
	}

	register_block_type(
		plugin_dir_path( __FILE__ ) . 'blocks/attribute-range',
		array( 'render_callback' => 'basalt_shop_attribute_range_block' )
	);
}
add_action( 'init', 'basalt_shop_register_blocks' );

/**
 * Sort attribute options the way a shopper reads them.
 *
 * Numbers by value, everything else naturally, so "E, F, G" and "75, 80, 85"
 * both end up in order regardless of how they were entered.
 *
 * @param string[] $options Option names.
 * @return string[]
 */
function basalt_shop_sort_options( array $options ): array {
	$options = array_values( array_unique( array_map( 'trim', $options ) ) );

	usort(
		$options,
		static function ( string $a, string $b ): int {
			if ( is_numeric( $a ) && is_numeric( $b ) ) {
				return (float) $a <=> (float) $b;
			}

			return strnatcasecmp( $a, $b );
		}
	);

	return $options;
}

/**
 * Render the attribute range block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $content    Unused.
 * @param WP_Block|null        $block      Block instance, for the product in context.
 * @return string
 */
function basalt_shop_attribute_range_block( $attributes, $content = '', $block = null ): string {
	$post_id = (int) ( $block instanceof WP_Block ? ( $block->context['postId'] ?? 0 ) : 0 );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- editor preview passes the product id, read only.
	$post_id = $post_id ?: (int) ( $_GET['postId'] ?? get_the_ID() );
	$product = $post_id ? wc_get_product( $post_id ) : null;

	if ( ! $product ) {
		return '';
	}

	$slugs = array_values( array_filter( array_map( 'trim', explode( ',', (string) ( $attributes['attributes'] ?? '' ) ) ) ) );

	if ( ! $slugs ) {
		return '';
	}

	$parts = array();

	foreach ( $slugs as $index => $slug ) {
		$taxonomy = str_starts_with( $slug, 'pa_' ) ? $slug : 'pa_' . $slug;
		$raw      = $product->get_attribute( $taxonomy );

		if ( '' === $raw ) {
			$raw = $product->get_attribute( $slug );
		}

		if ( '' === $raw ) {
			continue;
		}

		// WooCommerce joins options with WC_DELIMITER ("|"); older data and custom attributes may use commas.
		$options = basalt_shop_sort_options( preg_split( '/s*[|,]s*/', $raw ) ?: array() );
		$first   = reset( $options );
		$last    = end( $options );

		/* translators: 1: smallest option, 2: largest option */
		$range = $first === $last ? $first : sprintf( _x( '%1$s to %2$s', 'attribute range', 'basalt-shop' ), $first, $last );

		if ( $index > 0 || ! empty( $attributes['labelFirst'] ) ) {
			$label = wc_attribute_label( taxonomy_exists( $taxonomy ) ? $taxonomy : $slug, $product );
			$range = $label . ' ' . $range;
		}

		$parts[] = esc_html( $range );
	}

	if ( ! $parts ) {
		return '';
	}

	$separator = sprintf( '<span aria-hidden="true">%s</span>', esc_html( (string) ( $attributes['separator'] ?? ' · ' ) ) );

	return sprintf(
		'<p %1$s>%2$s</p>',
		get_block_wrapper_attributes( array( 'class' => 'basalt-attribute-range' ) ),
		implode( $separator, $parts )
	);
}

/* -------------------------------------------------------------------------
 * Availability text for products that do not manage stock
 * ---------------------------------------------------------------------- */

/**
 * Say "Available" instead of nothing.
 *
 * WooCommerce prints an availability line only when stock is managed or the
 * product is out of stock. A product that is simply in stock says nothing,
 * and in a grid where the next card says "Only 2 left" a blank reads as a
 * doubt. This fills the blank for in stock products; the wording is a
 * translatable string a theme can change.
 *
 * @param string     $text    Availability text.
 * @param WC_Product $product The product.
 * @return string
 */
function basalt_shop_availability_text( $text, $product ) {
	if ( '' === $text && $product->is_in_stock() && ! $product->managing_stock() && ! basalt_shop_in_store_only( $product ) ) {
		/**
		 * Filter whether in stock products without stock management get an availability line.
		 *
		 * @param bool $fill Default true.
		 */
		if ( apply_filters( 'basalt_shop_fill_availability', true ) ) {
			return __( 'Available', 'basalt-shop' );
		}
	}

	return $text;
}
add_filter( 'woocommerce_get_availability_text', 'basalt_shop_availability_text', 10, 2 );
