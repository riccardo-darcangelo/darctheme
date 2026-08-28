/**
 * Live preview for customizer settings that use postMessage transport.
 *
 * @package Basalt
 */

( function ( api ) {
	'use strict';

	api( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			var target = document.querySelector( '.site-branding__link' );

			if ( target ) {
				target.textContent = to;
			}
		} );
	} );

	api( 'blogdescription', function ( value ) {
		value.bind( function ( to ) {
			var target = document.querySelector( '.site-branding__description' );

			if ( target ) {
				target.textContent = to;
			}
		} );
	} );
}( window.wp.customize ) );
