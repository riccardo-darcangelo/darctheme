<?php
/**
 * What this installation still leaves open.
 *
 * A settings screen shows what the plugin does. It says nothing about the
 * things that are wrong before the plugin is involved: a site still on plain
 * HTTP, PHP errors printed into the page, an account actually called admin,
 * a writable wp-config.php, a debug log sitting in a public folder.
 *
 * None of it is fixed here. Half of these are decisions that belong to the
 * host or to wp-config.php, and a plugin that silently changes them is a
 * plugin that breaks a site at three in the morning. This lists them, says
 * why each one matters and what to do, and then gets out of the way.
 *
 * @package BasaltSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * The checks, as rows.
 *
 * @return array<int, array{state: string, title: string, note: string}>
 */
function basalt_security_review(): array {
	$rows = array();

	$rows[] = array(
		'state' => str_starts_with( (string) get_option( 'home' ), 'https://' ) ? 'ok' : 'bad',
		'title' => __( 'The site runs on HTTPS', 'basalt-security' ),
		'note'  => __( 'Without it every password and every session cookie travels in plain text. Certificates are free and every host offers them.', 'basalt-security' ),
	);

	$rows[] = array(
		'state' => ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) ? 'ok' : 'warn',
		'title' => __( 'The admin area requires HTTPS', 'basalt-security' ),
		'note'  => __( 'Add <code>define( \'FORCE_SSL_ADMIN\', true );</code> to wp-config.php once the certificate works.', 'basalt-security' ),
	);

	$debug = defined( 'WP_DEBUG' ) && WP_DEBUG && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY );

	$rows[] = array(
		'state' => $debug ? 'bad' : 'ok',
		'title' => __( 'PHP notices are not printed into the page', 'basalt-security' ),
		'note'  => __( 'An error message names file paths, table prefixes and plugin versions. On a live site set <code>WP_DEBUG_DISPLAY</code> to false and log to a file instead.', 'basalt-security' ),
	);

	$log_file = WP_CONTENT_DIR . '/debug.log';

	$rows[] = array(
		'state' => file_exists( $log_file ) ? 'warn' : 'ok',
		'title' => __( 'No debug log in a public folder', 'basalt-security' ),
		'note'  => __( 'wp-content/debug.log can be downloaded by anybody who guesses the name, and it is full of paths and queries. Delete it, or move the log outside the web root.', 'basalt-security' ),
	);

	$rows[] = array(
		'state' => get_user_by( 'login', 'admin' ) ? 'warn' : 'ok',
		'title' => __( 'No account called admin', 'basalt-security' ),
		'note'  => __( 'It is the name every script tries first, which halves the work of guessing. Create a second administrator with a different name, sign in as that one and delete this account, handing its content over.', 'basalt-security' ),
	);

	$rows[] = array(
		'state' => is_writable( ABSPATH . 'wp-config.php' ) ? 'warn' : 'ok',
		'title' => __( 'wp-config.php cannot be written to', 'basalt-security' ),
		'note'  => __( 'It holds the database password and the keys that sign every session. 0444 or 0440 is enough for WordPress to read it.', 'basalt-security' ),
	);

	$rows[] = array(
		'state' => ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ? 'ok' : 'warn',
		'title' => __( 'The file editor is off', 'basalt-security' ),
		'note'  => __( 'The Hardening switch above does this for you. Setting it in wp-config.php as well means it also holds while this plugin is being updated.', 'basalt-security' ),
	);

	if ( get_option( 'users_can_register' ) ) {
		$role = get_role( (string) get_option( 'default_role' ) );
		$high = $role && ( $role->has_cap( 'edit_posts' ) || $role->has_cap( 'upload_files' ) );

		$rows[] = array(
			'state' => $high ? 'bad' : 'warn',
			'title' => __( 'Anybody can register', 'basalt-security' ),
			'note'  => $high
				? __( 'And new accounts may write posts or upload files. Set the default role to Subscriber, or switch registration off, under Settings, General.', 'basalt-security' )
				: __( 'That is normal for a shop. The registration form is rate limited and does not confirm which addresses already exist. Switch it off under Settings, General if the site does not need accounts.', 'basalt-security' ),
		);
	}

	$rows[] = array(
		'state' => version_compare( PHP_VERSION, '8.1', '>=' ) ? 'ok' : 'bad',
		'title' => sprintf(
			/* translators: %s: PHP version */
			__( 'PHP is still receiving security fixes (running %s)', 'basalt-security' ),
			PHP_VERSION
		),
		'note'  => __( 'Older versions get no fixes at all, whatever the host says. Every host has a switch for this in its panel.', 'basalt-security' ),
	);

	$slug = (string) basalt_security_login_slug();

	$rows[] = array(
		'state' => '' !== $slug ? 'ok' : 'warn',
		'title' => __( 'The login page is not at wp-login.php', 'basalt-security' ),
		'note'  => __( 'Optional, and it is not a lock: it is the doormat the bots stand on. Almost all of them only ever try the one address.', 'basalt-security' ),
	);

	/**
	 * Filter the review rows.
	 *
	 * @param array<int, array{state: string, title: string, note: string}> $rows The rows.
	 */
	return (array) apply_filters( 'basalt_security_review', $rows );
}

/**
 * Render the review as a table.
 *
 * @return void
 */
function basalt_security_render_review(): void {
	$rows  = basalt_security_review();
	$open  = count( array_filter( $rows, static fn( $row ): bool => 'ok' !== $row['state'] ) );
	$marks = array(
		'ok'   => array( '&#10003;', '#116329' ),
		'warn' => array( '!', '#8a6100' ),
		'bad'  => array( '&#10007;', '#a3161b' ),
	);

	printf(
		'<p class="description">%s</p>',
		esc_html(
			$open
				/* translators: %d: number of open points */
				? sprintf( _n( '%d point is worth looking at. None of it is changed automatically.', '%d points are worth looking at. None of it is changed automatically.', $open, 'basalt-security' ), $open )
				: __( 'Nothing open. This list is checked when the page loads, so it stays honest.', 'basalt-security' )
		)
	);

	echo '<table class="widefat striped"><tbody>';

	foreach ( $rows as $row ) {
		$mark = $marks[ $row['state'] ] ?? $marks['warn'];

		printf(
			'<tr><td style="width:2em;text-align:center;color:%1$s;font-weight:700">%2$s</td><td><strong>%3$s</strong><br><span class="description">%4$s</span></td></tr>',
			esc_attr( $mark[1] ),
			wp_kses( $mark[0], array() ),
			esc_html( $row['title'] ),
			wp_kses( $row['note'], array( 'code' => array(), 'strong' => array() ) )
		);
	}

	echo '</tbody></table>';
}
