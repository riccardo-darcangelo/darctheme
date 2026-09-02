<?php
/**
 * Title: Row of reassurances
 * Slug: basalt/trust-row
 * Categories: basalt-content
 * Description: Four short reasons to trust you, in one row directly under the opening section: the things a first-time visitor checks before reading anything else.
 * Keywords: trust, reassurance, benefits, guarantees, why us, usp, proof
 * Viewport width: 1400
 *
 * @package Basalt
 *
 * Each reassurance is two paragraphs, a bold line and a quiet one, rather than
 * a heading and a paragraph. A reassurance is not a section: four headings
 * that say "Fitted in person" put four entries in the document outline with
 * nothing underneath them. The same reasoning as the row of figures.
 *
 * Four is the number for a reason. Three reads as a slogan, five wraps on a
 * tablet, and a visitor who has to count them has stopped reading.
 */

defined( 'ABSPATH' ) || exit;

$basalt_items = array(
	array(
		'lead' => _x( 'Fitted in person', 'Pattern placeholder', 'basalt' ),
		'text' => _x( 'Measured properly, every visit, at no charge.', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'lead' => _x( 'Sizes 65 to 115, cups A to L', 'Pattern placeholder', 'basalt' ),
		'text' => _x( 'The range most shops stop short of.', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'lead' => _x( 'Alterations in house', 'Pattern placeholder', 'basalt' ),
		'text' => _x( 'Straps, bands and hems adjusted on the spot.', 'Pattern placeholder', 'basalt' ),
	),
	array(
		'lead' => _x( 'Rated 4.9 by 35 customers', 'Pattern placeholder', 'basalt' ),
		'text' => _x( 'Read the reviews on Google, they are not ours to edit.', 'Pattern placeholder', 'basalt' ),
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}},"border":{"top":{"color":"var:preset|color|border","style":"solid","width":"1px"},"bottom":{"color":"var:preset|color|border","style":"solid","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-top-color:var(--wp--preset--color--border);border-top-style:solid;border-top-width:1px;border-bottom-color:var(--wp--preset--color--border);border-bottom-style:solid;border-bottom-width:1px;padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">
	<!-- wp:columns {"align":"wide","className":"is-style-tight"} -->
	<div class="wp-block-columns alignwide is-style-tight">
		<?php foreach ( $basalt_items as $basalt_item ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"0.25rem"}}}} -->
			<p style="margin-bottom:0.25rem"><strong><?php echo esc_html( $basalt_item['lead'] ); ?></strong></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"fontSize":"small","textColor":"contrast-soft"} -->
			<p class="has-contrast-soft-color has-text-color has-small-font-size"><?php echo esc_html( $basalt_item['text'] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
