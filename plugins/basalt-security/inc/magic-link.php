<?php
/**
 * Signing in with a link instead of a password.
 *
 * The reason this exists is not convenience. A shop customer signs in twice a
 * year, cannot remember which password they used, resets it, and picks
 * something worse than the last one. The link skips the password entirely: it
 * proves the same thing a password reset proves, which is that whoever is
 * asking can read that inbox, and it does it in one step rather than four.
 *
 * How it is kept honest:
 *
 * - The token is random, single use and short lived. Only its hash is stored,
 *   in a transient that expires on its own, so a database dump is not a pile
 *   of working sign in links.
 * - The link does not sign anybody in on its own. It opens a page with a
 *   button, and the button posts. Mail scanners and link previews follow every
 *   link in a message, and a token that a scanner can spend is a token that is
 *   gone before the person clicks it.
 * - The answer is always the same, whether or not the address has an account.
 *   A form that says "no account with that address" is a customer list.
 * - It is rate limited per address, and it stands down completely while the
 *   brute force lockout is in effect.
 * - Administrators are left out by default. A link in an inbox is as strong as
 *   that inbox, which is the right trade for a customer and the wrong one for
 *   somebody who can edit the site.
 *
 * Off by default: it changes how people sign in, and that is the owner's
 * decision rather than an activation side effect.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/** How long a link is good for. */
const BASALT_SECURITY_MAGIC_TTL = 15 * MINUTE_IN_SECONDS;

/** The action on the login page. */
const BASALT_SECURITY_MAGIC_ACTION = 'basalt_magic';

/**
 * Whether the feature is on.
 *
 * @return bool
 */
function basalt_security_magic_enabled(): bool {
	return basalt_security_enabled() && (bool) basalt_security_get( 'magic_link' );
}

/**
 * Whether this account may use a link.
 *
 * @param WP_User $user The account.
 * @return bool
 */
function basalt_security_magic_allowed( WP_User $user ): bool {
	$allowed = ! user_can( $user, 'edit_posts' ) || (bool) basalt_security_get( 'magic_for_staff' );

	/**
	 * Filter whether an account may sign in with a link.
	 *
	 * @param bool    $allowed Whether it may.
	 * @param WP_User $user    The account.
	 */
	return (bool) apply_filters( 'basalt_security_magic_allowed', $allowed, $user );
}

/**
 * The key a token is stored under.
 *
 * The token itself is never stored. What goes in is a keyed hash of it, so the
 * stored value cannot be turned back into a working link.
 *
 * @param string $token The raw token.
 * @return string
 */
function basalt_security_magic_key( string $token ): string {
	return 'bs_magic_' . hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
}

/**
 * The URL of the sign in page, whichever address the login page lives at.
 *
 * @param array<string, string> $args Extra query arguments.
 * @return string
 */
function basalt_security_magic_url( array $args = array() ): string {
	$args = array_merge( array( 'action' => BASALT_SECURITY_MAGIC_ACTION ), $args );

	return add_query_arg( $args, wp_login_url() );
}

/**
 * Offer the link under the password form.
 *
 * @return void
 */
function basalt_security_magic_login_link(): void {
	if ( ! basalt_security_magic_enabled() ) {
		return;
	}

	printf(
		'<p class="basalt-magic-offer" style="margin-top:1em"><a href="%1$s">%2$s</a></p>',
		esc_url( basalt_security_magic_url() ),
		esc_html__( 'Sign in with a link by email instead', 'basalt-security' )
	);
}
add_action( 'login_form', 'basalt_security_magic_login_link' );

/**
 * The same offer on the WooCommerce account form.
 *
 * @return void
 */
function basalt_security_magic_woo_link(): void {
	if ( ! basalt_security_magic_enabled() ) {
		return;
	}

	printf(
		'<p class="basalt-magic-offer"><a href="%1$s">%2$s</a></p>',
		esc_url( basalt_security_magic_url() ),
		esc_html__( 'Sign in with a link by email instead', 'basalt-security' )
	);
}
add_action( 'woocommerce_login_form_end', 'basalt_security_magic_woo_link' );

/**
 * Handle the two steps: asking for a link, and spending one.
 *
 * Hooked to login_init, which runs on wp-login.php whatever address it is
 * being served at, so this follows the moved login page without knowing
 * anything about it.
 *
 * @return void
 */
