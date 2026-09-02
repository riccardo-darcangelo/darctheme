<?php
/**
 * Rendering.
 *
 * Every control has a visible label tied to it with for/id, the error is a
 * paragraph the control references through aria-describedby, and the invalid
 * state is announced with aria-invalid. Placeholders are not labels here and
 * are not used at all.
 *
 * @package BasaltForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the form block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_forms_render( $attributes ): string {
	$attributes = basalt_forms_attributes( (array) $attributes );
	$form_id    = sanitize_key( (string) ( $attributes['formId'] ?? '' ) );

	if ( '' === $form_id ) {
		return '';
	}

	$wrapper = get_block_wrapper_attributes(
		array(
			'class' => 'basalt-form',
			'id'    => 'basalt-form-' . $form_id,
		)
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a marker in the URL after the redirect, carries no data.
	if ( isset( $_GET['basalt-form-sent'] ) && sanitize_key( wp_unslash( (string) $_GET['basalt-form-sent'] ) ) === $form_id ) {
		return sprintf(
			'<div %1$s><p class="basalt-form__success" role="status" tabindex="-1">%2$s</p></div>',
			$wrapper,
			esc_html( (string) $attributes['successMessage'] )
		);
	}

	$state  = $GLOBALS['basalt_forms_state'][ $form_id ] ?? array(
		'errors' => array(),
		'values' => array(),
	);
	$errors = $state['errors'];
	$values = $state['values'];
	$fields = basalt_forms_fields( $attributes );
	$prefix = 'bf-' . $form_id . '-';

	$out = '';

	if ( $errors ) {
		$out .= sprintf(
			'<div class="basalt-form__summary" role="alert" tabindex="-1">%s</div>',
			esc_html( $errors['_form'] ?? __( 'Please check the highlighted fields.', 'basalt-forms' ) )
		);
	}

	foreach ( $fields as $key => $field ) {
		$out .= basalt_forms_render_field( $key, $field, $prefix, (string) ( $values[ $key ] ?? '' ), $errors[ $key ] ?? '', $attributes );
	}

	// Honeypot. Off screen, out of the tab order, and hidden from assistive technology.
	$out .= sprintf(
		'<div class="basalt-form__hp" aria-hidden="true"><label for="%1$s">%2$s</label><input type="text" id="%1$s" name="bf[website]" tabindex="-1" autocomplete="off"></div>',
		esc_attr( $prefix . 'website' ),
		esc_html__( 'Leave this field empty', 'basalt-forms' )
	);

	// Consent.
	$consent_id    = $prefix . 'consent';
	$consent_error = $errors['consent'] ?? '';
	$out          .= sprintf(
		'<div class="basalt-form__field basalt-form__field--consent%5$s"><input type="checkbox" id="%1$s" name="bf[consent]" value="1" required aria-required="true"%4$s><label for="%1$s">%2$s <span class="basalt-form__required" aria-hidden="true">*</span></label>%3$s</div>',
		esc_attr( $consent_id ),
		wp_kses( (string) $attributes['consentText'], array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
		$consent_error ? sprintf( '<p class="basalt-form__error" id="%1$s-error">%2$s</p>', esc_attr( $consent_id ), esc_html( $consent_error ) ) : '',
		$consent_error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $consent_id . '-error' ) . '"' : '',
		$consent_error ? ' has-error' : ''
	);

	$out .= sprintf(
		'<input type="hidden" name="basalt_form_id" value="%1$s"><input type="hidden" name="basalt_form_token" value="%2$s">',
		esc_attr( $form_id ),
		esc_attr( basalt_forms_token( $form_id ) )
	);

	$out .= sprintf(
		'<div class="basalt-form__actions wp-block-buttons"><div class="wp-block-button"><button type="submit" class="wp-block-button__link wp-element-button">%s</button></div></div>',
		esc_html( (string) $attributes['submitLabel'] )
	);

	$action = (string) get_permalink();

	return sprintf(
		'<div %1$s><form method="post" action="%2$s#basalt-form-%3$s" class="basalt-form__form" novalidate="">%4$s</form></div>',
		$wrapper,
		esc_url( $action ),
		esc_attr( $form_id ),
		$out
	);
}

/**
 * Render one field.
 *
 * @param string               $key        Field key.
 * @param array<string, mixed> $field      Field definition.
 * @param string               $prefix     Id prefix.
 * @param string               $value      Current value.
 * @param string               $error      Error message, or empty.
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_forms_render_field( string $key, array $field, string $prefix, string $value, string $error, array $attributes ): string {
	$id       = $prefix . $key;
	$required = ! empty( $field['required'] );
	$aria     = ( $required ? ' required aria-required="true"' : '' )
		. ( $error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $id . '-error' ) . '"' : '' )
		. ( ! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : '' );

	$label = sprintf(
		'<label for="%1$s">%2$s%3$s</label>',
		esc_attr( $id ),
		esc_html( (string) $field['label'] ),
		$required ? ' <span class="basalt-form__required" aria-hidden="true">*</span>' : ' <span class="basalt-form__optional">' . esc_html__( '(optional)', 'basalt-forms' ) . '</span>'
	);

	switch ( $field['type'] ) {
		case 'textarea':
			$control = sprintf( '<textarea id="%1$s" name="bf[%2$s]" rows="6"%3$s>%4$s</textarea>', esc_attr( $id ), esc_attr( $key ), $aria, esc_textarea( $value ) );
			break;

		case 'select':
			$options = '<option value="">' . esc_html__( 'Please choose', 'basalt-forms' ) . '</option>';

			foreach ( basalt_forms_topics( $attributes ) as $topic ) {
				$options .= sprintf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $topic ), selected( $value, $topic, false ) );
			}

			$control = sprintf( '<select id="%1$s" name="bf[%2$s]"%3$s>%4$s</select>', esc_attr( $id ), esc_attr( $key ), $aria, $options );
			break;

		default:
			$control = sprintf(
				'<input type="%1$s" id="%2$s" name="bf[%3$s]" value="%4$s"%5$s%6$s>',
				esc_attr( (string) $field['type'] ),
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				$aria,
				'date' === $field['type'] ? ' min="' . esc_attr( wp_date( 'Y-m-d' ) ) . '"' : ''
			);
	}

	return sprintf(
		'<div class="basalt-form__field basalt-form__field--%1$s%4$s">%2$s%3$s%5$s</div>',
		esc_attr( $key ),
		$label,
		$control,
		$error ? ' has-error' : '',
		$error ? sprintf( '<p class="basalt-form__error" id="%1$s-error">%2$s</p>', esc_attr( $id ), esc_html( $error ) ) : ''
	);
}
