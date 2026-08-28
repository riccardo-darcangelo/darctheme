<?php
/**
 * "Getting started" screen under Appearance.
 *
 * Deliberately installs nothing by itself. Every action links into a core
 * screen, so WordPress performs it with the user's own permissions.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the screen.
 *
 * @return void
 */
function basalt_register_theme_page(): void {
	add_theme_page(
		__( 'Basalt', 'basalt' ),
		__( 'Basalt', 'basalt' ),
		'edit_theme_options',
		'basalt',
		'basalt_render_theme_page'
	);
}
add_action( 'admin_menu', 'basalt_register_theme_page' );

/**
 * Plugins the theme integrates with.
 *
 * @return array<int, array{slug: string, name: string, why: string, required: bool}>
 */
function basalt_recommended_plugins(): array {
	/**
	 * Filter the recommended plugin list.
	 *
	 * @param array<int, array<string, mixed>> $plugins Recommended plugins.
	 */
	return (array) apply_filters(
		'basalt_recommended_plugins',
		array(
			array(
				'slug'     => 'basalt-core',
				'name'     => 'Basalt Core',
				'why'      => __( 'Structured data, meta tags, breadcrumbs and the accessibility corrections for core blocks. Ships with the theme. Without it the templates still render, but the breadcrumb block is missing and no structured data is emitted.', 'basalt' ),
				'required' => true,
			),
			array(
				'slug'     => 'seo-by-rank-math',
				'name'     => 'Rank Math SEO',
				'why'      => __( 'Editorial control over titles, descriptions and redirects. Basalt Core detects it and steps out of the way, so nothing is emitted twice.', 'basalt' ),
				'required' => false,
			),
			array(
				'slug'     => 'fluentform',
				'name'     => 'Fluent Forms',
				'why'      => __( 'Contact and enquiry forms. Styled by the theme out of the box, and it loads its assets only on pages that contain a form.', 'basalt' ),
				'required' => false,
			),
			array(
				'slug'     => 'performant-translations',
				'name'     => 'Performant Translations',
				'why'      => __( 'Faster translation loading on non-English sites. Measurable on every request.', 'basalt' ),
				'required' => false,
			),
		)
	);
}

/**
 * Render the screen.
 *
 * @return void
 */
