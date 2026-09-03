<?php
/**
 * Accounts: password reset, registration, password quality, sessions.
 *
 * The login form gets all the attention, and it is the one door that is
 * already watched: the brute force module counts failures and locks the
 * address. The other three doors into an account are usually left open.
 *
 * - Password reset. Every request sends mail to somebody else's inbox. With
 *   no limit that is a way to send a hundred mails from a stranger's site,
 *   and the reply always says whether the address exists.
 * - Registration. If the site takes registrations, a script finds the form in
 *   a day, and WordPress helpfully answers "this email is already registered",
 *   which turns the form into a customer list.
 * - The new password itself. WordPress generates a strong one and then lets
 *   anybody replace it with "sommer2026" behind a confirmation checkbox.
 *
 * All three are the same shape: a limit per address, an answer that says
 * nothing about who exists, and a line in the log.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Count one attempt against a bucket, and say whether it is still allowed.
 *
 * Counts before it answers, so an address that is already over the limit
 * cannot get a free attempt by trying often enough.
 *
 * @param string $bucket Bucket name, for example "reset".
 * @param int    $limit  Attempts allowed per hour.
 * @return bool
 */
function basalt_security_account_throttle( string $bucket, int $limit ): bool {
	$ip = basalt_security_ip();

	if ( '' === $ip ) {
		return true;
	}

	$key   = 'bs_acct_' . $bucket . '_' . md5( $ip );
	$count = (int) get_transient( $key );

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	return $count < $limit;
}

/* -------------------------------------------------------------------------
 * Password reset
 * ---------------------------------------------------------------------- */

/**
 * Limit how often one address can ask for a reset mail.
 *
 * Core already answers the same way whether or not the account exists, since
 * Basalt Core filters the lost password errors. What is missing is the limit:
 * without it the form is a mail cannon pointed at whichever inbox the
 * attacker names.
 *
 * @param WP_Error $errors Errors so far.
 * @return void
 */
function basalt_security_limit_reset( $errors ): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'limit_resets' ) ) {
		return;
	}

	if ( ! $errors instanceof WP_Error ) {
		return;
	}

	$limit = max( 1, (int) basalt_security_get( 'reset_limit' ) );

	if ( basalt_security_account_throttle( 'reset', $limit ) ) {
		return;
	}

	basalt_security_log( 'reset_limit', 'Password reset requests over the limit' );

	$errors->add(
		'basalt_security_reset_limit',
		__( '<strong>Too many requests.</strong> Please wait an hour before asking for another password mail.', 'basalt-security' )
	);
}
add_action( 'lostpassword_post', 'basalt_security_limit_reset' );

/**
 * End every session when a password is reset.
 *
 * Somebody who resets a password has usually lost control of the account, or
 * thinks they have. Leaving the other sessions signed in means the reset
 * changes nothing for whoever is already inside.
 *
 * @param WP_User $user The user whose password was reset.
 * @return void
 */
function basalt_security_reset_sessions( $user ): void {
	if ( ! $user instanceof WP_User ) {
		return;
	}

	WP_Session_Tokens::get_instance( $user->ID )->destroy_all();
	basalt_security_log( 'reset_done', 'Password reset, all sessions ended' );
}
add_action( 'after_password_reset', 'basalt_security_reset_sessions' );

/* -------------------------------------------------------------------------
 * Registration
 * ---------------------------------------------------------------------- */

/**
 * A field nobody sees, on both registration forms.
 *
 * The same trick the contact form uses. It costs one hidden input and stops
 * the scripts that fill in every field they find, which is most of them.
 *
 * @return void
 */
function basalt_security_registration_honeypot(): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'guard_registration' ) ) {
		return;
	}

	printf(
		'<p style="position:absolute;left:-9999px" aria-hidden="true"><label>%1$s<input type="text" name="%2$s" value="" tabindex="-1" autocomplete="off"></label></p>',
		esc_html__( 'Leave this field empty', 'basalt-security' ),
		esc_attr( 'basalt_website' )
	);
}
add_action( 'register_form', 'basalt_security_registration_honeypot' );
add_action( 'woocommerce_register_form', 'basalt_security_registration_honeypot' );

