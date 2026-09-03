<?php
/**
 * The cart, as a panel that slides in from the side.
 *
 * WooCommerce ships a mini cart block, and it is a small application: a store
 * of its own, an interactivity runtime, a rendering layer, several hundred
 * kilobytes of JavaScript for a list of three items and a subtotal. On a shop
 * with forty products that is the heaviest thing on the page.
 *
 * This one is a details element with the cart rendered into it on the server.
 * It opens without JavaScript, it closes without JavaScript, the remove links
 * are ordinary links with a nonce, and the two buttons at the bottom are
 * ordinary links to the cart and the checkout. What JavaScript adds is the
 * three manners a panel needs: escape closes it, a click on the dimmed page
 * closes it, and the focus goes into the panel and comes back to the button.
 *
 * After adding something to the cart WooCommerce returns to the same page with
 * added-to-cart in the query, so the panel can simply be rendered open. That
 * is the moment the panel exists for, and it needs no script at all.
 *
 * One caveat, and it is inherent rather than a defect: this markup contains
 * the visitor's cart, so a page holding it must not be served from a shared
 * cache. WooCommerce sets the cookies that tell a sane cache to skip the page
 * once a cart exists, and an empty cart renders an empty panel, which is
 * harmless to cache.
 *
 * @package BasaltShop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether there is a cart to talk about at all.
 *
 * @return bool
 */
function basalt_shop_cart_ready(): bool {
	return basalt_shop_active() && function_exists( 'WC' ) && WC()->cart instanceof WC_Cart;
}

/**
 * Render the drawer block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_shop_cart_drawer_block( $attributes ): string {
	if ( ! basalt_shop_cart_ready() ) {
		return '';
	}

	$attributes = wp_parse_args(
		(array) $attributes,
		array(
			'label'     => __( 'Cart', 'basalt-shop' ),
			'showCount' => true,
			'showTotal' => false,
		)
	);

	$cart  = WC()->cart;
	$count = (int) $cart->get_cart_contents_count();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a marker WooCommerce puts in the URL after its own redirect, used to decide whether a panel starts open.
	$just_added = isset( $_GET['added-to-cart'] );

	$summary = sprintf(
		'<span class="basalt-cart__label">%1$s</span>%2$s%3$s',
		esc_html( (string) $attributes['label'] ),
		$attributes['showCount'] ? sprintf(
			'<span class="basalt-cart__count" aria-hidden="true">%d</span>',
			$count
		) : '',
		$attributes['showTotal'] && $count ? sprintf(
			'<span class="basalt-cart__total">%s</span>',
			wp_kses_post( $cart->get_cart_subtotal() )
		) : ''
	);

	/* translators: %d: number of items in the cart. */
	$reader = sprintf( _n( '%d item in the cart', '%d items in the cart', $count, 'basalt-shop' ), $count );

	$wrapper = get_block_wrapper_attributes( array( 'class' => 'basalt-cart' ) );

	return sprintf(
		'<details %1$s data-basalt-cart%2$s><summary class="basalt-cart__toggle">%3$s<span class="screen-reader-text">%4$s</span></summary><div class="basalt-cart__panel" role="dialog" aria-label="%5$s"><div class="basalt-cart__inner">%6$s</div></div></details>',
		$wrapper, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes().
		$just_added ? ' open' : '',
		$summary, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above.
		esc_html( $reader ),
		esc_attr__( 'Cart', 'basalt-shop' ),
		basalt_shop_cart_panel() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped in the function.
	);
}

/**
 * Where a remove link should come back to.
 *
 * WooCommerce sends its own remove links to the cart page, which is the right
 * answer for a cart page and the wrong one for a panel: taking something out
 * of the cart is not a reason to leave the product somebody is reading about.
 *
 * When the panel is being fetched by the script, the current URL is the
 * endpoint rather than a page, so the referer is used instead. wp_get_referer
 * validates it against this site, and anything else falls back to the cart.
 *
 * @return string
 */
function basalt_shop_cart_return_url(): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- deciding which URL to print, not acting on it.
	if ( isset( $_GET['basalt-cart-panel'] ) ) {
		$referer = wp_get_referer();

		return $referer ? $referer : (string) wc_get_cart_url();
	}

	$url = home_url( add_query_arg( array() ) );

	return remove_query_arg( array( 'added-to-cart', 'basalt-cart-panel', 'remove_item', 'removed_item', '_wpnonce' ), $url );
}

/**
 * The contents of the panel.
 *
 * @return string
 */
