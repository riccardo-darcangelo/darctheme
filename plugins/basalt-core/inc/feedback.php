<?php
/**
 * Micro feedback: "Was this helpful?" under a page that explains something.
 *
 * One question, two answers, and a text field that only appears after a no.
 * No cookies, no external service, no script: the buttons submit a form, the
 * answer is counted server side and the visitor comes back to the same place
 * through a redirect.
 *
 * What it deliberately does not do: identify anybody. There is no visitor id,
 * so the same person can answer twice. That is the price of asking without
 * tracking, and for a question like this the trend is what matters, not the
 * exact number.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/** Where the counts live. */
const BASALT_CORE_FEEDBACK_OPTION = 'basalt_core_feedback';

/** How many free text notes to keep per topic. */
const BASALT_CORE_FEEDBACK_NOTES = 100;

/**
 * All feedback collected so far.
 *
 * @return array<string, array{yes: int, no: int, notes: array<int, array{text: string, url: string, time: int}>}>
 */
function basalt_core_feedback_all(): array {
	return (array) get_option( BASALT_CORE_FEEDBACK_OPTION, array() );
}

/**
 * Record one answer.
 *
 * @param string $topic  Topic key.
 * @param string $answer yes or no.
 * @param string $note   Optional free text.
 * @param string $url    Page the answer came from.
 * @return void
 */
function basalt_core_feedback_record( string $topic, string $answer, string $note = '', string $url = '' ): void {
	$all = basalt_core_feedback_all();

	$entry = wp_parse_args(
		$all[ $topic ] ?? array(),
		array(
			'yes'   => 0,
			'no'    => 0,
			'notes' => array(),
		)
	);

	if ( 'yes' === $answer || 'no' === $answer ) {
		++$entry[ $answer ];
	}

	if ( '' !== $note ) {
		$entry['notes'][] = array(
			'text' => $note,
			'url'  => $url,
			'time' => time(),
		);

		$entry['notes'] = array_slice( $entry['notes'], -BASALT_CORE_FEEDBACK_NOTES );
	}

	$all[ $topic ] = $entry;

	update_option( BASALT_CORE_FEEDBACK_OPTION, $all, false );
}

/**
 * A token tying a submission to this site, without a nonce.
 *
 * Same reasoning as the form plugin: these blocks sit on pages a cache keeps
 * for days, and an expired nonce would turn a one click question into an
 * error message.
 *
 * @param string $topic Topic key.
 * @return string
 */
function basalt_core_feedback_token( string $topic ): string {
	return hash_hmac( 'sha256', 'basalt-feedback-' . $topic, wp_salt( 'nonce' ) );
}

/**
 * At most twenty answers per address and hour.
 *
 * @return bool
 */
