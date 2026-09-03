<?php
/**
 * The switches WordPress ships and leaves off.
 *
 * Every one of these is a decision core cannot make for everybody: XML-RPC is
 * still how some apps post, user names in the REST API are how some themes
 * build author pages, and the file editor is convenient right up to the
 * moment somebody gets a password. On a site that does none of that, they are
 * free surface to remove.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * XML-RPC off.
 *
 * The endpoint answers before authentication and offers system.multicall,
 * which lets one request try hundreds of passwords. Almost nothing uses it
 * any more; the block editor and the app talk REST.
 */
if ( basalt_security_enabled() && basalt_security_get( 'disable_xmlrpc' ) ) {
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', '__return_empty_array' );
	add_filter( 'pings_open', '__return_false', 20 );
}

/**
 * No file editor in the admin.
 *
 * Defined rather than filtered, because that is the constant core checks, and
 * defining it here means a site does not have to edit wp-config.php.
 */
if ( basalt_security_enabled() && basalt_security_get( 'disable_file_edit' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Take the version number out of the markup.
 *
 * Not a defence on its own: the version is guessable from the assets. It
 * simply keeps the site out of the lists that scanners build from generator
 * tags.
 *
 * @return void
 */
function basalt_security_hide_version(): void {
	if ( ! basalt_security_get( 'hide_version' ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );

	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'basalt_security_hide_version' );

/**
 * Stop the site listing its user names.
 *
 * Three doors lead to the same place: /?author=1 redirects to the author
 * archive and shows the login name in the URL, the REST users endpoint lists
 * everybody, and the sitemap has a users section.
 *
 * @return void
 */
function basalt_security_block_author_scan(): void {
	if ( ! basalt_security_get( 'disable_enumeration' ) || is_admin() || is_user_logged_in() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading a public query parameter.
	if ( isset( $_GET['author'] ) && ! is_admin() ) {
		basalt_security_not_found();
	}
}
add_action( 'template_redirect', 'basalt_security_block_author_scan', 1 );

/**
 * Hide the REST users endpoint from visitors who are not signed in.
 *
 * The block editor needs it, and an editor is signed in, so nothing in the
 * admin notices.
 *
 * @param array<string, mixed> $endpoints The REST endpoints.
 * @return array<string, mixed>
 */
function basalt_security_hide_rest_users( $endpoints ) {
	if ( ! basalt_security_get( 'disable_enumeration' ) || is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}
add_filter( 'rest_endpoints', 'basalt_security_hide_rest_users' );

/**
 * Drop the users section from the sitemap.
 *
 * @param WP_Sitemaps_Provider $provider The provider.
 * @param string               $name     Its name.
 * @return WP_Sitemaps_Provider|false
 */
function basalt_security_sitemap_users( $provider, $name ) {
	if ( basalt_security_get( 'disable_enumeration' ) && 'users' === $name ) {
		return false;
	}

	return $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'basalt_security_sitemap_users', 10, 2 );

/**
 * Application passwords only for people who could do the damage anyway.
 *
 * They are a real feature, but on a shop every customer account is a possible
 * long lived token, and no customer needs one.
 *
 * @param bool    $available Whether they are offered.
 * @param WP_User $user      The user.
 * @return bool
 */
function basalt_security_limit_app_passwords( $available, $user = null ) {
	if ( ! basalt_security_get( 'limit_app_passwords' ) ) {
		return $available;
	}

	return $user instanceof WP_User && user_can( $user, 'manage_options' );
}
add_filter( 'wp_is_application_passwords_available_for_user', 'basalt_security_limit_app_passwords', 10, 2 );

/**
 * A note in the log when somebody signs in, so the list shows who was there
 * and not only who was turned away.
 *
 * @param string       $login The user name.
 * @param WP_User|null $user  The user.
 * @return void
 */
function basalt_security_log_login( $login, $user = null ): void {
	basalt_security_log(
		'login',
		sprintf(
			/* translators: 1: user name, 2: role. */
			__( 'Signed in as "%1$s" (%2$s)', 'basalt-security' ),
			(string) $login,
			$user instanceof WP_User ? implode( ', ', (array) $user->roles ) : '?'
		)
	);
}
add_action( 'wp_login', 'basalt_security_log_login', 10, 2 );
