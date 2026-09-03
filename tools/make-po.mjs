/**
 * Build a .po from a .pot and a JSON table of translations.
 *
 * The POT is the list of strings the code actually contains; the JSON is what
 * they say in the target language. Keeping them apart means a regenerated POT
 * never loses a translation, and a missing one is reported rather than
 * silently shipped in English.
 *
 * Usage:
 *   node tools/make-po.mjs <pot> <json> <out.po> <locale>
 *
 * Then turn the .po into the binary catalogues WordPress reads:
 *   wp i18n make-mo <dir>
 *   wp i18n make-php <dir>
 */

import { readFileSync, writeFileSync } from 'node:fs';

const [ potFile, jsonFile, outFile, locale = 'de_DE' ] = process.argv.slice( 2 );

if ( ! potFile || ! jsonFile || ! outFile ) {
	console.error( 'Usage: node tools/make-po.mjs <pot> <json> <out.po> [locale]' );
	process.exit( 1 );
}

/**
 * Turn a gettext string literal into its value.
 *
 * @param {string} line One "..." line.
 * @return {string} The value.
 */
function unquote( line ) {
	const inner = line.trim().replace( /^"/, '' ).replace( /"$/, '' );

	return inner
		.replace( /\\n/g, '\n' )
		.replace( /\\t/g, '\t' )
		.replace( /\\"/g, '"' )
		.replace( /\\\\/g, '\\' );
}

/**
 * Turn a value into one or more gettext string literals.
 *
 * Multi line values are written the way gettext writes them: an empty first
 * line, then one literal per line, so a diff of a .po stays readable.
 *
 * @param {string} keyword msgid, msgstr and so on.
 * @param {string} value   The value.
 * @return {string} The lines.
 */
function quote( keyword, value ) {
	const escape = ( s ) =>
		'"' +
		s
			.replace( /\\/g, '\\\\' )
			.replace( /"/g, '\\"' )
			.replace( /\t/g, '\\t' )
			.replace( /\n/g, '\\n' ) +
		'"';

	if ( ! value.includes( '\n' ) ) {
		return keyword + ' ' + escape( value );
	}

	const parts = value.split( '\n' );
	const lines = [ keyword + ' ""' ];

	parts.forEach( ( part, index ) => {
		const last = index === parts.length - 1;

		if ( last && '' === part ) {
			return;
		}

		lines.push( escape( last ? part : part + '\n' ) );
	} );

	return lines.join( '\n' );
}

const pot = readFileSync( potFile, 'utf8' ).split( /\r?\n/ );
const table = JSON.parse( readFileSync( jsonFile, 'utf8' ) );

const blocks = [];
let current = { comments: [], ctxt: null, id: null, plural: null };
let field = null;

/** Finish the block being read. */
function flush() {
	if ( null !== current.id || current.comments.length ) {
		blocks.push( current );
	}

	current = { comments: [], ctxt: null, id: null, plural: null };
	field = null;
}

for ( const line of pot ) {
	if ( '' === line.trim() ) {
		flush();
		continue;
	}

	if ( line.startsWith( '#' ) ) {
		current.comments.push( line );
		continue;
	}

	const match = line.match( /^(msgctxt|msgid_plural|msgid|msgstr(?:\[\d\])?)\s+(.*)$/ );

	if ( match ) {
		const [ , keyword, rest ] = match;

		if ( 'msgctxt' === keyword ) {
			field = 'ctxt';
			current.ctxt = unquote( rest );
		} else if ( 'msgid' === keyword ) {
			field = 'id';
			current.id = unquote( rest );
		} else if ( 'msgid_plural' === keyword ) {
			field = 'plural';
			current.plural = unquote( rest );
		} else {
			field = 'skip';
		}

		continue;
	}

	if ( line.startsWith( '"' ) && field && 'skip' !== field ) {
		current[ field ] += unquote( line );
	}
}

flush();

const header = [
	'msgid ""',
	'msgstr ""',
	'"Project-Id-Version: \\n"',
	'"Report-Msgid-Bugs-To: https://darcdesign.de/themes/basalt/\\n"',
	'"POT-Creation-Date: \\n"',
	'"PO-Revision-Date: \\n"',
	'"Last-Translator: \\n"',
	'"Language-Team: \\n"',
	'"Language: ' + locale + '\\n"',
	'"MIME-Version: 1.0\\n"',
	'"Content-Type: text/plain; charset=UTF-8\\n"',
	'"Content-Transfer-Encoding: 8bit\\n"',
	'"Plural-Forms: nplurals=2; plural=(n != 1);\\n"',
	'"X-Generator: tools/make-po.mjs\\n"',
].join( '\n' );

const out = [ header ];
const missing = [];
let translated = 0;

for ( const block of blocks ) {
	if ( null === block.id || '' === block.id ) {
		continue;
	}

	const singular = table[ block.id ];
	const plural = null === block.plural ? null : table[ block.plural ];

	if ( undefined === singular ) {
		missing.push( block.id );
	}

	if ( null !== block.plural && undefined === plural ) {
		missing.push( block.plural );
	}

	const lines = block.comments.filter( ( line ) => line.startsWith( '#.' ) || line.startsWith( '#:' ) );

	if ( null !== block.ctxt ) {
		lines.push( quote( 'msgctxt', block.ctxt ) );
	}

	lines.push( quote( 'msgid', block.id ) );

	if ( null !== block.plural ) {
		lines.push( quote( 'msgid_plural', block.plural ) );
		lines.push( quote( 'msgstr[0]', singular ?? '' ) );
		lines.push( quote( 'msgstr[1]', plural ?? '' ) );
	} else {
		lines.push( quote( 'msgstr', singular ?? '' ) );
	}

	if ( undefined !== singular ) {
		translated++;
	}

	out.push( lines.join( '\n' ) );
}

writeFileSync( outFile, out.join( '\n\n' ) + '\n' );

console.log( outFile + ': ' + translated + ' translated, ' + missing.length + ' missing' );

if ( missing.length ) {
	console.log( missing.map( ( s ) => '  - ' + s.slice( 0, 90 ) ).join( '\n' ) );
}
