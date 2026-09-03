<?php
/**
 * The settings screen.
 *
 * One page under Settings, five sections, and the log underneath. Everything
 * lives in a single option, so reading the settings is one cached query.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the option.
 *
 * @return void
 */
function basalt_security_register_settings(): void {
	register_setting(
		'basalt_security',
		BASALT_SECURITY_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'basalt_security_sanitize',
			'default'           => basalt_security_defaults(),
		)
	);
}
add_action( 'admin_init', 'basalt_security_register_settings' );

/**
 * Clean what was submitted.
 *
 * @param mixed $input The submitted values.
 * @return array<string, mixed>
 */
function basalt_security_sanitize( $input ): array {
	$input = (array) $input;
	$out   = basalt_security_defaults();

	foreach ( array( 'block_wp_admin', 'brute_force', 'brute_notify', 'firewall', 'headers', 'content_policy', 'hsts', 'disable_xmlrpc', 'disable_enumeration', 'disable_file_edit', 'hide_version', 'limit_app_passwords', 'limit_resets', 'guard_registration', 'strong_passwords', 'magic_link', 'magic_for_staff' ) as $flag ) {
		$out[ $flag ] = ! empty( $input[ $flag ] );
	}

	/*
	 * sanitize_title, so whatever somebody types becomes something that can
	 * actually appear in a URL. Two names are refused: the one we are moving
	 * away from, and wp-admin.
	 */
	$slug = sanitize_title( (string) ( $input['login_slug'] ?? '' ) );

	if ( in_array( $slug, array( 'wp-login', 'wp-login-php', 'wp-admin', 'admin' ), true ) ) {
		add_settings_error( BASALT_SECURITY_OPTION, 'slug', __( 'That login address is one of the ones scanners already try. Pick another.', 'basalt-security' ) );
		$slug = '';
	}

	$out['login_slug'] = $slug;

	$out['brute_limit']   = min( 20, max( 2, (int) ( $input['brute_limit'] ?? 5 ) ) );
	$out['brute_minutes'] = min( 1440, max( 1, (int) ( $input['brute_minutes'] ?? 15 ) ) );

	$out['reset_limit']        = min( 20, max( 1, (int) ( $input['reset_limit'] ?? 3 ) ) );
	$out['registration_limit'] = min( 50, max( 1, (int) ( $input['registration_limit'] ?? 5 ) ) );
	$out['magic_limit']        = min( 20, max( 1, (int) ( $input['magic_limit'] ?? 5 ) ) );

	/*
	 * Eight is the floor rather than the default: below that a password is
	 * guessable offline in an afternoon, and a setting that allows it only
	 * looks like a decision somebody made on purpose.
	 */
	$out['password_min'] = min( 64, max( 8, (int) ( $input['password_min'] ?? 12 ) ) );

	$frame                 = (string) ( $input['frame_options'] ?? 'SAMEORIGIN' );
	$out['frame_options']  = in_array( $frame, array( 'SAMEORIGIN', 'DENY', 'off' ), true ) ? $frame : 'SAMEORIGIN';

	$ips = array();

	foreach ( preg_split( '/\R/', (string) ( $input['allow_ips'] ?? '' ) ) ?: array() as $line ) {
		$line = trim( $line );

		if ( filter_var( $line, FILTER_VALIDATE_IP ) ) {
			$ips[] = $line;
		}
	}

	$out['allow_ips'] = implode( "\n", $ips );

	return $out;
}

/**
 * The menu entry.
 *
 * @return void
 */
function basalt_security_menu(): void {
	add_options_page(
		__( 'Security', 'basalt-security' ),
		__( 'Security', 'basalt-security' ),
		'manage_options',
		'basalt-security',
		'basalt_security_render_settings'
	);
}
add_action( 'admin_menu', 'basalt_security_menu' );

/**
 * One row of the settings table.
 *
 * @param string               $key   Setting name.
 * @param string               $label Field label.
 * @param string               $type  Field type.
 * @param array<string, mixed> $args  description, choices.
 * @return void
 */
