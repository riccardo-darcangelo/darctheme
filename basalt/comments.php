<?php
/**
 * Comments.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/*
 * A password protected post must not leak its comments. WordPress loads this
 * template regardless, so the guard belongs here.
 */
if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title">
			<?php
			$basalt_comment_count = (int) get_comments_number();

			printf(
				esc_html(
					/* translators: %d: comment count. */
					_n( '%d comment', '%d comments', $basalt_comment_count, 'basalt' )
				),
				$basalt_comment_count // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cast to int above.
			);
			?>
		</h2>

		<ol class="comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 56,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'basalt' ),
				'next_text' => esc_html__( 'Newer comments', 'basalt' ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="comments__closed"><?php esc_html_e( 'Comments are closed.', 'basalt' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'         => 'comment-form',
			'title_reply'        => esc_html__( 'Leave a comment', 'basalt' ),
			'title_reply_to'     => esc_html__( 'Reply to %s', 'basalt' ),
			'cancel_reply_link'  => esc_html__( 'Cancel reply', 'basalt' ),
			'label_submit'       => esc_html__( 'Post comment', 'basalt' ),
			'class_submit'       => 'button',
			'comment_notes_before' => sprintf(
				'<p class="comment-form__notes">%s</p>',
				esc_html__( 'Your email address will not be published. Required fields are marked with an asterisk.', 'basalt' )
			),
		)
	);
	?>

</section>
