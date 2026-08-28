<?php
/**
 * Title: Text with a checklist
 * Slug: basalt/feature-list
 * Categories: basalt-content
 * Description: An image beside a heading, a paragraph and a list of ticked points.
 * Keywords: features, benefits, checklist, about, text and image
 * Viewport width: 1400
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
			<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
			<p class="is-style-eyebrow"><?php echo esc_html_x( 'Why us', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'The part that actually matters', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Say the thing a competitor cannot copy. Numbers, years, names of places: anything specific outperforms an adjective.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'A concrete promise, not a slogan', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'A second one, same length', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Three to five points, no more', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
