<?php
/**
 * Maintenance mode.
 *
 * A switch, three texts, and a real 503. Everything a site needs while it is
 * being worked on, and the reason a site does not need one of the maintenance
 * plugins that ship a page builder to draw a countdown.
 *
 * Why this is in the plugin and not the theme: being unreachable is something
 * the site does, not something it looks like. The look comes from a block
 * template named "maintenance" in the active theme, so a site can design the
 * page with the same blocks as every other page. Without such a template the
 * plugin prints a plain, readable fallback.
 *
 * Three things this gets right that a hand rolled "coming soon" page usually
 * does not:
 *
 * - The status is 503 with a Retry-After header, so a search engine treats the
 *   outage as temporary and keeps the pages it has indexed. A 200 with a
 *   "back soon" page invites Google to index that text instead.
 * - Editors keep browsing the site while visitors see the notice, which is the
 *   only way to check the work behind the curtain.
 * - Nothing is cached: the headers say so explicitly, because a maintenance
 *   page cached at the edge outlives the maintenance.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the notice should replace the page for the current request.
 *
 * @return bool
 */
function basalt_core_maintenance_active(): bool {
	if ( ! basalt_core_get( 'maintenance_enabled' ) ) {
		return false;
	}

	/**
	 * Filter who walks past the maintenance page.
	 *
	 * Anyone who may edit posts, by default: the people who need to look at
	 * the site while it is being worked on.
	 *
	 * @param bool $bypass Whether the current user sees the real site.
	 */
	return ! (bool) apply_filters( 'basalt_core_maintenance_bypass', current_user_can( 'edit_posts' ) );
}

/**
 * Seconds until the announced time, for the Retry-After header.
 *
 * The setting is a time of day, so "18:00" entered in the morning means today
 * and entered in the evening means tomorrow. An hour when nothing is set.
 *
 * @return int
 */
function basalt_core_maintenance_retry_after(): int {
	$until = (string) basalt_core_get( 'maintenance_until' );

	if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $until, $m ) ) {
		return HOUR_IN_SECONDS;
	}

	$now    = new DateTimeImmutable( 'now', wp_timezone() );
	$target = $now->setTime( (int) $m[1], (int) $m[2] );

	if ( $target <= $now ) {
		$target = $target->modify( '+1 day' );
	}

	return max( 60, $target->getTimestamp() - $now->getTimestamp() );
}

/**
 * The announced time, formatted for reading.
 *
 * @return string
 */
function basalt_core_maintenance_until_text(): string {
	$until = (string) basalt_core_get( 'maintenance_until' );

	if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $until, $m ) ) {
		return '';
	}

	$time = ( new DateTimeImmutable( 'today', wp_timezone() ) )->setTime( (int) $m[1], (int) $m[2] );

	/**
	 * Filter the formatted time shown on the maintenance page.
	 *
	 * @param string $text  The formatted time.
	 * @param string $until The raw setting, HH:MM.
	 */
	return (string) apply_filters(
		'basalt_core_maintenance_until',
		wp_date( (string) get_option( 'time_format', 'H:i' ), $time->getTimestamp() ),
		$until
	);
}

/**
 * One of the three texts.
 *
 * @param string $field headline, message or until.
 * @return string
 */
function basalt_core_maintenance_text( string $field ): string {
	if ( 'until' === $field ) {
		return basalt_core_maintenance_until_text();
	}

	$value = (string) basalt_core_get( 'maintenance_' . $field );

	if ( '' !== $value ) {
		return $value;
	}

	return 'headline' === $field ? __( 'We will be back shortly', 'basalt-core' ) : '';
}

/**
 * Serve the maintenance page instead of the requested one.
 *
 * @param string $template Template file WordPress resolved.
 * @return string
 */
