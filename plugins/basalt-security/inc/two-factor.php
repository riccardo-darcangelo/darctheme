<?php
/**
 * A second factor, from an authenticator app.
 *
 * A password is one secret, and every year makes it a worse one: it is
 * reused, it is phished, it turns up in a breach of a site that had nothing
 * to do with this one. The second factor is the answer that actually works,
 * and for a small site it costs one app on a phone.
 *
 * Time based codes (TOTP, RFC 6238) rather than codes by mail or SMS. Mail is
 * the same inbox that can already reset the password, so it is not a second
 * factor at all; SMS needs a paid service and loses to a SIM swap. An
 * authenticator app needs nothing from the server and works offline.
 *
 * The whole algorithm is thirty lines of HMAC and arithmetic, which is why it
 * is here rather than in a dependency: a library for this is a supply chain
 * for this.
 *
 * How the login works:
 *
 * 1. Password is checked by WordPress as usual. On success wp_login fires.
 * 2. If the account has a second factor, the auth cookies are cleared again
 *    at once, a short lived handle goes into a cookie, and the browser is
 *    sent to the code screen. Nothing is signed in yet.
 * 3. The code is checked; only then are the cookies set for real.
 *
 * The handle lives in a cookie rather than in the URL, because a URL ends up
 * in access logs, in browser history and in the referer of the next request.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/** Where the encrypted secret is kept. */
const BASALT_SECURITY_2FA_SECRET = '_basalt_2fa_secret';

/** Where the hashed recovery codes are kept. */
const BASALT_SECURITY_2FA_CODES = '_basalt_2fa_codes';

/** The last time step that was accepted, so a code cannot be replayed. */
const BASALT_SECURITY_2FA_USED = '_basalt_2fa_used';

/** The cookie carrying the half finished login. */
const BASALT_SECURITY_2FA_COOKIE = 'basalt_2fa';

/** How long somebody has to type the code. */
const BASALT_SECURITY_2FA_TTL = 5 * MINUTE_IN_SECONDS;

/** The action on the login page. */
const BASALT_SECURITY_2FA_ACTION = 'basalt_2fa';

/* -------------------------------------------------------------------------
 * The algorithm
 * ---------------------------------------------------------------------- */

/**
 * Base32, because that is what authenticator apps read.
 *
 * @param string $binary Raw bytes.
 * @return string
 */
function basalt_security_base32_encode( string $binary ): string {
	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$bits     = '';

	foreach ( str_split( $binary ) as $char ) {
		$bits .= str_pad( decbin( ord( $char ) ), 8, '0', STR_PAD_LEFT );
	}

	$out = '';

	foreach ( str_split( $bits, 5 ) as $chunk ) {
		$out .= $alphabet[ bindec( str_pad( $chunk, 5, '0', STR_PAD_RIGHT ) ) ];
	}

	return $out;
}

/**
 * And back again.
 *
 * @param string $secret Base32 text.
 * @return string Raw bytes.
 */
function basalt_security_base32_decode( string $secret ): string {
	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$secret   = strtoupper( preg_replace( '/[^A-Za-z2-7]/', '', $secret ) ?? '' );
	$bits     = '';

	foreach ( str_split( $secret ) as $char ) {
		$index = strpos( $alphabet, $char );

		if ( false === $index ) {
			continue;
		}

		$bits .= str_pad( decbin( $index ), 5, '0', STR_PAD_LEFT );
	}

	$out = '';

	foreach ( str_split( $bits, 8 ) as $chunk ) {
		if ( 8 === strlen( $chunk ) ) {
			$out .= chr( bindec( $chunk ) );
		}
	}

	return $out;
}

/**
 * The six digit code for one moment in time.
 *
 * @param string $secret Base32 secret.
 * @param int    $step   The time step, or 0 for now.
 * @return string
 */
function basalt_security_totp( string $secret, int $step = 0 ): string {
	$step   = $step ?: (int) floor( time() / 30 );
	$binary = basalt_security_base32_decode( $secret );
	$packed = pack( 'N*', 0 ) . pack( 'N*', $step );
	$hash   = hash_hmac( 'sha1', $packed, $binary, true );
	$offset = ord( $hash[19] ) & 0x0F;

	$number = (
		( ( ord( $hash[ $offset ] ) & 0x7F ) << 24 ) |
		( ( ord( $hash[ $offset + 1 ] ) & 0xFF ) << 16 ) |
		( ( ord( $hash[ $offset + 2 ] ) & 0xFF ) << 8 ) |
		( ord( $hash[ $offset + 3 ] ) & 0xFF )
	) % 1000000;

	return str_pad( (string) $number, 6, '0', STR_PAD_LEFT );
}

