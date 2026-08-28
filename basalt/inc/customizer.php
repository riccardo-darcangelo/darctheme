<?php
/**
 * Customizer settings.
 *
 * Only options that change the page shell live here. Anything that is content
 * belongs in the editor, and anything that is styling belongs in theme.json.
 * Keeping that line sharp is what stops a theme from turning into a page
 * builder that a buyer can never migrate away from.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every theme option.
 *
 * @return array<string, mixed>
 */
function basalt_option_defaults(): array {
	/**
	 * Filter the theme option defaults.
	 *
	 * @param array<string, mixed> $defaults Option defaults keyed by option name.
	 */
	return (array) apply_filters(
		'basalt_option_defaults',
		array(
			// Header.
			'header_layout'          => 'split',
			'header_sticky'          => true,
			'header_show_search'     => true,
			// Blog.
			'archive_layout'         => 'grid',
			'archive_show_excerpt'   => true,
			'archive_show_meta'      => true,
			'single_show_author_box' => true,
			'single_show_reading_time' => true,
			// Footer.
			'footer_copyright'       => '',
			'footer_show_legal_menu' => true,
			// Structured data.
			'schema_enabled'         => true,
			'schema_entity_type'     => 'Organization',
			'schema_entity_name'     => '',
			'schema_logo'            => 0,
			'schema_phone'           => '',
			'schema_email'           => '',
			'schema_street'          => '',
			'schema_postal_code'     => '',
			'schema_city'            => '',
			'schema_region'          => '',
			'schema_country'         => '',
			'schema_opening_hours'   => '',
			'schema_price_range'     => '',
			'schema_profiles'        => '',
			// Meta tags.
			'meta_enabled'           => true,
			'meta_default_image'     => 0,
			'meta_twitter_site'      => '',
		)
	);
}

/**
 * Read a theme option with its default applied.
 *
 * @param string $key Option name without the basalt_ prefix.
 * @return mixed
 */
function basalt_get_option( string $key ) {
	$defaults = basalt_option_defaults();
	$default  = $defaults[ $key ] ?? '';

	return get_theme_mod( $key, $default );
}