function basalt_core_maintenance_template( $template ) {
	if ( ! basalt_core_maintenance_active() ) {
		return $template;
	}

	status_header( 503 );
	nocache_headers();
	header( 'Retry-After: ' . basalt_core_maintenance_retry_after() );
	add_filter( 'wp_robots', 'wp_robots_no_robots' );

	$block_template = null;

	if ( wp_is_block_theme() ) {
		foreach ( array_unique( array( get_stylesheet(), get_template() ) ) as $theme ) {
			$block_template = get_block_template( $theme . '//maintenance', 'wp_template' );

			if ( $block_template && ! empty( $block_template->content ) ) {
				break;
			}

			$block_template = null;
		}
	}

	if ( $block_template ) {
		/*
		 * These two globals are what template-canvas.php renders. Setting them
		 * is how a block theme serves a template that is not part of the
		 * template hierarchy.
		 */
		$GLOBALS['_wp_current_template_id']      = $block_template->id;
		$GLOBALS['_wp_current_template_content'] = $block_template->content;

		return ABSPATH . WPINC . '/template-canvas.php';
	}

	basalt_core_maintenance_fallback();
}
add_filter( 'template_include', 'basalt_core_maintenance_template', 999 );

/**
 * A readable page for a theme that has no maintenance template.
 *
 * Deliberately plain: no assets, no fonts, nothing that could fail while the
 * site is half assembled.
 *
 * @return void
 */
function basalt_core_maintenance_fallback(): void {
	$headline = basalt_core_maintenance_text( 'headline' );
	$message  = basalt_core_maintenance_text( 'message' );
	$until    = basalt_core_maintenance_until_text();
	$phone    = (string) basalt_core_get( 'phone' );

	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo esc_html( $headline . ' | ' . get_bloginfo( 'name' ) ); ?></title>
		<style>
			body { margin: 0; display: grid; place-items: center; min-height: 100vh; padding: 2rem; background: #16191d; color: #ffffff; font: 1rem/1.6 system-ui, -apple-system, "Segoe UI", sans-serif; }
			main { max-width: 34rem; }
			h1 { font-size: clamp(1.75rem, 5vw, 2.75rem); line-height: 1.15; margin: 0 0 1rem; }
			p { margin: 0 0 1rem; }
			a { color: inherit; }
		</style>
	</head>
	<body>
		<main>
			<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
			<h1><?php echo esc_html( $headline ); ?></h1>
			<?php if ( $message ) : ?>
				<p><?php echo esc_html( $message ); ?></p>
			<?php endif; ?>
			<?php if ( $until ) : ?>
				<p><?php printf( /* translators: %s: time of day. */ esc_html__( 'Expected back at %s.', 'basalt-core' ), esc_html( $until ) ); ?></p>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
			<?php endif; ?>
		</main>
	</body>
	</html>
	<?php
	exit;
}

/**
 * Render callback for the maintenance text block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_core_maintenance_block( $attributes ): string {
	$attributes = wp_parse_args(
		(array) $attributes,
		array(
			'field'  => 'headline',
			'level'  => 0,
			'prefix' => '',
			'suffix' => '',
		)
	);

	$field = in_array( $attributes['field'], array( 'headline', 'message', 'until' ), true ) ? $attributes['field'] : 'headline';
	$text  = basalt_core_maintenance_text( $field );

	if ( '' === $text ) {
		return '';
	}

	$level = (int) $attributes['level'];
	$tag   = $level >= 1 && $level <= 6 ? 'h' . $level : 'p';

	return sprintf(
		'<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
		$tag,
		get_block_wrapper_attributes( array( 'class' => 'basalt-maintenance-text is-field-' . $field ) ),
		esc_html( (string) $attributes['prefix'] ),
		esc_html( $text ),
		esc_html( (string) $attributes['suffix'] )
	);
}

/**
 * Remind whoever is logged in that visitors cannot see the site.
 *
 * @return void
 */
function basalt_core_maintenance_notice(): void {
	if ( ! basalt_core_get( 'maintenance_enabled' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Maintenance mode is on. Visitors get the maintenance page and a 503 status; you and anyone who can edit posts see the real site.', 'basalt-core' ),
		esc_url( admin_url( 'options-general.php?page=basalt-core#basalt-core-maintenance' ) ),
		esc_html__( 'Turn it off', 'basalt-core' )
	);
}
add_action( 'admin_notices', 'basalt_core_maintenance_notice' );
