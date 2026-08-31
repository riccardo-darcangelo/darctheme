<?php
/**
 * Title: Row of figures
 * Slug: basalt/stats-row
 * Categories: basalt-content
 * Description: Three or four numbers with a short label under each, for the facts a visitor wants before they read anything else.
 * Keywords: stats, numbers, figures, facts, key data
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The number and its label are one paragraph each rather than a heading and a
 * paragraph. A figure is not a section title, and putting four of them in the
 * heading outline gives a screen reader user four entries that say "140" and
 * nothing about what 140 is.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"align":"center","fontSize":"display"} -->
			<p class="has-text-align-center has-display-font-size"><?php echo esc_html_x( '140', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","fontSize":"small","textColor":"contrast-soft"} -->
			<p class="has-text-align-center has-contrast-soft-color has-text-color has-small-font-size"><?php echo esc_html_x( 'Machines in the fleet', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"align":"center","fontSize":"display"} -->
			<p class="has-text-align-center has-display-font-size"><?php echo esc_html_x( '28', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","fontSize":"small","textColor":"contrast-soft"} -->
			<p class="has-text-align-center has-contrast-soft-color has-text-color has-small-font-size"><?php echo esc_html_x( 'Years on building sites', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"align":"center","fontSize":"display"} -->
			<p class="has-text-align-center has-display-font-size"><?php echo esc_html_x( '1.4', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","fontSize":"small","textColor":"contrast-soft"} -->
			<p class="has-text-align-center has-contrast-soft-color has-text-color has-small-font-size"><?php echo esc_html_x( 'Working days to delivery', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
