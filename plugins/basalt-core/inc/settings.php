<?php
/**
 * Settings: one option array, one settings screen.
 *
 * Stored as a single option rather than as theme mods, so the data belongs to
 * the site and survives a theme change. That is the whole reason this lives in
 * a plugin.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

const BASALT_CORE_OPTION = 'basalt_core_settings';

/**
 * Defaults for every setting.
 *
 * @return array<string, mixed>
 */
function basalt_core_defaults(): array {
	return array(
		'meta_enabled'       => true,
		'meta_default_image' => 0,
		'meta_twitter_site'  => '',
		'schema_enabled'     => true,
		'entity_type'        => 'Organization',
		'entity_name'        => '',
		'logo'               => 0,
		'phone'              => '',
		'email'              => '',
		'street'             => '',
		'postal_code'        => '',
		'city'               => '',
		'region'             => '',
		'country'            => '',
		'opening_hours'      => '',
		'price_range'        => '',
		'profiles'           => '',
		// Off by default: a floating control is the site owner's decision to make.
		'preferences_enabled'  => false,
		'preferences_position' => 'right',
		// Login screen branding, also opt in.
		'login_enabled'          => false,
		'login_logo'             => 0,
		'login_logo_width'       => 320,
		'login_background'       => '',
		'login_form_background'  => '',
		'login_accent'           => '',
		'login_background_image' => 0,
		'login_generic_errors'   => false,
	);
}

/**
 * Read one setting.
 *
 * @param string $key Setting name.
 * @return mixed
 */
function basalt_core_get( string $key ) {
	static $settings = null;

	if ( null === $settings ) {
		$settings = wp_parse_args( (array) get_option( BASALT_CORE_OPTION, array() ), basalt_core_defaults() );
	}

	return $settings[ $key ] ?? null;
}

/**
 * Schema.org types offered for the site entity.
 *
 * @return array<string, string>
 */
function basalt_core_entity_types(): array {
	/**
	 * Filter the selectable Schema.org entity types.
	 *
	 * @param array<string, string> $types Label keyed by Schema.org type.
	 */
	return (array) apply_filters(
		'basalt_core_entity_types',
		array(
			'Organization'                => __( 'Organization', 'basalt-core' ),
			'LocalBusiness'               => __( 'Local business', 'basalt-core' ),
			'HomeAndConstructionBusiness' => __( 'Construction or trade business', 'basalt-core' ),
			'ProfessionalService'         => __( 'Professional service', 'basalt-core' ),
			'Store'                       => __( 'Store', 'basalt-core' ),
			'ClothingStore'               => __( 'Clothing or fashion store', 'basalt-core' ),
			'Person'                      => __( 'Person', 'basalt-core' ),
		)
	);
}

/**
 * Register the option and its sanitizer.
 *
 * @return void
 */
function basalt_core_register_settings(): void {
	register_setting(
		'basalt_core',
		BASALT_CORE_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'basalt_core_sanitize',
			'default'           => basalt_core_defaults(),
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'basalt_core_register_settings' );

/**
 * Sanitize the whole option array.
 *
 * @param mixed $input Raw submitted value.
 * @return array<string, mixed>
 */
function basalt_core_sanitize( $input ): array {
	$input  = (array) $input;
	$out    = basalt_core_defaults();
	$lines  = static function ( $value ): string {
		$parts = preg_split( '/\R/', (string) $value ) ?: array();

		return implode( "\n", array_values( array_filter( array_map( 'trim', $parts ) ) ) );
	};

	$out['meta_enabled']   = ! empty( $input['meta_enabled'] );
	$out['schema_enabled'] = ! empty( $input['schema_enabled'] );

	$out['meta_default_image'] = absint( $input['meta_default_image'] ?? 0 );
	$out['logo']               = absint( $input['logo'] ?? 0 );

	$out['meta_twitter_site'] = ltrim( sanitize_text_field( (string) ( $input['meta_twitter_site'] ?? '' ) ), '@' );

	$type              = (string) ( $input['entity_type'] ?? '' );
	$out['entity_type'] = array_key_exists( $type, basalt_core_entity_types() ) ? $type : 'Organization';

	foreach ( array( 'entity_name', 'phone', 'street', 'postal_code', 'city', 'region', 'price_range' ) as $key ) {
		$out[ $key ] = sanitize_text_field( (string) ( $input[ $key ] ?? '' ) );
	}

	$out['email'] = sanitize_email( (string) ( $input['email'] ?? '' ) );

	$country         = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) ( $input['country'] ?? '' ) ) );
	$out['country']  = 2 === strlen( $country ) ? $country : '';

	$out['opening_hours'] = $lines( $input['opening_hours'] ?? '' );

	// Profile URLs, one per line, each validated.
	$urls = array();

	foreach ( preg_split( '/\R/', (string) ( $input['profiles'] ?? '' ) ) ?: array() as $line ) {
		$url = esc_url_raw( trim( $line ) );

		if ( $url ) {
			$urls[] = $url;
		}
	}

	$out['profiles'] = implode( "\n", $urls );

	$out['preferences_enabled']  = ! empty( $input['preferences_enabled'] );
	$out['preferences_position'] = 'left' === ( $input['preferences_position'] ?? '' ) ? 'left' : 'right';

	$out['login_enabled']        = ! empty( $input['login_enabled'] );
	$out['login_generic_errors'] = ! empty( $input['login_generic_errors'] );

	$out['login_logo']             = absint( $input['login_logo'] ?? 0 );
	$out['login_background_image'] = absint( $input['login_background_image'] ?? 0 );

	// Clamped rather than rejected: an out of range width is a typo, not an attack.
	$out['login_logo_width'] = min( 480, max( 80, absint( $input['login_logo_width'] ?? 320 ) ) );

	/*
	 * Anything that is not a plain hex colour becomes an empty string, and the
	 * login module falls back to its default. That is what keeps arbitrary text
	 * out of the stylesheet this option feeds.
	 */
	foreach ( array( 'login_background', 'login_form_background', 'login_accent' ) as $key ) {
		$out[ $key ] = basalt_core_hex_color( $input[ $key ] ?? '' );
	}

	return $out;
}