/**
 * Whether a code is right, allowing for a clock that is slightly off.
 *
 * One step either side, which is the usual tolerance: half a minute of drift
 * in each direction, and no more, because every extra step is another code an
 * attacker may guess.
 *
 * @param string $secret Base32 secret.
 * @param string $code   What was typed.
 * @return int The time step that matched, or 0.
 */
function basalt_security_totp_check( string $secret, string $code ): int {
	$code = preg_replace( '/\D/', '', $code ) ?? '';

	if ( 6 !== strlen( $code ) ) {
		return 0;
	}

	$now = (int) floor( time() / 30 );

	foreach ( array( 0, -1, 1 ) as $drift ) {
		$step = $now + $drift;

		if ( hash_equals( basalt_security_totp( $secret, $step ), $code ) ) {
			return $step;
		}
	}

	return 0;
}

/* -------------------------------------------------------------------------
 * The secret
 * ---------------------------------------------------------------------- */

/**
 * Whether the second factor can be offered at all.
 *
 * The secret is stored encrypted, and without OpenSSL there is nothing to
 * encrypt it with. Storing it in the clear would mean a database dump is a
 * pile of working second factors, which is worse than not offering one.
 *
 * @return bool
 */
function basalt_security_2fa_available(): bool {
	return function_exists( 'openssl_encrypt' ) && basalt_security_enabled() && (bool) basalt_security_get( 'two_factor' );
}

/**
 * The key the secret is encrypted with.
 *
 * From the salts in wp-config.php, so a stolen database on its own is not
 * enough: the attacker also needs the file.
 *
 * @return string
 */
function basalt_security_2fa_key(): string {
	return hash( 'sha256', wp_salt( 'secure_auth' ), true );
}

/**
 * Store a secret for an account.
 *
 * @param int    $user_id The account.
 * @param string $secret  Base32 secret.
 * @return void
 */
function basalt_security_2fa_store( int $user_id, string $secret ): void {
	$iv = random_bytes( 16 );

	$cipher = openssl_encrypt( $secret, 'aes-256-cbc', basalt_security_2fa_key(), OPENSSL_RAW_DATA, $iv );

	update_user_meta( $user_id, BASALT_SECURITY_2FA_SECRET, base64_encode( $iv . $cipher ) );
}

/**
 * Read it back.
 *
 * @param int $user_id The account.
 * @return string Base32 secret, or an empty string.
 */
function basalt_security_2fa_secret( int $user_id ): string {
	$stored = (string) get_user_meta( $user_id, BASALT_SECURITY_2FA_SECRET, true );

	if ( '' === $stored || ! function_exists( 'openssl_decrypt' ) ) {
		return '';
	}

	$raw = base64_decode( $stored, true );

	if ( false === $raw || strlen( $raw ) <= 16 ) {
		return '';
	}

	$plain = openssl_decrypt( substr( $raw, 16 ), 'aes-256-cbc', basalt_security_2fa_key(), OPENSSL_RAW_DATA, substr( $raw, 0, 16 ) );

	return is_string( $plain ) ? $plain : '';
}

/**
 * Whether this account has the second factor switched on.
 *
 * @param int $user_id The account.
 * @return bool
 */
function basalt_security_2fa_active( int $user_id ): bool {
	return basalt_security_2fa_available() && '' !== basalt_security_2fa_secret( $user_id );
}

/**
 * Switch it off and forget everything about it.
 *
 * @param int $user_id The account.
 * @return void
 */
function basalt_security_2fa_forget( int $user_id ): void {
	delete_user_meta( $user_id, BASALT_SECURITY_2FA_SECRET );
	delete_user_meta( $user_id, BASALT_SECURITY_2FA_CODES );
	delete_user_meta( $user_id, BASALT_SECURITY_2FA_USED );
}

/**
 * Fresh recovery codes, returned in the clear once and stored as hashes.
 *
 * @param int $user_id The account.
 * @return string[] The codes, to be shown exactly once.
 */
