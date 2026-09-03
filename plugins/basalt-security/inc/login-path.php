<?php
/**
 * The login page moves.
 *
 * Nearly all of the login traffic a small site gets is a bot posting to
 * /wp-login.php. Renaming the page does not make a password stronger, but it
 * takes the site out of the list the bots work through, and the requests that
 * remain are worth looking at.
 *
 * Nothing is rewritten in the database and no file is touched: the new
 * address loads wp-login.php directly, and every link WordPress builds to
 * wp-login.php is rewritten on the way out.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * The chosen login address, or an empty string when the feature is off.
 *
 * The constant wins, so a site that has locked itself out can put one line in
 * wp-config.php and get back in.
 *
 * @return string
 */
function basalt_security_login_slug(): string {
	if ( defined( 'BASALT_SECURITY_LOGIN_SLUG' ) ) {
		return sanitize_title( (string) BASALT_SECURITY_LOGIN_SLUG );
	}

	return sanitize_title( (string) basalt_security_get( 'login_slug' ) );
}

/**
 * Paths under wp-admin that stay reachable for logged out visitors.
 *
 * Both are endpoints plugins and themes use for anonymous requests. Blocking
 * them breaks forms and payment callbacks, and neither one leaks anything.
 *
 * @return string[]
 */
function basalt_security_admin_allowlist(): array {
	/**
	 * Filter the wp-admin files that stay open.
	 *
	 * @param string[] $files File names below wp-admin.
	 */
	return (array) apply_filters(
		'basalt_security_admin_allowlist',
		array( 'admin-ajax.php', 'admin-post.php' )
	);
}

/**
 * Serve the login page at the new address, and nothing at the old one.
 *
 * @return void
 */
function basalt_security_login_route(): void {
	if ( ! basalt_security_enabled() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$slug = basalt_security_login_slug();

	if ( '' === $slug ) {
		return;
	}

	$path = basalt_security_path();

	if ( $path === $slug ) {
		/*
		 * Only remembered here, not served here. wp-login.php needs constants
		 * that wp-settings.php defines after this hook has run: requiring it
		 * now dies on AUTOSAVE_INTERVAL. So the request is marked, the URL is
		 * emptied so nothing else tries to resolve it, and the page is loaded
		 * on wp_loaded, which is late enough and still before the main query.
		 *
		 * SCRIPT_NAME is what wp-login.php reads to build its own form action;
		 * the site_url filter below turns that back into the new address.
		 */
		global $pagenow;

		$GLOBALS['basalt_security_serve_login'] = true;

		$pagenow                = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- that is the point.
		$_SERVER['SCRIPT_NAME'] = '/wp-login.php';
		$_SERVER['REQUEST_URI'] = '/' . str_repeat( '-/', 10 );

		return;
	}

	if ( 'wp-login.php' === $path ) {
		basalt_security_log( 'login-path', 'Request to the old login address' );
		basalt_security_not_found();
	}

	if ( ! basalt_security_get( 'block_wp_admin' ) || is_user_logged_in() ) {
		return;
	}

	if ( str_starts_with( $path, 'wp-admin' ) && ! in_array( basename( $path ), basalt_security_admin_allowlist(), true ) ) {
		basalt_security_not_found();
	}
}
add_action( 'plugins_loaded', 'basalt_security_login_route', 1 );

/**
 * Load the login page, once WordPress is far enough along to survive it.
 *
 * @return void
 */
function basalt_security_serve_login(): void {
	if ( empty( $GLOBALS['basalt_security_serve_login'] ) ) {
		return;
	}

	/*
	 * wp-login.php is written to run at the top level of a request, so its
	 * variables are globals. Included from inside a function they would be
	 * locals, and the core functions it calls, which declare `global $error`
	 * and friends, would read empty ones. Declaring them here gives the file
	 * the scope it expects.
	 */
	global $action, $error, $errors, $interim_login, $user, $user_login, $redirect_to,
		$requested_redirect_to, $rememberme, $reauth, $http_post, $customize_login,
		$login_link_separator, $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;

	require_once ABSPATH . 'wp-login.php';
	exit;
}
add_action( 'wp_loaded', 'basalt_security_serve_login' );

/**
 * Rewrite every URL WordPress builds to wp-login.php.
 *
 * site_url() is where login, logout, lost password and registration links all
 * come from, so one filter covers them; wp_redirect catches the redirects
 * wp-login.php makes to itself after a failed attempt.
 *
 * @param string $url The URL.
 * @return string
 */
function basalt_security_rewrite_login_url( $url ) {
	$slug = basalt_security_login_slug();

	if ( '' === $slug || ! is_string( $url ) || ! str_contains( $url, 'wp-login.php' ) ) {
		return $url;
	}

	return str_replace( 'wp-login.php', $slug, $url );
}
add_filter( 'site_url', 'basalt_security_rewrite_login_url' );
add_filter( 'network_site_url', 'basalt_security_rewrite_login_url' );
add_filter( 'wp_redirect', 'basalt_security_rewrite_login_url' );
add_filter( 'login_url', 'basalt_security_rewrite_login_url' );
add_filter( 'logout_url', 'basalt_security_rewrite_login_url' );
add_filter( 'lostpassword_url', 'basalt_security_rewrite_login_url' );
add_filter( 'register_url', 'basalt_security_rewrite_login_url' );

/**
 * The same for the address in emails, where a bare wp-login.php link would
 * otherwise send somebody to a 404.
 *
 * @param string $message The email body.
 * @return string
 */
function basalt_security_rewrite_login_email( $message ) {
	return is_string( $message ) ? basalt_security_rewrite_login_url( $message ) : $message;
}
add_filter( 'retrieve_password_message', 'basalt_security_rewrite_login_email' );
add_filter( 'wp_new_user_notification_email', 'basalt_security_rewrite_login_email_array' );
add_filter( 'wp_password_change_notification_email', 'basalt_security_rewrite_login_email_array' );

/**
 * Those two filters pass an array with the message inside it.
 *
 * @param array<string, mixed> $email The email parts.
 * @return array<string, mixed>
 */
function basalt_security_rewrite_login_email_array( $email ) {
	if ( is_array( $email ) && isset( $email['message'] ) ) {
		$email['message'] = basalt_security_rewrite_login_url( (string) $email['message'] );
	}

	return $email;
}
