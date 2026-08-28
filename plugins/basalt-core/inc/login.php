<?php
/**
 * Login screen branding.
 *
 * Replaces the WordPress logo and the default colours on wp-login.php with the
 * site's own, so a client does not land on a page that belongs to somebody else
 * halfway through their own workflow.
 *
 * Scope, and what is deliberately absent
 * --------------------------------------
 * Only structured options: a logo, three colours, an optional background image.
 * There is no free CSS field. An administrator can already do more damage
 * elsewhere, so this is not about privilege; it is that a raw CSS box on the
 * login screen is a large surface for a small benefit, and it makes every
 * future change to this page a guess about what a customer pasted into it.
 *
 * There is also no "change the login URL" option. Moving wp-login.php stops
 * some automated noise in the logs and stops no attacker who looks at
 * /wp-json/ or an author archive. It reliably breaks integrations that post to
 * the known endpoint. If a site needs that, it needs a plugin whose whole job
 * is rewriting that URL, not a side feature of this one.
 *
 * What is here for security rather than looks is the generic error message,
 * which closes username enumeration through the login form.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether login branding should apply.
 *
 * @return bool
 */
function basalt_core_login_enabled(): bool {
	/**
	 * Filter whether the login screen is branded.
	 *
	 * @param bool $enabled Whether to brand the login screen.
	 */
	return (bool) apply_filters( 'basalt_core_login_enabled', (bool) basalt_core_get( 'login_enabled' ) );
}

/**
 * Validate a hex colour.
 *
 * Implemented here rather than with sanitize_hex_color(), which is not loaded
 * on every request. Returns an empty string for anything that is not a plain
 * three or six digit hex colour, so nothing else can reach the stylesheet.
 *
 * @param mixed $value Raw value.
 * @return string A #rrggbb value, or an empty string.
 */
function basalt_core_hex_color( $value ): string {
	$value = trim( (string) $value );

	return preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value ) ? $value : '';
}

/**
 * Relative luminance of a hex colour, per WCAG.
 *
 * @param string $hex A #rgb or #rrggbb value.
 * @return float Between 0 and 1.
 */
