<?php
/**
 * SEO plugin detection.
 *
 * Split out so there is exactly one place to teach the theme about a new
 * plugin, and so the rest of the code asks a question ("does something else
 * already own this?") instead of checking class names inline.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * The SEO plugins Basalt knows about, and what each of them takes over.
 *
 * @return array<string, array{check: callable, handles: string[]}>
 */
function basalt_core_known_seo_plugins(): array {
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
 * The multilingual plugins Basalt knows about.
 *
 * Separate from the SEO plugins because they overlap on exactly one
 * responsibility. None of them writes meta descriptions and none of them builds
 * a schema graph, so a site running Polylang and nothing else still wants
 * everything Basalt Core emits. What they do own is hreflang, and they own it
 * completely: only the translation plugin knows which URLs are translations of
 * each other.
 *
 * Basalt Core emits no hreflang of its own, and this list is why it can stay
 * that way without the question coming back. A site with one language does not
 * need hreflang, and a site with several has one of these installed. A
 * self-referencing hreflang emitted by a theme on a monolingual site is noise
 * at best, and on a translated site it is a second, contradicting answer.
 *
 * @return array<string, array{check: callable, handles: string[]}>
 */
function basalt_core_known_multilingual_plugins(): array {
	return array(
		'wpml'           => array(
			'check'   => static fn(): bool => defined( 'ICL_SITEPRESS_VERSION' ),
			'handles' => array( 'hreflang' ),
		),
		'polylang'       => array(
			'check'   => static fn(): bool => defined( 'POLYLANG_VERSION' ),
			'handles' => array( 'hreflang' ),
		),
		'translatepress' => array(
			'check'   => static fn(): bool => defined( 'TRP_PLUGIN_VERSION' ),
			'handles' => array( 'hreflang' ),
		),
		'weglot'         => array(
			'check'   => static fn(): bool => defined( 'WEGLOT_VERSION' ),
			'handles' => array( 'hreflang' ),
		),
		'gtranslate'     => array(
			'check'   => static fn(): bool => defined( 'GTRANSLATE_VERSION' ),
			'handles' => array( 'hreflang' ),
		),
	);
}

/**
 * Whether an active plugin already owns a responsibility.
 *
 * @param string $responsibility One of meta, schema, breadcrumbs, sitemap, hreflang.
 * @return bool
 */
function basalt_core_seo_plugin_handles( string $responsibility ): bool {
	static $cache = array();

	if ( isset( $cache[ $responsibility ] ) ) {
		return $cache[ $responsibility ];
	}

	$handled = false;
	$plugins = array_merge( basalt_core_known_seo_plugins(), basalt_core_known_multilingual_plugins() );

	foreach ( $plugins as $plugin ) {
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
	$handled = (bool) apply_filters( 'basalt_core_seo_plugin_handles', $handled, $responsibility );

	$cache[ $responsibility ] = $handled;

	return $handled;
}

/**
 * The display name of the active SEO plugin, if any.
 *
 * @return string Empty string when none is active.
 */
function basalt_core_active_seo_plugin(): string {
	$names = array(
		'rank-math' => 'Rank Math SEO',
		'yoast'     => 'Yoast SEO',
		'seopress'  => 'SEOPress',
		'aioseo'    => 'All in One SEO',
		'slim-seo'  => 'Slim SEO',
		'squirrly'  => 'Squirrly SEO',
	);

	foreach ( basalt_core_known_seo_plugins() as $slug => $plugin ) {
		if ( ( $plugin['check'] )() ) {
			return $names[ $slug ] ?? $slug;
		}
	}

	return '';
}
