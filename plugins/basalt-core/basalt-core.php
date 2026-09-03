<?php
/**
 * Plugin Name: Basalt Core
 * Description: Structured data, meta tags, breadcrumbs and accessibility corrections. The part of a Basalt site that must survive a theme change.
 * Version: 1.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Riccardo D'Arcangelo
 * License: GPL-2.0-or-later
 * Text Domain: basalt-core
 *
 * Why this is a plugin
 * --------------------
 * A theme decides how a site looks. Everything here decides what a site *is*
 * to a search engine and to assistive technology: the business behind it, its
 * address and opening hours, the Schema.org graph, the breadcrumb trail.
 *
 * Putting that in a theme means the customer loses their structured data the
 * day they change theme, and it means the settings live in theme_mods, which
 * WordPress stores per stylesheet. That is not hypothetical: while testing, a
 * switch from the parent theme to the child theme silently reset every
 * business detail, because theme_mods do not carry across.
 *
 * These settings are options. They belong to the site.
 *
 * The plugin is useful on its own, under any theme. Basalt simply knows how to
 * render the blocks it provides.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

define( 'BASALT_CORE_VERSION', '1.1.0' );
define( 'BASALT_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'BASALT_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Modules, in load order.
 *
 * seo-plugins is first: the others ask it whether a dedicated SEO plugin has
 * already taken over a responsibility.
 */
foreach ( array( 'color', 'seo-plugins', 'settings', 'demos', 'meta-tags', 'breadcrumbs', 'opening-hours', 'maintenance', 'feedback', 'robots-txt', 'llms', 'indexing', 'schema', 'accessibility', 'plugin-fixes', 'preferences', 'login', 'blocks' ) as $basalt_core_module ) {
	require_once BASALT_CORE_DIR . 'inc/' . $basalt_core_module . '.php';
}

unset( $basalt_core_module );

/**
 * Load translations.
 *
 * @return void
 */
function basalt_core_load_textdomain(): void {
	load_plugin_textdomain( 'basalt-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'basalt_core_load_textdomain' );
