<?php
/**
 * Plugin Name: Basalt Security
 * Description: The security a small site actually needs, and nothing that slows it down: a login page at an address only you know, brute force lockout, a small request filter, security headers, and the hardening WordPress leaves switched off. No database tables, no cron, no external service, no dashboard widgets.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Riccardo D'Arcangelo
 * Author URI: https://darcdesign.de/
 * License: GPL-2.0-or-later
 * Text Domain: basalt-security
 *
 * What this is, and what it is not
 * --------------------------------
 * Security plugins get big because every feature sounds reasonable in
 * isolation: a malware scanner, a file integrity check, a country blocker, a
 * dashboard of graphs. Each one costs a query on every request or a cron job
 * that runs while a visitor waits, and most of them protect against something
 * a shared host already handles. This plugin does the five things that stop
 * the attacks a small WordPress site actually sees:
 *
 * 1. The login page moves to an address the bots do not have, and the old one
 *    stops existing. That alone ends most of the traffic.
 * 2. What still gets through is locked out after a few failed attempts, with
 *    the lockout getting longer each time.
 * 3. A short list of patterns blocks the obvious probes before WordPress has
 *    to think about them.
 * 4. Response headers that turn off a class of browser side attacks.
 * 5. The hardening switches WordPress ships but leaves off: no file editor,
 *    no XML-RPC, no user enumeration, no version number in the markup.
 *
 * What it deliberately does not do: scan files, count visitors, phone home,
 * add two factor authentication (a dedicated plugin does that better), or
 * write to the database on a normal page view. On a request that is not a
 * login attempt and not an attack, this plugin costs one option read.
 *
 * Locked out?
 * -----------
 * Put this in wp-config.php and the login page is back at its usual address:
 *
 *     define( 'BASALT_SECURITY_LOGIN_SLUG', '' );
 *
 * And this switches the whole plugin off without touching the database:
 *
 *     define( 'BASALT_SECURITY_OFF', true );
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

define( 'BASALT_SECURITY_VERSION', '1.0.0' );
define( 'BASALT_SECURITY_DIR', plugin_dir_path( __FILE__ ) );

/** Where the settings live. */
const BASALT_SECURITY_OPTION = 'basalt_security_settings';

/**
 * Defaults.
 *
 * Everything that cannot lock anybody out is on. The login address is empty,
 * because a slug somebody has not chosen is a slug they cannot remember.
 *
 * @return array<string, mixed>
 */
function basalt_security_defaults(): array {
	return array(
		// Login.
		'login_slug'         => '',
		'block_wp_admin'     => true,

		// Brute force.
		'brute_force'        => true,
		'brute_limit'        => 5,
		'brute_minutes'      => 15,
		'brute_notify'       => false,

		// Requests.
		'firewall'           => true,
		'allow_ips'          => '',

		// Headers.
		'headers'            => true,
		'frame_options'      => 'SAMEORIGIN',
		'content_policy'     => true,
		'hsts'               => false,

		// Hardening.
		'disable_xmlrpc'     => true,
		'disable_enumeration' => true,
		'disable_file_edit'  => true,
		'hide_version'       => true,
		'limit_app_passwords' => true,
	);
}

/**
 * Read one setting.
 *
 * Static, so a request reads the option once however many modules ask.
 *
 * @param string $key Setting name.
 * @return mixed
 */
function basalt_security_get( string $key ) {
	static $settings = null;

	if ( null === $settings ) {
		$settings = wp_parse_args( (array) get_option( BASALT_SECURITY_OPTION, array() ), basalt_security_defaults() );
	}

	return $settings[ $key ] ?? null;
}

/**
 * Whether the plugin should do anything at all.
 *
 * @return bool
 */
function basalt_security_enabled(): bool {
	return ! ( defined( 'BASALT_SECURITY_OFF' ) && BASALT_SECURITY_OFF );
}

/**
 * The address of whoever is asking.
 *
 * REMOTE_ADDR and nothing else by default. A forwarded header is a header:
 * anybody can send one, and trusting it hands an attacker a way to look like
 * a different address on every request. A site behind a proxy sets the real
 * address in its server configuration, or filters this.
 *
 * @return string
 */
function basalt_security_ip(): string {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );

	/**
	 * Filter the client address.
	 *
	 * @param string $ip The address from REMOTE_ADDR.
	 */
	$ip = (string) apply_filters( 'basalt_security_client_ip', $ip );

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Whether this address is on the allow list.
 *
 * @return bool
 */
function basalt_security_allowed(): bool {
	$ip = basalt_security_ip();

	if ( '' === $ip ) {
		return false;
	}

	static $list = null;

	if ( null === $list ) {
		$lines = preg_split( '/\R/', (string) basalt_security_get( 'allow_ips' ) ) ?: array();
		$list  = array_values( array_filter( array_map( 'trim', $lines ) ) );
	}

	return in_array( $ip, $list, true );
}

/**
 * The requested path, relative to the WordPress root and without slashes.
 *
 * @return string
 */
function basalt_security_path(): string {
	static $path = null;

	if ( null !== $path ) {
		return $path;
	}

	$uri  = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );

	if ( '' !== $home && '/' !== $home && str_starts_with( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}

	$path = trim( $path, '/' );

	return $path;
}

/**
 * Answer with a 404 and stop.
 *
 * Not a 403: a 403 confirms that something is there. For a login page that
 * has moved, "there is nothing here" is the honest answer to give a scanner.
 *
 * @return void
 */
function basalt_security_not_found(): void {
	status_header( 404 );
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "404\n";
	exit;
}

foreach ( array( 'log', 'login-path', 'brute-force', 'firewall', 'headers', 'hardening', 'settings' ) as $basalt_security_module ) {
	require_once BASALT_SECURITY_DIR . 'inc/' . $basalt_security_module . '.php';
}

unset( $basalt_security_module );

/**
 * Load translations.
 *
 * @return void
 */
function basalt_security_load_textdomain(): void {
	load_plugin_textdomain( 'basalt-security', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'basalt_security_load_textdomain' );

/**
 * Remember the login address in an option of its own on activation, so the
 * settings screen can warn if the two ever disagree after a manual change.
 *
 * @return void
 */
register_activation_hook(
	__FILE__,
	static function (): void {
		add_option( BASALT_SECURITY_OPTION, basalt_security_defaults() );
		flush_rewrite_rules();
	}
);
