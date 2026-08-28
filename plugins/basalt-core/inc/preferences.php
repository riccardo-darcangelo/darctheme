<?php
/**
 * Visitor display preferences.
 *
 * A small panel that lets a visitor adjust how this site is presented to them,
 * stored in their browser so the choice survives to the next visit.
 *
 * What this is not
 * ----------------
 * It is not an accessibility overlay. Overlays that promise automatic
 * conformance are widely rejected by the accessibility community, and for good
 * reasons: they rewrite the DOM and inject ARIA at runtime, which interferes
 * with the assistive technology a user has already configured, and they let a
 * site owner believe the underlying problems are solved when they are not.
 *
 * This panel touches presentation only. It changes type size, spacing, motion,
 * link underlines and contrast. It adds no ARIA, rewrites no markup, and claims
 * no conformance. Everything it offers is something a user could also set in
 * their operating system or browser; the panel exists because many people do
 * not know those settings exist, and because a per-site choice is sometimes
 * what is actually wanted.
 *
 * The system settings win by default: prefers-reduced-motion and
 * prefers-contrast are read as the starting values, and the panel only departs
 * from them when the visitor asks it to.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * The preferences the panel offers.
 *
 * Each one is a genuine presentation choice the site can honour honestly. The
 * list is deliberately short: a panel with thirty switches is a panel nobody
 * reads.
 *
 * @return array<string, array{type: string, label: string, help?: string, choices?: array<string, string>, default: string}>
 */
function basalt_core_preference_schema(): array {
	/**
	 * Filter the preferences offered in the display panel.
	 *
	 * @param array<string, array<string, mixed>> $preferences Preference definitions.
	 */
	return (array) apply_filters(
		'basalt_core_preferences',
		array(
			'text-size'      => array(
				'type'    => 'radio',
				'label'   => __( 'Text size', 'basalt-core' ),
				'default' => '100',
				'choices' => array(
					'100' => __( 'Normal', 'basalt-core' ),
					'115' => __( 'Large', 'basalt-core' ),
					'130' => __( 'Larger', 'basalt-core' ),
					'150' => __( 'Largest', 'basalt-core' ),
				),
			),
			'line-height'    => array(
				'type'    => 'checkbox',
				'label'   => __( 'More space between lines', 'basalt-core' ),
				'help'    => __( 'Increases line height and paragraph spacing.', 'basalt-core' ),
				'default' => '',
			),
			'letter-spacing' => array(
				'type'    => 'checkbox',
				'label'   => __( 'More space between letters and words', 'basalt-core' ),
				'help'    => __( 'Helps if letters appear to run together.', 'basalt-core' ),
				'default' => '',
			),
			'underline'      => array(
				'type'    => 'checkbox',
				'label'   => __( 'Underline every link', 'basalt-core' ),
				'help'    => __( 'Makes links identifiable without relying on colour.', 'basalt-core' ),
				'default' => '',
			),
			'contrast'       => array(
				'type'    => 'checkbox',
				'label'   => __( 'Higher contrast', 'basalt-core' ),
				'help'    => __( 'Darkens text and borders. Follows your system setting until you change it here.', 'basalt-core' ),
				'default' => '',
			),
			'motion'         => array(
				'type'    => 'checkbox',
				'label'   => __( 'Reduce motion', 'basalt-core' ),
				'help'    => __( 'Stops transitions and animations. Follows your system setting until you change it here.', 'basalt-core' ),
				'default' => '',
			),
			'unstick'        => array(
				'type'    => 'checkbox',
				'label'   => __( 'Do not keep the header on screen', 'basalt-core' ),
				'help'    => __( 'Frees vertical space, which matters when the page is magnified.', 'basalt-core' ),
				'default' => '',
			),
		)
	);
}

/**
 * Whether the panel should render.
 *
 * @return bool
 */
function basalt_core_preferences_enabled(): bool {
	/**
	 * Filter whether the display preferences panel renders.
	 *
	 * @param bool $enabled Whether to render the panel.
	 */
	return (bool) apply_filters( 'basalt_core_preferences_enabled', (bool) basalt_core_get( 'preferences_enabled' ) );
}

/**
 * Apply the stored preferences before the first paint.
 *
 * This is the one place an inline, render blocking script is the right answer.
 * The preferences live in localStorage, and a deferred script would apply them
 * after the page has already been painted at the default size: the visitor
 * would watch the text jump on every single page load, which is worse than not
 * offering the feature.
 *
 * The script is tiny, has no dependencies, and every storage access is wrapped
 * because localStorage throws rather than returning null in a private window
 * and in browsers configured to block site data.
 *
 * @return void
 */
