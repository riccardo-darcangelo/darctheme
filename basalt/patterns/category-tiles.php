<?php
/**
 * Title: Four collection tiles
 * Slug: basalt/category-tiles
 * Categories: basalt-catalog
 * Description: Four image tiles, each linking to a category or collection, in a grid that reflows on its own. The whole tile is the link, and the words on it say where it goes. The overlay ships fully opaque so the text is readable before an image is added; add yours and lower it.
 * Keywords: categories, collections, tiles, grid, shop, range, departments
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * Each tile is a cover block in the "Tile" style, which stretches the heading
 * link over the whole block. One link per tile, not one on the image and one on
 * the words: two links to the same place read as two stops to a screen reader
 * and as a stutter to a keyboard.
 *
 * The eyebrow inside a tile is set to the base colour explicitly. Its default
 * is the accent, which does not clear 4.5:1 on the primary overlay.
 */

defined( 'ABSPATH' ) || exit;

$basalt_tiles = array(
	array(
		'eyebrow' => _x( 'From 65 to 115', 'Pattern placeholder', 'basalt' ),
		'title'   => _x( 'First collection', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'eyebrow' => _x( 'New this season', 'Pattern placeholder', 'basalt' ),
		'title'   => _x( 'Second collection', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'eyebrow' => _x( 'For every day', 'Pattern placeholder', 'basalt' ),
		'title'   => _x( 'Third collection', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'eyebrow' => _x( 'Gifts', 'Pattern placeholder', 'basalt' ),
		'title'   => _x( 'Fourth collection', 'Pattern placeholder', 'basalt' ),
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide"><?php echo esc_html_x( 'Browse the range', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"grid","minimumColumnWidth":"15rem"}} -->
	<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--30)">
		<?php foreach ( $basalt_tiles as $basalt_tile ) : ?>
		<!-- wp:cover {"overlayColor":"primary","dimRatio":100,"minHeight":20,"minHeightUnit":"rem","className":"is-style-tile","layout":{"type":"constrained"}} -->
		<div class="wp-block-cover is-style-tile" style="min-height:20rem"><span aria-hidden="true" class="wp-block-cover__background has-primary-background-color has-background-dim-100 has-background-dim"></span>
			<div class="wp-block-cover__inner-container">
				<!-- wp:paragraph {"className":"is-style-eyebrow","textColor":"base"} -->
				<p class="is-style-eyebrow has-base-color has-text-color"><?php echo esc_html( $basalt_tile['eyebrow'] ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":3,"textColor":"base","fontSize":"x-large"} -->
				<h3 class="wp-block-heading has-base-color has-text-color has-x-large-font-size"><a href="#"><?php echo esc_html( $basalt_tile['title'] ); ?></a></h3>
				<!-- /wp:heading -->
			</div>
		</div>
		<!-- /wp:cover -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