/**
 * The registration checks: honeypot, limit, and no confirmation of who exists.
 *
 * The last one is a trade. Telling somebody "this address already has an
 * account" is friendlier, and it also answers the only question an attacker
 * has. The message here points at the two ways out, signing in and resetting
 * the password, without confirming anything.
 *
 * @param WP_Error $errors Errors so far.
 * @return WP_Error
 */
function basalt_security_registration_errors( $errors ) {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'guard_registration' ) || ! $errors instanceof WP_Error ) {
		return $errors;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- reading a honeypot on a public form, no state changes here.
	if ( '' !== trim( (string) wp_unslash( $_POST['basalt_website'] ?? '' ) ) ) {
		basalt_security_log( 'registration_bot', 'Registration with the honeypot filled in' );

		return new WP_Error( 'basalt_security_bot', __( '<strong>Error:</strong> Registration could not be completed.', 'basalt-security' ) );
	}

	$limit = max( 1, (int) basalt_security_get( 'registration_limit' ) );

	if ( ! basalt_security_account_throttle( 'register', $limit ) ) {
		basalt_security_log( 'registration_limit', 'Registrations over the limit' );

		return new WP_Error(
			'basalt_security_registration_limit',
			__( '<strong>Too many attempts.</strong> Please try again in an hour.', 'basalt-security' )
		);
	}

	$exists = array( 'username_exists', 'email_exists', 'registerfail' );
	$found  = array_intersect( $exists, $errors->get_error_codes() );

	if ( $found ) {
		foreach ( $exists as $code ) {
			$errors->remove( $code );
		}

		$errors->add(
			'basalt_security_registration_failed',
			sprintf(
				/* translators: 1: sign in URL, 2: lost password URL */
				__( '<strong>Error:</strong> An account could not be created with these details. If you already have one, <a href="%1$s">sign in</a> or <a href="%2$s">request a new password</a>.', 'basalt-security' ),
				esc_url( wp_login_url() ),
				esc_url( wp_lostpassword_url() )
			)
		);
	}

	return $errors;
}
add_filter( 'registration_errors', 'basalt_security_registration_errors' );

/**
 * The same checks on the WooCommerce registration form.
 *
 * WooCommerce runs its own registration and never reaches registration_errors,
 * so the guard has to be hung on its own hook or the shop is the way around it.
 *
 * @param WP_Error $errors Errors so far.
 * @return WP_Error
 */
function basalt_security_woo_registration_errors( $errors ) {
	return basalt_security_registration_errors( $errors );
}
add_filter( 'woocommerce_process_registration_errors', 'basalt_security_woo_registration_errors' );
add_filter( 'woocommerce_registration_errors', 'basalt_security_woo_registration_errors' );

/* -------------------------------------------------------------------------
 * Password quality
 * ---------------------------------------------------------------------- */

/**
 * The passwords that are not allowed, whatever their length.
 *
 * Not a dictionary: a dictionary belongs in a service, and shipping a
 * megabyte of leaked passwords with a theme is not proportionate. This is the
 * short list of what people actually type when a form asks them to think of
 * something, plus whatever the site itself is called.
 *
 * @param WP_User|null $user The account, when known.
 * @return string[]
 */
function basalt_security_weak_passwords( $user = null ): array {
	$words = array( 'password', 'passwort', 'kennwort', 'geheim', 'welcome', 'willkommen', 'letmein', 'qwerty', 'qwertz', 'admin', 'administrator', 'iloveyou', 'sommer', 'summer', 'winter', 'frühling', 'test' );

	$site = sanitize_title( (string) get_bloginfo( 'name' ) );

	if ( '' !== $site ) {
		$words[] = str_replace( '-', '', $site );
	}

	if ( $user instanceof WP_User ) {
		$words[] = sanitize_title( $user->user_login );
		$words[] = sanitize_title( (string) strstr( (string) $user->user_email, '@', true ) );
	}

	/**
	 * Filter the words a password may not be built around.
	 *
	 * @param string[]     $words The list.
	 * @param WP_User|null $user  The account, when known.
	 */
	return array_values( array_filter( array_unique( (array) apply_filters( 'basalt_security_weak_passwords', $words, $user ) ) ) );
}

