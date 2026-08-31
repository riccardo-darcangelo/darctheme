<?php
/**
 * Form plugin support.
 *
 * Contact Form 7 and WPForms together sit on more sites than any theme, and a
 * buyer will have installed one of them before they have finished reading the
 * documentation. Neither can match a theme it has never seen, so the theme
 * meets them halfway. See assets/css/forms.css for what each one needs and why.
 *
 * Gravity Forms is deliberately absent. It ships its own design system with its
 * own token layer, it is styled through that rather than through theme CSS, and
 * it is commercial, so a theme cannot test against it before shipping. A rule
 * written blind against markup nobody has run is worse than no rule.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stylesheet handles that mean a supported form plugin is rendering here.
 *
 * Asking whether the plugin put its own stylesheet on this request is a
 * better question than trying to work out whether the page contains a form:
 * it is right for shortcodes, blocks and widgets alike, and it never loads on
 * a page the plugin itself decided to skip. It is the plugin's call, not the
 * theme's, and the two do not currently agree: WPForms loads per page,
 * Contact Form 7 loads on every page until a site sets WPCF7_LOAD_CSS or the
 * wpcf7_load_css filter. Following it is still strictly better than loading
 * unconditionally, and it corrects itself when Contact Form 7 changes.
 *
 * @return string[]
 */
function basalt_form_plugin_handles(): array {
	/**
	 * Filter the handles that trigger the form stylesheet.
	 *
	 * @param string[] $handles Stylesheet handles.
	 */
	return (array) apply_filters(
		'basalt_form_plugin_handles',
		array(
			'contact-form-7',
			'wpforms-full',
			'wpforms-modern-full',
			'wpforms-classic-full',
		)
	);
}

/**
 * Load the form stylesheet when one of those plugins is on the page.
 *
 * Priority 20: both plugins enqueue at the default 10, so by the time this
 * runs the answer is known.
 *
 * @return void
 */
function basalt_forms_assets(): void {
	foreach ( basalt_form_plugin_handles() as $handle ) {
		if ( ! wp_style_is( $handle, 'enqueued' ) ) {
			continue;
		}

		wp_enqueue_style(
			'basalt-forms',
			BASALT_URI . 'assets/css/forms.css',
			array( 'basalt-components' ),
			basalt_asset_version( 'assets/css/forms.css' )
		);

		return;
	}
}
add_action( 'wp_enqueue_scripts', 'basalt_forms_assets', 20 );