/**
 * Register customizer panels, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function basalt_customize_register( $wp_customize ): void {
	$defaults = basalt_option_defaults();

	// Live preview for the parts that can be swapped without a reload.
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->selective_refresh->add_partial(
		'blogname',
		array(
			'selector'        => '.site-branding__link',
			'render_callback' => static fn() => get_bloginfo( 'name', 'display' ),
		)
	);

	$wp_customize->selective_refresh->add_partial(
		'blogdescription',
		array(
			'selector'        => '.site-branding__description',
			'render_callback' => static fn() => get_bloginfo( 'description', 'display' ),
		)
	);

	$wp_customize->add_panel(
		'basalt_theme',
		array(
			'title'    => __( 'Theme options', 'basalt' ),
			'priority' => 30,
		)
	);

	/* ---------------------------------------------------------------------
	 * Header
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'basalt_header',
		array(
			'title' => __( 'Header', 'basalt' ),
			'panel' => 'basalt_theme',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'header_layout',
		$defaults['header_layout'],
		'basalt_sanitize_header_layout',
		array(
			'label'   => __( 'Layout', 'basalt' ),
			'section' => 'basalt_header',
			'type'    => 'select',
			'choices' => array(
				'split'   => __( 'Logo left, menu right', 'basalt' ),
				'stacked' => __( 'Logo centred, menu below', 'basalt' ),
				'compact' => __( 'Logo left, menu behind a button', 'basalt' ),
			),
		)
	);

	basalt_add_setting(
		$wp_customize,
		'header_sticky',
		$defaults['header_sticky'],
		'basalt_sanitize_checkbox',
		array(
			'label'       => __( 'Keep the header visible while scrolling', 'basalt' ),
			'description' => __( 'Uses CSS position: sticky. No scroll listener runs on the main thread.', 'basalt' ),
			'section'     => 'basalt_header',
			'type'        => 'checkbox',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'header_show_search',
		$defaults['header_show_search'],
		'basalt_sanitize_checkbox',
		array(
			'label'   => __( 'Show the search field in the header', 'basalt' ),
			'section' => 'basalt_header',
			'type'    => 'checkbox',
		)
	);

	/* ---------------------------------------------------------------------
	 * Blog
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'basalt_blog',
		array(
			'title' => __( 'Blog and archives', 'basalt' ),
			'panel' => 'basalt_theme',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'archive_layout',
		$defaults['archive_layout'],
		'basalt_sanitize_archive_layout',
		array(
			'label'   => __( 'Archive layout', 'basalt' ),
			'section' => 'basalt_blog',
			'type'    => 'select',
			'choices' => array(
				'grid' => __( 'Card grid', 'basalt' ),
				'list' => __( 'List', 'basalt' ),
			),
		)
	);

	foreach ( array(
		'archive_show_excerpt'     => __( 'Show excerpts on archives', 'basalt' ),
		'archive_show_meta'        => __( 'Show date and author on archives', 'basalt' ),
		'single_show_author_box'   => __( 'Show the author box on single posts', 'basalt' ),
		'single_show_reading_time' => __( 'Show estimated reading time', 'basalt' ),
	) as $key => $label ) {
		basalt_add_setting(
			$wp_customize,
			$key,
			$defaults[ $key ],
			'basalt_sanitize_checkbox',
			array(
				'label'   => $label,
				'section' => 'basalt_blog',
				'type'    => 'checkbox',
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Footer
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'basalt_footer',
		array(
			'title' => __( 'Footer', 'basalt' ),
			'panel' => 'basalt_theme',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'footer_copyright',
		$defaults['footer_copyright'],
		'basalt_sanitize_inline_html',
		array(
			'label'       => __( 'Copyright line', 'basalt' ),
			'description' => __( 'Leave empty to use the site title and the current year. {year} is replaced with the current year.', 'basalt' ),
			'section'     => 'basalt_footer',
			'type'        => 'textarea',
		),
		'postMessage'
	);

	$wp_customize->selective_refresh->add_partial(
		'footer_copyright',
		array(
			'selector'        => '.site-footer__copyright',
			'render_callback' => 'basalt_get_copyright_text',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'footer_show_legal_menu',
		$defaults['footer_show_legal_menu'],
		'basalt_sanitize_checkbox',
		array(
			'label'   => __( 'Show the legal menu next to the copyright', 'basalt' ),
			'section' => 'basalt_footer',
			'type'    => 'checkbox',
		)
	);

	/* ---------------------------------------------------------------------
	 * Search engines: meta tags
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'basalt_meta',
		array(
			'title'       => __( 'Search engines: meta tags', 'basalt' ),
			'description' => __( 'Basalt only emits these when no SEO plugin is active, so nothing is ever duplicated.', 'basalt' ),
			'panel'       => 'basalt_theme',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'meta_enabled',
		$defaults['meta_enabled'],
		'basalt_sanitize_checkbox',
		array(
			'label'   => __( 'Emit description, Open Graph and Twitter tags', 'basalt' ),
			'section' => 'basalt_meta',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'meta_default_image',
		array(
			'default'           => $defaults['meta_default_image'],
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'meta_default_image',
			array(
				'label'       => __( 'Fallback sharing image', 'basalt' ),
				'description' => __( 'Used when a page has no featured image. 1200 by 630 pixels works everywhere.', 'basalt' ),
				'section'     => 'basalt_meta',
				'mime_type'   => 'image',
			)
		)
	);

	basalt_add_setting(
		$wp_customize,
		'meta_twitter_site',
		$defaults['meta_twitter_site'],
		'basalt_sanitize_handle',
		array(
			'label'       => __( 'X / Twitter handle', 'basalt' ),
			'description' => __( 'Without the @.', 'basalt' ),
			'section'     => 'basalt_meta',
			'type'        => 'text',
		)
	);

	/* ---------------------------------------------------------------------
	 * Search engines: structured data
	 * ------------------------------------------------------------------ */

	$wp_customize->add_section(
		'basalt_schema',
		array(
			'title'       => __( 'Search engines: structured data', 'basalt' ),
			'description' => __( 'Feeds the Schema.org JSON-LD graph. Rich results in Google depend on these being accurate, so leave a field empty rather than guessing.', 'basalt' ),
			'panel'       => 'basalt_theme',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'schema_enabled',
		$defaults['schema_enabled'],
		'basalt_sanitize_checkbox',
		array(
			'label'       => __( 'Emit structured data', 'basalt' ),
			'description' => __( 'Switched off automatically while an SEO plugin outputs its own graph.', 'basalt' ),
			'section'     => 'basalt_schema',
			'type'        => 'checkbox',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'schema_entity_type',
		$defaults['schema_entity_type'],
		'basalt_sanitize_entity_type',
		array(
			'label'   => __( 'The site represents', 'basalt' ),
			'section' => 'basalt_schema',
			'type'    => 'select',
			'choices' => basalt_schema_entity_types(),
		)
	);

	basalt_add_setting(
		$wp_customize,
		'schema_entity_name',
		$defaults['schema_entity_name'],
		'sanitize_text_field',
		array(
			'label'       => __( 'Legal or business name', 'basalt' ),
			'description' => __( 'Leave empty to use the site title.', 'basalt' ),
			'section'     => 'basalt_schema',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'schema_logo',
		array(
			'default'           => $defaults['schema_logo'],
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'schema_logo',
			array(
				'label'       => __( 'Logo for search results', 'basalt' ),
				'description' => __( 'Leave empty to use the site logo. Google wants at least 112 pixels on the shorter side.', 'basalt' ),
				'section'     => 'basalt_schema',
				'mime_type'   => 'image',
			)
		)
	);

	$contact_fields = array(
		'schema_phone'       => array( __( 'Phone', 'basalt' ), 'sanitize_text_field' ),
		'schema_email'       => array( __( 'Email', 'basalt' ), 'sanitize_email' ),
		'schema_street'      => array( __( 'Street and number', 'basalt' ), 'sanitize_text_field' ),
		'schema_postal_code' => array( __( 'Postal code', 'basalt' ), 'sanitize_text_field' ),
		'schema_city'        => array( __( 'City', 'basalt' ), 'sanitize_text_field' ),
		'schema_region'      => array( __( 'Region or state', 'basalt' ), 'sanitize_text_field' ),
		'schema_country'     => array( __( 'Country code, for example DE', 'basalt' ), 'basalt_sanitize_country_code' ),
		'schema_price_range' => array( __( 'Price range, for example €€', 'basalt' ), 'sanitize_text_field' ),
	);

	foreach ( $contact_fields as $key => $config ) {
		basalt_add_setting(
			$wp_customize,
			$key,
			$defaults[ $key ],
			$config[1],
			array(
				'label'   => $config[0],
				'section' => 'basalt_schema',
				'type'    => 'text',
			)
		);
	}

	basalt_add_setting(
		$wp_customize,
		'schema_opening_hours',
		$defaults['schema_opening_hours'],
		'sanitize_textarea_field',
		array(
			'label'       => __( 'Opening hours', 'basalt' ),
			'description' => __( 'One rule per line, in Schema.org notation: Mo-Fr 08:00-18:00', 'basalt' ),
			'section'     => 'basalt_schema',
			'type'        => 'textarea',
		)
	);

	basalt_add_setting(
		$wp_customize,
		'schema_profiles',
		$defaults['schema_profiles'],
		'basalt_sanitize_url_list',
		array(
			'label'       => __( 'Profile URLs', 'basalt' ),
			'description' => __( 'One URL per line. Emitted as sameAs, which is how search engines connect the site to its social profiles.', 'basalt' ),
			'section'     => 'basalt_schema',
			'type'        => 'textarea',
		)
	);
}
add_action( 'customize_register', 'basalt_customize_register' );

/**
 * Register a setting and its control in one call.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @param string               $id           Setting id.
 * @param mixed                $default      Default value.
 * @param callable-string      $sanitize     Sanitize callback.
 * @param array<string, mixed> $control      Control arguments.
 * @param string               $transport    Setting transport.
 * @return void
 */
function basalt_add_setting( $wp_customize, string $id, $default, string $sanitize, array $control, string $transport = 'refresh' ): void {
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => $default,
			'sanitize_callback' => $sanitize,
			'transport'         => $transport,
			'capability'        => 'edit_theme_options',
		)
	);

	$wp_customize->add_control( $id, $control );
}

