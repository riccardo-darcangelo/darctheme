<?php
/**
 * Brute force lockout.
 *
 * Count the failures per address, stop answering after a few, and make the
 * pause longer every time the same address comes back. Counters live in
 * transients, so they expire on their own and leave nothing behind.
 *
 * Two decisions worth knowing about.
 *
 * The lockout is per address, not per account. Locking an account is what an
 * attacker wants: three wrong passwords and the owner is out. An address that
 * guesses is the thing worth stopping.
 *
 * There is no artificial delay before the answer. A sleep() holds a PHP
 * worker, so a handful of attackers can occupy every worker the host has and
 * take the site down with the very feature meant to protect it.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Transient key for the failure counter.
 *
 * @param string $ip The address.
 * @return string
 */
function basalt_security_fail_key( string $ip ): string {
	return 'bs_fail_' . md5( $ip );
}

/**
 * Transient key for the lockout.
 *
 * @param string $ip The address.
 * @return string
 */
function basalt_security_lock_key( string $ip ): string {
	return 'bs_lock_' . md5( $ip );
}

/**
 * Transient key for how often this address has been locked out already.
 *
 * @param string $ip The address.
 * @return string
 */
function basalt_security_strike_key( string $ip ): string {
	return 'bs_strike_' . md5( $ip );
}

/**
 * Seconds left on the lockout, or zero.
 *
 * @return int
 */
function basalt_security_locked_for(): int {
	if ( ! basalt_security_get( 'brute_force' ) || basalt_security_allowed() ) {
		return 0;
	}

	$ip = basalt_security_ip();

	if ( '' === $ip ) {
		return 0;
	}

	$until = (int) get_transient( basalt_security_lock_key( $ip ) );

	return $until > time() ? $until - time() : 0;
}

/**
 * Refuse the attempt while the address is locked out.
 *
 * On the authenticate filter, so it covers the login form, XML-RPC and
 * anything else that authenticates through WordPress.
 *
 * @param null|WP_User|WP_Error $user     The user so far.
 * @param string                $username The submitted name.
 * @return null|WP_User|WP_Error
 */
function basalt_security_block_locked( $user, $username = '' ) {
	if ( '' === (string) $username ) {
		return $user;
	}

	$left = basalt_security_locked_for();

	if ( ! $left ) {
		return $user;
	}

	/*
	 * Remembered for two filters below: the message has to survive a generic
	 * error replacement, and this refusal must not count as another failure.
	 */
	$GLOBALS['basalt_security_locked_out'] = true;

	return new WP_Error(
		'basalt_security_locked',
		sprintf(
			/* translators: %s: human readable time, for example "15 minutes". */
			esc_html__( 'Too many failed attempts from this address. Please try again in %s.', 'basalt-security' ),
			human_time_diff( time(), time() + $left )
		)
	);
}
add_filter( 'authenticate', 'basalt_security_block_locked', 30, 2 );

/**
 * Count a failure, and lock out once there are too many.
 *
 * @param string $username The name that was tried.
 * @return void
 */
