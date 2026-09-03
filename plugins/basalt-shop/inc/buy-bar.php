<?php
/**
 * The buy bar: the price and the button, kept within reach on a phone.
 *
 * A product page on a phone is a column of pictures, a description, a size
 * table and a row of reviews. The button that actually sells the thing scrolls
 * out of sight in the first second and never comes back unless the visitor
 * scrolls all the way up again. Every shop that measures this ends up with the
 * same bar at the bottom of the screen.
 *
 * Two rules kept it small:
 *
 * The bar does not contain a second add to cart form. A second form means a
 * second set of variation selects, a second quantity field and two states to
 * keep in step. The button here belongs to the form that is already on the
 * page, through the HTML form attribute, so pressing it is exactly the same
 * event as pressing the real one. Where that cannot work, because a variation
 * has to be chosen first, the button is a link that takes the visitor to the
 * form instead.
 *
 * And it works without JavaScript. Without it the bar simply stays visible,
 * which is the behaviour of most shops anyway. With it the bar keeps out of
 * the way until the real button has scrolled past.
 *
 * @package BasaltShop
 */

defined( 'ABSPATH' ) || exit;

/** The id given to the add to cart form on a product page. */
const BASALT_SHOP_FORM_ID = 'basalt-shop-buy';

/**
 * Whether the bar should be built for this request.
 *
 * @return bool
 */
function basalt_shop_buy_bar_enabled(): bool {
	if ( ! basalt_shop_active() || ! is_product() || post_password_required() ) {
		return false;
	}

	/**
	 * Filter whether the buy bar is used at all.
	 *
	 * @param bool $enabled Whether to render it.
	 */
	return (bool) apply_filters( 'basalt_shop_buy_bar', 'yes' === basalt_shop_setting( 'buy_bar' ) );
}

/**
 * Give the add to cart form an id, so a button elsewhere can submit it.
 *
 * The classic template and the two block versions all end up as one form with
 * class "cart". Only the first one is touched, and only if it has no id yet:
 * an id that already exists belongs to somebody else.
 *
 * @param string $content Rendered block.
 * @param array  $block   Parsed block.
 * @return string
 */
function basalt_shop_tag_cart_form( $content, $block ) {
	static $tagged = false;

	if ( $tagged || ! basalt_shop_buy_bar_enabled() ) {
		return $content;
	}

	$name = (string) ( $block['blockName'] ?? '' );

	if ( ! in_array( $name, array( 'woocommerce/add-to-cart-form', 'woocommerce/add-to-cart-with-options' ), true ) ) {
		return $content;
	}

	if ( ! preg_match( '/<form\b[^>]*>/', $content, $match ) || str_contains( $match[0], ' id=' ) ) {
		return $content;
	}

	$tagged  = true;
	$form    = substr_replace( $match[0], ' id="' . BASALT_SHOP_FORM_ID . '"', 5, 0 );

	return str_replace( $match[0], $form, $content );
}
add_filter( 'render_block', 'basalt_shop_tag_cart_form', 5, 2 );

/**
 * The same for the classic product template.
 *
 * These two hooks sit outside the form, so the markup between them can be
 * caught and the id put in. A theme using the classic templates rather than
 * the WooCommerce blocks then gets the same bar without changing anything.
 *
 * @return void
 */
function basalt_shop_classic_form_open(): void {
	if ( basalt_shop_buy_bar_enabled() ) {
		ob_start();
		basalt_shop_classic_buffer( true );
	}
}

/**
 * Whether this plugin is the one holding an output buffer open.
 *
 * ob_get_level() is no answer: WordPress and half the plugin directory keep
 * buffers of their own, and closing somebody else's is how a page ends up
 * half rendered.
 *
 * @param bool|null $set Set the flag, or read it when null.
 * @return bool
 */
function basalt_shop_classic_buffer( ?bool $set = null ): bool {
	static $open = false;

	if ( null !== $set ) {
		$open = $set;
	}

	return $open;
}
add_action( 'woocommerce_before_add_to_cart_form', 'basalt_shop_classic_form_open', 1 );

