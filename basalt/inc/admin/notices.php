<?php
/**
 * Admin notices.
 *
 * One dismissible notice after activation, nothing else. A theme that nags on
 * every screen is the fastest way to a one star review.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * User meta key that records the dismissal.
 */
const BASALT_WELCOME_DISMISSED = 'basalt_welcome_dismissed';

/**
 * Show the welcome notice once per user.
 *
 * @return void
 */
function basalt_welcome_notice(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard' ), true ) ) {
		return;
	}

	if ( get_user_meta( get_current_user_id(), BASALT_WELCOME_DISMISSED, true ) ) {
		return;
	}

	$dismiss_url = wp_nonce_url(
		add_query_arg( 'basalt-dismiss-welcome', '1' ),
		'basalt_dismiss_welcome'
	);
	?>
	<div class="notice notice-info">
		<p>
			<strong><?php esc_html_e( 'Basalt is active.', 'basalt' ); ?></strong>
			<?php esc_html_e( 'Three short steps get the site to a solid starting point.', 'basalt' ); ?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'themes.php?page=basalt' ) ); ?>">
				<?php esc_html_e( 'Getting started', 'basalt' ); ?>
			</a>
			<a class="button-link" href="<?php echo esc_url( $dismiss_url ); ?>">
				<?php esc_html_e( 'Dismiss', 'basalt' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'basalt_welcome_notice' );

/**
 * Record the dismissal.
 *
 * @return void
 */
function basalt_dismiss_welcome_notice(): void {
	if ( ! isset( $_GET['basalt-dismiss-welcome'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	check_admin_referer( 'basalt_dismiss_welcome' );

	update_user_meta( get_current_user_id(), BASALT_WELCOME_DISMISSED, 1 );

	wp_safe_redirect( remove_query_arg( array( 'basalt-dismiss-welcome', '_wpnonce' ) ) );
	exit;
}
add_action( 'admin_init', 'basalt_dismiss_welcome_notice' );

/**
 * Warn when the site is set to discourage search engines.
 *
 * On an SEO focused theme this is worth surfacing: the setting is commonly left
 * on after a launch and silently costs the site every ranking it would have had.
 *
 * @return void
 */
function basalt_search_engine_visibility_notice(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'blog_public' ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'appearance_page_basalt' ), true ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Search engines are blocked.', 'basalt' ),
		esc_html__( 'This site is set to discourage indexing, so none of the SEO output has any effect.', 'basalt' ),
		esc_url( admin_url( 'options-reading.php' ) ),
		esc_html__( 'Change it in Reading settings', 'basalt' )
	);
}
add_action( 'admin_notices', 'basalt_search_engine_visibility_notice' );
