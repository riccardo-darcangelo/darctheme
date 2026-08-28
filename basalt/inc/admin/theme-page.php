<?php
/**
 * "Getting started" screen under Appearance.
 *
 * Deliberately does not install anything by itself. The previous generation of
 * this theme downloaded plugin ZIPs over cURL and wrote to the active_plugins
 * option directly, which bypasses capability checks, filesystem abstraction and
 * signature handling. Everything here links into the core plugin installer, so
 * WordPress performs the install with the user's own permissions.
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
 * None of them are required; the theme is fully functional without any of them.
 *
 * @return array<int, array{slug: string, name: string, why: string}>
 */
function basalt_recommended_plugins(): array {
	/**
	 * Filter the recommended plugin list.
	 *
	 * @param array<int, array{slug: string, name: string, why: string}> $plugins Recommended plugins.
	 */
	return (array) apply_filters(
		'basalt_recommended_plugins',
		array(
			array(
				'slug' => 'seo-by-rank-math',
				'name' => 'Rank Math SEO',
				'why'  => __( 'Editorial control over titles, descriptions and redirects. Basalt detects it and steps out of the way for meta tags, structured data and breadcrumbs.', 'basalt' ),
			),
			array(
				'slug' => 'fluentform',
				'name' => 'Fluent Forms',
				'why'  => __( 'Contact and enquiry forms. Styled by the theme out of the box, and it loads its assets only on pages that contain a form.', 'basalt' ),
			),
			array(
				'slug' => 'advanced-custom-fields',
				'name' => 'Advanced Custom Fields',
				'why'  => __( 'Structured fields for custom post types, for example technical specifications on a product page.', 'basalt' ),
			),
			array(
				'slug' => 'performant-translations',
				'name' => 'Performant Translations',
				'why'  => __( 'Faster translation loading on non-English sites. Measurable on every request.', 'basalt' ),
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
			<?php esc_html_e( 'Three things get a new site to a good starting point. Everything else is optional.', 'basalt' ); ?>
		</p>

		<div class="basalt-admin__grid">
			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '1. Identity', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Upload the logo and set the site title and tagline. The tagline is used as the fallback meta description, so write it as a sentence a search engine could show.', 'basalt' ); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>">
						<?php esc_html_e( 'Open site identity', 'basalt' ); ?>
					</a>
				</p>
			</div>

			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '2. Structured data', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Tell search engines who is behind the site: business name, address, phone and opening hours. This is what produces the knowledge panel and local results.', 'basalt' ); ?></p>
				<p>
					<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=basalt_schema' ) ); ?>">
						<?php esc_html_e( 'Open structured data', 'basalt' ); ?>
					</a>
				</p>
			</div>

			<div class="basalt-admin__card">
				<h2><?php esc_html_e( '3. Menus', 'basalt' ); ?></h2>
				<p><?php esc_html_e( 'Assign a primary menu. Until you do, the header lists your top level pages so the site stays navigable.', 'basalt' ); ?></p>
				<p>
					<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">
						<?php esc_html_e( 'Open menus', 'basalt' ); ?>
					</a>
				</p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Design tokens', 'basalt' ); ?></h2>
		<p>
			<?php esc_html_e( 'Colours, the type scale and the spacing steps are defined once in theme.json and apply to the front end, the block editor, the patterns and the block styles at the same time.', 'basalt' ); ?>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: the theme.json file name, wrapped in a code element. */
				esc_html__( 'Basalt uses classic PHP templates, so WordPress does not offer the site editor here and there is no visual panel for these values. Edit %s in a child theme; the block editor picks the change up immediately.', 'basalt' ),
				'<code>theme.json</code>'
			);
			?>
		</p>

		<h2><?php esc_html_e( 'Plugins that fit', 'basalt' ); ?></h2>
		<p><?php esc_html_e( 'Basalt requires none of these. Install what the project actually needs.', 'basalt' ); ?></p>

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
					<td><strong><?php echo esc_html( $plugin['name'] ); ?></strong></td>
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
 * @param string $slug WordPress.org plugin slug.
 * @return void
 */
function basalt_render_plugin_action( string $slug ): void {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed = false;

	foreach ( array_keys( get_plugins() ) as $plugin_file ) {
		if ( str_starts_with( (string) $plugin_file, $slug . '/' ) ) {
			$installed = is_plugin_active( $plugin_file );

			if ( ! $installed ) {
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

			printf( '<span class="basalt-admin__status is-active">%s</span>', esc_html__( 'Active', 'basalt' ) );

			return;
		}
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
	$actions = (array) $actions;

	$actions['basalt-start'] = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url( 'themes.php?page=basalt' ) ),
		esc_html__( 'Getting started', 'basalt' )
	);

	return $actions;
}
add_filter( 'theme_action_links_' . get_template(), 'basalt_theme_action_links' );
