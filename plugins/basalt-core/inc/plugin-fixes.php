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

	if ( class_exists( 'Cookie_Notice' ) ) {
		/*
		 * Cookie Notice paints the accept button in a colour from its own
		 * settings and leaves the label white, whatever that colour turns out
		 * to be. Its default, #00a99d, gives 2.93:1 where 4.5:1 is required,
		 * and that is on the primary control of a banner a site is obliged to
		 * make usable.
		 *
		 * The background is left alone. It is a colour somebody chose, and a
		 * theme that quietly repaints a brand colour is worse than the problem.
		 * Only the label moves, to whichever of near black or near white can
		 * actually be read on it, so a site that picks a dark button keeps its
		 * white label and this rule is not emitted at all.
		 */
		$options = get_option( 'cookie_notice_options', array() );
		$button  = basalt_core_hex_color( is_array( $options ) ? ( $options['colors']['button'] ?? '' ) : '' );

		if ( '' !== $button && basalt_core_contrast_ratio( $button, '#ffffff' ) < 4.5 ) {
			/*
			 * Two ids in the selector, which is not a typo. The plugin's own
			 * rule is #cookie-notice .cn-button:not(.cn-button-custom), and it
			 * sets color: inherit, so a single id loses to it. Repeating the
			 * ancestor id is the smallest way to win without !important, which
			 * would take the decision away from a site that has a good reason
			 * to set its own.
			 */
			$fixes['cookie-notice-contrast'] = sprintf(
				'#cookie-notice #cn-accept-cookie,#cookie-notice #cn-refuse-cookie{color:%s}',
				basalt_core_readable_on( $button )
			);
		}
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
 * Registered on the login screen as well. Consent banners render there too,
 * and a banner that cannot be read is no less of a problem for standing between
 * somebody and the login form.
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
add_action( 'login_enqueue_scripts', 'basalt_core_print_plugin_fixes', 20 );
