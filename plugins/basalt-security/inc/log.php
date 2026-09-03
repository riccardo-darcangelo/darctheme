<?php
/**
 * The log.
 *
 * One option, the last hundred events, written only when something actually
 * happens: a lockout, a blocked request, a login. A normal page view never
 * touches it, which is the difference between a log and a performance
 * problem.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/** Where the log lives. */
const BASALT_SECURITY_LOG_OPTION = 'basalt_security_log';

/** How many events to keep. */
const BASALT_SECURITY_LOG_MAX = 100;

/**
 * Write one event.
 *
 * @param string $type  Short machine readable type, for example "lockout".
 * @param string $note  What happened, one line.
 * @return void
 */
function basalt_security_log( string $type, string $note = '' ): void {
	$log = (array) get_option( BASALT_SECURITY_LOG_OPTION, array() );

	$log[] = array(
		'time' => time(),
		'type' => $type,
		'ip'   => basalt_security_ip(),
		'note' => mb_substr( $note, 0, 200 ),
		'path' => mb_substr( basalt_security_path(), 0, 120 ),
	);

	if ( count( $log ) > BASALT_SECURITY_LOG_MAX ) {
		$log = array_slice( $log, -BASALT_SECURITY_LOG_MAX );
	}

	// Not autoloaded: nothing on the front end ever reads this.
	update_option( BASALT_SECURITY_LOG_OPTION, $log, false );
}

/**
 * The log, newest first.
 *
 * @return array<int, array{time: int, type: string, ip: string, note: string, path: string}>
 */
function basalt_security_log_read(): array {
	return array_reverse( (array) get_option( BASALT_SECURITY_LOG_OPTION, array() ) );
}

/**
 * Empty it.
 *
 * @return void
 */
function basalt_security_log_clear(): void {
	update_option( BASALT_SECURITY_LOG_OPTION, array(), false );
}