function basalt_security_magic_route(): void {
	if ( ! basalt_security_magic_enabled() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading which screen was asked for; every branch below verifies what it needs.
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['action'] ) ) : '';

	if ( BASALT_SECURITY_MAGIC_ACTION !== $action ) {
		return;
	}

	if ( is_user_logged_in() ) {
		wp_safe_redirect( basalt_security_magic_destination( wp_get_current_user() ) );
		exit;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presence only; the token is the credential and is checked below.
	$token = isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['token'] ) ) : '';

	if ( '' !== $token ) {
		basalt_security_magic_confirm( $token );
		exit;
	}

	basalt_security_magic_request();
	exit;
}
add_action( 'login_init', 'basalt_security_magic_route' );

/**
 * Step one: the form, and sending the mail.
 *
 * @return void
 */
function basalt_security_magic_request(): void {
	$sent    = false;
	$error   = '';
	$address = '';

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$nonce = isset( $_POST['basalt_magic_nonce'] ) ? sanitize_key( wp_unslash( (string) $_POST['basalt_magic_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'basalt_magic_request' ) ) {
			$error = __( 'That form had gone stale. Please try again.', 'basalt-security' );
		} else {
			$address = sanitize_email( wp_unslash( (string) ( $_POST['basalt_magic_email'] ?? '' ) ) );

			if ( ! is_email( $address ) ) {
				$error = __( 'Please enter the email address your account uses.', 'basalt-security' );
			} elseif ( ! basalt_security_account_throttle( 'magic', max( 1, (int) basalt_security_get( 'magic_limit' ) ) ) ) {
				basalt_security_log( 'magic_limit', 'Sign in links over the limit' );
				$error = __( 'Too many requests. Please wait an hour, or sign in with your password.', 'basalt-security' );
			} else {
				// Always the same answer, whether or not this address has an account.
				$sent = true;
				basalt_security_magic_send( $address );
			}
		}
	}

	basalt_security_magic_screen( $sent, $error, $address );
}

/**
 * Make a link and mail it, if there is somebody to mail it to.
 *
 * @param string $address The address that was entered.
 * @return void
 */
function basalt_security_magic_send( string $address ): void {
	$user = get_user_by( 'email', $address );

	if ( ! $user instanceof WP_User || ! basalt_security_magic_allowed( $user ) ) {
		return;
	}

	// A locked out address does not get a way around the lockout.
	if ( function_exists( 'basalt_security_locked_for' ) && basalt_security_locked_for() > 0 ) {
		basalt_security_log( 'magic_blocked', 'Sign in link asked for while locked out' );
		return;
	}

	$token = wp_generate_password( 32, false );

	set_transient(
		basalt_security_magic_key( $token ),
		array(
			'user' => (int) $user->ID,
			'made' => time(),
		),
		BASALT_SECURITY_MAGIC_TTL
	);

	$url = basalt_security_magic_url( array( 'token' => $token ) );

	$message = sprintf(
		/* translators: 1: site name, 2: the link, 3: number of minutes */
		__(
			'Somebody asked to sign in to %1$s with this address.

Open this link and press the button on the page:

%2$s

The link works once and stops working after %3$d minutes. If this was not you, nothing has happened and you can ignore this message.',
			'basalt-security'
		),
		wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ),
		$url,
		(int) ( BASALT_SECURITY_MAGIC_TTL / MINUTE_IN_SECONDS )
	);

	wp_mail(
		$user->user_email,
		sprintf(
			/* translators: %s: site name */
			__( 'Your sign in link for %s', 'basalt-security' ),
			wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES )
		),
		$message
	);

	basalt_security_log( 'magic_sent', 'Sign in link sent' );
}

/**
 * Step two: the page the link opens, and the button that spends the token.
 *
 * The GET only looks the token up. Nothing is signed in and nothing is used up
 * until the POST, because every mail scanner between the sender and the
 * recipient follows links, and one of them would otherwise spend the token
 * before the person ever saw the message.
 *
 * @param string $token The token from the link.
 * @return void
 */
