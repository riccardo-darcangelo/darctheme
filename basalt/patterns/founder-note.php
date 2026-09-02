<?php
/**
 * Title: A word from the owner
 * Slug: basalt/founder-note
 * Categories: basalt-content
 * Description: A portrait beside a few sentences in the owner's own voice, signed with a name. The section a small business most often leaves out, and the one visitors remember.
 * Keywords: owner, founder, about, portrait, personal, story, who we are
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The portrait comes first in the markup and on the left, because a face is
 * what makes the words underneath it believable. Written in the first person:
 * "we" is a company, "I" is somebody who will be there when you walk in.
 *
 * The signature is a paragraph in the soft colour, not a caption and not a
 * heading. It belongs to the text above it, and that is where a screen reader
 * puts it.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"38%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:38%">
			<!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
			<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:4/5;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
			<p class="is-style-eyebrow"><?php echo esc_html_x( 'Who you will meet', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'Why I still open the door myself', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size"><?php echo esc_html_x( 'Two or three sentences in the first person: how the business started, what you refuse to compromise on, and what a customer can expect from you personally.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'One more paragraph if there is a story worth telling, such as the generation before you or the day you moved to the current address. Cut anything that reads like a brochure.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-soft","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
			<p class="has-contrast-soft-color has-text-color has-small-font-size" style="margin-top:var(--wp--preset--spacing--30)"><strong><?php echo esc_html_x( 'First and last name', 'Pattern placeholder', 'basalt' ); ?></strong><br><?php echo esc_html_x( 'Owner, third generation', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