function basalt_security_count_failure( $username, $error = null ): void {
	if ( ! basalt_security_get( 'brute_force' ) || basalt_security_allowed() ) {
		return;
	}

	/*
	 * The attempt we refused ourselves is not a new guess. Counting it would
	 * extend the lockout every time somebody reloads the login page, and the
	 * doubling would run away.
	 */
	if ( $error instanceof WP_Error && in_array( 'basalt_security_locked', (array) $error->get_error_codes(), true ) ) {
		return;
	}

	$ip = basalt_security_ip();

	if ( '' === $ip ) {
		return;
	}

	$limit   = max( 2, (int) basalt_security_get( 'brute_limit' ) );
	$minutes = max( 1, (int) basalt_security_get( 'brute_minutes' ) );
	$window  = max( HOUR_IN_SECONDS, $minutes * MINUTE_IN_SECONDS * 2 );
	$fails   = (int) get_transient( basalt_security_fail_key( $ip ) ) + 1;

	if ( $fails < $limit ) {
		set_transient( basalt_security_fail_key( $ip ), $fails, $window );
		return;
	}

	/*
	 * Each lockout doubles the next one, up to a day. An address that comes
	 * back a fifth time is not a customer who forgot a password.
	 */
	$strikes  = (int) get_transient( basalt_security_strike_key( $ip ) ) + 1;
	$duration = min( DAY_IN_SECONDS, $minutes * MINUTE_IN_SECONDS * ( 2 ** ( $strikes - 1 ) ) );

	delete_transient( basalt_security_fail_key( $ip ) );
	set_transient( basalt_security_strike_key( $ip ), $strikes, DAY_IN_SECONDS );
	set_transient( basalt_security_lock_key( $ip ), time() + $duration, $duration );

	basalt_security_log(
		'lockout',
		sprintf(
			/* translators: 1: number of minutes, 2: the user name that was tried. */
			__( 'Locked out for %1$d minutes after failed logins as "%2$s"', 'basalt-security' ),
			(int) round( $duration / MINUTE_IN_SECONDS ),
			mb_substr( (string) $username, 0, 60 )
		)
	);

	if ( basalt_security_get( 'brute_notify' ) ) {
		basalt_security_notify_lockout( (string) $username, $duration );
	}
}
add_action( 'wp_login_failed', 'basalt_security_count_failure', 10, 2 );

/**
 * Forget the failures once somebody gets in.
 *
 * @return void
 */
function basalt_security_clear_failures(): void {
	$ip = basalt_security_ip();

	if ( '' === $ip ) {
		return;
	}

	delete_transient( basalt_security_fail_key( $ip ) );
	delete_transient( basalt_security_lock_key( $ip ) );
}
add_action( 'wp_login', 'basalt_security_clear_failures' );

/**
 * Tell the administration address, at most once an hour.
 *
 * A mail per attempt would be the attacker choosing how much mail the site
 * sends, which is its own denial of service.
 *
 * @param string $username The name that was tried.
 * @param int    $duration Lockout length in seconds.
 * @return void
 */
function basalt_security_notify_lockout( string $username, int $duration ): void {
	if ( get_transient( 'bs_notified' ) ) {
		return;
	}

	set_transient( 'bs_notified', 1, HOUR_IN_SECONDS );

	$site = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );

	wp_mail(
		(string) get_option( 'admin_email' ),
		/* translators: %s: site name. */
		sprintf( __( '[%s] Login attempts blocked', 'basalt-security' ), $site ),
		sprintf(
			/* translators: 1: IP address, 2: user name, 3: minutes. */
			__( "The address %1\$s was locked out after repeated failed logins as \"%2\$s\".\n\nThe lockout lasts %3\$d minutes and gets longer if it happens again. Further lockouts in the next hour are not mailed.\n", 'basalt-security' ),
			basalt_security_ip(),
			$username,
			(int) round( $duration / MINUTE_IN_SECONDS )
		)
	);
}

/**
 * Say nothing useful on the login screen about why an attempt failed.
 *
 * Only when Basalt Core is not already doing it, so the two do not fight over
 * the same message.
 *
 * @param WP_Error $error The login error.
 * @return WP_Error
 */
function basalt_security_generic_login_error( $error ) {
	if ( function_exists( 'basalt_core_login_generic_error' ) ) {
		return $error;
	}

	$codes = (array) $error->get_error_codes();

	foreach ( array( 'invalid_username', 'invalid_email', 'incorrect_password', 'empty_password' ) as $code ) {
		if ( in_array( $code, $codes, true ) ) {
			return new WP_Error( 'invalid_login', __( '<strong>Error:</strong> Those details are not correct.', 'basalt-security' ) );
		}
	}

	return $error;
}
add_filter( 'wp_login_errors', 'basalt_security_generic_login_error' );

/**
 * Keep the lockout message when Basalt Core is making messages generic.
 *
 * @param bool $keep Whether the message stands.
 * @return bool
 */
function basalt_security_keep_lockout_message( $keep ) {
	return ! empty( $GLOBALS['basalt_security_locked_out'] ) ? true : $keep;
}
add_filter( 'basalt_core_login_keep_error', 'basalt_security_keep_lockout_message' );
add_filter( 'shake_error_codes', static fn( $codes ) => array_merge( (array) $codes, array( 'invalid_login', 'basalt_security_locked' ) ) );
