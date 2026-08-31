<?php
/**
 * Title: Two alternating image and text rows
 * Slug: basalt/media-alternating
 * Categories: basalt-content
 * Description: Two rows where the image and the text swap sides, for explaining two things one after the other without repeating the same layout.
 * Keywords: image, text, media, alternating, features, split
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The sides swap by swapping the markup, not by reversing it in CSS. That
 * matters on a phone, where the columns stack in the order they are written:
 * the first row leads with its image, the second leads with its heading. A row
 * reversed in CSS alone would still be read image first, and the visual order
 * and the reading order would disagree.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
			<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:4/3;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'The first thing', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Two or three sentences. The image carries the feeling, the text carries the fact, and neither should be doing both.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'The second thing', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Same length as the first. Two rows of very different lengths look like one of them was an afterthought.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
			<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:4/3;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
