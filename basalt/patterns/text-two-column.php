<?php
/**
 * Title: Heading with two columns of text
 * Slug: basalt/text-two-column
 * Categories: basalt-content
 * Description: A section heading with the prose set in two columns beside it, for a longer explanation that would be a wall of text in one.
 * Keywords: text, two columns, prose, about, explanation
 * Viewport width: 1400
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"34%"} -->
		<div class="wp-block-column" style="flex-basis:34%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'How we got here', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"is-style-lead"} -->
			<p class="is-style-lead"><?php echo esc_html_x( 'Open with the sentence somebody would repeat to a colleague. The rest of the section earns its place by explaining it.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'Two or three paragraphs is the right length here. Anything longer belongs on its own page, and anything shorter did not need a section of its own.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'The second column continues the thought rather than starting a new one. Two columns of unrelated text read as two sections that lost their headings.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'On a phone these stack, so write them in the order you want them read.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
