<?php
/**
 * Environment guard.
 *
 * WordPress honours the "Requires PHP" and "Requires at least" headers in
 * style.css when a theme is activated through the admin, but not when a theme
 * is dropped into wp-content/themes over FTP or restored from a backup. This
 * module catches that case so the site shows a notice instead of a white screen.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimum supported versions. Keep in sync with style.css.
 */
const BASALT_MIN_PHP = '8.1';
const BASALT_MIN_WP  = '6.6';

/**
 * Whether the current environment can run the theme.
 *
 * @return bool
 */
function basalt_environment_is_supported(): bool {
	return version_compare( PHP_VERSION, BASALT_MIN_PHP, '>=' )
		&& version_compare( get_bloginfo( 'version' ), BASALT_MIN_WP, '>=' );
}

/**
 * Warn administrators when the environment is below the supported minimum.
 *
 * The theme still renders; unsupported means untested, not broken. Failing
 * loudly here would leave the site owner with no way back into the admin.
 *
 * @return void
 */
function basalt_environment_notice(): void {
	if ( basalt_environment_is_supported() || ! current_user_can( 'switch_themes' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		sprintf(
			/* translators: 1: required PHP version, 2: required WordPress version, 3: current PHP version, 4: current WordPress version. */
			esc_html__( 'Basalt requires PHP %1$s and WordPress %2$s or newer. This site runs PHP %3$s and WordPress %4$s. Please update before reporting problems.', 'basalt' ),
			esc_html( BASALT_MIN_PHP ),
			esc_html( BASALT_MIN_WP ),
			esc_html( PHP_VERSION ),
			esc_html( get_bloginfo( 'version' ) )
		)
	);
}
add_action( 'admin_notices', 'basalt_environment_notice' );
