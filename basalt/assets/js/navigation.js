/**
 * Primary navigation.
 *
 * No dependencies, no jQuery. The menu works without this file: the markup is
 * a plain nested list of links, so a failed script leaves the site navigable.
 *
 * @package Basalt
 */

( function () {
	'use strict';

	var strings = window.basaltNavStrings || {};

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

	/**
	 * Whether the viewport is wide enough for the horizontal menu.
	 *
	 * Kept in sync with the --nav-breakpoint media query in components.css.
	 */
	var desktop = window.matchMedia( '(min-width: 62rem)' );

	function init() {
		var nav = document.getElementById( 'primary-navigation' );
		var toggle = document.querySelector( '.nav-toggle' );

		if ( ! nav ) {
			return;
		}

		if ( toggle ) {
			setUpMenuToggle( nav, toggle );
		}

		setUpSubmenus( nav );
	}

	/**
	 * The button that opens the whole menu on narrow viewports.
	 *
	 * @param {HTMLElement} nav    The navigation element.
	 * @param {HTMLElement} toggle The toggle button.
	 */
	function setUpMenuToggle( nav, toggle ) {
		var label = toggle.querySelector( '.nav-toggle__label' );

		function setState( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			document.documentElement.classList.toggle( 'has-menu-open', open );

			if ( label ) {
				label.textContent = open
					? strings.closeMenu || 'Close menu'
					: strings.openMenu || 'Menu';
			}

			if ( open ) {
				// Move focus into the menu so the next Tab lands on a link.
				var first = nav.querySelector( FOCUSABLE );

				if ( first ) {
					first.focus();
				}
			}
		}

		toggle.addEventListener( 'click', function () {
			setState( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		// Escape closes the menu and returns focus to the button that opened it.
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'Escape' || toggle.getAttribute( 'aria-expanded' ) !== 'true' ) {
				return;
			}

			setState( false );
			toggle.focus();
		} );

		// A click outside the navigation closes it.
		document.addEventListener( 'click', function ( event ) {
			if ( toggle.getAttribute( 'aria-expanded' ) !== 'true' ) {
				return;
			}

			if ( nav.contains( event.target ) || toggle.contains( event.target ) ) {
				return;
			}

			setState( false );
		} );

		// Keep focus inside the menu while it covers the screen.
		nav.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'Tab' || toggle.getAttribute( 'aria-expanded' ) !== 'true' || desktop.matches ) {
				return;
			}

			var items = Array.prototype.filter.call(
				nav.querySelectorAll( FOCUSABLE ),
				function ( element ) {
					return element.offsetParent !== null;
				}
			);

			if ( ! items.length ) {
				return;
			}

			var first = items[ 0 ];
			var last = items[ items.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				toggle.focus();
			}
		} );

		// Resizing to desktop must not leave the page locked in the open state.
		desktop.addEventListener( 'change', function ( event ) {
			if ( event.matches ) {
				setState( false );
			}
		} );
	}

	/**
	 * The buttons that open individual submenus.
	 *
	 * @param {HTMLElement} nav The navigation element.
	 */
	function setUpSubmenus( nav ) {
		var toggles = nav.querySelectorAll( '.submenu-toggle' );

		Array.prototype.forEach.call( toggles, function ( toggle ) {
			var item = toggle.closest( '.menu-item' );
			var submenu = item ? item.querySelector( '.submenu' ) : null;

			if ( ! submenu ) {
				return;
			}

			// The id is generated in PHP; make sure aria-controls resolves.
			if ( ! submenu.id ) {
				submenu.id = toggle.getAttribute( 'aria-controls' ) || '';
			}

			toggle.addEventListener( 'click', function () {
				var open = toggle.getAttribute( 'aria-expanded' ) === 'true';

				closeSiblings( item );
				toggle.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
				item.classList.toggle( 'is-open', ! open );
			} );

			// On desktop, leaving the item with the keyboard closes the submenu.
			item.addEventListener( 'focusout', function ( event ) {
				if ( ! desktop.matches ) {
					return;
				}

				if ( item.contains( event.relatedTarget ) ) {
					return;
				}

				toggle.setAttribute( 'aria-expanded', 'false' );
				item.classList.remove( 'is-open' );
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'Escape' ) {
				return;
			}

			var open = nav.querySelector( '.menu-item.is-open' );

			if ( ! open ) {
				return;
			}

			var toggle = open.querySelector( '.submenu-toggle' );

			open.classList.remove( 'is-open' );

			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	/**
	 * Close submenus that sit at the same level as the one being opened.
	 *
	 * @param {HTMLElement} item The menu item being opened.
	 */
	function closeSiblings( item ) {
		var parent = item.parentElement;

		if ( ! parent ) {
			return;
		}

		Array.prototype.forEach.call( parent.children, function ( sibling ) {
			if ( sibling === item ) {
				return;
			}

			sibling.classList.remove( 'is-open' );

			var toggle = sibling.querySelector( ':scope > .submenu-toggle' );

			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
}() );
