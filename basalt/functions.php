<?php
/**
 * Basalt bootstrap.
 *
 * This file only defines constants and loads the modules in inc/.
 * Put real logic into a module, never here, so child themes can
 * unhook a single concern instead of the whole theme.
 *
 * @package Basalt
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme version. Also used as the asset cache buster in production.
 * Keep in sync with the Version header in style.css.
 */
define( 'BASALT_VERSION', '2.0.0' );

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
 * Filterable so a child theme can drop a module it wants to replace
 * wholesale, for example: unset the 'seo' entry when a dedicated SEO
 * plugin should own every meta tag.
 *
 * @param string[] $modules Module slugs relative to inc/.
 */
$basalt_modules = apply_filters(
	'basalt_modules',
	array(
		'compat',
		'setup',
		'assets',
		'template-functions',
		'template-tags',
		'navigation',
		'accessibility',
		'performance',
		'blocks',
		'customizer',
		// Loaded before seo, which asks it whether a plugin owns an output.
		'integrations/seo-plugins',
		'seo',
		'integrations/woocommerce',
		'admin/theme-page',
		'admin/notices',
	)
);

foreach ( $basalt_modules as $basalt_module ) {
	basalt_load_module( (string) $basalt_module );
}

unset( $basalt_modules, $basalt_module );
