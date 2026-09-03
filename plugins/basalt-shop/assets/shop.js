/**
 * Basalt Shop: the two manners a bar and a panel need.
 *
 * Both features work with this file missing. The buy bar is then simply
 * always visible on a narrow screen, and the cart panel opens and closes
 * through the details element, which is what a details element is for.
 *
 * What is added here: the bar stays out of the way while the real button is
 * on screen, and the panel behaves like a panel, closing on escape and on a
 * click outside, moving the focus in and putting it back afterwards.
 */

( function () {
	'use strict';

	/* The buy bar ---------------------------------------------------------- */

	var bar = document.querySelector( '[data-basalt-buybar]' );

	if ( bar && 'IntersectionObserver' in window ) {
		var form = document.getElementById( 'basalt-shop-buy' );

		if ( form ) {
			// From here the CSS hides the bar by default and shows it on demand.
			// The flag goes on the bar itself: an attribute of the same name on
			// the root element would also be matched by [data-basalt-buybar].
			bar.setAttribute( 'data-auto', '' );

			new IntersectionObserver(
				function ( entries ) {
					bar.setAttribute( 'data-state', entries[ 0 ].isIntersecting ? 'away' : 'here' );
				},
				{ rootMargin: '0px 0px -25% 0px' }
			).observe( form );
		}
	}

	/* The cart panel ------------------------------------------------------- */

	var cart = document.querySelector( '[data-basalt-cart]' );

	if ( ! cart ) {
		return;
	}

	var toggle = cart.querySelector( 'summary' );
	var panel = cart.querySelector( '.basalt-cart__panel' );
	var closer = cart.querySelector( '[data-basalt-cart-close]' );

	function close( refocus ) {
		if ( ! cart.open ) {
			return;
		}

		cart.open = false;

		if ( refocus && toggle ) {
			toggle.focus();
		}
	}

	if ( closer ) {
		// Only useful once something can close it: without a script, escape and
		// the summary already do the job and a second button is clutter.
		closer.hidden = false;
		closer.addEventListener( 'click', function () {
			close( true );
		} );
	}

	cart.addEventListener( 'toggle', function () {
		document.body.style.overflow = cart.open ? 'hidden' : '';

		if ( cart.open && panel ) {
			var target = panel.querySelector( 'a, button' );

			if ( target ) {
				target.focus();
			}
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			close( true );
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( cart.open && ! cart.contains( event.target ) ) {
			close( false );
		}
	} );

	// Opened by the server after something was added: start at the top of it.
	if ( cart.open && panel ) {
		panel.scrollTop = 0;
	}

	/*
	 * On a block theme WooCommerce adds to the cart over its own API and the
	 * page never reloads, so the panel has to be told. Both events are
	 * listened for: the blocks fire the first, the classic AJAX buttons on a
	 * product grid fire the second through jQuery.
	 */
	var busy = false;

	function refresh( open ) {
		if ( busy ) {
			return;
		}

		busy = true;

		fetch( '?basalt-cart-panel=1', { credentials: 'same-origin', headers: { Accept: 'application/json' } } )
			.then( function ( response ) {
				return response.ok ? response.json() : null;
			} )
			.then( function ( data ) {
				busy = false;

				if ( ! data ) {
					return;
				}

				var inner = cart.querySelector( '.basalt-cart__inner' );
				var count = cart.querySelector( '.basalt-cart__count' );
				var total = cart.querySelector( '.basalt-cart__total' );

				if ( inner ) {
					inner.innerHTML = data.html;

					var button = inner.querySelector( '[data-basalt-cart-close]' );

					if ( button ) {
						button.hidden = false;
						button.addEventListener( 'click', function () {
							close( true );
						} );
					}
				}

				if ( count ) {
					count.textContent = data.count;
				}

				if ( total ) {
					total.innerHTML = data.subtotal;
				}

				if ( open ) {
					cart.open = true;
				}
			} )
			.catch( function () {
				busy = false;
			} );
	}

	document.body.addEventListener( 'wc-blocks_added_to_cart', function () {
		refresh( true );
	} );

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart removed_from_cart', function () {
			refresh( true );
		} );
	}
}() );