/**
 * Schema.org types offered for the site entity.
 *
 * @return array<string, string>
 */
function basalt_schema_entity_types(): array {
	/**
	 * Filter the selectable Schema.org entity types.
	 *
	 * A child theme for a specific industry should narrow this down, for
	 * example to HomeAndConstructionBusiness for a trade business.
	 *
	 * @param array<string, string> $types Type label keyed by Schema.org type.
	 */
	return (array) apply_filters(
		'basalt_schema_entity_types',
		array(
			'Organization'               => __( 'Organization', 'basalt' ),
			'LocalBusiness'              => __( 'Local business', 'basalt' ),
			'HomeAndConstructionBusiness' => __( 'Construction or trade business', 'basalt' ),
			'ProfessionalService'        => __( 'Professional service', 'basalt' ),
			'Store'                      => __( 'Store', 'basalt' ),
			'Person'                     => __( 'Person', 'basalt' ),
		)
	);
}

/* -------------------------------------------------------------------------
 * Sanitize callbacks
 * ---------------------------------------------------------------------- */

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function basalt_sanitize_checkbox( $value ): bool {
	return (bool) $value;
}

/**
 * Sanitize the header layout choice.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_header_layout( $value ): string {
	return in_array( $value, array( 'split', 'stacked', 'compact' ), true ) ? (string) $value : 'split';
}

/**
 * Sanitize the archive layout choice.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_archive_layout( $value ): string {
	return in_array( $value, array( 'grid', 'list' ), true ) ? (string) $value : 'grid';
}

/**
 * Sanitize the Schema.org entity type against the allowed list.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_entity_type( $value ): string {
	return array_key_exists( (string) $value, basalt_schema_entity_types() ) ? (string) $value : 'Organization';
}

/**
 * Sanitize an ISO 3166-1 alpha-2 country code.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_country_code( $value ): string {
	$value = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $value ) );

	return 2 === strlen( $value ) ? $value : '';
}

/**
 * Sanitize a social handle.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_handle( $value ): string {
	return ltrim( sanitize_text_field( (string) $value ), '@' );
}

/**
 * Sanitize a newline separated list of URLs.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_url_list( $value ): string {
	$lines = preg_split( '/\R/', (string) $value ) ?: array();
	$urls  = array();

	foreach ( $lines as $line ) {
		$url = esc_url_raw( trim( $line ) );

		if ( $url ) {
			$urls[] = $url;
		}
	}

	return implode( "\n", $urls );
}

/**
 * Sanitize a short string that may contain a link or basic emphasis.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function basalt_sanitize_inline_html( $value ): string {
	return wp_kses( (string) $value, basalt_allowed_inline_html() );
}

/**
 * The rendered copyright line.
 *
 * @return string
 */
function basalt_get_copyright_text(): string {
	$custom = (string) basalt_get_option( 'footer_copyright' );

	if ( '' === trim( $custom ) ) {
		return sprintf(
			/* translators: 1: year, 2: site title. */
			esc_html__( '© %1$s %2$s', 'basalt' ),
			esc_html( wp_date( 'Y' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
	}

	return wp_kses(
		str_replace( '{year}', wp_date( 'Y' ), $custom ),
		basalt_allowed_inline_html()
	);
}

/**
 * Live preview script for the customizer.
 *
 * @return void
 */
function basalt_customize_preview_js(): void {
	wp_enqueue_script(
		'basalt-customizer-preview',
		BASALT_URI . 'assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		basalt_asset_version( 'assets/js/customizer-preview.js' ),
		array( 'strategy' => 'defer' )
	);
}
add_action( 'customize_preview_init', 'basalt_customize_preview_js' );
