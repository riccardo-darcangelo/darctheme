<?php
/**
 * Title: Address and opening hours
 * Slug: basalt/contact-details
 * Categories: basalt-cta
 * Description: Where you are, when you are open and how to reach you, in three columns. Fill it in to match Settings, Search and schema, so visitors and search engines are told the same thing.
 * Keywords: contact, address, opening hours, phone, email, where to find us
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The phone number and the email are real links, not text. A number that
 * cannot be tapped on a phone is a number that gets copied wrong, and on this
 * kind of page the phone is usually the point.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'Where to find us', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--40)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"fontSize":"small","className":"is-style-eyebrow"} -->
			<h3 class="wp-block-heading is-style-eyebrow has-small-font-size"><?php echo esc_html_x( 'Address', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'Street and number', 'Pattern placeholder', 'basalt' ); ?><br><?php echo esc_html_x( 'Postcode and town', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"fontSize":"small","className":"is-style-eyebrow"} -->
			<h3 class="wp-block-heading is-style-eyebrow has-small-font-size"><?php echo esc_html_x( 'Open', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p><?php echo esc_html_x( 'Monday to Friday, 7 to 18', 'Pattern placeholder', 'basalt' ); ?><br><?php echo esc_html_x( 'Saturday by arrangement', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"fontSize":"small","className":"is-style-eyebrow"} -->
			<h3 class="wp-block-heading is-style-eyebrow has-small-font-size"><?php echo esc_html_x( 'Reach us', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p><a href="tel:+490000000000"><?php echo esc_html_x( '+49 000 0000000', 'Pattern placeholder', 'basalt' ); ?></a><br><a href="mailto:hello@example.com">hello@example.com</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