function basalt_shop_cart_panel(): string {
	$cart  = WC()->cart;
	$items = $cart->get_cart();

	$close = sprintf(
		'<div class="basalt-cart__head"><p class="basalt-cart__heading">%1$s</p><button type="button" class="basalt-cart__close" data-basalt-cart-close hidden>%2$s</button></div>',
		esc_html__( 'Your cart', 'basalt-shop' ),
		esc_html__( 'Close', 'basalt-shop' )
	);

	if ( ! $items ) {
		return $close . sprintf(
			'<p class="basalt-cart__empty">%1$s</p><p class="basalt-cart__actions"><a class="wp-block-button__link wp-element-button" href="%2$s">%3$s</a></p>',
			esc_html__( 'Nothing in it yet.', 'basalt-shop' ),
			esc_url( (string) wc_get_page_permalink( 'shop' ) ),
			esc_html__( 'Have a look around', 'basalt-shop' )
		);
	}

	$rows = '';

	foreach ( $items as $key => $item ) {
		$product = $item['data'] ?? null;

		if ( ! $product instanceof WC_Product || ! apply_filters( 'woocommerce_widget_cart_item_visible', true, $item, $key ) ) {
			continue;
		}

		$quantity = (int) ( $item['quantity'] ?? 0 );
		$link     = $product->is_visible() ? (string) $product->get_permalink( $item ) : '';
		$name     = wp_strip_all_tags( (string) $product->get_name() );

		$title = '' !== $link
			? sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html( $name ) )
			: esc_html( $name );

		$rows .= sprintf(
			'<li class="basalt-cart__item">%1$s<div class="basalt-cart__item-text"><p class="basalt-cart__item-name">%2$s</p><p class="basalt-cart__item-meta">%3$s%4$s</p></div><p class="basalt-cart__item-price">%5$s</p><a class="basalt-cart__remove" href="%6$s" aria-label="%7$s">&times;</a></li>',
			$product->get_image( 'woocommerce_gallery_thumbnail', array( 'class' => 'basalt-cart__item-image', 'loading' => 'lazy' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce builds the img tag.
			$title, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			esc_html( sprintf( '%d x ', $quantity ) ),
			wp_kses_post( (string) $product->get_price_html() ),
			wp_kses_post( (string) apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $quantity ), $item, $key ) ),
			esc_url(
				add_query_arg(
					array(
						'remove_item' => $key,
						'_wpnonce'    => wp_create_nonce( 'woocommerce-cart' ),
					),
					basalt_shop_cart_return_url()
				)
			),
			/* translators: %s: product name. */
			esc_attr( sprintf( __( 'Remove %s from the cart', 'basalt-shop' ), $name ) )
		);
	}

	$foot = sprintf(
		'<div class="basalt-cart__foot"><p class="basalt-cart__subtotal"><span>%1$s</span><span>%2$s</span></p><p class="basalt-cart__note">%3$s</p><div class="basalt-cart__actions"><a class="wp-block-button__link wp-element-button basalt-cart__button--quiet" href="%4$s">%5$s</a><a class="wp-block-button__link wp-element-button" href="%6$s">%7$s</a></div></div>',
		esc_html__( 'Subtotal', 'basalt-shop' ),
		wp_kses_post( (string) WC()->cart->get_cart_subtotal() ),
		esc_html__( 'Shipping and any discount are worked out at the checkout.', 'basalt-shop' ),
		esc_url( (string) wc_get_cart_url() ),
		esc_html__( 'View cart', 'basalt-shop' ),
		esc_url( (string) wc_get_checkout_url() ),
		esc_html__( 'Checkout', 'basalt-shop' )
	);

	return $close . '<ul class="basalt-cart__items">' . $rows . '</ul>' . $foot;
}

/**
 * Answer with the current panel, for the script to drop in.
 *
 * WooCommerce catches the submit on a block theme and adds to the cart over
 * its own API, so the page never reloads and a panel rendered on the server
 * would still be showing the cart from before the click. This is the smallest
 * possible answer to that: the same markup the block renders, as JSON, for the
 * one moment it is needed.
 *
 * Nothing is written here and nothing is accepted from the request, so there
 * is no nonce: this reads the cart that belongs to the cookie the browser
 * already sent, and a different site cannot read the answer.
 *
 * @return void
 */
function basalt_shop_cart_endpoint(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read of the caller's own cart, see above.
	if ( ! isset( $_GET['basalt-cart-panel'] ) || ! basalt_shop_cart_ready() ) {
		return;
	}

	$cart = WC()->cart;

	nocache_headers();
	wp_send_json(
		array(
			'count'    => (int) $cart->get_cart_contents_count(),
			'subtotal' => wp_kses_post( (string) $cart->get_cart_subtotal() ),
			'html'     => basalt_shop_cart_panel(),
		)
	);
}
add_action( 'template_redirect', 'basalt_shop_cart_endpoint', 5 );

/**
 * Keep the page from being cached once there is something in the cart.
 *
 * A cart in the markup is personal data. WooCommerce already sets its own
 * cookies for this; the constant is what most page caches on shared hosting
 * actually look at.
 *
 * @return void
 */
function basalt_shop_cart_no_cache(): void {
	if ( ! basalt_shop_cart_ready() || defined( 'DONOTCACHEPAGE' ) ) {
		return;
	}

	if ( WC()->cart->get_cart_contents_count() > 0 ) {
		define( 'DONOTCACHEPAGE', true );
	}
}
add_action( 'template_redirect', 'basalt_shop_cart_no_cache', 20 );
