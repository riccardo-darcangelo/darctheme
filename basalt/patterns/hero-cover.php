<?php
/**
 * Title: Hero with a background image
 * Slug: basalt/hero-cover
 * Categories: basalt-hero
 * Description: A full width band with the headline over it, ready for a background image. The overlay starts fully opaque so the text is readable before an image is added; add yours and lower the overlay until it looks right. The headline is the page level one, so use it on the "Landing page" template, which prints no title of its own.
 * Keywords: hero, cover, image, background, opening
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The overlay is not decoration. Text over a photograph passes or fails
 * contrast depending on which photograph, and the only honest way to hold a
 * ratio is to put a known colour between the two.
 *
 * It ships fully opaque, which looks like a plain band, because a cover block
 * with no image yet is a 60 percent overlay over the page background: white
 * text on it measures 3.56:1, and that is the state the pattern is inserted
 * in. Shipping something that fails until the buyer does the next step is not
 * a pattern, it is homework.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"overlayColor":"primary","dimRatio":100,"minHeight":72,"minHeightUnit":"vh","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);min-height:72vh"><span aria-hidden="true" class="wp-block-cover__background has-primary-background-color has-background-dim-100 has-background-dim"></span>
	<div class="wp-block-cover__inner-container">
		<!-- wp:heading {"textAlign":"center","level":1,"textColor":"base","fontSize":"display"} -->
		<h1 class="wp-block-heading has-text-align-center has-base-color has-text-color has-display-font-size"><?php echo esc_html_x( 'One sentence that says what you do', 'Pattern placeholder', 'basalt' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","className":"is-style-lead","textColor":"base"} -->
		<p class="has-text-align-center is-style-lead has-base-color has-text-color"><?php echo esc_html_x( 'Replace the image with your own, and keep the overlay: it is what holds the text readable whatever the photograph turns out to be.', 'Pattern placeholder', 'basalt' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
			<!-- wp:button {"backgroundColor":"base","textColor":"contrast"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-contrast-color has-base-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html_x( 'Request a quote', 'Pattern placeholder', 'basalt' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
</div>
<!-- /wp:cover -->
