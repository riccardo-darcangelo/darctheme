/**
 * Progressive enhancements.
 *
 * Everything here is observer based. There is no scroll event listener in the
 * theme, because a listener that runs on every frame is one of the easiest ways
 * to lose the INP budget on a mid range phone.
 *
 * @package Basalt
 */

( function () {
	'use strict';

	function init() {
		setUpHeaderState();
		setUpBackToTop();
	}

	/**
	 * Add a class to the header once the page has scrolled past the top.
	 *
	 * Uses a zero height sentinel element and IntersectionObserver instead of a
	 * scroll handler, so the work happens off the main thread.
	 */
	function setUpHeaderState() {
		var header = document.querySelector( '.site-header' );

		if ( ! header || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var sentinel = document.createElement( 'div' );

		sentinel.className = 'header-sentinel';
		sentinel.setAttribute( 'aria-hidden', 'true' );
		header.parentNode.insertBefore( sentinel, header );

		new IntersectionObserver(
			function ( entries ) {
				header.classList.toggle( 'is-scrolled', ! entries[ 0 ].isIntersecting );
			},
			{ threshold: 0 }
		).observe( sentinel );
	}

	/**
	 * Reveal the back to top button once the header has scrolled out of view.
	 */
	function setUpBackToTop() {
		var button = document.querySelector( '.back-to-top' );
		var header = document.querySelector( '.site-header' );

		if ( ! button || ! header || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		new IntersectionObserver(
			function ( entries ) {
				button.hidden = entries[ 0 ].isIntersecting;
			},
			{ threshold: 0 }
		).observe( header );

		button.addEventListener( 'click', function () {
			var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			window.scrollTo( {
				top: 0,
				behavior: reduced ? 'auto' : 'smooth'
			} );

			// Scrolling alone does not move focus, which strands keyboard users.
			var skipTarget = document.querySelector( '.skip-link' ) || document.body;

			skipTarget.focus( { preventScroll: true } );
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
}() );
