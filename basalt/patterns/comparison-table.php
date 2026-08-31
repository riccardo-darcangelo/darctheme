<?php
/**
 * Title: Comparison table
 * Slug: basalt/comparison-table
 * Categories: basalt-content
 * Description: A table comparing two or three options across the same rows, with the first column as the row header.
 * Keywords: comparison, table, versus, options, differences
 * Viewport width: 1200
 *
 * @package Basalt
 *
 * The first cell of each row is a th with scope="row", which is what lets a
 * screen reader say "Load capacity, 200 kilograms" instead of reading a wall
 * of numbers with nothing attached to them. Core's table block keeps the
 * markup, so editing the text does not undo it.
 *
 * The theme wraps any table wider than its column in a scrollable region and
 * labels it, so this does not need to be responsive by itself.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html_x( 'Which one fits', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:table {"className":"is-style-specs"} -->
	<figure class="wp-block-table is-style-specs">
		<table>
			<thead>
				<tr>
					<th scope="col"><?php echo esc_html_x( 'What matters', 'Pattern placeholder', 'basalt' ); ?></th>
					<th scope="col"><?php echo esc_html_x( 'The small one', 'Pattern placeholder', 'basalt' ); ?></th>
					<th scope="col"><?php echo esc_html_x( 'The big one', 'Pattern placeholder', 'basalt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th scope="row"><?php echo esc_html_x( 'Load capacity', 'Pattern placeholder', 'basalt' ); ?></th>
					<td><?php echo esc_html_x( '200 kg', 'Pattern placeholder', 'basalt' ); ?></td>
					<td><?php echo esc_html_x( '500 kg', 'Pattern placeholder', 'basalt' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html_x( 'Power supply', 'Pattern placeholder', 'basalt' ); ?></th>
					<td><?php echo esc_html_x( '230 V', 'Pattern placeholder', 'basalt' ); ?></td>
					<td><?php echo esc_html_x( '400 V', 'Pattern placeholder', 'basalt' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html_x( 'Crew to rig it', 'Pattern placeholder', 'basalt' ); ?></th>
					<td><?php echo esc_html_x( 'Two people', 'Pattern placeholder', 'basalt' ); ?></td>
					<td><?php echo esc_html_x( 'Two people and a trailer', 'Pattern placeholder', 'basalt' ); ?></td>
				</tr>
			</tbody>
		</table>
	</figure>
	<!-- /wp:table -->
</div>
<!-- /wp:group -->