function basalt_render_theme_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$theme = wp_get_theme( get_template() );
	?>
	<div class="wrap basalt-admin">
		<h1>
			<?php
			printf(
				/* translators: %s: theme version. */
				esc_html__( 'Basalt %s', 'basalt' ),
				esc_html( (string) $theme->get( 'Version' ) )
			);
			?>
		</h1>

		<p class="basalt-admin__intro">
			<?php esc_html_e( 'Four things get a new site to a good starting point. Everything else is optional.', 'basalt' ); ?>
		</p>

		<div class="basalt-admin__grid">
			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '1. Pick a style', 'basalt' ); ?></h2>
				<p>
					<?php esc_html_e( 'Basalt ships four complete looks, including a high contrast variation where every colour clears WCAG AAA. Switching one changes colours, type and spacing everywhere at once.', 'basalt' ); ?>
				</p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fwp_global_styles' ) ); ?>">
						<?php esc_html_e( 'Open styles', 'basalt' ); ?>
					</a>
				</p>
			</div>

			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '2. Identity', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Set the site title, tagline and logo. The tagline is the fallback meta description, so write it as a sentence a search engine could show.', 'basalt' ); ?></p>
				<p>
					<a class="button" href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">
						<?php esc_html_e( 'Site settings', 'basalt' ); ?>
					</a>
				</p>
			</div>

			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '3. Header and footer', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Both are template parts. Edit them once in the site editor and every template follows, including the navigation menu.', 'basalt' ); ?></p>
				<p>
					<a class="button" href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fpatterns' ) ); ?>">
						<?php esc_html_e( 'Edit template parts', 'basalt' ); ?>
					</a>
				</p>
			</div>

			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '4. Who is behind the site', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Business name, address, phone and opening hours. This is what produces the knowledge panel and local results.', 'basalt' ); ?></p>
				<p>
					<?php if ( defined( 'BASALT_CORE_VERSION' ) ) : ?>
						<a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=basalt-core' ) ); ?>">
							<?php esc_html_e( 'Search and schema settings', 'basalt' ); ?>
						</a>
					<?php else : ?>
						<em><?php esc_html_e( 'Install and activate Basalt Core to reach these settings.', 'basalt' ); ?></em>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Design tokens', 'basalt' ); ?></h2>
		<p>
			<?php esc_html_e( 'Colours, the type scale and the spacing steps come from theme.json. Change them under Styles and the front end, the editor, the patterns and the block styles all follow. A child theme can override the same file for a permanent change.', 'basalt' ); ?>
		</p>

		<h2><?php esc_html_e( 'Plugins', 'basalt' ); ?></h2>
		<table class="widefat basalt-admin__plugins">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Plugin', 'basalt' ); ?></th>
					<th scope="col"><?php esc_html_e( 'What it adds', 'basalt' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'basalt' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( basalt_recommended_plugins() as $plugin ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
						<?php if ( ! empty( $plugin['required'] ) ) : ?>
							<br><span class="basalt-admin__status"><?php esc_html_e( 'Bundled', 'basalt' ); ?></span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $plugin['why'] ); ?></td>
					<td><?php basalt_render_plugin_action( $plugin['slug'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Render the install or "already installed" state for one plugin.
 *
 * @param string $slug Plugin directory slug.
 * @return void
 */
function basalt_render_plugin_action( string $slug ): void {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	foreach ( array_keys( get_plugins() ) as $plugin_file ) {
		if ( ! str_starts_with( (string) $plugin_file, $slug . '/' ) ) {
			continue;
		}

		if ( is_plugin_active( $plugin_file ) ) {
			printf( '<span class="basalt-admin__status is-active">%s</span>', esc_html__( 'Active', 'basalt' ) );

			return;
		}

		/*
		 * The screen itself only requires edit_theme_options, which does not
		 * imply the right to activate a plugin. On multisite a site
		 * administrator has the former and not the latter unless the network
		 * allows it. Core would reject the request anyway, so this is not a
		 * privilege boundary; offering a link that leads to a permission error
		 * is simply a bug.
		 */
		if ( ! current_user_can( 'activate_plugin', $plugin_file ) ) {
			printf( '<span class="basalt-admin__status">%s</span>', esc_html__( 'Installed, not active', 'basalt' ) );

			return;
		}

		printf(
			'<a href="%1$s">%2$s</a>',
			esc_url(
				wp_nonce_url(
					self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_file ) ),
					'activate-plugin_' . $plugin_file
				)
			),
			esc_html__( 'Activate', 'basalt' )
		);

		return;
	}

	if ( ! current_user_can( 'install_plugins' ) ) {
		printf( '<span class="basalt-admin__status">%s</span>', esc_html__( 'Not installed', 'basalt' ) );

		return;
	}

	printf(
		'<a class="thickbox open-plugin-details-modal" href="%1$s">%2$s</a>',
		esc_url(
			self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . rawurlencode( $slug ) . '&TB_iframe=true&width=772&height=800' )
		),
		esc_html__( 'View and install', 'basalt' )
	);
}

/**
 * Styles and the thickbox dependency for the screen.
 *
 * @param string $hook Current admin screen hook.
 * @return void
 */
function basalt_theme_page_assets( $hook ): void {
	if ( 'appearance_page_basalt' !== $hook ) {
		return;
	}

	add_thickbox();

	wp_enqueue_style(
		'basalt-admin',
		BASALT_URI . 'assets/css/admin.css',
		array(),
		basalt_asset_version( 'assets/css/admin.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'basalt_theme_page_assets' );

/**
 * Link the screen from the theme card on Appearance > Themes.
 *
 * @param string[] $actions Theme action links.
 * @return string[]
 */
function basalt_theme_action_links( $actions ) {
	$actions               = (array) $actions;
	$actions['basalt-start'] = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url( 'themes.php?page=basalt' ) ),
		esc_html__( 'Getting started', 'basalt' )
	);

	return $actions;
}
add_filter( 'theme_action_links_' . get_template(), 'basalt_theme_action_links' );
