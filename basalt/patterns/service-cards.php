<?php
/**
 * Title: Three services
 * Slug: basalt/service-cards
 * Categories: basalt-catalog
 * Description: Three cards, each with a heading, two lines and a link, for the things you offer.
 * Keywords: services, cards, offer, what we do, three columns
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The link text says where it goes. "Read more" three times on one page gives
 * a screen reader user a list of three identical links and no way to choose,
 * which is the most common avoidable failure on a page like this.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'What we do', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--40)">
		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Hire', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'By the day, the week or the month, delivered and collected.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a href="#"><?php echo esc_html_x( 'What we hire out', 'Pattern placeholder', 'basalt' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Set up on site', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Our crew rigs it, tests it under load and hands it over ready to work.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a href="#"><?php echo esc_html_x( 'How setting up works', 'Pattern placeholder', 'basalt' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Service and repair', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Our own workshop, so a machine that fails on a Friday is replaced on the Friday.', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a href="#"><?php echo esc_html_x( 'What the workshop covers', 'Pattern placeholder', 'basalt' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
