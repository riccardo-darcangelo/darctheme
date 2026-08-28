<?php
/**
 * Title: Catalog grid, three cards
 * Slug: basalt/catalog-grid
 * Categories: basalt-catalog
 * Description: Three cards with an image, a title, a short description and a link. For products, services or projects.
 * Keywords: cards, products, services, grid, catalog, portfolio
 * Viewport width: 1400
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_cards = array(
	array(
		'title' => _x( 'First item', 'Pattern placeholder', 'basalt' ),
		'text'  => _x( 'One sentence on what it is and one on who it suits. Put the model name in the heading, that is what people search for.', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'title' => _x( 'Second item', 'Pattern placeholder', 'basalt' ),
		'text'  => _x( 'Keep the descriptions the same length across cards, otherwise the grid looks unfinished.', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'title' => _x( 'Third item', 'Pattern placeholder', 'basalt' ),
		'text'  => _x( 'Link every card to its own page. A page per item is what makes the long tail of search work.', 'Pattern placeholder', 'basalt' ),
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide"><?php echo esc_html_x( 'What we offer', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--30)">
		<?php foreach ( $basalt_cards as $basalt_card ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"className":"is-style-card","layout":{"type":"constrained"}} -->
			<div class="wp-block-group is-style-card">
				<!-- wp:image {"aspectRatio":"8/5","scale":"cover","sizeSlug":"large","className":"is-style-rounded-lg"} -->
				<figure class="wp-block-image size-large is-style-rounded-lg"><img alt="" style="aspect-ratio:8/5;object-fit:cover"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"fontSize":"large"} -->
				<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $basalt_card['title'] ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"contrast-soft"} -->
				<p class="has-contrast-soft-color has-text-color"><?php echo esc_html( $basalt_card['text'] ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:paragraph -->
				<p><a href="#"><?php echo esc_html_x( 'Details', 'Pattern placeholder', 'basalt' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
