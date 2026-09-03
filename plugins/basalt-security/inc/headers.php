<?php
/**
 * Response headers.
 *
 * Four headers that cost nothing and close a class of attack, plus a content
 * policy narrow enough to be safe on a site nobody has audited.
 *
 * What is deliberately missing from that policy: script-src. A strict script
 * policy needs a nonce on every inline script, the nonce has to be different
 * on every response, and the moment a page is cached at the edge the nonce in
 * the HTML and the one in the header stop matching, which breaks the site in
 * a way that is very hard to see. object-src, base-uri and form-action need
 * no nonce and still remove the three tricks an injected tag usually relies
 * on.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send them.
 *
 * @return void
 */
function basalt_security_headers(): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'headers' ) || headers_sent() ) {
		return;
	}

	// Stop the browser guessing that an upload is really a script.
	header( 'X-Content-Type-Options: nosniff' );

	// Send the page address to other origins, but never the query string.
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );

	// Nothing here needs a camera.
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );

	$frame = (string) basalt_security_get( 'frame_options' );

	if ( in_array( $frame, array( 'SAMEORIGIN', 'DENY' ), true ) ) {
		header( 'X-Frame-Options: ' . $frame );
	}

	if ( basalt_security_get( 'content_policy' ) ) {
		/**
		 * Filter the content security policy.
		 *
		 * @param string $policy The policy sent with every response.
		 */
		$policy = (string) apply_filters(
			'basalt_security_content_policy',
			"object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors " . ( 'DENY' === $frame ? "'none'" : "'self'" )
		);

		if ( '' !== $policy ) {
			header( 'Content-Security-Policy: ' . $policy );
		}
	}

	/*
	 * HSTS is the one header that cannot be taken back: a browser that has
	 * seen it refuses plain HTTP for the whole max-age, whatever the site
	 * says later. Off by default, and only ever sent over HTTPS.
	 */
	if ( basalt_security_get( 'hsts' ) && is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=15552000' );
	}
}
add_action( 'send_headers', 'basalt_security_headers' );

/**
 * The admin and the login screen get them too.
 *
 * send_headers only fires on the front end, and the login form is exactly the
 * page where clickjacking protection matters.
 *
 * @return void
 */
function basalt_security_headers_admin(): void {
	if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) ) {
		basalt_security_headers();
	}
}
add_action( 'admin_init', 'basalt_security_headers_admin' );
add_action( 'login_init', 'basalt_security_headers' );

/**
 * Drop the pingback header.
 *
 * @param array<string, string> $headers The headers WordPress is about to send.
 * @return array<string, string>
 */
function basalt_security_remove_pingback_header( $headers ) {
	if ( basalt_security_get( 'disable_xmlrpc' ) ) {
		unset( $headers['X-Pingback'] );
	}

	return $headers;
}
add_filter( 'wp_headers', 'basalt_security_remove_pingback_header' );
