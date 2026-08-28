/**
 * Display preferences panel.
 *
 * The boot script in the page head has already applied the stored preferences
 * before first paint. This file only wires the controls, keeps them in sync
 * with what was applied, and writes changes back to storage.
 *
 * No dependencies, no build step.
 *
 * @package BasaltCore
 */

( function () {
	'use strict';

	var STORAGE_KEY = 'basaltDisplayPreferences';

	var root = document.documentElement;
	var container = document.querySelector( '.basalt-a11y' );

	if ( ! container ) {
		return;
	}

	var trigger = container.querySelector( '.basalt-a11y__trigger' );
	var panel = container.querySelector( '.basalt-a11y__panel' );
	var closeButton = container.querySelector( '.basalt-a11y__close' );
	var resetButton = container.querySelector( '.basalt-a11y__reset' );
	var status = container.querySelector( '.basalt-a11y__status' );
	var inputs = container.querySelectorAll( '[data-a11y-pref]' );

	if ( ! trigger || ! panel ) {
		return;
	}

	/**
	 * Read the stored preferences.
	 *
	 * Every access is guarded: localStorage throws rather than returning null
	 * in a private window and in browsers set to block site data. A visitor in
	 * that situation still gets a working panel, it simply forgets.
	 *
	 * @returns {Object}
	 */
	function read() {
		try {
			return JSON.parse( localStorage.getItem( STORAGE_KEY ) || '{}' ) || {};
		} catch ( error ) {
			return {};
		}
	}

	/**
	 * Persist the preferences.
	 *
	 * @param {Object} preferences Preference map.
	 */
	function write( preferences ) {
		try {
			localStorage.setItem( STORAGE_KEY, JSON.stringify( preferences ) );
		} catch ( error ) {
			// Storage unavailable or full. The preference still applies to this page.
		}
	}

	/**
	 * Announce a change without moving focus.
	 *
	 * The status element is a live region, so a screen reader user hears the
	 * result of flipping a switch they cannot see the effect of.
	 *
	 * @param {string} message Text to announce.
	 */
	function announce( message ) {
		if ( ! status ) {
			return;
		}

		status.textContent = '';

		// Re-setting the text in the next frame is what makes repeated
		// announcements of the same string actually announce again.
		window.requestAnimationFrame( function () {
			status.textContent = message;
		} );
	}

	/**
	 * Apply one preference to the document and store it.
	 *
	 * @param {string} key   Preference name.
	 * @param {string} value Value, or an empty string to clear it.
	 */
	function apply( key, value ) {
		var preferences = read();

		if ( value ) {
			root.setAttribute( 'data-a11y-' + key, value );
			preferences[ key ] = value;
		} else {
			root.removeAttribute( 'data-a11y-' + key );

			/*
			 * Stored as an empty string rather than deleted. The boot script
			 * treats a missing key as "ask the operating system", so deleting
			 * it would let a system preference switch the setting back on after
			 * the visitor deliberately turned it off.
			 */
			preferences[ key ] = '';
		}

		write( preferences );
	}

	/**
	 * Set the controls to match what is currently applied.
	 */
	function syncControls() {
		Array.prototype.forEach.call( inputs, function ( input ) {
			var key = input.getAttribute( 'data-a11y-pref' );
			var current = root.getAttribute( 'data-a11y-' + key );

			if ( input.type === 'radio' ) {
				// Nothing applied means the default choice is the current one.
				input.checked = current ? input.value === current : input.defaultChecked;
			} else {
				input.checked = current === input.value;
			}
		} );
	}

	Array.prototype.forEach.call( inputs, function ( input ) {
		input.addEventListener( 'change', function () {
			var key = input.getAttribute( 'data-a11y-pref' );

			if ( input.type === 'radio' ) {
				apply( key, input.value );
			} else {
				apply( key, input.checked ? input.value : '' );
			}

			var label = input.closest( 'label' );
			var name = label ? label.textContent.trim().replace( /\s+/g, ' ' ) : key;

			announce( name + ': ' + ( input.checked ? 'on' : 'off' ) );
		} );
	} );

	if ( resetButton ) {
		resetButton.addEventListener( 'click', function () {
			Array.prototype.forEach.call( inputs, function ( input ) {
				root.removeAttribute( 'data-a11y-' + input.getAttribute( 'data-a11y-pref' ) );
			} );

			try {
				localStorage.removeItem( STORAGE_KEY );
			} catch ( error ) {
				// Nothing to remove.
			}

			syncControls();
			announce( status ? status.getAttribute( 'data-reset-message' ) || '' : '' );
		} );
	}

	/*
	 * <dialog>.showModal() gives focus containment, Escape handling and inert
	 * background for free. Reimplementing those by hand is how focus traps end
	 * up with bugs.
	 */
	trigger.addEventListener( 'click', function () {
		syncControls();

		if ( typeof panel.showModal === 'function' ) {
			panel.showModal();
		} else {
			panel.setAttribute( 'open', '' );
		}
	} );

	if ( closeButton ) {
		closeButton.addEventListener( 'click', function () {
			panel.close();
		} );
	}

	// Returning focus to the trigger is the caller's job, not the dialog's.
	panel.addEventListener( 'close', function () {
		trigger.focus();
	} );

	// A click on the backdrop closes the panel, which is what people expect.
	panel.addEventListener( 'click', function ( event ) {
		if ( event.target === panel ) {
			panel.close();
		}
	} );

	syncControls();
}() );
