<?php
/**
 * Title: Latest three posts
 * Slug: basalt/post-grid
 * Categories: basalt-content
 * Description: The three most recent posts as cards with their featured image, title and excerpt, for putting the journal on a page that is not the journal.
 * Keywords: posts, blog, news, journal, latest, query
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * The heading above the loop is what names it. Two query loops on one page
 * with no headings between them are two lists a screen reader user cannot
 * tell apart.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'From the journal', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-query alignwide" style="margin-top:var(--wp--preset--spacing--40)">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"8/5","style":{"border":{"radius":"8px"}}} /-->

			<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"medium"} /-->

			<!-- wp:post-excerpt {"moreText":"","excerptLength":22,"fontSize":"small","textColor":"contrast-soft"} /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
