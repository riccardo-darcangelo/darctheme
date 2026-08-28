<?php
/**
 * Title: Technical specifications
 * Slug: basalt/specs-table
 * Categories: basalt-content
 * Description: A two column table of technical data. Collapses to stacked rows on phones instead of scrolling sideways.
 * Keywords: table, specifications, data, technical, product
 * Viewport width: 1000
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_rows = array(
	array( _x( 'Load capacity', 'Pattern placeholder', 'basalt' ), _x( '200 kg', 'Pattern placeholder', 'basalt' ) ),
	array( _x( 'Maximum height', 'Pattern placeholder', 'basalt' ), _x( '30 m', 'Pattern placeholder', 'basalt' ) ),
	array( _x( 'Lifting speed', 'Pattern placeholder', 'basalt' ), _x( '30 m/min', 'Pattern placeholder', 'basalt' ) ),
	array( _x( 'Power supply', 'Pattern placeholder', 'basalt' ), _x( '230 V', 'Pattern placeholder', 'basalt' ) ),
	array( _x( 'Weight', 'Pattern placeholder', 'basalt' ), _x( '85 kg', 'Pattern placeholder', 'basalt' ) ),
);
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":2,"fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-x-large-font-size"><?php echo esc_html_x( 'Technical data', 'Pattern placeholder', 'basalt' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:table {"className":"is-style-specs"} -->
	<figure class="wp-block-table is-style-specs">
		<table>
			<tbody>
			<?php foreach ( $basalt_rows as $basalt_row ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $basalt_row[0] ); ?></th>
					<td><?php echo esc_html( $basalt_row[1] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<figcaption class="wp-element-caption"><?php echo esc_html_x( 'Subject to change. Values are for the standard configuration.', 'Pattern placeholder', 'basalt' ); ?></figcaption>
	</figure>
	<!-- /wp:table -->
</div>
<!-- /wp:group -->
