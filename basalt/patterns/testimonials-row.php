<?php
/**
 * Title: Two quotations
 * Slug: basalt/testimonials-row
 * Categories: basalt-content
 * Description: Two customer quotations side by side, each with who said it.
 * Keywords: testimonials, quotes, reviews, customers, references
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * Real blockquote and cite elements rather than styled paragraphs. A quotation
 * that is only styled to look like one is not a quotation to anything that is
 * not looking at it.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'What customers say', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--40)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:quote {"className":"is-style-testimonial"} -->
			<blockquote class="wp-block-quote is-style-testimonial">
				<!-- wp:paragraph -->
				<p><?php echo esc_html_x( 'One specific thing that went right, in the customer\'s own words. Specific beats enthusiastic.', 'Pattern placeholder', 'basalt' ); ?></p>
				<!-- /wp:paragraph -->
				<cite><?php echo esc_html_x( 'A name, a role, a town', 'Pattern placeholder', 'basalt' ); ?></cite>
			</blockquote>
			<!-- /wp:quote -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:quote {"className":"is-style-testimonial"} -->
			<blockquote class="wp-block-quote is-style-testimonial">
				<!-- wp:paragraph -->
				<p><?php echo esc_html_x( 'A second one, about something different. Two quotations that praise the same thing read as one.', 'Pattern placeholder', 'basalt' ); ?></p>
				<!-- /wp:paragraph -->
				<cite><?php echo esc_html_x( 'A second name and town', 'Pattern placeholder', 'basalt' ); ?></cite>
			</blockquote>
			<!-- /wp:quote -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
