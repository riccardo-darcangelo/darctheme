<?php
/**
 * Title: Three prices
 * Slug: basalt/pricing-table
 * Categories: basalt-catalog
 * Description: Three cards with a price, what is included and a button. The middle one is marked as the usual choice.
 * Keywords: pricing, prices, plans, packages, rates
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The recommended card is marked with a word as well as a colour. Colour alone
 * is not a cue, and "most chosen" is also the thing a visitor is actually
 * looking for, so saying it in text is better copy and better markup at once.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"textAlign":"center"} -->
	<h2 class="wp-block-heading has-text-align-center"><?php echo esc_html_x( 'What it costs', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"contrast-soft","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
	<p class="has-text-align-center has-contrast-soft-color has-text-color" style="margin-bottom:var(--wp--preset--spacing--40)"><?php echo esc_html_x( 'Per week, delivery and collection included. Longer hires are quoted individually.', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Day rate', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"x-large"} -->
			<p class="has-x-large-font-size"><?php echo esc_html_x( '90 euro', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'One working day on site', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Set up by our crew', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'Enquire', 'Pattern placeholder', 'basalt' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
			<p class="is-style-eyebrow"><?php echo esc_html_x( 'Most chosen', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Week rate', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"x-large"} -->
			<p class="has-x-large-font-size"><?php echo esc_html_x( '340 euro', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Five working days', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Replacement within 24 hours', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Delivery and collection included', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'Enquire', 'Pattern placeholder', 'basalt' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card"} -->
		<div class="wp-block-column is-style-card">
			<!-- wp:heading {"level":3,"fontSize":"medium"} -->
			<h3 class="wp-block-heading has-medium-font-size"><?php echo esc_html_x( 'Month rate', 'Pattern placeholder', 'basalt' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"x-large"} -->
			<p class="has-x-large-font-size"><?php echo esc_html_x( '1090 euro', 'Pattern placeholder', 'basalt' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Four weeks on site', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php echo esc_html_x( 'Service visit halfway', 'Pattern placeholder', 'basalt' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'Enquire', 'Pattern placeholder', 'basalt' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
