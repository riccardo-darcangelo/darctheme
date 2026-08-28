/**
 * Generate languages/basalt.pot from the theme sources.
 *
 * WP-CLI's `wp i18n make-pot` is the canonical tool and should be used when it
 * is available. This script exists so the POT file can be regenerated on a
 * machine that only has Node, which is the common case on Windows.
 *
 * Usage:
 *   node tools/make-pot.mjs [themeDir] [outFile]
 */

import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative, sep } from 'node:path';

const themeDir = process.argv[ 2 ] ?? 'basalt';
const outFile = process.argv[ 3 ] ?? join( themeDir, 'languages', 'basalt.pot' );
const domain = 'basalt';

/**
 * Gettext calls we recognise, and where the arguments sit.
 *
 * singular / plural / context are 1-based argument positions; domain is the
 * position of the text domain argument, used to skip strings that belong to
 * another domain.
 */
const FUNCTIONS = {
	__: { singular: 1, domain: 2 },
	_e: { singular: 1, domain: 2 },
	esc_html__: { singular: 1, domain: 2 },
	esc_html_e: { singular: 1, domain: 2 },
	esc_attr__: { singular: 1, domain: 2 },
	esc_attr_e: { singular: 1, domain: 2 },
	_x: { singular: 1, context: 2, domain: 3 },
	_ex: { singular: 1, context: 2, domain: 3 },
	esc_html_x: { singular: 1, context: 2, domain: 3 },
	esc_attr_x: { singular: 1, context: 2, domain: 3 },
	_n: { singular: 1, plural: 2, domain: 4 },
	_nx: { singular: 1, plural: 2, context: 4, domain: 5 },
};

const NAMES = Object.keys( FUNCTIONS ).sort( ( a, b ) => b.length - a.length );

/** @type {Map<string, {msgid: string, msgidPlural?: string, msgctxt?: string, refs: string[], comments: string[]}>} */
const entries = new Map();

/**
 * Walk a directory and yield every PHP file.
 *
 * @param {string} dir Directory to walk.
 * @returns {string[]}
 */
function phpFiles( dir ) {
	const found = [];

	for ( const name of readdirSync( dir ) ) {
		if ( name === 'node_modules' || name === '.git' ) {
			continue;
		}

		const full = join( dir, name );

		if ( statSync( full ).isDirectory() ) {
			found.push( ...phpFiles( full ) );
		} else if ( name.endsWith( '.php' ) ) {
			found.push( full );
		}
	}

	return found;
}

/**
 * Read one PHP string literal starting at `start`, handling escapes.
 *
 * @param {string} source Source code.
 * @param {number} start  Index of the opening quote.
 * @returns {{value: string, end: number}|null}
 */
function readString( source, start ) {
	const quote = source[ start ];

	if ( quote !== '"' && quote !== "'" ) {
		return null;
	}

	let value = '';

	for ( let i = start + 1; i < source.length; i++ ) {
		const char = source[ i ];

		if ( char === '\\' ) {
			const next = source[ i + 1 ];

			if ( quote === "'" ) {
				// Single quoted PHP only escapes \' and \\.
				value += next === "'" || next === '\\' ? next : char + next;
			} else {
				const map = { n: '\n', t: '\t', r: '\r', '"': '"', '\\': '\\', $: '$' };
				value += map[ next ] ?? char + next;
			}

			i++;
			continue;
		}

		if ( char === quote ) {
			return { value, end: i };
		}

		value += char;
	}

	return null;
}

/**
 * Parse the argument list of a gettext call.
 *
 * Only literal string arguments are captured; anything else becomes null, which
 * is enough to check the domain and to skip dynamic strings.
 *
 * @param {string} source Source code.
 * @param {number} open   Index of the opening parenthesis.
 * @returns {{args: (string|null)[], end: number}}
 */
function readArguments( source, open ) {
	const args = [];
	let depth = 0;
	let current = null;
	let started = false;

	for ( let i = open; i < source.length; i++ ) {
		const char = source[ i ];

		if ( char === '(' ) {
			depth++;

			if ( depth === 1 ) {
				started = true;
				continue;
			}
		}

		if ( char === ')' ) {
			depth--;

			if ( depth === 0 ) {
				args.push( current );

				return { args, end: i };
			}
		}

		if ( ! started ) {
			continue;
		}

		if ( depth === 1 && char === ',' ) {
			args.push( current );
			current = null;
			continue;
		}

		if ( char === '"' || char === "'" ) {
			const literal = readString( source, i );

			if ( ! literal ) {
				return { args, end: i };
			}

			// Only a lone literal counts; concatenation makes it dynamic.
			current = current === null ? literal.value : null;
			i = literal.end;
			continue;
		}

		if ( depth === 1 && char.trim() !== '' ) {
			current = current === null ? null : current;
		}
	}

	return { args, end: source.length };
}

