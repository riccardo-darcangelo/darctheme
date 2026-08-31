<?php
/**
 * Accessibility corrections for widely used plugins.
 *
 * Same reasoning as inc/accessibility.php, applied outside core: a correction
 * that is needed under any theme belongs in the plugin, so that a customer does
 * not lose it by changing theme.
 *
 * Nothing here patches plugin code. Each fix is a rule that stands on its own,
 * is scoped to markup the plugin controls, and is only printed when that plugin
 * is active. If the plugin fixes the problem itself, the rule stops matching
 * and costs nothing.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * The corrections that apply to the current request.
 *
 * @return array<string, string> Slug to CSS.
 */
function basalt_core_plugin_fixes(): array {
	$fixes = array();

	if ( class_exists( 'WooCommerce' ) ) {
		/*
		 * WooCommerce prints the mini cart drawer on every page of a store,
		 * closed, as aria-hidden="true" around six focusable controls. Nothing
		 * removes them from the tab order: only pointer-events is switched off,
		 * which stops the mouse and not the keyboard. A keyboard user tabbing
		 * through a product page therefore walks into a cart drawer that is not
		 * on screen and that a screen reader has been told to ignore, and there
		 * is no visible focus to follow.
		 *
		 * aria-hidden is the state WooCommerce itself binds through the
		 * Interactivity API, so keying off it stays correct when the drawer
		 * opens. visibility rather than display, because it leaves the drawer's
		 * transition intact.
		 */
		$fixes['woocommerce-mini-cart'] = '.wc-block-mini-cart__drawer[aria-hidden="true"]{visibility:hidden}';
	}

	/**
	 * Filter the plugin accessibility corrections.
	 *
	 * Unset an entry to drop one, for example after the plugin has fixed it.
	 *
	 * @param array<string, string> $fixes Slug to CSS.
	 */
	return (array) apply_filters( 'basalt_core_plugin_fixes', $fixes );
}

/**
 * Print the corrections.
 *
 * Inline rather than a stylesheet: this is a few hundred bytes that must be
 * present on every page of the site, and a separate request would cost more
 * than the rules do.
 *
 * @return void
 */
function basalt_core_print_plugin_fixes(): void {
	$css = implode( '', basalt_core_plugin_fixes() );

	if ( '' === $css ) {
		return;
	}

	wp_register_style( 'basalt-core-plugin-fixes', false, array(), BASALT_CORE_VERSION );
	wp_enqueue_style( 'basalt-core-plugin-fixes' );
	wp_add_inline_style( 'basalt-core-plugin-fixes', $css );
}
add_action( 'wp_enqueue_scripts', 'basalt_core_print_plugin_fixes', 20 );
