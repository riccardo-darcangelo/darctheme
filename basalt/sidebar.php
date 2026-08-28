<?php
/**
 * The sidebar.
 *
 * Rendered as a complementary landmark only when it actually has content, so
 * an empty aside never reaches the accessibility tree.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

if ( ! basalt_has_sidebar() ) {
	return;
}
?>

<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'basalt' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
