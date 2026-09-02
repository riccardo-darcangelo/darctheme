<?php
/**
 * Title: Visit the shop
 * Slug: basalt/store-visit
 * Categories: basalt-cta
 * Description: A photograph of the shop beside the address, the opening hours and two buttons: directions and a phone call. For a business whose website exists to bring people through the door.
 * Keywords: shop, store, visit, directions, opening hours, local, call, find us
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The directions button is an ordinary link to a map, so it opens whichever
 * maps app the phone has. The phone number is a tel: link on a button rather
 * than a line of text: on a phone the call is the conversion, and a number
 * that has to be copied first is a number that is not called.
 *
 * The address and the hours are paragraphs, not a table and not headings. A
 * screen reader user reads them in order, exactly as a sighted visitor does.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"46%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:46%">
			<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
			<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:4/3;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
			<p class="is-style-eyebrow"><?php echo esc_html_x( 'Come and see us', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html_x( 'Try it on, in the shop', 'Pattern placeholder', 'basalt' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'One sentence on what happens when somebody walks in: who greets them, whether they need an appointment, how long it takes. Take away the uncertainty and the visit follows.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><strong><?php echo esc_html_x( 'Street and number, postcode and town', 'Pattern placeholder', 'basalt' ); ?></strong><br><?php echo esc_html_x( 'Tuesday to Friday 10 to 18, Saturday 10 to 14', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
			<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://www.google.com/maps/search/?api=1&amp;query=Street+1+Town"><?php echo esc_html_x( 'Get directions', 'Pattern placeholder', 'basalt' ); ?></a></div>
				<!-- /wp:button -->

				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="tel:+490000000000"><?php echo esc_html_x( 'Call +49 000 0000000', 'Pattern placeholder', 'basalt' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