/**
 * Whether a password is good enough, and why not.
 *
 * Length first, because length is what actually helps. Then the check that
 * the password is not simply the site name or the account name with a year
 * after it, which is what a rule about capitals and digits produces.
 *
 * @param string       $password The proposed password.
 * @param WP_User|null $user     The account, when known.
 * @return string Empty when the password is fine, otherwise the reason.
 */
function basalt_security_password_problem( string $password, $user = null ): string {
	$min = max( 8, (int) basalt_security_get( 'password_min' ) );

	if ( mb_strlen( $password ) < $min ) {
		return sprintf(
			/* translators: %d: minimum number of characters */
			__( 'Please use at least %d characters. A short sentence is easier to remember than a short password and much harder to guess.', 'basalt-security' ),
			$min
		);
	}

	$flat = preg_replace( '/[^a-z0-9]/', '', mb_strtolower( $password ) ) ?? '';
	$bare = preg_replace( '/[0-9!.]+$/', '', $flat ) ?? '';

	foreach ( basalt_security_weak_passwords( $user ) as $word ) {
		if ( '' !== $word && ( $flat === $word || $bare === $word ) ) {
			return __( 'That password is one of the first anybody tries. Please pick something that has nothing to do with this site or your name.', 'basalt-security' );
		}
	}

	return '';
}

/**
 * Check the password on the reset form.
 *
 * @param WP_Error     $errors Errors so far.
 * @param WP_User|null $user   The account.
 * @return void
 */
function basalt_security_check_reset_password( $errors, $user = null ): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'strong_passwords' ) || ! $errors instanceof WP_Error ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the reset key is the credential here and core has already checked it.
	$password = (string) wp_unslash( $_POST['pass1'] ?? '' );

	if ( '' === $password ) {
		return;
	}

	$problem = basalt_security_password_problem( $password, $user instanceof WP_User ? $user : null );

	if ( '' !== $problem ) {
		$errors->add( 'basalt_security_weak_password', '<strong>' . esc_html__( 'Error:', 'basalt-security' ) . '</strong> ' . esc_html( $problem ) );
	}
}
add_action( 'validate_password_reset', 'basalt_security_check_reset_password', 10, 2 );

/**
 * Check the password on the profile and user creation forms.
 *
 * @param WP_Error $errors Errors so far.
 * @param bool     $update Whether an existing user is being updated.
 * @param stdClass $user   The user data being saved.
 * @return void
 */
function basalt_security_check_profile_password( $errors, $update = false, $user = null ): void {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'strong_passwords' ) || ! $errors instanceof WP_Error ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the profile form has been verified by core before this fires.
	$password = (string) wp_unslash( $_POST['pass1'] ?? '' );

	if ( '' === $password ) {
		return;
	}

	$account = null;

	if ( $user && ! empty( $user->ID ) ) {
		$account = get_user_by( 'id', (int) $user->ID ) ?: null;
	}

	$problem = basalt_security_password_problem( $password, $account );

	if ( '' !== $problem ) {
		$errors->add( 'basalt_security_weak_password', '<strong>' . esc_html__( 'Error:', 'basalt-security' ) . '</strong> ' . esc_html( $problem ) );
	}
}
add_action( 'user_profile_update_errors', 'basalt_security_check_profile_password', 10, 3 );

/**
 * Check the password a customer picks in the shop.
 *
 * WooCommerce has registration and a password change of its own, both of them
 * outside the core forms.
 *
 * @param WP_Error $errors Errors so far.
 * @return WP_Error
 */
function basalt_security_check_woo_password( $errors ) {
	if ( ! basalt_security_enabled() || ! basalt_security_get( 'strong_passwords' ) || ! $errors instanceof WP_Error ) {
		return $errors;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verifies its own nonce before these filters run.
	$password = (string) wp_unslash( $_POST['password'] ?? $_POST['password_1'] ?? '' );
	// phpcs:enable

	if ( '' === $password ) {
		return $errors;
	}

	$problem = basalt_security_password_problem( $password, wp_get_current_user()->ID ? wp_get_current_user() : null );

	if ( '' !== $problem ) {
		$errors->add( 'basalt_security_weak_password', esc_html( $problem ) );
	}

	return $errors;
}
add_filter( 'woocommerce_process_registration_errors', 'basalt_security_check_woo_password', 20 );
add_action( 'woocommerce_save_account_details_errors', 'basalt_security_check_woo_password', 20 );