/**
 * Collect the translator comment directly above a line, if any.
 *
 * @param {string[]} lines Source split into lines.
 * @param {number}   index Zero based line index of the call.
 * @returns {string[]}
 */
function translatorComment( lines, index ) {
	for ( let i = index; i >= Math.max( 0, index - 3 ); i-- ) {
		const line = lines[ i ] ?? '';
		const match = line.match( /translators:\s*(.+?)\s*(?:\*\/|$)/i );

		if ( match ) {
			return [ match[ 1 ] ];
		}
	}

	return [];
}

/**
 * Escape a string for the POT format.
 *
 * @param {string} value Raw value.
 * @returns {string}
 */
function escapePo( value ) {
	return value
		.replace( /\\/g, '\\\\' )
		.replace( /"/g, '\\"' )
		.replace( /\n/g, '\\n' )
		.replace( /\t/g, '\\t' );
}

for ( const file of phpFiles( themeDir ) ) {
	const source = readFileSync( file, 'utf8' );
	const lines = source.split( '\n' );
	const reference = relative( themeDir, file ).split( sep ).join( '/' );

	for ( const name of NAMES ) {
		const pattern = new RegExp( `(?<![\\w$>])${ name }\\s*\\(`, 'g' );
		let match;

		while ( ( match = pattern.exec( source ) ) !== null ) {
			const spec = FUNCTIONS[ name ];
			const { args } = readArguments( source, match.index + match[ 0 ].length - 1 );

			const singular = args[ spec.singular - 1 ];

			if ( typeof singular !== 'string' || singular === '' ) {
				continue;
			}

			const textDomain = args[ spec.domain - 1 ];

			if ( textDomain !== domain ) {
				continue;
			}

			const context = spec.context ? args[ spec.context - 1 ] : undefined;
			const plural = spec.plural ? args[ spec.plural - 1 ] : undefined;

			const key = `${ context ?? '' }${ singular }${ plural ?? '' }`;
			const line = source.slice( 0, match.index ).split( '\n' ).length;

			if ( ! entries.has( key ) ) {
				entries.set( key, {
					msgid: singular,
					msgidPlural: typeof plural === 'string' ? plural : undefined,
					msgctxt: typeof context === 'string' ? context : undefined,
					refs: [],
					comments: translatorComment( lines, line - 1 ),
				} );
			}

			entries.get( key ).refs.push( `${ reference }:${ line }` );
		}
	}
}

const sorted = [ ...entries.values() ].sort( ( a, b ) =>
	( a.refs[ 0 ] ?? '' ).localeCompare( b.refs[ 0 ] ?? '' )
);

const header = `# Copyright (C) 2026 Riccardo D'Arcangelo
# This file is distributed under the GNU General Public License v2 or later.
msgid ""
msgstr ""
"Project-Id-Version: Basalt 2.0.0\\n"
"Report-Msgid-Bugs-To: https://darcdesign.de/themes/basalt/\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Language-Team: LANGUAGE <LL@li.org>\\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\\n"
"X-Domain: ${ domain }\\n"
`;

const body = sorted
	.map( ( entry ) => {
		const parts = [];

		for ( const comment of entry.comments ) {
			parts.push( `#. translators: ${ comment }` );
		}

		parts.push( `#: ${ entry.refs.join( ' ' ) }` );

		if ( entry.msgctxt !== undefined ) {
			parts.push( `msgctxt "${ escapePo( entry.msgctxt ) }"` );
		}

		parts.push( `msgid "${ escapePo( entry.msgid ) }"` );

		if ( entry.msgidPlural !== undefined ) {
			parts.push( `msgid_plural "${ escapePo( entry.msgidPlural ) }"` );
			parts.push( 'msgstr[0] ""' );
			parts.push( 'msgstr[1] ""' );
		} else {
			parts.push( 'msgstr ""' );
		}

		return parts.join( '\n' );
	} )
	.join( '\n\n' );

writeFileSync( outFile, `${ header }\n${ body }\n`, 'utf8' );

console.log( `Wrote ${ sorted.length } strings to ${ outFile }` );
