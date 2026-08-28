<?php
/**
 * Performance defaults.
 *
 * Everything here is asset level work a theme is allowed to do. Caching, image
 * conversion and database tuning are plugin territory on purpose: a buyer who
 * switches theme must not lose those.
 *
 * Every behaviour is filterable, because a site with a page builder or a
 * caching plugin may want a different trade-off.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load only the block stylesheets the page actually renders.
 *
 * Without this WordPress ships one combined stylesheet containing every core
 * block. On a typical page that is 60 to 100 KB of CSS for a handful of blocks.
 *
 * @return bool
 */
function basalt_separate_block_assets(): bool {
	/**
	 * Filter whether core block styles load per block.
	 *
	 * @param bool $separate Whether to split block styles.
	 */
	return (bool) apply_filters( 'basalt_separate_core_block_assets', true );
}
add_filter( 'should_load_separate_core_block_assets', 'basalt_separate_block_assets' );

/**
 * Drop the emoji detection script and its stylesheet.
 *
 * Every modern browser and operating system renders emoji natively. The
 * polyfill costs a render blocking inline script plus a DNS lookup to s.w.org
 * for the fallback images.
 *
 * @return void
 */
function basalt_disable_emoji(): void {
	/**
	 * Filter whether the emoji polyfill is removed.
	 *
	 * @param bool $disable Whether to remove the emoji scripts.
	 */
	if ( ! apply_filters( 'basalt_disable_emoji', true ) ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', 'basalt_remove_emoji_tinymce_plugin' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'basalt_disable_emoji' );

/**
 * Remove the emoji plugin from the classic editor.
 *
 * @param string[] $plugins TinyMCE plugins.
 * @return string[]
 */
function basalt_remove_emoji_tinymce_plugin( $plugins ) {
	return array_diff( (array) $plugins, array( 'wpemoji' ) );
}

/**
 * Remove head entries that leak the WordPress version or serve dead protocols.
 *
 * @return void
 */
function basalt_clean_head(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'basalt_clean_head' );

/**
 * Strip the query string version from the generator meta on feeds.
 *
 * @param string $generator Generator string.
 * @return string
 */
function basalt_remove_feed_generator( $generator ) {
	return '';
}
add_filter( 'the_generator', 'basalt_remove_feed_generator' );

/**
 * Speculative loading rules.
 *
 * WordPress 6.8 ships the Speculation Rules API and prerenders links on
 * hover by default. Basalt keeps that but excludes URLs where a speculative
 * request would do real work: carts, checkouts, logout links and anything
 * carrying a nonce.
 *
 * @param array<string, mixed> $rules Speculation rules configuration.
 * @return array<string, mixed>
 */
function basalt_speculation_rules_exclusions( $rules ) {
	$excluded = array(
		'/wp-login.php',
		'/wp-admin/*',
		'/cart/*',
		'/checkout/*',
		'/my-account/*',
		'/*\\?*add-to-cart=*',
		'/*\\?*_wpnonce=*',
	);

	/**
	 * Filter the URL patterns excluded from speculative loading.
	 *
	 * @param string[] $excluded URL patterns.
	 */
	$excluded = (array) apply_filters( 'basalt_speculation_exclusions', $excluded );

	if ( ! isset( $rules['prerender'] ) || ! is_array( $rules['prerender'] ) ) {
		return $rules;
	}

	foreach ( $rules['prerender'] as $index => $rule ) {
		if ( ! isset( $rule['where']['and'] ) || ! is_array( $rule['where']['and'] ) ) {
			continue;
		}

		$rules['prerender'][ $index ]['where']['and'][] = array(
			'not' => array( 'href_matches' => $excluded ),
		);
	}

	return $rules;
}
add_filter( 'wp_speculation_rules_configuration', 'basalt_speculation_rules_exclusions' );

/**
 * Keep oEmbed discovery links out of the head.
 *
 * They only matter when another site embeds this one, which is rare enough that
 * paying for two extra head entries on every page view is not worth it.
 *
 * @return void
 */
function basalt_disable_oembed_discovery(): void {
	/**
	 * Filter whether oEmbed discovery links are removed.
	 *
	 * @param bool $disable Whether to remove the links.
	 */
	if ( ! apply_filters( 'basalt_disable_oembed_discovery', true ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'basalt_disable_oembed_discovery' );

/**
 * Skip lazy loading for images in the first screenful.
 *
 * WordPress already skips the first content image, but a template that renders
 * a hero above the loop confuses that heuristic. This raises the threshold so
 * the first two images on singular views load eagerly.
 *
 * @param int $threshold Number of images that skip lazy loading.
 * @return int
 */
function basalt_lazy_load_threshold( $threshold ) {
	return is_singular() ? 2 : (int) $threshold;
}
add_filter( 'wp_omit_loading_attr_threshold', 'basalt_lazy_load_threshold' );

/**
 * Preconnect to the origins the theme genuinely uses.
 *
 * Basalt makes no third party requests of its own. The hook stays so a child
 * theme has one obvious place to declare its own origins, and so the default
 * s.w.org preconnect can be dropped when the emoji script is gone.
 *
 * @param string[] $urls          Resource URLs.
 * @param string   $relation_type Relation type.
 * @return string[]
 */
function basalt_resource_hints( $urls, $relation_type ) {
	if ( 'dns-prefetch' !== $relation_type ) {
		return $urls;
	}

	return array_values(
		array_filter(
			(array) $urls,
			static function ( $url ) {
				$host = is_array( $url ) ? ( $url['href'] ?? '' ) : $url;

				return false === strpos( (string) $host, 's.w.org' );
			}
		)
	);
}
add_filter( 'wp_resource_hints', 'basalt_resource_hints', 10, 2 );

/**
 * Ask the browser to keep the previous page painted during navigation.
 *
 * A same origin view transition removes the white flash between pages. It is
 * opt-in per browser and degrades to a normal navigation everywhere else.
 *
 * @return void
 */
function basalt_view_transition_meta(): void {
	/**
	 * Filter whether cross document view transitions are enabled.
	 *
	 * @param bool $enabled Whether to opt in.
	 */
	if ( ! apply_filters( 'basalt_enable_view_transitions', true ) ) {
		return;
	}

	echo '<meta name="view-transition" content="same-origin">' . "\n";
}
add_action( 'wp_head', 'basalt_view_transition_meta', 1 );
