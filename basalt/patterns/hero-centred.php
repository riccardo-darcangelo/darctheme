<?php
/**
 * Title: Centred hero
 * Slug: basalt/hero-centred
 * Categories: basalt-hero
 * Description: A centred headline, a lead paragraph and two buttons, on a tinted band. The headline is the page level one, so use it on the "Landing page" template, which prints no title of its own.
 * Keywords: hero, header, intro, opening, centred
 * Viewport width: 1400
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
	<p class="has-text-align-center is-style-eyebrow"><?php echo esc_html_x( 'Since 1998', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"display"} -->
	<h1 class="wp-block-heading has-text-align-center has-display-font-size"><?php echo esc_html_x( 'One sentence that says what you do', 'Pattern placeholder', 'basalt' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"is-style-lead","textColor":"contrast-soft"} -->
	<p class="has-text-align-center is-style-lead has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'A second sentence for the part that does not fit in the first. Who it is for, where you work, how quickly you answer.', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'Request a quote', 'Pattern placeholder', 'basalt' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'See the range', 'Pattern placeholder', 'basalt' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
