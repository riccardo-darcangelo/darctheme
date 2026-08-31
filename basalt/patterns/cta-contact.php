<?php
/**
 * Title: Call to action with contact details
 * Slug: basalt/cta-contact
 * Categories: basalt-cta
 * Description: A closing section with a headline, a button and the phone number and email as real links.
 * Keywords: cta, contact, call to action, enquiry, phone
 * Viewport width: 1200
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"primary","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","textColor":"base"} -->
	<h2 class="wp-block-heading has-text-align-center has-base-color has-text-color"><?php echo esc_html_x( 'Tell us what you need', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
	<p class="has-text-align-center has-large-font-size"><?php echo esc_html_x( 'Send us the project and we will come back with a quote, usually the same working day.', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
	<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
		<!-- wp:button {"backgroundColor":"base","textColor":"contrast"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-contrast-color has-base-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html_x( 'Send an enquiry', 'Pattern placeholder', 'basalt' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

	<!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base","fontSize":"small"} -->
	<p class="has-text-align-center has-base-color has-text-color has-link-color has-small-font-size" style="margin-top:var(--wp--preset--spacing--30)"><a href="tel:+490000000000"><?php echo esc_html_x( '+49 000 0000000', 'Pattern placeholder', 'basalt' ); ?></a> · <a href="mailto:hello@example.com">hello@example.com</a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