function basalt_security_field( string $key, string $label, string $type = 'text', array $args = array() ): void {
	$name  = BASALT_SECURITY_OPTION . '[' . $key . ']';
	$id    = 'basalt-security-' . $key;
	$value = basalt_security_get( $key );
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<?php if ( 'checkbox' === $type ) : ?>
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( (bool) $value ); ?> />
			<?php elseif ( 'textarea' === $type ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text code"><?php echo esc_textarea( (string) $value ); ?></textarea>
			<?php elseif ( 'select' === $type ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( (array) ( $args['choices'] ?? array() ) as $choice => $choice_label ) : ?>
						<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $value, $choice ); ?>><?php echo esc_html( $choice_label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text" />
			<?php endif; ?>

			<?php if ( ! empty( $args['description'] ) ) : ?>
				<p class="description"><?php echo wp_kses( (string) $args['description'], array( 'code' => array(), 'strong' => array() ) ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render the page.
 *
 * @return void
 */
function basalt_security_render_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$slug = basalt_security_login_slug();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Security', 'basalt-security' ); ?></h1>

		<?php if ( $slug ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Your login page:', 'basalt-security' ); ?></strong>
					<code><?php echo esc_html( home_url( '/' . $slug . '/' ) ); ?></code>
				</p>
				<p>
					<?php esc_html_e( 'Bookmark it. The old address answers with a 404 for everybody, including you. If you ever lose it, put this line in wp-config.php and the usual address comes back:', 'basalt-security' ); ?>
					<code>define( 'BASALT_SECURITY_LOGIN_SLUG', '' );</code>
				</p>
			</div>
		<?php endif; ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'basalt_security' ); ?>

			<h2><?php esc_html_e( 'Login page', 'basalt-security' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Almost all login traffic on a small site is a bot posting to wp-login.php. Moving the page ends most of it, and what remains is worth reading.', 'basalt-security' ); ?></p>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field(
					'login_slug',
					__( 'Login address', 'basalt-security' ),
					'text',
					array( 'description' => __( 'One word, no slashes, for example <code>tueröffner</code>. Empty leaves the login page where it is.', 'basalt-security' ) )
				);
				basalt_security_field(
					'block_wp_admin',
					__( 'Hide wp-admin from visitors who are not signed in', 'basalt-security' ),
					'checkbox',
					array( 'description' => __( 'Answers 404 instead of redirecting to the login page, which would give the new address away. Only applies when a login address is set. admin-ajax.php and admin-post.php stay reachable.', 'basalt-security' ) )
				);
				?>
			</table>

			<h2><?php esc_html_e( 'Failed logins', 'basalt-security' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field( 'brute_force', __( 'Lock out after repeated failures', 'basalt-security' ), 'checkbox' );
				basalt_security_field( 'brute_limit', __( 'Attempts allowed', 'basalt-security' ), 'number', array( 'description' => __( 'Per address, not per account: locking an account is what an attacker wants.', 'basalt-security' ) ) );
				basalt_security_field( 'brute_minutes', __( 'First lockout in minutes', 'basalt-security' ), 'number', array( 'description' => __( 'Doubles with every further lockout from the same address, up to a day.', 'basalt-security' ) ) );
				basalt_security_field( 'brute_notify', __( 'Email me about lockouts', 'basalt-security' ), 'checkbox', array( 'description' => __( 'At most one message an hour, so an attacker cannot decide how much mail the site sends.', 'basalt-security' ) ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Accounts', 'basalt-security' ); ?></h2>
			<p class="description"><?php esc_html_e( 'The login form is watched above. These are the other three ways into an account: the password mail, the registration form, and whatever password somebody picks at the end of it.', 'basalt-security' ); ?></p>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field( 'limit_resets', __( 'Limit password reset requests', 'basalt-security' ), 'checkbox', array( 'description' => __( 'Every request sends mail to an address a stranger typed. Without a limit the form is a way to send a hundred messages from your site.', 'basalt-security' ) ) );
				basalt_security_field( 'reset_limit', __( 'Reset requests per hour', 'basalt-security' ), 'number', array( 'description' => __( 'Per address. Three is plenty for somebody who mistyped their email.', 'basalt-security' ) ) );
				basalt_security_field( 'guard_registration', __( 'Guard the registration form', 'basalt-security' ), 'checkbox', array( 'description' => __( 'A hidden field no person fills in, a limit per address, and an answer that does not confirm which addresses already have an account. Covers the WooCommerce form as well.', 'basalt-security' ) ) );
				basalt_security_field( 'registration_limit', __( 'Registrations per hour', 'basalt-security' ), 'number', array( 'description' => __( 'Per address. A household behind one connection rarely needs more than a handful.', 'basalt-security' ) ) );
				basalt_security_field( 'strong_passwords', __( 'Refuse obvious passwords', 'basalt-security' ), 'checkbox', array( 'description' => __( 'Length, and nothing built around the site name or the account name. No rule about capitals and digits: that produces Sommer2026! and nothing safer.', 'basalt-security' ) ) );
				basalt_security_field( 'magic_link', __( 'Offer a sign in link by email', 'basalt-security' ), 'checkbox', array( 'description' => __( 'A customer who signs in twice a year does not remember a password, resets it, and picks a worse one. A link proves the same thing the reset proves, in one step instead of four. Off by default, because it changes how people sign in.', 'basalt-security' ) ) );
				basalt_security_field( 'magic_for_staff', __( 'Links for accounts that can edit the site too', 'basalt-security' ), 'checkbox', array( 'description' => __( 'A link is exactly as strong as the inbox it lands in. That is the right trade for a customer and the wrong one for an editor.', 'basalt-security' ) ) );
				basalt_security_field( 'magic_limit', __( 'Links per hour', 'basalt-security' ), 'number', array( 'description' => __( 'Per address, like the other two.', 'basalt-security' ) ) );
				basalt_security_field( 'password_min', __( 'Shortest password allowed', 'basalt-security' ), 'number', array( 'description' => __( 'Twelve characters. A short sentence is easier to remember and much harder to guess than eight characters of noise.', 'basalt-security' ) ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Requests', 'basalt-security' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field(
					'firewall',
					__( 'Block obvious probes', 'basalt-security' ),
					'checkbox',
					array( 'description' => __( 'A short list of patterns: traversal, union select, script tags in a URL, requests for .env or an old phpunit runner. The URL is always checked, a submitted form only for visitors who are not signed in.', 'basalt-security' ) )
				);
				basalt_security_field(
					'allow_ips',
					__( 'Addresses that skip all of this', 'basalt-security' ),
					'textarea',
					array( 'description' => __( 'One per line. Your office, a monitoring service. Anything that is not a valid address is dropped when you save.', 'basalt-security' ) )
				);
				?>
			</table>

			<h2><?php esc_html_e( 'Headers', 'basalt-security' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field( 'headers', __( 'Send security headers', 'basalt-security' ), 'checkbox', array( 'description' => __( 'nosniff, a referrer policy, and a permissions policy that switches off camera, microphone and location.', 'basalt-security' ) ) );
				basalt_security_field(
					'frame_options',
					__( 'Allow the site in a frame', 'basalt-security' ),
					'select',
					array(
						'choices'     => array(
							'SAMEORIGIN' => __( 'Only on this site', 'basalt-security' ),
							'DENY'       => __( 'Nowhere', 'basalt-security' ),
							'off'        => __( 'Anywhere, send no header', 'basalt-security' ),
						),
						'description' => __( 'Stops another site loading yours invisibly and collecting clicks.', 'basalt-security' ),
					)
				);
				basalt_security_field(
					'content_policy',
					__( 'Content security policy', 'basalt-security' ),
					'checkbox',
					array( 'description' => __( 'A narrow one: no plugins, no rewritten base URL, forms only to this site. Deliberately says nothing about scripts, because a script policy needs a nonce per response and breaks the moment a page is cached.', 'basalt-security' ) )
				);
				basalt_security_field(
					'hsts',
					__( 'Require HTTPS in the browser (HSTS)', 'basalt-security' ),
					'checkbox',
					array( 'description' => __( 'Only switch this on once HTTPS works everywhere: a browser that has seen the header refuses plain HTTP for six months, whatever the site says afterwards.', 'basalt-security' ) )
				);
				?>
			</table>

			<h2><?php esc_html_e( 'Hardening', 'basalt-security' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				basalt_security_field( 'disable_xmlrpc', __( 'Switch off XML-RPC', 'basalt-security' ), 'checkbox', array( 'description' => __( 'It answers before authentication and lets one request try hundreds of passwords. Leave it on only if an app still posts through it.', 'basalt-security' ) ) );
				basalt_security_field( 'disable_enumeration', __( 'Do not list user names', 'basalt-security' ), 'checkbox', array( 'description' => __( 'Closes ?author=1, the REST users endpoint and the users sitemap for visitors who are not signed in.', 'basalt-security' ) ) );
				basalt_security_field( 'disable_file_edit', __( 'No theme and plugin editor', 'basalt-security' ), 'checkbox', array( 'description' => __( 'A stolen password should not come with a code editor.', 'basalt-security' ) ) );
				basalt_security_field( 'hide_version', __( 'Keep the version out of the markup', 'basalt-security' ), 'checkbox' );
				basalt_security_field( 'limit_app_passwords', __( 'Application passwords for administrators only', 'basalt-security' ), 'checkbox', array( 'description' => __( 'On a shop, every customer account would otherwise be a possible long lived token.', 'basalt-security' ) ) );
				?>
			</table>

			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'What this installation still leaves open', 'basalt-security' ); ?></h2>
		<?php basalt_security_render_review(); ?>

		<h2><?php esc_html_e( 'What happened', 'basalt-security' ); ?></h2>
		<?php basalt_security_render_log(); ?>
	</div>
	<?php
}

/**
 * The log table.
 *
 * @return void
 */
function basalt_security_render_log(): void {
	$log = basalt_security_log_read();

	if ( ! $log ) {
		echo '<p class="description">' . esc_html__( 'Nothing yet. Lockouts, blocked requests and sign-ins show up here.', 'basalt-security' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr>';
	echo '<th>' . esc_html__( 'When', 'basalt-security' ) . '</th>';
	echo '<th>' . esc_html__( 'What', 'basalt-security' ) . '</th>';
	echo '<th>' . esc_html__( 'Address', 'basalt-security' ) . '</th>';
	echo '<th>' . esc_html__( 'Detail', 'basalt-security' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( array_slice( $log, 0, 50 ) as $entry ) {
		printf(
			'<tr><td>%1$s</td><td><code>%2$s</code></td><td>%3$s</td><td>%4$s<br><span class="description">%5$s</span></td></tr>',
			esc_html( wp_date( (string) get_option( 'date_format' ) . ' ' . (string) get_option( 'time_format' ), (int) $entry['time'] ) ),
			esc_html( (string) $entry['type'] ),
			esc_html( (string) $entry['ip'] ),
			esc_html( (string) $entry['note'] ),
			esc_html( (string) ( $entry['path'] ?? '' ) )
		);
	}

	echo '</tbody></table>';

	printf(
		'<p><a class="button" href="%1$s">%2$s</a></p>',
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=basalt_security_clear_log' ), 'basalt_security_clear_log' ) ),
		esc_html__( 'Clear the log', 'basalt-security' )
	);
}

/**
 * Empty the log.
 *
 * @return void
 */
function basalt_security_handle_clear_log(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You cannot do that.', 'basalt-security' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'basalt_security_clear_log' );
	basalt_security_log_clear();

	wp_safe_redirect( admin_url( 'options-general.php?page=basalt-security' ) );
	exit;
}
add_action( 'admin_post_basalt_security_clear_log', 'basalt_security_handle_clear_log' );

/**
 * A link to the settings from the plugin list.
 *
 * @param string[] $links The action links.
 * @return string[]
 */
function basalt_security_action_link( $links ) {
	array_unshift(
		$links,
		sprintf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( 'options-general.php?page=basalt-security' ) ), esc_html__( 'Settings', 'basalt-security' ) )
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( BASALT_SECURITY_DIR . 'basalt-security.php' ), 'basalt_security_action_link' );