/**
 * Close the buffer and print the form with its id.
 *
 * @return void
 */
function basalt_shop_classic_form_close(): void {
	if ( ! basalt_shop_classic_buffer() ) {
		return;
	}

	basalt_shop_classic_buffer( false );

	$html = (string) ob_get_clean();

	if ( preg_match( '/<form\b[^>]*>/', $html, $match ) && ! str_contains( $match[0], ' id=' ) ) {
		$html = str_replace( $match[0], substr_replace( $match[0], ' id="' . BASALT_SHOP_FORM_ID . '"', 5, 0 ), $html );
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup produced by WooCommerce, passed through unchanged.
}
add_action( 'woocommerce_after_add_to_cart_form', 'basalt_shop_classic_form_close', 99 );

/**
 * What the bar's button should be.
 *
 * Four cases, and the difference between them is whether pressing a button
 * can finish the job on its own.
 *
 * @param WC_Product $product The product.
 * @return array{kind: string, label: string, url: string}
 */
function basalt_shop_buy_bar_action( $product ): array {
	if ( basalt_shop_in_store_only( $product ) ) {
		$url = basalt_shop_setting( 'appointment_url' );

		return array(
			'kind'  => $url ? 'link' : 'none',
			'label' => basalt_shop_setting( 'appointment_label' ),
			'url'   => $url,
		);
	}

	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return array( 'kind' => 'none', 'label' => '', 'url' => '' );
	}

	if ( $product->is_type( 'external' ) ) {
		return array(
			'kind'  => 'link',
			'label' => (string) $product->single_add_to_cart_text(),
			'url'   => (string) $product->add_to_cart_url(),
		);
	}

	/*
	 * A variable or grouped product cannot be bought from down here: something
	 * has to be chosen first. The button takes the visitor to the choice
	 * rather than pretending it can be skipped.
	 */
	if ( $product->is_type( array( 'variable', 'grouped' ) ) ) {
		return array(
			'kind'  => 'jump',
			'label' => __( 'Choose your size', 'basalt-shop' ),
			'url'   => '#' . BASALT_SHOP_FORM_ID,
		);
	}

	return array(
		'kind'  => 'submit',
		'label' => (string) $product->single_add_to_cart_text(),
		'url'   => '',
	);
}

/**
 * Render the bar into the footer.
 *
 * @return void
 */
function basalt_shop_buy_bar(): void {
	if ( ! basalt_shop_buy_bar_enabled() ) {
		return;
	}

	$product = wc_get_product( get_the_ID() );

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$action = basalt_shop_buy_bar_action( $product );

	if ( 'none' === $action['kind'] ) {
		return;
	}

	$price = (string) $product->get_price_html();
	$title = wp_strip_all_tags( (string) $product->get_name() );

	if ( 'submit' === $action['kind'] ) {
		$button = sprintf(
			'<button type="submit" name="add-to-cart" value="%1$d" form="%2$s" class="wp-block-button__link wp-element-button basalt-buybar__button">%3$s</button>',
			(int) $product->get_id(),
			esc_attr( BASALT_SHOP_FORM_ID ),
			esc_html( $action['label'] )
		);
	} else {
		$button = sprintf(
			'<a href="%1$s" class="wp-block-button__link wp-element-button basalt-buybar__button">%2$s</a>',
			esc_url( $action['url'] ),
			esc_html( $action['label'] )
		);
	}

	printf(
		'<div class="basalt-buybar" data-basalt-buybar><div class="basalt-buybar__inner"><div class="basalt-buybar__text"><span class="basalt-buybar__title">%1$s</span><span class="basalt-buybar__price">%2$s</span></div>%3$s</div></div>',
		esc_html( $title ),
		wp_kses_post( $price ),
		$button // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above.
	);
}
add_action( 'wp_footer', 'basalt_shop_buy_bar', 20 );
