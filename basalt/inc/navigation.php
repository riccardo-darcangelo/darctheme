<?php
/**
 * Navigation: accessible walker, fallback menu, current-page markers.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nav menu walker that adds a real button for every submenu.
 *
 * A submenu that only opens on hover is unusable with a keyboard and on touch.
 * This walker renders the link and a separate toggle button, so the parent item
 * stays a working link while the button owns the open/close state and the
 * aria-expanded value that assistive technology reads.
 */
class Basalt_Walker_Nav_Menu extends Walker_Nav_Menu {

	/**
	 * Open a submenu level.
	 *
	 * @param string   $output Menu markup, passed by reference.
	 * @param int      $depth  Current depth.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"submenu submenu--depth-{$depth}\">\n";
	}

	/**
	 * Close a submenu level.
	 *
	 * @param string   $output Menu markup, passed by reference.
	 * @param int      $depth  Current depth.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Render a single menu item.
	 *
	 * @param string   $output            Menu markup, passed by reference.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Current depth.
	 * @param stdClass $args              Menu arguments.
	 * @param int      $current_object_id Current object ID.
	 * @return void
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item          = $data_object;
		$has_children  = in_array( 'menu-item-has-children', (array) $item->classes, true );
		$classes       = array_filter( (array) $item->classes );
		$classes[]     = 'menu-item-' . $item->ID;
		$class_attr    = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
		$title         = apply_filters( 'the_title', $item->title, $item->ID );
		$link_attr     = array(
			'href'   => $item->url,
			'title'  => $item->attr_title,
			'target' => $item->target,
			'rel'    => $item->xfn,
			'class'  => 'menu-item__link',
		);
		$is_current    = in_array( 'current-menu-item', (array) $item->classes, true );
		$is_ancestor   = in_array( 'current-menu-ancestor', (array) $item->classes, true );
		$toggle_id     = 'submenu-toggle-' . $item->ID;
		$controlled_id = 'submenu-' . $item->ID;

		if ( $is_current ) {
			$link_attr['aria-current'] = 'page';
		} elseif ( $is_ancestor ) {
			$link_attr['aria-current'] = 'true';
		}

		/** This filter is documented in wp-includes/class-walker-nav-menu.php */
		$link_attr = apply_filters( 'nav_menu_link_attributes', $link_attr, $item, $args, $depth );

		$attributes = '';

		foreach ( $link_attr as $name => $value ) {
			if ( '' === $value || false === $value || null === $value ) {
				continue;
			}

			$value       = ( 'href' === $name ) ? esc_url( $value ) : esc_attr( $value );
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), $value );
		}

		$output .= sprintf(
			'<li class="menu-item %1$s%2$s">',
			esc_attr( $class_attr ),
			$has_children ? ' menu-item--has-submenu' : ''
		);

		$output .= sprintf(
			'<a%1$s>%2$s</a>',
			$attributes,
			esc_html( $title )
		);

		if ( $has_children ) {
			$output .= sprintf(
				'<button type="button" class="submenu-toggle" id="%1$s" aria-expanded="false" aria-controls="%2$s"><span class="screen-reader-text">%3$s</span><span class="submenu-toggle__icon" aria-hidden="true"></span></button>',
				esc_attr( $toggle_id ),
				esc_attr( $controlled_id ),
				esc_html(
					sprintf(
						/* translators: %s: parent menu item title. */
						__( 'Show submenu of %s', 'basalt' ),
						$title
					)
				)
			);
		}
	}

	/**
	 * Close a menu item.
	 *
	 * @param string   $output      Menu markup, passed by reference.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Current depth.
	 * @param stdClass $args        Menu arguments.
	 * @return void
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= "</li>\n";
	}
}

/**
 * Render a registered nav menu location.
 *
 * @param string               $location Menu location slug.
 * @param array<string, mixed> $args     Overrides for wp_nav_menu().
 * @return void
 */
function basalt_nav_menu( string $location, array $args = array() ): void {
	if ( ! has_nav_menu( $location ) && 'primary' !== $location ) {
		return;
	}

	wp_nav_menu(
		wp_parse_args(
			$args,
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'menu menu--' . $location,
				'depth'          => 3,
				'walker'         => new Basalt_Walker_Nav_Menu(),
				'fallback_cb'    => 'basalt_nav_menu_fallback',
			)
		)
	);
}

/**
 * Fallback when no menu is assigned to the primary location.
 *
 * Lists top level pages so a fresh install is navigable before the site owner
 * builds a menu, and points administrators at the menu screen.
 *
 * @param array<string, mixed> $args Menu arguments.
 * @return void
 */
function basalt_nav_menu_fallback( $args ): void {
	$pages = wp_list_pages(
		array(
			'echo'        => false,
			'title_li'    => '',
			'depth'       => 1,
			'sort_column' => 'menu_order, post_title',
		)
	);

	if ( ! $pages ) {
		return;
	}

	printf(
		'<ul class="menu menu--fallback">%s</ul>',
		wp_kses_post( str_replace( 'page_item', 'menu-item page_item', $pages ) )
	);

	if ( current_user_can( 'edit_theme_options' ) ) {
		printf(
			'<p class="menu-notice"><a href="%1$s">%2$s</a></p>',
			esc_url( admin_url( 'nav-menus.php' ) ),
			esc_html__( 'Set up your menu', 'basalt' )
		);
	}
}

/**
 * Whether the primary navigation should render at all.
 *
 * @return bool
 */
function basalt_has_primary_navigation(): bool {
	return has_nav_menu( 'primary' ) || (bool) get_pages( array( 'number' => 1 ) );
}