function basalt_core_luminance( string $hex ): float {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	$channels = array_map(
		static function ( string $pair ): float {
			$value = hexdec( $pair ) / 255;

			return $value <= 0.03928 ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
		},
		str_split( $hex, 2 )
	);

	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * Contrast ratio between two hex colours.
 *
 * @param string $one First colour.
 * @param string $two Second colour.
 * @return float Between 1 and 21.
 */
function basalt_core_contrast_ratio( string $one, string $two ): float {
	$a = basalt_core_luminance( $one );
	$b = basalt_core_luminance( $two );

	return ( max( $a, $b ) + 0.05 ) / ( min( $a, $b ) + 0.05 );
}

/**
 * The resolved login colours, with defaults filled in.
 *
 * @return array{page: string, form: string, accent: string, text: string, page_text: string}
 */
function basalt_core_login_colors(): array {
	$page   = basalt_core_hex_color( basalt_core_get( 'login_background' ) ) ?: '#f4f5f7';
	$form   = basalt_core_hex_color( basalt_core_get( 'login_form_background' ) ) ?: '#ffffff';
	$accent = basalt_core_hex_color( basalt_core_get( 'login_accent' ) ) ?: '#c2410c';

	/*
	 * Two text colours, not one.
	 *
	 * The form has its own background, and the links below it sit on the page
	 * background. Colouring both from the form background is how the "lost your
	 * password" link ends up dark on a dark page: it looked right while the
	 * page background stayed light, and became unreadable the moment it did
	 * not.
	 */
	$on = static function ( string $background ): string {
		return basalt_core_contrast_ratio( $background, '#000000' ) >= basalt_core_contrast_ratio( $background, '#ffffff' )
			? '#16191d'
			: '#f2f4f5';
	};

	return array(
		'page'      => $page,
		'form'      => $form,
		'accent'    => $accent,
		'text'      => $on( $form ),
		'page_text' => $on( $page ),
	);
}

/**
 * Print the login stylesheet.
 *
 * Every value that reaches this CSS has been through the hex validator or
 * through wp_get_attachment_image_url, so nothing arbitrary can be written
 * into the stylesheet even if the option row is edited directly in the
 * database.
 *
 * @return void
 */
function basalt_core_login_styles(): void {
	if ( ! basalt_core_login_enabled() ) {
		return;
	}

	$colors     = basalt_core_login_colors();
	$logo_id    = (int) basalt_core_get( 'login_logo' );
	$logo_width = min( 480, max( 80, (int) basalt_core_get( 'login_logo_width' ) ) );
	$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	$bg_id      = (int) basalt_core_get( 'login_background_image' );
	$bg_url     = $bg_id ? wp_get_attachment_image_url( $bg_id, 'full' ) : '';

	$css = '';

	$css .= 'body.login{background-color:' . $colors['page'] . ';}';

	if ( $bg_url ) {
		$css .= 'body.login{background-image:url(' . esc_url_raw( $bg_url ) . ');background-size:cover;background-position:center;}';
	}

	if ( $logo_url ) {
		/*
		 * The logo replaces the background image of the existing heading link.
		 * The heading still contains the site name as text for assistive
		 * technology, which is why this is done with CSS rather than by
		 * replacing the markup.
		 */
		$css .= '.login h1 a{background-image:url(' . esc_url_raw( $logo_url ) . ');'
			. 'background-size:contain;background-position:center;'
			. 'width:' . $logo_width . 'px;height:' . (int) round( $logo_width * 0.4 ) . 'px;}';
	}

	$css .= '.login form{background:' . $colors['form'] . ';color:' . $colors['text'] . ';'
		. 'border-radius:8px;border:1px solid rgba(0,0,0,.08);box-shadow:0 8px 24px rgba(0,0,0,.08);}';

	$css .= '.login label,.login form .input,.login input[type=text],.login input[type=password]{color:' . $colors['text'] . ';}';

	$css .= '.wp-core-ui .button-primary{background:' . $colors['accent'] . ';border-color:' . $colors['accent'] . ';'
		. 'color:#fff;text-shadow:none;box-shadow:none;}';

	$css .= '.wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus{background:' . $colors['accent'] . ';'
		. 'border-color:' . $colors['accent'] . ';filter:brightness(.9);}';

	/*
	 * Focus visibility is not negotiable, and the login form is the one page
	 * where a user who cannot see the focus ring is completely stuck. Drawn in
	 * the accent colour with a fixed width rather than inheriting whatever the
	 * browser default happens to be against the chosen background.
	 */
	$css .= '.login :focus-visible{outline:2px solid ' . $colors['accent'] . ';outline-offset:2px;}';
	// These sit on the page background, not on the form.
	$css .= '.login #backtoblog a,.login #nav a{color:' . $colors['page_text'] . ';}';
	$css .= '.login #backtoblog a:hover,.login #nav a:hover,.login #backtoblog a:focus,.login #nav a:focus{color:' . $colors['page_text'] . ';text-decoration:underline;}';

	wp_register_style( 'basalt-core-login', false, array(), BASALT_CORE_VERSION );
	wp_enqueue_style( 'basalt-core-login' );
	wp_add_inline_style( 'basalt-core-login', $css );
}
add_action( 'login_enqueue_scripts', 'basalt_core_login_styles' );

/**
 * Point the login logo at this site rather than at wordpress.org.
 *
 * @return string
 */
function basalt_core_login_logo_url(): string {
	return home_url( '/' );
}

/**
 * Use the site name as the login logo's accessible name.
 *
 * @return string
 */
function basalt_core_login_logo_title(): string {
	return get_bloginfo( 'name', 'display' );
}

/**
 * Hook the logo link filters only when branding is on.
 *
 * @return void
 */
function basalt_core_login_links(): void {
	if ( ! basalt_core_login_enabled() ) {
		return;
	}

	add_filter( 'login_headerurl', 'basalt_core_login_logo_url' );
	add_filter( 'login_headertext', 'basalt_core_login_logo_title' );
}
add_action( 'login_init', 'basalt_core_login_links' );

/**
 * Replace login failures with a single generic message.
 *
 * The default messages say whether the username exists, which turns the login
 * form into a user enumeration oracle: an attacker learns which accounts are
 * real before spending any effort on passwords.
 *
 * The trade is real and worth stating: a legitimate user who mistypes their
 * username no longer learns that it was the username. That is the cost of not
 * answering the same question for everyone else.
 *
 * @param string $error The error markup WordPress produced.
 * @return string
 */
function basalt_core_login_generic_error( $error ) {
	if ( ! basalt_core_login_enabled() || ! basalt_core_get( 'login_generic_errors' ) ) {
		return $error;
	}

	// Password reset and similar flows produce messages worth keeping.
	if ( ! did_action( 'wp_login_failed' ) ) {
		return $error;
	}

	return esc_html__( 'The username or password is not correct.', 'basalt-core' );
}
add_filter( 'login_errors', 'basalt_core_login_generic_error' );

/**
 * Stop the same enumeration through the lost password form.
 *
 * Hiding it on the login form and not here would tell an attacker exactly as
 * much, so it follows the same setting.
 *
 * The mechanism matters. An earlier version rewrote the error text, which
 * cannot work: core adds its "there is no account with that address" error
 * *after* this filter runs, so there was nothing to rewrite yet and the two
 * cases still produced visibly different pages. Verified against the live
 * install before and after.
 *
 * What closes it is making the unknown case take the same exit as the known
 * one. At this point core has looked the user up and found nothing, and has not
 * yet added its error, so redirecting to the confirmation screen produces a
 * response identical to a successful request.
 *
 * An error that is already present is left alone: an empty field is a form
 * validation problem and says nothing about which accounts exist.
 *
 * Residual, and deliberately so: if the account exists but the mail cannot be
 * sent, core reports that failure and the two cases differ again. Swallowing it
 * would mean a site with a broken mailer tells every user their reset is on the
 * way while nothing is sent, which is a worse failure than the signal it hides.
 * A broken mailer is a misconfiguration to fix, not to mask.
 *
 * @param WP_Error           $errors    Errors so far.
 * @param WP_User|false|null $user_data The user found, or false.
 * @return WP_Error
 */
function basalt_core_lost_password_generic( $errors, $user_data = null ) {
	if ( ! basalt_core_login_enabled() || ! basalt_core_get( 'login_generic_errors' ) || ! is_wp_error( $errors ) ) {
		return $errors;
	}

	if ( $user_data ) {
		return $errors;
	}

	/*
	 * Core reaches "no such account" by two different routes, and both have to
	 * be caught.
	 *
	 * An entry containing an @ is treated as an email address and gets
	 * invalid_email added before this filter runs. Anything else is looked up
	 * as a username, and invalidcombo is added after. So the unknown account
	 * arrives here either carrying one specific error or carrying none at all,
	 * depending only on whether the visitor typed an @.
	 */
	$codes       = $errors->get_error_codes();
	$enumerating = array_diff( $codes, array( 'invalid_email', 'invalidcombo' ) );

	// Any other error, such as an empty field, is a real validation message.
	if ( $enumerating ) {
		return $errors;
	}

	wp_safe_redirect( site_url( 'wp-login.php?checkemail=confirm', 'login' ) );
	exit;
}
add_filter( 'lostpassword_errors', 'basalt_core_lost_password_generic', 10, 2 );

/**
 * Warn in the admin when the chosen login colours fail WCAG contrast.
 *
 * A colour picker that lets someone build an unreadable login screen without
 * saying so is a poor tool, and on this page the consequence is worse than
 * cosmetic: the login form is where a user who cannot read the interface has
 * no way around it.
 *
 * The thresholds are the WCAG AA minimums: 4.5:1 for text, 3:1 for a
 * non-text indicator such as the focus ring.
 *
 * @return void
 */
function basalt_core_login_contrast_notice(): void {
	if ( ! basalt_core_login_enabled() ) {
		return;
	}

	$colors  = basalt_core_login_colors();
	$issues  = array();

	$text_ratio = basalt_core_contrast_ratio( $colors['form'], $colors['text'] );

	if ( $text_ratio < 4.5 ) {
		$issues[] = sprintf(
			/* translators: %s: contrast ratio, for example 3.2. */
			__( 'Form text against the form background is %s to 1. WCAG AA asks for at least 4.5.', 'basalt-core' ),
			number_format_i18n( $text_ratio, 1 )
		);
	}

	// White button text on the accent colour.
	$button_ratio = basalt_core_contrast_ratio( $colors['accent'], '#ffffff' );

	if ( $button_ratio < 4.5 ) {
		$issues[] = sprintf(
			/* translators: %s: contrast ratio. */
			__( 'White button text on the accent colour is %s to 1. WCAG AA asks for at least 4.5.', 'basalt-core' ),
			number_format_i18n( $button_ratio, 1 )
		);
	}

	// The focus ring sits on the form, and is a non-text indicator.
	$focus_ratio = basalt_core_contrast_ratio( $colors['accent'], $colors['form'] );

	if ( $focus_ratio < 3 ) {
		$issues[] = sprintf(
			/* translators: %s: contrast ratio. */
			__( 'The focus ring against the form background is %s to 1. WCAG AA asks for at least 3, and a focus ring nobody can see is the one that strands keyboard users on the login form.', 'basalt-core' ),
			number_format_i18n( $focus_ratio, 1 )
		);
	}

	$page_ratio = basalt_core_contrast_ratio( $colors['page'], $colors['page_text'] );

	if ( $page_ratio < 4.5 ) {
		$issues[] = sprintf(
			/* translators: %s: contrast ratio. */
			__( 'The links below the form against the page background are %s to 1. WCAG AA asks for at least 4.5.', 'basalt-core' ),
			number_format_i18n( $page_ratio, 1 )
		);
	}

	if ( ! $issues ) {
		return;
	}

	echo '<div class="notice notice-warning inline"><p><strong>'
		. esc_html__( 'These login colours do not meet WCAG AA:', 'basalt-core' )
		. '</strong></p><ul style="list-style:disc;margin-left:1.5em">';

	foreach ( $issues as $issue ) {
		echo '<li>' . esc_html( $issue ) . '</li>';
	}

	echo '</ul></div>';
}

/**
 * Give the login page a main landmark.
 *
 * wp-login.php has none, so a screen reader user has no way to jump to the
 * content and an audit reports the page as having no main region. That is
 * core's markup rather than ours, verified by measuring the page with branding
 * switched off, but this plugin is already the place where login accessibility
 * is handled.
 *
 * login_header fires immediately before the #login container opens and
 * login_footer after it closes, so a wrapper opened and closed on that pair
 * nests correctly around everything between them.
 *
 * Tied to the branding switch on purpose. It changes core's markup, and a site
 * with custom login CSS written against a direct child selector would notice.
 * Someone who has opted into us restyling this page has already accepted that;
 * someone who has not should get core untouched.
 *
 * @return void
 */
function basalt_core_login_main_open(): void {
	if ( ! basalt_core_login_enabled() || ! basalt_core_login_landmark_enabled() ) {
		return;
	}

	echo '<main class="basalt-login-main">';
}
add_action( 'login_header', 'basalt_core_login_main_open' );

/**
 * Close the main landmark.
 *
 * @return void
 */
function basalt_core_login_main_close(): void {
	if ( ! basalt_core_login_enabled() || ! basalt_core_login_landmark_enabled() ) {
		return;
	}

	echo '</main>';
}
add_action( 'login_footer', 'basalt_core_login_main_close' );

/**
 * Whether to add the main landmark to the login page.
 *
 * @return bool
 */
function basalt_core_login_landmark_enabled(): bool {
	/**
	 * Filter whether the login page gets a main landmark.
	 *
	 * @param bool $enabled Whether to wrap the login content.
	 */
	return (bool) apply_filters( 'basalt_core_login_landmark', true );
}
