<?php
/**
 * Title: How it works, in steps
 * Slug: basalt/steps-process
 * Categories: basalt-content
 * Description: Three numbered steps explaining what happens between an enquiry and the work being done.
 * Keywords: steps, process, how it works, numbered
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * An ordered list rather than three columns of loose text. The order is the
 * content: a screen reader announces "list, three items" and the position of
 * each one, which is exactly what the numbers are drawn for sighted readers.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:paragraph {"className":"is-style-eyebrow"} -->
	<p class="is-style-eyebrow"><?php echo esc_html_x( 'How it works', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'From the first call to the machine on site', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:list {"ordered":true,"className":"is-style-steps","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
	<ol class="wp-block-list is-style-steps" style="margin-top:var(--wp--preset--spacing--40)">
		<!-- wp:list-item -->
		<li><strong><?php echo esc_html_x( 'Tell us the job', 'Pattern placeholder', 'basalt' ); ?></strong><br><?php echo esc_html_x( 'What has to go up, how high, and when. A photograph of the site answers most of it.', 'Pattern placeholder', 'basalt' ); ?></li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li><strong><?php echo esc_html_x( 'We pick the machine', 'Pattern placeholder', 'basalt' ); ?></strong><br><?php echo esc_html_x( 'Usually the same working day, with the price and the delivery date in writing.', 'Pattern placeholder', 'basalt' ); ?></li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li><strong><?php echo esc_html_x( 'Delivered and set up', 'Pattern placeholder', 'basalt' ); ?></strong><br><?php echo esc_html_x( 'Our crew rigs it, tests it under load and hands it over. Collection is included.', 'Pattern placeholder', 'basalt' ); ?></li>
		<!-- /wp:list-item -->
	</ol>
	<!-- /wp:list -->
</div>
<!-- /wp:group -->