function basalt_security_magic_confirm( string $token ): void {
	$stored = get_transient( basalt_security_magic_key( $token ) );
	$user   = is_array( $stored ) ? get_user_by( 'id', (int) ( $stored['user'] ?? 0 ) ) : null;

	if ( ! $user instanceof WP_User || ! basalt_security_magic_allowed( $user ) ) {
		basalt_security_magic_screen(
			false,
			__( 'That link has been used already, or it has expired. Ask for a new one.', 'basalt-security' ),
			''
		);
		return;
	}

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$nonce = isset( $_POST['basalt_magic_nonce'] ) ? sanitize_key( wp_unslash( (string) $_POST['basalt_magic_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'basalt_magic_confirm' ) ) {
			basalt_security_magic_screen( false, __( 'That page had gone stale. Open the link from the email again.', 'basalt-security' ), '' );
			return;
		}

		// Single use: gone before the session exists, so a replay finds nothing.
		delete_transient( basalt_security_magic_key( $token ) );

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, false );
		do_action( 'wp_login', $user->user_login, $user );

		basalt_security_log( 'magic_login', 'Signed in with a link' );

		wp_safe_redirect( basalt_security_magic_destination( $user ) );
		exit;
	}

	basalt_security_magic_button( $user, $token );
}

/**
 * Where somebody lands after signing in.
 *
 * @param WP_User $user The account.
 * @return string
 */
function basalt_security_magic_destination( WP_User $user ): string {
	$url = user_can( $user, 'edit_posts' ) ? admin_url() : home_url( '/' );

	if ( function_exists( 'wc_get_page_permalink' ) && ! user_can( $user, 'edit_posts' ) ) {
		$account = (string) wc_get_page_permalink( 'myaccount' );
		$url     = '' !== $account ? $account : $url;
	}

	/**
	 * Filter where a link sign in lands.
	 *
	 * @param string  $url  The destination.
	 * @param WP_User $user The account.
	 */
	return (string) apply_filters( 'basalt_security_magic_destination', $url, $user );
}

/**
 * The request screen, in the login page's own furniture.
 *
 * @param bool   $sent    Whether a message was just sent.
 * @param string $error   An error to show.
 * @param string $address What was entered.
 * @return void
 */
function basalt_security_magic_screen( bool $sent, string $error, string $address ): void {
	login_header( __( 'Sign in with a link', 'basalt-security' ), '', new WP_Error() );

	if ( '' !== $error ) {
		printf( '<div id="login_error" class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
	}

	if ( $sent ) {
		printf(
			'<p class="message">%s</p>',
			esc_html__( 'If that address has an account, a sign in link is on its way. It works once and expires in fifteen minutes.', 'basalt-security' )
		);
	} else {
		?>
		<form name="basaltmagic" method="post">
			<p>
				<label for="basalt_magic_email"><?php esc_html_e( 'Email address', 'basalt-security' ); ?></label>
				<input type="email" name="basalt_magic_email" id="basalt_magic_email" class="input" value="<?php echo esc_attr( $address ); ?>" size="20" autocomplete="email" required />
			</p>
			<?php wp_nonce_field( 'basalt_magic_request', 'basalt_magic_nonce' ); ?>
			<p class="submit">
				<button type="submit" class="button button-primary button-large" style="width:100%">
					<?php esc_html_e( 'Send me a link', 'basalt-security' ); ?>
				</button>
			</p>
		</form>
		<?php
	}

	printf(
		'<p id="nav"><a href="%1$s">%2$s</a></p>',
		esc_url( wp_login_url() ),
		esc_html__( 'Sign in with a password instead', 'basalt-security' )
	);

	login_footer();
}

/**
 * The page the link opens: one button, and nothing has happened yet.
 *
 * @param WP_User $user  The account the token belongs to.
 * @param string  $token The token.
 * @return void
 */
function basalt_security_magic_button( WP_User $user, string $token ): void {
	login_header( __( 'Sign in', 'basalt-security' ), '', new WP_Error() );
	?>
	<form name="basaltmagicconfirm" method="post" action="<?php echo esc_url( basalt_security_magic_url() ); ?>">
		<p class="message">
			<?php
			printf(
				/* translators: %s: the account's display name */
				esc_html__( 'Signing in as %s.', 'basalt-security' ),
				esc_html( $user->display_name )
			);
			?>
		</p>
		<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>" />
		<?php wp_nonce_field( 'basalt_magic_confirm', 'basalt_magic_nonce' ); ?>
		<p class="submit">
			<button type="submit" class="button button-primary button-large" style="width:100%">
				<?php esc_html_e( 'Sign me in', 'basalt-security' ); ?>
			</button>
		</p>
	</form>
	<?php
	login_footer();
}