function basalt_core_preferences_boot_script(): void {
	if ( ! basalt_core_preferences_enabled() ) {
		return;
	}

	$keys = wp_json_encode( array_keys( basalt_core_preference_schema() ) );

	$script = <<<JS
(function(){
	var root = document.documentElement;
	var keys = {$keys};
	var stored = {};

	try {
		stored = JSON.parse( localStorage.getItem( 'basaltDisplayPreferences' ) || '{}' ) || {};
	} catch ( e ) {
		stored = {};
	}

	/*
	 * The operating system setting is the starting point. A visitor who has
	 * already asked for reduced motion or more contrast should not have to ask
	 * again here, and the panel only overrides it once they touch that control.
	 */
	function system( query ) {
		try {
			return window.matchMedia( query ).matches;
		} catch ( e ) {
			return false;
		}
	}

	if ( ! ( 'motion' in stored ) && system( '(prefers-reduced-motion: reduce)' ) ) {
		stored.motion = '1';
	}

	if ( ! ( 'contrast' in stored ) && system( '(prefers-contrast: more)' ) ) {
		stored.contrast = '1';
	}

	for ( var i = 0; i < keys.length; i++ ) {
		var key = keys[ i ];
		var value = stored[ key ];

		if ( value ) {
			root.setAttribute( 'data-a11y-' + key, String( value ) );
		}
	}

	// The trigger is only shown once this has run, so it is never a dead button.
	root.classList.add( 'basalt-a11y-ready' );
}());
JS;

	wp_print_inline_script_tag( $script, array( 'id' => 'basalt-core-preferences-boot' ) );
}
add_action( 'wp_head', 'basalt_core_preferences_boot_script', 1 );

/**
 * Enqueue the panel's stylesheet and script.
 *
 * @return void
 */
function basalt_core_preferences_assets(): void {
	if ( ! basalt_core_preferences_enabled() ) {
		return;
	}

	wp_enqueue_style(
		'basalt-core-preferences',
		BASALT_CORE_URL . 'assets/preferences.css',
		array(),
		BASALT_CORE_VERSION
	);

	wp_enqueue_script(
		'basalt-core-preferences',
		BASALT_CORE_URL . 'assets/preferences.js',
		array(),
		BASALT_CORE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'basalt_core_preferences_assets' );

/**
 * Render the trigger and the panel.
 *
 * The markup is server rendered so the labels are translatable and so the panel
 * is present in the DOM for anything that inspects the page. The controls do
 * nothing until the script runs, which is why the trigger stays hidden until
 * the boot script has confirmed JavaScript is available.
 *
 * @return void
 */
function basalt_core_preferences_panel(): void {
	if ( ! basalt_core_preferences_enabled() ) {
		return;
	}

	$position = 'left' === basalt_core_get( 'preferences_position' ) ? 'left' : 'right';
	?>
	<div class="basalt-a11y basalt-a11y--<?php echo esc_attr( $position ); ?>">
		<button type="button" class="basalt-a11y__trigger" aria-haspopup="dialog" aria-controls="basalt-a11y-panel">
			<span class="basalt-a11y__icon" aria-hidden="true"></span>
			<span class="basalt-a11y__trigger-label"><?php esc_html_e( 'Display settings', 'basalt-core' ); ?></span>
		</button>

		<dialog class="basalt-a11y__panel" id="basalt-a11y-panel" aria-labelledby="basalt-a11y-title">
			<form method="dialog" class="basalt-a11y__form">
				<div class="basalt-a11y__head">
					<h2 class="basalt-a11y__title" id="basalt-a11y-title">
						<?php esc_html_e( 'Display settings', 'basalt-core' ); ?>
					</h2>
					<button type="button" class="basalt-a11y__close">
						<span class="basalt-a11y__close-icon" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Close', 'basalt-core' ); ?></span>
					</button>
				</div>

				<p class="basalt-a11y__intro">
					<?php esc_html_e( 'These settings change how this site looks for you. They are stored in this browser, on this device, and are not sent anywhere.', 'basalt-core' ); ?>
				</p>

				<?php foreach ( basalt_core_preference_schema() as $key => $preference ) : ?>
					<?php if ( 'radio' === $preference['type'] ) : ?>
						<fieldset class="basalt-a11y__group">
							<legend class="basalt-a11y__legend"><?php echo esc_html( $preference['label'] ); ?></legend>
							<div class="basalt-a11y__choices">
								<?php foreach ( (array) $preference['choices'] as $value => $label ) : ?>
									<label class="basalt-a11y__choice">
										<input
											type="radio"
											name="<?php echo esc_attr( $key ); ?>"
											value="<?php echo esc_attr( $value ); ?>"
											data-a11y-pref="<?php echo esc_attr( $key ); ?>"
											<?php checked( $value, $preference['default'] ); ?>
										>
										<span><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php else : ?>
						<div class="basalt-a11y__row">
							<label class="basalt-a11y__switch">
								<input
									type="checkbox"
									value="1"
									data-a11y-pref="<?php echo esc_attr( $key ); ?>"
								>
								<span class="basalt-a11y__switch-label">
									<span class="basalt-a11y__switch-name"><?php echo esc_html( $preference['label'] ); ?></span>
									<?php if ( ! empty( $preference['help'] ) ) : ?>
										<span class="basalt-a11y__help"><?php echo esc_html( $preference['help'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>

				<div class="basalt-a11y__actions">
					<button type="button" class="basalt-a11y__reset">
						<?php esc_html_e( 'Reset to defaults', 'basalt-core' ); ?>
					</button>
				</div>

				<p
					class="basalt-a11y__status"
					role="status"
					data-reset-message="<?php esc_attr_e( 'Display settings reset.', 'basalt-core' ); ?>"
					data-saved-message="<?php esc_attr_e( 'Saved.', 'basalt-core' ); ?>"
				></p>
			</form>
		</dialog>
	</div>
	<?php
}
add_action( 'wp_footer', 'basalt_core_preferences_panel' );
