<?php
/**
 * Basalt bootstrap.
 *
 * Basalt is a block theme. The templates are HTML in templates/ and parts/, so
 * this file is deliberately small: theme supports, assets, block styles and the
 * pattern registration, and nothing else.
 *
 * Structured data, meta tags, breadcrumbs and the accessibility corrections for
 * core blocks live in the Basalt Core plugin. They describe what the site is
 * rather than how it looks, and a customer must not lose them by changing
 * theme.
 *
 * @package Basalt
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/** Theme version. Also the asset cache buster in production. */
define( 'BASALT_VERSION', '3.0.0' );

/** Absolute path to the parent theme, with a trailing slash. */
define( 'BASALT_DIR', trailingslashit( get_template_directory() ) );

/** URL of the parent theme, with a trailing slash. */
define( 'BASALT_URI', trailingslashit( get_template_directory_uri() ) );

/**
 * Load a theme module from inc/.
 *
 * @param string $module Path relative to inc/, without the .php extension.
 * @return void
 */
function basalt_load_module( string $module ): void {
	/*
	 * The module list is filterable, so the value reaching this function is not
	 * necessarily one of the strings below. Constraining it to the characters a
	 * module name can contain means a filter cannot turn this into an arbitrary
	 * include, whether by mistake or otherwise.
	 */
	if ( ! preg_match( '#^[a-z0-9-]+(/[a-z0-9-]+)*$#', $module ) ) {
		return;
	}

	$path = BASALT_DIR . 'inc/' . $module . '.php';

	if ( is_readable( $path ) ) {
		require_once $path;
		return;
	}

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		wp_trigger_error( __FUNCTION__, sprintf( 'Basalt module "%s" is missing.', esc_html( $module ) ) );
	}
}

/**
 * Modules, in load order.
 *
 * Filterable so a child theme can drop one it wants to replace wholesale.
 *
 * @param string[] $modules Module slugs relative to inc/.
 */
$basalt_modules = apply_filters(
	'basalt_modules',
	array(
		'compat',
		'setup',
		'assets',
		'blocks',
		'accessibility',
		'performance',
		'template-functions',
		'admin/theme-page',
		'integrations/elementor',
		'integrations/forms',
		'integrations/woocommerce',
	)
);

foreach ( $basalt_modules as $basalt_module ) {
	basalt_load_module( (string) $basalt_module );
}

unset( $basalt_modules, $basalt_module );
