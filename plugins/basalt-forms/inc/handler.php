<?php
/**
 * Submission handling.
 *
 * Plain POST to the page the form is on, no JavaScript. On success the
 * visitor is redirected back to the same page with a marker in the URL, so a
 * reload does not resend (the post/redirect/get pattern). On error the page
 * renders again in the same request, with the entered values kept and the
 * message at the field that caused it.
 *
 * The form's attributes are read from the block in the page content, so the
 * server validates against what the editor configured and not against what
 * the browser sent.
 *
 * @package BasaltForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Per request state: form id to array of errors and values.
 *
 * @var array<string, array{errors: array<string, string>, values: array<string, string>}>
 */
$GLOBALS['basalt_forms_state'] = array();

/**
 * A token that ties a submission to a form without expiring.
 *
 * A nonce would be the WordPress reflex, but a nonce expires and a contact
 * page is exactly the kind of page a cache keeps for days. Visitors would then
 * fail on a form they filled in correctly. The token proves the form was
 * rendered by this site; the honeypot and the rate limit do the anti-spam work.
 *
 * @param string $form_id Form identifier.
 * @return string
 */
function basalt_forms_token( string $form_id ): string {
	return hash_hmac( 'sha256', 'basalt-form-' . $form_id, wp_salt( 'nonce' ) );
}

/**
 * The fields a form has, in order, given its attributes.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return array<string, array{label: string, type: string, required: bool, autocomplete: string}>
 */
function basalt_forms_fields( array $attributes ): array {
	$fields = array(
		'name'  => array(
			'label'        => (string) $attributes['nameLabel'],
			'type'         => 'text',
			'required'     => true,
			'autocomplete' => 'name',
		),
		'email' => array(
			'label'        => (string) $attributes['emailLabel'],
			'type'         => 'email',
			'required'     => true,
			'autocomplete' => 'email',
		),
	);

	if ( ! empty( $attributes['showPhone'] ) ) {
		$fields['phone'] = array(
			'label'        => (string) $attributes['phoneLabel'],
			'type'         => 'tel',
			'required'     => false,
			'autocomplete' => 'tel',
		);
	}

	if ( ! empty( $attributes['showTopic'] ) ) {
		$fields['topic'] = array(
			'label'        => (string) $attributes['topicLabel'],
			'type'         => 'select',
			'required'     => true,
			'autocomplete' => '',
		);
	}

	if ( ! empty( $attributes['showDate'] ) ) {
		$fields['date'] = array(
			'label'        => (string) $attributes['dateLabel'],
			'type'         => 'date',
			'required'     => false,
			'autocomplete' => '',
		);
	}

	if ( ! empty( $attributes['showMessage'] ) ) {
		$fields['message'] = array(
			'label'        => (string) $attributes['messageLabel'],
			'type'         => 'textarea',
			'required'     => ! empty( $attributes['messageRequired'] ),
			'autocomplete' => '',
		);
	}

	return $fields;
}

/**
 * The topic options, one per line of the attribute.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string[]
 */
function basalt_forms_topics( array $attributes ): array {
	$lines = preg_split( '/\R/', (string) ( $attributes['topics'] ?? '' ) ) ?: array();

	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}

/**
 * Find the form block with the given id in a block tree.
 *
 * @param array<int, array<string, mixed>> $blocks  Parsed blocks.
 * @param string                           $form_id Form identifier.
 * @return array<string, mixed>|null Attributes, or null.
 */