function basalt_security_2fa_recovery_codes( int $user_id ): array {
	$codes  = array();
	$hashes = array();

	for ( $i = 0; $i < 8; $i++ ) {
		$code     = strtolower( wp_generate_password( 5, false, false ) . '-' . wp_generate_password( 5, false, false ) );
		$codes[]  = $code;
		$hashes[] = wp_hash_password( $code );
	}

	update_user_meta( $user_id, BASALT_SECURITY_2FA_CODES, $hashes );

	return $codes;
}

/**
 * Spend a recovery code, if it is one.
 *
 * @param int    $user_id The account.
 * @param string $code    What was typed.
 * @return bool
 */
function basalt_security_2fa_use_recovery( int $user_id, string $code ): bool {
	$code   = strtolower( trim( $code ) );
	$hashes = (array) get_user_meta( $user_id, BASALT_SECURITY_2FA_CODES, true );

	foreach ( $hashes as $index => $hash ) {
		if ( wp_check_password( $code, (string) $hash ) ) {
			unset( $hashes[ $index ] );
			update_user_meta( $user_id, BASALT_SECURITY_2FA_CODES, array_values( $hashes ) );
			basalt_security_log( '2fa_recovery', 'Signed in with a recovery code' );

			return true;
		}
	}

	return false;
}

/**
 * Check a code against everything that could make it valid.
 *
 * @param int    $user_id The account.
 * @param string $code    What was typed.
 * @return bool
 */
function basalt_security_2fa_verify( int $user_id, string $code ): bool {
	$secret = basalt_security_2fa_secret( $user_id );

	if ( '' === $secret ) {
		return false;
	}

	$step = basalt_security_totp_check( $secret, $code );

	if ( $step ) {
		// A code is good for thirty seconds and for one login, not both.
		if ( (int) get_user_meta( $user_id, BASALT_SECURITY_2FA_USED, true ) >= $step ) {
			return false;
		}

		update_user_meta( $user_id, BASALT_SECURITY_2FA_USED, $step );

		return true;
	}

	return basalt_security_2fa_use_recovery( $user_id, $code );
}

/**
 * The string an authenticator app wants.
 *
 * @param WP_User $user   The account.
 * @param string  $secret Base32 secret.
 * @return string
 */
function basalt_security_2fa_uri( WP_User $user, string $secret ): string {
	$site = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );

	return sprintf(
		'otpauth://totp/%1$s:%2$s?secret=%3$s&issuer=%1$s&algorithm=SHA1&digits=6&period=30',
		rawurlencode( $site ),
		rawurlencode( $user->user_login ),
		$secret
	);
}

/* -------------------------------------------------------------------------
 * The login, in two steps
 * ---------------------------------------------------------------------- */

/**
 * Park a half finished login and send the browser to the code screen.
 *
 * Called from wp_login, and from the magic link, which is one factor like a
 * password is and therefore does not get to skip the second one.
 *
 * @param WP_User $user     The account.
 * @param bool    $remember Whether to stay signed in afterwards.
 * @param string  $redirect Where to go when it is done.
 * @return void
 */
