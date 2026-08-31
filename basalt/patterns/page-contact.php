<?php
/**
 * Title: Contact page
 * Slug: basalt/page-contact
 * Categories: basalt-page
 * Block Types: core/post-content
 * Post Types: page
 * Description: A complete contact page: how to reach you, when you are open, what happens after an enquiry, and room for a form.
 * Keywords: page, contact, enquiry, address, opening hours, full page
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * No form is included, because the theme does not ship one and a fake one made
 * of inputs that go nowhere is worse than an empty space. Put the shortcode or
 * the block of whichever form plugin you use where the note says so; Contact
 * Form 7 and WPForms are both styled by the theme.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:pattern {"slug":"basalt/contact-details"} /-->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'Send us the job', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"textColor":"contrast-soft"} -->
	<p class="has-contrast-soft-color has-text-color"><?php echo esc_html_x( 'Replace this paragraph with your form. Contact Form 7 and WPForms are both styled by the theme, so their fields and buttons will already match.', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"basalt/steps-process"} /-->

<!-- wp:pattern {"slug":"basalt/faq-accordion"} /-->
