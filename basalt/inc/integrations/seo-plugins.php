<?php
/**
 * SEO plugin detection.
 *
 * Split out so there is exactly one place to teach the theme about a new
 * plugin, and so the rest of the code asks a question ("does something else
 * already own this?") instead of checking class names inline.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * The SEO plugins Basalt knows about, and what each of them takes over.
 *
 * @return array<string, array{check: callable, handles: string[]}>
 */
function basalt_known_seo_plugins(): array {
	return array(
		'rank-math' => array(
			'check'   => static fn(): bool => defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' ),
			'handles' => array( 'meta', 'schema', 'breadcrumbs', 'sitemap' ),
		),
		'yoast'     => array(
			'check'   => static fn(): bool => defined( 'WPSEO_VERSION' ),
			'handles' => array( 'meta', 'schema', 'breadcrumbs', 'sitemap' ),
		),
		'seopress'  => array(
			'check'   => static fn(): bool => defined( 'SEOPRESS_VERSION' ),
			'handles' => array( 'meta', 'schema', 'breadcrumbs', 'sitemap' ),
		),
		'aioseo'    => array(
			'check'   => static fn(): bool => defined( 'AIOSEO_VERSION' ),
			'handles' => array( 'meta', 'schema', 'breadcrumbs', 'sitemap' ),
		),
		'slim-seo'  => array(
			'check'   => static fn(): bool => defined( 'SLIM_SEO_VER' ),
			'handles' => array( 'meta', 'schema', 'sitemap' ),
		),
		'squirrly'  => array(
			'check'   => static fn(): bool => defined( 'SQ_VERSION' ),
			'handles' => array( 'meta', 'schema' ),
		),
	);
}

/**
 * Whether an active SEO plugin already owns a responsibility.
 *
 * @param string $responsibility One of meta, schema, breadcrumbs, sitemap.
 * @return bool
 */
function basalt_seo_plugin_handles( string $responsibility ): bool {
	static $cache = array();

	if ( isset( $cache[ $responsibility ] ) ) {
		return $cache[ $responsibility ];
	}

	$handled = false;

	foreach ( basalt_known_seo_plugins() as $plugin ) {
		if ( ! in_array( $responsibility, $plugin['handles'], true ) ) {
			continue;
		}

		if ( ( $plugin['check'] )() ) {
			$handled = true;
			break;
		}
	}

	/**
	 * Filter whether an SEO plugin owns a responsibility.
	 *
	 * Set this to false to force the theme's own output back on, for example
	 * when a plugin is installed but its schema module is switched off.
	 *
	 * @param bool   $handled        Whether a plugin handles it.
	 * @param string $responsibility The responsibility being checked.
	 */
	$handled = (bool) apply_filters( 'basalt_seo_plugin_handles', $handled, $responsibility );

	$cache[ $responsibility ] = $handled;

	return $handled;
}

/**
 * The slug of the active SEO plugin, if any.
 *
 * @return string
 */
function basalt_active_seo_plugin(): string {
	foreach ( basalt_known_seo_plugins() as $slug => $plugin ) {
		if ( ( $plugin['check'] )() ) {
			return $slug;
		}
	}

	return '';
}