function basalt_forms_find_block( array $blocks, string $form_id ): ?array {
	foreach ( $blocks as $block ) {
		if ( 'basalt/form' === ( $block['blockName'] ?? '' ) && ( $block['attrs']['formId'] ?? '' ) === $form_id ) {
			return (array) $block['attrs'];
		}

		if ( 'core/block' === ( $block['blockName'] ?? '' ) && ! empty( $block['attrs']['ref'] ) ) {
			$reusable = get_post( (int) $block['attrs']['ref'] );

			if ( $reusable ) {
				$found = basalt_forms_find_block( parse_blocks( $reusable->post_content ), $form_id );

				if ( $found ) {
					return $found;
				}
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = basalt_forms_find_block( (array) $block['innerBlocks'], $form_id );

			if ( $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Attributes with their defaults from block.json filled in.
 *
 * @param array<string, mixed> $attributes Stored attributes.
 * @return array<string, mixed>
 */
function basalt_forms_attributes( array $attributes ): array {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( 'basalt/form' );

	if ( $type ) {
		foreach ( (array) $type->attributes as $key => $schema ) {
			if ( ! array_key_exists( $key, $attributes ) && isset( $schema['default'] ) ) {
				$attributes[ $key ] = $schema['default'];
			}
		}
	}

	return $attributes;
}

/**
 * Handle a submission, if there is one.
 *
 * @return void
 */
function basalt_forms_handle(): void {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['basalt_form_id'] ) ) {
		return;
	}

	$form_id = sanitize_key( wp_unslash( (string) $_POST['basalt_form_id'] ) );
	$post    = get_post();

	if ( '' === $form_id || ! $post ) {
		return;
	}

	$attributes = basalt_forms_find_block( parse_blocks( $post->post_content ), $form_id );

	if ( null === $attributes ) {
		return;
	}

	$attributes = basalt_forms_attributes( $attributes );
	$fields     = basalt_forms_fields( $attributes );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the token below replaces the nonce, see basalt_forms_token().
	$raw    = isset( $_POST['bf'] ) && is_array( $_POST['bf'] ) ? wp_unslash( $_POST['bf'] ) : array();
	$values = array();
	$errors = array();

	// phpcs:ignore WordWordPress.Security.NonceVerification.Missing
	$token = isset( $_POST['basalt_form_token'] ) ? (string) wp_unslash( $_POST['basalt_form_token'] ) : '';

	if ( ! hash_equals( basalt_forms_token( $form_id ), $token ) ) {
		$errors['_form'] = __( 'This form could not be verified. Please reload the page and try again.', 'basalt-forms' );
	}

	// The honeypot: a field no person sees, which no person fills in.
	if ( '' !== trim( (string) ( $raw['website'] ?? '' ) ) ) {
		$errors['_form'] = __( 'Your message could not be sent.', 'basalt-forms' );
	}

	foreach ( $fields as $key => $field ) {
		$value = (string) ( $raw[ $key ] ?? '' );
		$value = 'textarea' === $field['type'] ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );

		if ( mb_strlen( $value ) > ( 'textarea' === $field['type'] ? 5000 : 200 ) ) {
			$errors[ $key ] = __( 'This entry is too long.', 'basalt-forms' );
		}

		if ( '' === $value ) {
			if ( $field['required'] ) {
				$errors[ $key ] = __( 'Please fill in this field.', 'basalt-forms' );
			}
		} elseif ( 'email' === $field['type'] && ! is_email( $value ) ) {
			$errors[ $key ] = __( 'Please enter a complete email address, so that we can reply.', 'basalt-forms' );
		} elseif ( 'tel' === $field['type'] && ! preg_match( '/^[0-9+][0-9 \/().-]{4,}$/', $value ) ) {
			$errors[ $key ] = __( 'Please enter a phone number with digits only.', 'basalt-forms' );
		} elseif ( 'date' === $field['type'] ) {
			$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );

			if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
				$errors[ $key ] = __( 'Please enter a valid date.', 'basalt-forms' );
			} elseif ( $date < new DateTimeImmutable( 'today', wp_timezone() ) ) {
				$errors[ $key ] = __( 'Please pick a date that is not in the past.', 'basalt-forms' );
			} else {
				$value = wp_date( (string) get_option( 'date_format' ), $date->getTimestamp() );
			}
		} elseif ( 'select' === $field['type'] && ! in_array( $value, basalt_forms_topics( $attributes ), true ) ) {
			$errors[ $key ] = __( 'Please choose one of the options.', 'basalt-forms' );
		}

		$values[ $key ] = $value;
	}

	if ( empty( $raw['consent'] ) ) {
		$errors['consent'] = __( 'Please confirm that you have read the privacy policy.', 'basalt-forms' );
	}

	if ( ! $errors && ! basalt_forms_rate_limit_ok() ) {
		$errors['_form'] = __( 'Too many messages in a short time. Please try again in an hour, or call us.', 'basalt-forms' );
	}

	if ( $errors ) {
		$GLOBALS['basalt_forms_state'][ $form_id ] = array(
			'errors' => $errors,
			'values' => $values,
		);
		return;
	}

	basalt_forms_deliver( $form_id, $attributes, $fields, $values );

	$url = add_query_arg( 'basalt-form-sent', $form_id, remove_query_arg( 'basalt-form-sent', (string) get_permalink( $post ) ) );

	wp_safe_redirect( $url . '#basalt-form-' . $form_id );
	exit;
}
add_action( 'template_redirect', 'basalt_forms_handle' );

/**
 * At most five submissions per address per hour.
 *
 * @return bool
 */
function basalt_forms_rate_limit_ok(): bool {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );

	if ( '' === $ip ) {
		return true;
	}

	$key   = 'basalt_forms_rl_' . md5( $ip );
	$count = (int) get_transient( $key );

	/**
	 * Filter the number of submissions allowed per address and hour.
	 *
	 * @param int $limit The limit.
	 */
	$limit = (int) apply_filters( 'basalt_forms_rate_limit', 5 );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * Mail the submission and store it.
 *
 * @param string                                                                          $form_id    Form identifier.
 * @param array<string, mixed>                                                            $attributes Block attributes.
 * @param array<string, array{label: string, type: string, required: bool, autocomplete: string}> $fields Field definitions.
 * @param array<string, string>                                                           $values     Sanitized values.
 * @return void
 */
function basalt_forms_deliver( string $form_id, array $attributes, array $fields, array $values ): void {
	$labelled = array();

	foreach ( $fields as $key => $field ) {
		$labelled[ $field['label'] ] = $values[ $key ];
	}

	$source = (string) get_permalink();
	$to     = is_email( (string) $attributes['recipient'] ) ? (string) $attributes['recipient'] : (string) get_option( 'admin_email' );

	$subject = str_replace(
		array( '%name%', '%site%' ),
		array( $values['name'], wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ) ),
		(string) $attributes['subject']
	);

	$body = '';

	foreach ( $labelled as $label => $value ) {
		$body .= $label . ":\n" . $value . "\n\n";
	}

	/* translators: %s: page URL */
	$body .= sprintf( __( 'Sent from %s', 'basalt-forms' ), $source ) . "\n";

	$headers = array( 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>' );

	/**
	 * Fires before a submission is mailed and stored.
	 *
	 * @param string                $form_id  Form identifier.
	 * @param array<string, string> $labelled Label to value.
	 * @param array<string, mixed>  $attributes Block attributes.
	 */
	do_action( 'basalt_forms_before_deliver', $form_id, $labelled, $attributes );

	wp_mail( $to, $subject, $body, $headers );
	basalt_forms_store_entry( $form_id, $labelled, $source );
}