/**
 * Add the settings screen.
 *
 * Under Settings rather than Appearance: this is site data, not styling.
 *
 * @return void
 */
function basalt_core_settings_page(): void {
	add_options_page(
		__( 'Search engines and structured data', 'basalt-core' ),
		__( 'Search & Schema', 'basalt-core' ),
		'manage_options',
		'basalt-core',
		'basalt_core_render_settings'
	);
}
add_action( 'admin_menu', 'basalt_core_settings_page' );

/**
 * Render one field.
 *
 * @param string               $key   Setting key.
 * @param string               $label Field label.
 * @param string               $type  text, email, textarea, checkbox or select.
 * @param array<string, mixed> $args  description, choices.
 * @return void
 */
function basalt_core_field( string $key, string $label, string $type = 'text', array $args = array() ): void {
	$name        = BASALT_CORE_OPTION . '[' . $key . ']';
	$id          = 'basalt-core-' . $key;
	$value       = basalt_core_get( $key );
	$description = $args['description'] ?? '';
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<?php if ( 'checkbox' === $type ) : ?>
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( (bool) $value ); ?> />
			<?php elseif ( 'textarea' === $type ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4" class="large-text code"><?php echo esc_textarea( (string) $value ); ?></textarea>
			<?php elseif ( 'select' === $type ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( (array) ( $args['choices'] ?? array() ) as $choice => $choice_label ) : ?>
						<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $value, $choice ); ?>>
							<?php echo esc_html( $choice_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text" />
			<?php endif; ?>

			<?php if ( $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render the settings screen.
 *
 * @return void
 */
function basalt_core_render_settings(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$plugin = basalt_core_active_seo_plugin();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Search engines and structured data', 'basalt-core' ); ?></h1>

		<?php if ( $plugin ) : ?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: name of the detected SEO plugin. */
						esc_html__( '%s is active and owns meta tags, structured data and breadcrumbs. Basalt Core stays silent so nothing is emitted twice. The business details below are still worth filling in: they are used by the breadcrumb block and are ready if you ever remove the plugin.', 'basalt-core' ),
						'<strong>' . esc_html( $plugin ) . '</strong>'
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( ! get_option( 'blog_public' ) ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Search engines are blocked.', 'basalt-core' ); ?></strong>
					<?php esc_html_e( 'This site discourages indexing, so none of this has any effect.', 'basalt-core' ); ?>
					<a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>"><?php esc_html_e( 'Reading settings', 'basalt-core' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'basalt_core' ); ?>

			<h2><?php esc_html_e( 'Who is behind this site', 'basalt-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'This produces the knowledge panel and local results. Leave a field empty rather than guessing; wrong structured data is worse than none.', 'basalt-core' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<?php
				basalt_core_field( 'schema_enabled', __( 'Emit structured data', 'basalt-core' ), 'checkbox' );
				basalt_core_field(
					'entity_type',
					__( 'The site represents', 'basalt-core' ),
					'select',
					array( 'choices' => basalt_core_entity_types() )
				);
				basalt_core_field( 'entity_name', __( 'Legal or business name', 'basalt-core' ), 'text', array( 'description' => __( 'Leave empty to use the site title.', 'basalt-core' ) ) );
				basalt_core_field( 'phone', __( 'Phone', 'basalt-core' ) );
				basalt_core_field( 'email', __( 'Email', 'basalt-core' ), 'email' );
				basalt_core_field( 'street', __( 'Street and number', 'basalt-core' ) );
				basalt_core_field( 'postal_code', __( 'Postal code', 'basalt-core' ) );
				basalt_core_field( 'city', __( 'City', 'basalt-core' ) );
				basalt_core_field( 'region', __( 'Region or state', 'basalt-core' ) );
				basalt_core_field( 'country', __( 'Country code', 'basalt-core' ), 'text', array( 'description' => __( 'Two letters, for example DE.', 'basalt-core' ) ) );
				basalt_core_field( 'opening_hours', __( 'Opening hours', 'basalt-core' ), 'textarea', array( 'description' => __( 'One rule per line in Schema.org notation: Mo-Fr 08:00-18:00', 'basalt-core' ) ) );
				basalt_core_field( 'price_range', __( 'Price range', 'basalt-core' ), 'text', array( 'description' => __( 'For example €€. Ignored for Organization and Person.', 'basalt-core' ) ) );
				basalt_core_field( 'profiles', __( 'Profile URLs', 'basalt-core' ), 'textarea', array( 'description' => __( 'One per line. Emitted as sameAs, which connects the site to its social profiles.', 'basalt-core' ) ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Meta tags', 'basalt-core' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				basalt_core_field( 'meta_enabled', __( 'Emit description, Open Graph and Twitter tags', 'basalt-core' ), 'checkbox' );
				basalt_core_field( 'meta_twitter_site', __( 'X / Twitter handle', 'basalt-core' ), 'text', array( 'description' => __( 'Without the @.', 'basalt-core' ) ) );
				basalt_core_field( 'meta_default_image', __( 'Fallback sharing image ID', 'basalt-core' ), 'number', array( 'description' => __( 'Attachment ID of the image used when a page has no featured image. 1200 by 630 pixels works everywhere.', 'basalt-core' ) ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Visitor display settings', 'basalt-core' ); ?></h2>
			<p class="description" style="max-width:60em">
				<?php esc_html_e( 'Adds a small button to the front end that lets a visitor adjust text size, spacing, contrast and motion for themselves. The choice is stored in their browser and applies on their next visit.', 'basalt-core' ); ?>
			</p>
			<p class="description" style="max-width:60em">
				<strong><?php esc_html_e( 'This is not an accessibility overlay.', 'basalt-core' ); ?></strong>
				<?php esc_html_e( 'It changes presentation only. It adds no ARIA, rewrites no markup, and does not make a site conformant. Overlays that claim otherwise are rejected by the accessibility community, and rightly so.', 'basalt-core' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<?php
				basalt_core_field( 'preferences_enabled', __( 'Show the display settings button', 'basalt-core' ), 'checkbox' );
				basalt_core_field(
					'preferences_position',
					__( 'Button position', 'basalt-core' ),
					'select',
					array(
						'choices'     => array(
							'right' => __( 'Bottom right', 'basalt-core' ),
							'left'  => __( 'Bottom left', 'basalt-core' ),
						),
						'description' => __( 'Move it to the side that does not collide with a cookie banner or a chat widget.', 'basalt-core' ),
					)
				);
				?>
			</table>

			<h2><?php esc_html_e( 'Login screen', 'basalt-core' ); ?></h2>
			<p class="description" style="max-width:60em">
				<?php esc_html_e( 'Replaces the WordPress logo and the default colours on the login page with the site\'s own.', 'basalt-core' ); ?>
			</p>
			<?php basalt_core_login_contrast_notice(); ?>
			<table class="form-table" role="presentation">
				<?php
				basalt_core_field( 'login_enabled', __( 'Brand the login screen', 'basalt-core' ), 'checkbox' );
				basalt_core_field( 'login_logo', __( 'Logo, attachment ID', 'basalt-core' ), 'number', array( 'description' => __( 'Leave at 0 to keep the WordPress logo. A wide logo on a transparent background works best.', 'basalt-core' ) ) );
				basalt_core_field( 'login_logo_width', __( 'Logo width in pixels', 'basalt-core' ), 'number', array( 'description' => __( 'Between 80 and 480.', 'basalt-core' ) ) );
				basalt_core_field( 'login_background', __( 'Page background', 'basalt-core' ), 'text', array( 'description' => __( 'Hex colour, for example #f4f5f7. Anything else is ignored.', 'basalt-core' ) ) );
				basalt_core_field( 'login_form_background', __( 'Form background', 'basalt-core' ), 'text', array( 'description' => __( 'Hex colour. The label and field text switches between dark and light automatically, whichever reads better on it.', 'basalt-core' ) ) );
				basalt_core_field( 'login_accent', __( 'Button and focus colour', 'basalt-core' ), 'text', array( 'description' => __( 'Hex colour. Also used for the focus ring.', 'basalt-core' ) ) );
				basalt_core_field( 'login_background_image', __( 'Background image, attachment ID', 'basalt-core' ), 'number', array( 'description' => __( 'Optional. Covers the page behind the form.', 'basalt-core' ) ) );
				basalt_core_field(
					'login_generic_errors',
					__( 'Do not reveal whether a username exists', 'basalt-core' ),
					'checkbox',
					array( 'description' => __( 'Recommended. The default messages say whether the account exists, which lets an attacker confirm real usernames before trying any passwords. The cost: someone who mistypes their username no longer learns that it was the username.', 'basalt-core' ) )
				);
				?>
			</table>
			<p class="description" style="max-width:60em">
				<?php esc_html_e( 'There is deliberately no custom CSS box and no option to move the login URL. Moving wp-login.php stops some automated noise and no actual attacker, and it breaks integrations that post to the known address; a site that needs it needs a plugin dedicated to that job.', 'basalt-core' ); ?>
			</p>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