function basalt_core_feedback_allowed(): bool {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );

	if ( '' === $ip ) {
		return true;
	}

	$key   = 'basalt_feedback_rl_' . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= 20 ) {
		return false;
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * Handle a submission.
 *
 * @return void
 */
function basalt_core_feedback_handle(): void {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['basalt_feedback_topic'] ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- token below, see basalt_core_feedback_token().
	$topic  = sanitize_key( wp_unslash( (string) $_POST['basalt_feedback_topic'] ) );
	$token  = (string) wp_unslash( (string) ( $_POST['basalt_feedback_token'] ?? '' ) );
	$answer = sanitize_key( wp_unslash( (string) ( $_POST['basalt_feedback_answer'] ?? '' ) ) );
	$note   = sanitize_textarea_field( wp_unslash( (string) ( $_POST['basalt_feedback_note'] ?? '' ) ) );
	// phpcs:enable

	if ( '' === $topic || ! hash_equals( basalt_core_feedback_token( $topic ), $token ) ) {
		return;
	}

	$state = 'thanks';

	if ( basalt_core_feedback_allowed() ) {
		if ( 'no' === $answer && '' === $note ) {
			// The no is counted straight away; the note is a second, optional step.
			basalt_core_feedback_record( $topic, 'no', '', (string) get_permalink() );
			$state = 'more';
		} elseif ( '' !== $note ) {
			basalt_core_feedback_record( $topic, '', mb_substr( $note, 0, 1000 ), (string) get_permalink() );
		} else {
			basalt_core_feedback_record( $topic, 'yes', '', (string) get_permalink() );
		}
	}

	$url = add_query_arg(
		array(
			'basalt-feedback' => $topic,
			'state'           => $state,
		),
		remove_query_arg( array( 'basalt-feedback', 'state' ), (string) get_permalink() )
	);

	wp_safe_redirect( $url . '#basalt-feedback-' . $topic );
	exit;
}
add_action( 'template_redirect', 'basalt_core_feedback_handle' );

/**
 * Render the feedback block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function basalt_core_feedback_block( $attributes ): string {
	$attributes = wp_parse_args(
		(array) $attributes,
		array(
			'topic'     => '',
			'question'  => __( 'Was this helpful?', 'basalt-core' ),
			'yesLabel'  => __( 'Yes', 'basalt-core' ),
			'noLabel'   => __( 'No', 'basalt-core' ),
			'moreLabel' => __( 'Thank you. What was missing?', 'basalt-core' ),
			'sendLabel' => __( 'Send', 'basalt-core' ),
			'callLabel' => '',
			'callUrl'   => '',
		)
	);

	$topic = sanitize_key( (string) $attributes['topic'] );

	if ( '' === $topic ) {
		$post  = get_post();
		$topic = $post ? sanitize_key( $post->post_name ?: 'page-' . $post->ID ) : 'site';
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- markers in the URL after a redirect, no data.
	$shown = sanitize_key( wp_unslash( (string) ( $_GET['basalt-feedback'] ?? '' ) ) );
	$state = sanitize_key( wp_unslash( (string) ( $_GET['state'] ?? '' ) ) );
	// phpcs:enable

	$hidden = sprintf(
		'<input type="hidden" name="basalt_feedback_topic" value="%1$s"><input type="hidden" name="basalt_feedback_token" value="%2$s">',
		esc_attr( $topic ),
		esc_attr( basalt_core_feedback_token( $topic ) )
	);

	if ( $shown === $topic && 'more' === $state ) {
		$body = sprintf(
			'<form method="post" action="%1$s#basalt-feedback-%2$s" class="basalt-feedback__more">
				<label for="basalt-feedback-note-%2$s">%3$s</label>
				<textarea id="basalt-feedback-note-%2$s" name="basalt_feedback_note" rows="2"></textarea>
				%4$s
				<p class="basalt-feedback__actions"><button type="submit" class="wp-block-button__link wp-element-button">%5$s</button>%6$s</p>
			</form>',
			esc_url( (string) get_permalink() ),
			esc_attr( $topic ),
			esc_html( (string) $attributes['moreLabel'] ),
			$hidden,
			esc_html( (string) $attributes['sendLabel'] ),
			$attributes['callLabel'] && $attributes['callUrl']
				? sprintf( ' <a href="%1$s">%2$s</a>', esc_url( (string) $attributes['callUrl'] ), esc_html( (string) $attributes['callLabel'] ) )
				: ''
		);
	} elseif ( $shown === $topic ) {
		$body = sprintf( '<p class="basalt-feedback__thanks" role="status">%s</p>', esc_html__( 'Thank you.', 'basalt-core' ) );
	} else {
		$body = sprintf(
			'<form method="post" action="%1$s#basalt-feedback-%2$s" class="basalt-feedback__ask">
				<p class="basalt-feedback__question">%3$s</p>
				%4$s
				<p class="basalt-feedback__actions">
					<button type="submit" name="basalt_feedback_answer" value="yes" class="wp-block-button__link wp-element-button is-style-outline">%5$s</button>
					<button type="submit" name="basalt_feedback_answer" value="no" class="wp-block-button__link wp-element-button is-style-outline">%6$s</button>
				</p>
			</form>',
			esc_url( (string) get_permalink() ),
			esc_attr( $topic ),
			esc_html( (string) $attributes['question'] ),
			$hidden,
			esc_html( (string) $attributes['yesLabel'] ),
			esc_html( (string) $attributes['noLabel'] )
		);
	}

	return sprintf(
		'<div %1$s id="basalt-feedback-%2$s">%3$s</div>',
		get_block_wrapper_attributes( array( 'class' => 'basalt-feedback' ) ),
		esc_attr( $topic ),
		$body
	);
}

/**
 * Show what came in, on the settings screen.
 *
 * @return void
 */
function basalt_core_feedback_report(): void {
	$all = basalt_core_feedback_all();

	if ( ! $all ) {
		echo '<p class="description">' . esc_html__( 'Nothing yet. The Feedback block collects answers here.', 'basalt-core' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr>';
	echo '<th>' . esc_html__( 'Topic', 'basalt-core' ) . '</th>';
	echo '<th>' . esc_html__( 'Helpful', 'basalt-core' ) . '</th>';
	echo '<th>' . esc_html__( 'Not helpful', 'basalt-core' ) . '</th>';
	echo '<th>' . esc_html__( 'Latest notes', 'basalt-core' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $all as $topic => $entry ) {
		$notes = array_slice( (array) ( $entry['notes'] ?? array() ), -5 );
		$list  = '';

		foreach ( array_reverse( $notes ) as $note ) {
			$list .= sprintf(
				'<li>%1$s <span class="description">%2$s</span></li>',
				esc_html( (string) $note['text'] ),
				esc_html( wp_date( (string) get_option( 'date_format' ), (int) $note['time'] ) )
			);
		}

		printf(
			'<tr><th scope="row">%1$s</th><td>%2$d</td><td>%3$d</td><td><ul>%4$s</ul></td></tr>',
			esc_html( (string) $topic ),
			(int) ( $entry['yes'] ?? 0 ),
			(int) ( $entry['no'] ?? 0 ),
			$list ? wp_kses_post( $list ) : '<li>' . esc_html__( 'none', 'basalt-core' ) . '</li>'
		);
	}

	echo '</tbody></table>';
}