function basalt_security_2fa_begin( WP_User $user, bool $remember, string $redirect ): void {
	wp_clear_auth_cookie();
	wp_set_current_user( 0 );

	$handle = wp_generate_password( 32, false );

	set_transient(
		'bs_2fa_' . hash_hmac( 'sha256', $handle, wp_salt( 'auth' ) ),
		array(
			'user'     => $user->ID,
			'remember' => $remember,
			'redirect' => $redirect,
		),
		BASALT_SECURITY_2FA_TTL
	);

	setcookie(
		BASALT_SECURITY_2FA_COOKIE,
		$handle,
		array(
			'expires'  => time() + BASALT_SECURITY_2FA_TTL,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	wp_safe_redirect( add_query_arg( 'action', BASALT_SECURITY_2FA_ACTION, wp_login_url() ) );
	exit;
}

/**
 * Catch a finished password login that still owes a code.
 *
 * @param string  $login The user name.
 * @param WP_User $user  The account.
 * @return void
 */
function basalt_security_2fa_intercept( $login, $user = null ): void {
	if ( ! $user instanceof WP_User || ! basalt_security_2fa_active( $user->ID ) ) {
		return;
	}

	// Already through the second step in this request: nothing to do.
	if ( ! empty( $GLOBALS['basalt_security_2fa_done'] ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- reading the login form's own fields after WordPress has verified it.
	$remember = ! empty( $_POST['rememberme'] );
	$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( (string) $_REQUEST['redirect_to'] ) ) : '';
	// phpcs:enable

	basalt_security_2fa_begin( $user, $remember, $redirect );
}
add_action( 'wp_login', 'basalt_security_2fa_intercept', 5, 2 );

/**
 * The parked login belonging to this browser, if any.
 *
 * @return array{user: int, remember: bool, redirect: string}|null
 */
function basalt_security_2fa_pending(): ?array {
	$handle = isset( $_COOKIE[ BASALT_SECURITY_2FA_COOKIE ] ) ? sanitize_text_field( wp_unslash( (string) $_COOKIE[ BASALT_SECURITY_2FA_COOKIE ] ) ) : '';

	if ( '' === $handle ) {
		return null;
	}

	$data = get_transient( 'bs_2fa_' . hash_hmac( 'sha256', $handle, wp_salt( 'auth' ) ) );

	return is_array( $data ) && ! empty( $data['user'] ) ? $data : null;
}

/**
 * Throw the parked login away.
 *
 * @return void
 */
function basalt_security_2fa_clear(): void {
	$handle = isset( $_COOKIE[ BASALT_SECURITY_2FA_COOKIE ] ) ? sanitize_text_field( wp_unslash( (string) $_COOKIE[ BASALT_SECURITY_2FA_COOKIE ] ) ) : '';

	if ( '' !== $handle ) {
		delete_transient( 'bs_2fa_' . hash_hmac( 'sha256', $handle, wp_salt( 'auth' ) ) );
	}

	setcookie(
		BASALT_SECURITY_2FA_COOKIE,
		' ',
		array(
			'expires'  => time() - YEAR_IN_SECONDS,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);
}

/**
 * The second step: ask for the code, and check it.
 *
 * @return void
 */
function basalt_security_2fa_screen(): void {
	$pending = basalt_security_2fa_pending();

	if ( ! $pending ) {
		wp_safe_redirect( wp_login_url() );
		exit;
	}

	$user  = get_user_by( 'id', (int) $pending['user'] );
	$error = '';

	if ( ! $user instanceof WP_User ) {
		basalt_security_2fa_clear();
		wp_safe_redirect( wp_login_url() );
		exit;
	}

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$nonce = isset( $_POST['basalt_2fa_nonce'] ) ? sanitize_key( wp_unslash( (string) $_POST['basalt_2fa_nonce'] ) ) : '';
		$code  = isset( $_POST['basalt_2fa_code'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['basalt_2fa_code'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'basalt_2fa' ) ) {
			$error = __( 'That page had gone stale. Please sign in again.', 'basalt-security' );
		} elseif ( ! basalt_security_account_throttle( '2fa', 10 ) ) {
			basalt_security_log( '2fa_limit', 'Too many code attempts' );
			basalt_security_2fa_clear();
			$error = __( 'Too many attempts. Please sign in again.', 'basalt-security' );
		} elseif ( basalt_security_2fa_verify( $user->ID, $code ) ) {
			basalt_security_2fa_clear();

			$GLOBALS['basalt_security_2fa_done'] = true;

			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, ! empty( $pending['remember'] ) );
			basalt_security_log( '2fa_login', 'Signed in with a second factor' );

			$redirect = ! empty( $pending['redirect'] ) ? (string) $pending['redirect'] : admin_url();

			wp_safe_redirect( apply_filters( 'login_redirect', $redirect, $redirect, $user ) );
			exit;
		} else {
			basalt_security_log( '2fa_failed', 'Wrong code' );
			$error = __( 'That code is not right. Codes change every thirty seconds, so try the current one.', 'basalt-security' );
		}
	}

	login_header( __( 'One more step', 'basalt-security' ), '', new WP_Error() );

	if ( '' !== $error ) {
		printf( '<div id="login_error" class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
	}
	?>
	<form name="basalt2fa" method="post" action="<?php echo esc_url( add_query_arg( 'action', BASALT_SECURITY_2FA_ACTION, wp_login_url() ) ); ?>">
		<p><?php esc_html_e( 'Open your authenticator app and type the six digit code. A recovery code works too.', 'basalt-security' ); ?></p>
		<p>
			<label for="basalt_2fa_code"><?php esc_html_e( 'Code', 'basalt-security' ); ?></label>
			<input type="text" name="basalt_2fa_code" id="basalt_2fa_code" class="input" inputmode="numeric" autocomplete="one-time-code" autofocus size="20" required />
		</p>
		<?php wp_nonce_field( 'basalt_2fa', 'basalt_2fa_nonce' ); ?>
		<p class="submit">
			<button type="submit" class="button button-primary button-large" style="width:100%"><?php esc_html_e( 'Sign in', 'basalt-security' ); ?></button>
		</p>
	</form>
	<p id="nav"><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Start again', 'basalt-security' ); ?></a></p>
	<?php
	login_footer();
	exit;
}
add_action( 'login_form_' . BASALT_SECURITY_2FA_ACTION, 'basalt_security_2fa_screen' );

/* -------------------------------------------------------------------------
 * Setting it up, on the profile screen
 * ---------------------------------------------------------------------- */

/**
 * The section on the profile.
 *
 * An administrator editing somebody else can switch the second factor off,
 * which is what "I lost my phone" needs, but cannot switch it on: that needs
 * the phone in the room.
 *
 * @param WP_User $user The account being edited.
 * @return void
 */
function basalt_security_2fa_profile( $user ): void {
	if ( ! basalt_security_2fa_available() || ! $user instanceof WP_User ) {
		return;
	}

	$self   = get_current_user_id() === $user->ID;
	$active = basalt_security_2fa_active( $user->ID );

	echo '<h2 id="basalt-2fa">' . esc_html__( 'Two factor authentication', 'basalt-security' ) . '</h2>';

	if ( ! $self && ! $active ) {
		echo '<p class="description">' . esc_html__( 'Only the account holder can set this up: it needs their phone.', 'basalt-security' ) . '</p>';
		return;
	}

	echo '<table class="form-table" role="presentation"><tbody>';

	if ( $active ) {
		$left = count( (array) get_user_meta( $user->ID, BASALT_SECURITY_2FA_CODES, true ) );

		printf(
			'<tr><th scope="row">%1$s</th><td><p><strong>%2$s</strong></p><p class="description">%3$s</p><p><label><input type="checkbox" name="basalt_2fa_off" value="1" /> %4$s</label></p>%5$s</td></tr>',
			esc_html__( 'Status', 'basalt-security' ),
			esc_html__( 'On.', 'basalt-security' ),
			esc_html(
				sprintf(
					/* translators: %d: number of recovery codes left */
					_n( '%d recovery code left.', '%d recovery codes left.', $left, 'basalt-security' ),
					$left
				)
			),
			esc_html__( 'Switch two factor authentication off for this account', 'basalt-security' ),
			$self
				? '<p><label><input type="checkbox" name="basalt_2fa_new_codes" value="1" /> ' . esc_html__( 'Give me a fresh set of recovery codes', 'basalt-security' ) . '</label></p>'
				: ''
		);
	} else {
		$secret = (string) get_user_meta( $user->ID, '_basalt_2fa_pending', true );

		if ( '' === $secret ) {
			$secret = basalt_security_base32_encode( random_bytes( 20 ) );
			update_user_meta( $user->ID, '_basalt_2fa_pending', $secret );
		}

		printf(
			'<tr><th scope="row">%1$s</th><td>
				<p>%2$s</p>
				<p><code style="font-size:1.1em;letter-spacing:.1em">%3$s</code></p>
				<p class="description">%4$s <a href="%5$s">%6$s</a></p>
				<p><label for="basalt_2fa_code">%7$s</label><br /><input type="text" name="basalt_2fa_code" id="basalt_2fa_code" class="regular-text" inputmode="numeric" autocomplete="off" /></p>
				<p class="description">%8$s</p>
			</td></tr>',
			esc_html__( 'Set it up', 'basalt-security' ),
			esc_html__( 'Add this key to an authenticator app, for example Aegis, 2FAS, Ente Auth or the one built into your password manager.', 'basalt-security' ),
			esc_html( trim( chunk_split( $secret, 4, ' ' ) ) ),
			esc_html__( 'On a phone this link adds it in one tap:', 'basalt-security' ),
			// esc_url drops any scheme it does not know, and otpauth is one of them.
			esc_url( basalt_security_2fa_uri( $user, $secret ), array( "otpauth" ) ),
			esc_html__( 'open in the app', 'basalt-security' ),
			esc_html__( 'Then type the code it shows, to prove it arrived:', 'basalt-security' ),
			esc_html__( 'There is no QR code on purpose. Drawing one here would mean sending the key to an outside service, and the key is the whole secret.', 'basalt-security' )
		);
	}

	echo '</tbody></table>';
}
add_action( 'show_user_profile', 'basalt_security_2fa_profile' );
add_action( 'edit_user_profile', 'basalt_security_2fa_profile' );

/**
 * Save what the profile screen asked for.
 *
 * @param int $user_id The account being saved.
 * @return void
 */
function basalt_security_2fa_profile_save( $user_id ): void {
	if ( ! basalt_security_2fa_available() || ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- the profile form is verified by WordPress before this fires.
	$off   = ! empty( $_POST['basalt_2fa_off'] );
	$fresh = ! empty( $_POST['basalt_2fa_new_codes'] );
	$code  = isset( $_POST['basalt_2fa_code'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['basalt_2fa_code'] ) ) : '';
	// phpcs:enable

	if ( $off ) {
		basalt_security_2fa_forget( (int) $user_id );
		basalt_security_log( '2fa_off', 'Second factor switched off' );

		return;
	}

	if ( $fresh && basalt_security_2fa_active( (int) $user_id ) && get_current_user_id() === (int) $user_id ) {
		set_transient( 'bs_2fa_codes_' . $user_id, basalt_security_2fa_recovery_codes( (int) $user_id ), 5 * MINUTE_IN_SECONDS );

		return;
	}

	if ( '' === $code || basalt_security_2fa_active( (int) $user_id ) || get_current_user_id() !== (int) $user_id ) {
		return;
	}

	$secret = (string) get_user_meta( $user_id, '_basalt_2fa_pending', true );

	if ( '' === $secret || ! basalt_security_totp_check( $secret, $code ) ) {
		set_transient( 'bs_2fa_error_' . $user_id, 1, 60 );

		return;
	}

	basalt_security_2fa_store( (int) $user_id, $secret );
	delete_user_meta( $user_id, '_basalt_2fa_pending' );
	set_transient( 'bs_2fa_codes_' . $user_id, basalt_security_2fa_recovery_codes( (int) $user_id ), 5 * MINUTE_IN_SECONDS );
	basalt_security_log( '2fa_on', 'Second factor switched on' );
}
add_action( 'personal_options_update', 'basalt_security_2fa_profile_save' );
add_action( 'edit_user_profile_update', 'basalt_security_2fa_profile_save' );

/**
 * Show the recovery codes once, right after they are made.
 *
 * @return void
 */
function basalt_security_2fa_notices(): void {
	$user_id = get_current_user_id();

	if ( get_transient( 'bs_2fa_error_' . $user_id ) ) {
		delete_transient( 'bs_2fa_error_' . $user_id );

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'That code did not match, so two factor authentication is still off. The key is still on this page: check that your app has it and try the current code.', 'basalt-security' )
		);
	}

	$codes = get_transient( 'bs_2fa_codes_' . $user_id );

	if ( ! is_array( $codes ) || ! $codes ) {
		return;
	}

	delete_transient( 'bs_2fa_codes_' . $user_id );

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong></p><p>%2$s</p><p style="font-family:monospace;font-size:1.1em;line-height:2">%3$s</p><p>%4$s</p></div>',
		esc_html__( 'Your recovery codes', 'basalt-security' ),
		esc_html__( 'Each one works once, in place of a code from the app. Print them or put them in your password manager now: this is the only time they are shown.', 'basalt-security' ),
		esc_html( implode( '   ', $codes ) ),
		esc_html__( 'Without them, a lost phone means an administrator has to switch the second factor off for you.', 'basalt-security' )
	);
}
add_action( 'admin_notices', 'basalt_security_2fa_notices' );

/**
 * Nudge staff who have not set it up, when the site asks them to.
 *
 * A nudge rather than a lock. Refusing the login of somebody who has no
 * second factor yet is how a site ends up with nobody able to get in, and
 * that is a worse outcome than the one it prevents.
 *
 * @return void
 */
function basalt_security_2fa_nudge(): void {
	if ( ! basalt_security_2fa_available() || ! basalt_security_get( 'two_factor_staff' ) ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( ! $user_id || ! current_user_can( 'edit_posts' ) || basalt_security_2fa_active( $user_id ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'This site asks everybody who can edit it to use a second factor. Yours is not set up yet.', 'basalt-security' ),
		esc_url( admin_url( 'profile.php#basalt-2fa' ) ),
		esc_html__( 'Set it up now', 'basalt-security' )
	);
}
add_action( 'admin_notices', 'basalt_security_2fa_nudge' );
