<?php
/**
 * A short list of patterns, checked early.
 *
 * This is not a web application firewall and does not pretend to be one. It
 * is the doormat: the probes that arrive by the hundred looking for a
 * forgotten .env file, an old phpunit runner or a query string with a union
 * select in it. Blocking those costs one regular expression and saves
 * WordPress from booting a full page for a scanner.
 *
 * Three rules keep it from becoming a support problem:
 *
 * - The URL is always checked. The request body is only checked for visitors
 *   who are not logged in, because an editor pasting a code sample into a
 *   post is not an attack, and a filter that eats their work is worse than
 *   the attack it prevents.
 * - Anyone who may edit posts passes untouched.
 * - Every pattern has to be specific enough that a real sentence cannot match
 *   it. "select" is a word; "union select" is not.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paths that only a scanner asks for.
 *
 * @return string[]
 */
function basalt_security_probe_paths(): array {
	/**
	 * Filter the paths answered with a 404 straight away.
	 *
	 * @param string[] $paths Paths relative to the site root, lower case.
	 */
	return (array) apply_filters(
		'basalt_security_probe_paths',
		array(
			'.env',
			'.git/config',
			'.git/head',
			'wp-config.php.bak',
			'wp-config.php.save',
			'wp-config.php.old',
			'wp-config.txt',
			'wp-config-sample.php.bak',
			'vendor/phpunit/phpunit/src/util/php/eval-stdin.php',
			'wp-content/debug.log',
			'phpinfo.php',
			'info.php',
			'shell.php',
			'wso.php',
			'adminer.php',
		)
	);
}

/**
 * The patterns. One expression, so the check is a single pass.
 *
 * @return string
 */
function basalt_security_pattern(): string {
	static $pattern = null;

	if ( null !== $pattern ) {
		return $pattern;
	}

	$parts = array(
		// Traversal and null bytes.
		'\.\./\.\./',
		'%2e%2e%2f',
		'%00',
		// SQL injection, in the shapes that appear in real probes.
		'union[\s+]+(all[\s+]+)?select',
		'information_schema',
		'\bsleep\s*\(\s*\d',
		'benchmark\s*\(\s*\d+\s*,',
		'concat\s*\(\s*0x',
		'\bor\s+1\s*=\s*1\b',
		// Code execution.
		'base64_decode\s*\(',
		'\beval\s*\(\s*(base64|gzinflate|str_rot13)',
		'(shell_exec|passthru|proc_open|popen)\s*\(',
		'\$\{jndi:',
		// Cross site scripting.
		'<script\b',
		'javascript:\s*[a-z(]',
		'\son(error|load|click|mouseover)\s*=',
		'document\.cookie',
		// PHP wrappers.
		'php://(input|filter)',
		'data:text/html',
	);

	$pattern = '#(' . implode( '|', $parts ) . ')#i';

	return $pattern;
}

/**
 * Whether one string trips a pattern.
 *
 * Values are cut at four kilobytes: an attack that needs more than that to
 * express itself is not one of the patterns above, and scanning a whole post
 * body would be the expensive part.
 *
 * @param mixed $value The value to check.
 * @return bool
 */
function basalt_security_suspicious( $value ): bool {
	if ( is_array( $value ) ) {
		foreach ( $value as $item ) {
			if ( basalt_security_suspicious( $item ) ) {
				return true;
			}
		}

		return false;
	}

	if ( ! is_scalar( $value ) ) {
		return false;
	}

	$string = mb_substr( (string) $value, 0, 4096 );

	if ( '' === $string ) {
		return false;
	}

	return (bool) preg_match( basalt_security_pattern(), rawurldecode( $string ) );
}

/**
 * Look at the request, once, before WordPress does anything expensive.
 *
 * @return void
 */
function basalt_security_firewall(): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'firewall' ) ) {
		return;
	}

	if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || basalt_security_allowed() ) {
		return;
	}

	$path = strtolower( basalt_security_path() );

	/*
	 * With XML-RPC switched off, core still answers /xmlrpc.php with a fault
	 * and a 200, which keeps the endpoint on every scanner's list. Nothing
	 * there is worth answering.
	 */
	if ( 'xmlrpc.php' === $path && basalt_security_get( 'disable_xmlrpc' ) ) {
		basalt_security_log( 'blocked:xmlrpc', 'Request to xmlrpc.php while it is switched off' );
		basalt_security_not_found();
	}

	foreach ( basalt_security_probe_paths() as $probe ) {
		if ( $path === $probe || str_ends_with( $path, '/' . $probe ) ) {
			basalt_security_block( 'probe', $probe );
		}
	}

	// A URL this long is not a URL somebody typed.
	if ( strlen( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ) > 2000 ) {
		basalt_security_block( 'long-url', 'Request URI over 2000 characters' );
	}

	if ( basalt_security_suspicious( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ) ) {
		basalt_security_block( 'pattern', 'In the URL' );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading the raw request to decide whether to answer it at all.
	if ( ! empty( $_GET ) && basalt_security_suspicious( wp_unslash( $_GET ) ) ) {
		basalt_security_block( 'pattern', 'In the query string' );
	}

	/*
	 * The body is only inspected for visitors who are not signed in. Everyone
	 * else may legitimately send markup: a post, a pattern, a snippet in a
	 * support form.
	 */
	if ( is_user_logged_in() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- same reason.
	if ( ! empty( $_POST ) && basalt_security_suspicious( wp_unslash( $_POST ) ) ) {
		basalt_security_block( 'pattern', 'In the submitted form' );
	}
}
add_action( 'plugins_loaded', 'basalt_security_firewall', 2 );

/**
 * Refuse the request.
 *
 * @param string $type Why.
 * @param string $note What matched.
 * @return void
 */
function basalt_security_block( string $type, string $note ): void {
	basalt_security_log( 'blocked:' . $type, $note );

	status_header( 403 );
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "403\n";
	exit;
}
