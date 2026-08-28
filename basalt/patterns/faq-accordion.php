<?php
/**
 * Title: FAQ with structured data
 * Slug: basalt/faq-accordion
 * Categories: basalt-faq
 * Description: Questions and answers as expandable items. Every item styled as "FAQ item" is picked up automatically and emitted as FAQPage structured data.
 * Keywords: faq, questions, accordion, schema, structured data, rich results
 * Viewport width: 1200
 *
 * @package Basalt
 *
 * The block style "is-style-faq" is what inc/seo.php looks for. Keep it on the
 * details blocks; without it the section still renders, it just no longer
 * produces structured data.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
	<p class="is-style-eyebrow"><?php echo esc_html_x( 'FAQ', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'Questions we get asked', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"textColor":"contrast-soft","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|30"}}}} -->
	<p class="has-contrast-soft-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--30)"><?php echo esc_html_x( 'Write the question the way a customer would ask it out loud, and answer it in the first sentence. Search engines quote that first sentence.', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:details {"summary":"<?php echo esc_attr_x( 'How quickly can you deliver?', 'Pattern placeholder', 'basalt' ); ?>","className":"is-style-faq"} -->
	<details class="wp-block-details is-style-faq"><summary><?php echo esc_html_x( 'How quickly can you deliver?', 'Pattern placeholder', 'basalt' ); ?></summary>
		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'Answer in two or three sentences. Give the concrete number first, then the condition it depends on.', 'Pattern placeholder', 'basalt' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"summary":"<?php echo esc_attr_x( 'Which areas do you cover?', 'Pattern placeholder', 'basalt' ); ?>","className":"is-style-faq"} -->
	<details class="wp-block-details is-style-faq"><summary><?php echo esc_html_x( 'Which areas do you cover?', 'Pattern placeholder', 'basalt' ); ?></summary>
		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'Name the regions explicitly. A place name in an answer is one of the few things that reliably helps a local search.', 'Pattern placeholder', 'basalt' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"summary":"<?php echo esc_attr_x( 'What does it cost?', 'Pattern placeholder', 'basalt' ); ?>","className":"is-style-faq"} -->
	<details class="wp-block-details is-style-faq"><summary><?php echo esc_html_x( 'What does it cost?', 'Pattern placeholder', 'basalt' ); ?></summary>
		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'Even a range beats "on request". A page that answers the price question keeps the visitor from leaving to find it elsewhere.', 'Pattern placeholder', 'basalt' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</div>
<!-- /wp:group -->
