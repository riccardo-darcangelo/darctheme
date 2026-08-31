<?php
/**
 * Title: Row of client logos
 * Slug: basalt/logo-strip
 * Categories: basalt-content
 * Description: A quiet row of four logos under one line of text. Give each logo the company name as its alternative text: a logo is a name, and an empty alt attribute deletes it.
 * Keywords: logos, clients, customers, partners, references, trust
 * Viewport width: 1400
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:paragraph {"align":"center","fontSize":"small","textColor":"contrast-soft"} -->
	<p class="has-text-align-center has-contrast-soft-color has-text-color has-small-font-size"><?php echo esc_html_x( 'Working for', 'Pattern placeholder', 'basalt' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"verticalAlignment":"center"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center" style="margin-top:var(--wp--preset--spacing--30)">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"aspectRatio":"3/1","scale":"contain","sizeSlug":"medium"} -->
		<figure class="wp-block-image size-medium"><img alt="" style="aspect-ratio:3/1;object-fit:contain"/></figure>
		<!-- /wp:image --></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"aspectRatio":"3/1","scale":"contain","sizeSlug":"medium"} -->
		<figure class="wp-block-image size-medium"><img alt="" style="aspect-ratio:3/1;object-fit:contain"/></figure>
		<!-- /wp:image --></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"aspectRatio":"3/1","scale":"contain","sizeSlug":"medium"} -->
		<figure class="wp-block-image size-medium"><img alt="" style="aspect-ratio:3/1;object-fit:contain"/></figure>
		<!-- /wp:image --></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"aspectRatio":"3/1","scale":"contain","sizeSlug":"medium"} -->
		<figure class="wp-block-image size-medium"><img alt="" style="aspect-ratio:3/1;object-fit:contain"/></figure>
		<!-- /wp:image --></div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
