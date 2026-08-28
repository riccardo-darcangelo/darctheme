/**
 * Build the distributable theme ZIP.
 *
 * Produces dist/basalt-<version>.zip containing a single top level basalt/
 * folder, which is what WordPress and the Envato review queue expect.
 * Development files never enter the archive: the exclude list below is the
 * single source of truth for what ships.
 *
 * Usage:
 *   node tools/package.mjs            build the theme ZIP
 *   node tools/package.mjs --market   also build the ThemeForest upload bundle
 */

import { existsSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { deflateRawSync } from 'node:zlib';
import { basename, join, relative, sep } from 'node:path';

const themeDir = 'basalt';
const distDir = 'dist';

/** Paths that must never ship inside the theme ZIP. */
const EXCLUDE = [
	/(^|\/)\.git($|\/)/,
	/(^|\/)\.idea($|\/)/,
	/(^|\/)node_modules($|\/)/,
	/(^|\/)\.DS_Store$/,
	/(^|\/)Thumbs\.db$/,
	/(^|\/)\.editorconfig$/,
	/(^|\/)\.eslintrc/,
	/(^|\/)phpcs\.xml/,
	/(^|\/)composer\.(json|lock)$/,
	/(^|\/)package(-lock)?\.json$/,
	/\.map$/,
	/\.po$/,
];

/**
 * Read the theme version from the style.css header.
 *
 * @returns {string}
 */
function themeVersion() {
	const header = readFileSync( join( themeDir, 'style.css' ), 'utf8' );
	const match = header.match( /^\s*Version:\s*(.+)$/m );

	if ( ! match ) {
		throw new Error( 'No Version header found in style.css' );
	}

	return match[ 1 ].trim();
}

/**
 * List every file that belongs in the package.
 *
 * @param {string} dir Directory to walk.
 * @returns {string[]} Paths relative to the repository root.
 */
function collect( dir ) {
	const files = [];

	for ( const name of readdirSync( dir ) ) {
		const full = join( dir, name );
		const rel = relative( '.', full ).split( sep ).join( '/' );

		if ( EXCLUDE.some( ( pattern ) => pattern.test( rel ) ) ) {
			continue;
		}

		if ( statSync( full ).isDirectory() ) {
			files.push( ...collect( full ) );
		} else {
			files.push( rel );
		}
	}

	return files;
}

/**
 * Checks that would fail an Envato review, run before packaging.
 *
 * @param {string[]} files Files about to be packaged.
 * @returns {string[]} Problems found.
 */
function audit( files ) {
	const problems = [];
	/*
	 * A block theme needs templates/index.html where a classic theme needed
	 * index.php. WordPress decides which kind a theme is by exactly that file,
	 * so its absence would silently turn the theme back into a classic one and
	 * take the site editor with it.
	 */
	const required = [
		'style.css',
		'templates/index.html',
		'parts/header.html',
		'parts/footer.html',
		'functions.php',
		'theme.json',
		'readme.txt',
		'screenshot.png',
		'languages/basalt.pot',
	];

	for ( const name of required ) {
		if ( ! files.includes( `${ themeDir }/${ name }` ) ) {
			problems.push( `Missing required file: ${ name }` );
		}
	}

	const style = readFileSync( join( themeDir, 'style.css' ), 'utf8' );

	for ( const header of [ 'Theme Name', 'Version', 'License', 'Text Domain', 'Requires PHP', 'Requires at least' ] ) {
		if ( ! new RegExp( `^\\s*${ header }:`, 'm' ).test( style ) ) {
			problems.push( `style.css is missing the "${ header }" header` );
		}
	}

	for ( const file of files.filter( ( f ) => f.endsWith( '.php' ) ) ) {
		const source = readFileSync( file, 'utf8' );

		// Debug output left in a release is an instant rejection.
		if ( /\b(var_dump|print_r|error_log)\s*\(/.test( source ) ) {
			problems.push( `Debug output left in ${ file }` );
		}

		// Remote calls from a theme need a very good reason.
		if ( /\b(curl_init|file_get_contents\s*\(\s*['"]https?:)/.test( source ) ) {
			problems.push( `Remote request in ${ file }` );
		}

		if ( /\b(eval|base64_decode|create_function)\s*\(/.test( source ) ) {
			problems.push( `Disallowed construct in ${ file }` );
		}
	}

	// Screenshot must be 1200x900 for a crisp render on high density displays.
	const screenshot = join( themeDir, 'screenshot.png' );

	if ( existsSync( screenshot ) ) {
		const buffer = readFileSync( screenshot );
		const width = buffer.readUInt32BE( 16 );
		const height = buffer.readUInt32BE( 20 );

		if ( width !== 1200 || height !== 900 ) {
			problems.push( `screenshot.png is ${ width }x${ height }, expected 1200x900` );
		}
	}

	return problems;
}

/**
 * CRC-32 lookup table, built once.
 *
 * @type {Int32Array}
 */
const CRC_TABLE = ( () => {
	const table = new Int32Array( 256 );

	for ( let i = 0; i < 256; i++ ) {
		let value = i;

		for ( let bit = 0; bit < 8; bit++ ) {
			value = value & 1 ? ( value >>> 1 ) ^ 0xedb88320 : value >>> 1;
		}

		table[ i ] = value;
	}

	return table;
} )();

/**
 * CRC-32 of a buffer, as required by the ZIP format.
 *
 * @param {Buffer} buffer Input.
 * @returns {number} Unsigned 32 bit checksum.
 */
function crc32( buffer ) {
	let crc = -1;

	for ( let i = 0; i < buffer.length; i++ ) {
		crc = ( crc >>> 8 ) ^ CRC_TABLE[ ( crc ^ buffer[ i ] ) & 0xff ];
	}

	return ( crc ^ -1 ) >>> 0;
}

/**
 * Encode a timestamp as an MS-DOS date and time pair.
 *
 * @param {Date} date Timestamp.
 * @returns {{time: number, date: number}}
 */
function dosTimestamp( date ) {
	const year = Math.max( 1980, date.getFullYear() );

	return {
		time: ( date.getHours() << 11 ) | ( date.getMinutes() << 5 ) | ( date.getSeconds() >> 1 ),
		date: ( ( year - 1980 ) << 9 ) | ( ( date.getMonth() + 1 ) << 5 ) | date.getDate(),
	};
}

/**
 * Write a ZIP archive.
 *
 * Implemented here rather than shelled out for one reason: the archive has to
 * unpack correctly on the Linux server the theme ends up on, and neither
 * Windows tool gets that right. PowerShell's Compress-Archive writes entry
 * names with backslash separators, so "basalt/style.css" arrives as a single
 * file literally named "basalt\style.css" and WordPress reports a broken theme.
 * The bsdtar that ships with Windows cannot write ZIP at all; given a .zip
 * extension it silently produces a TAR.
 *
 * Both failures are invisible on the machine that built the archive, which is
 * the worst kind. Sixty lines of ZIP format is cheaper than that class of bug.
 *
 * Only file entries are written. Directory entries are optional in the format
 * and unpackers create the paths anyway, which conveniently means an empty
 * directory can never end up in the package.
 *
 * @param {string} output Output file.
 * @param {Array<{path: string, name: string}>} files Source path plus the name
 *                                                    it takes inside the archive.
 * @returns {void}
 */
function zip( output, files ) {
	const locals = [];
	const central = [];
	let offset = 0;

	for ( const file of files ) {
		const name = file.name.split( sep ).join( '/' );
		const nameBuffer = Buffer.from( name, 'utf8' );
		const content = readFileSync( file.path );
		const deflated = deflateRawSync( content, { level: 9 } );

		// Storing uncompressed is smaller for data deflate cannot improve.
		const stored = deflated.length >= content.length;
		const payload = stored ? content : deflated;
		const method = stored ? 0 : 8;

		const stamp = dosTimestamp( statSync( file.path ).mtime );
		const checksum = crc32( content );

		const localHeader = Buffer.alloc( 30 );
		localHeader.writeUInt32LE( 0x04034b50, 0 );
		localHeader.writeUInt16LE( 20, 4 ); // Version needed to extract.
		localHeader.writeUInt16LE( 0x0800, 6 ); // Names are UTF-8.
		localHeader.writeUInt16LE( method, 8 );
		localHeader.writeUInt16LE( stamp.time, 10 );
		localHeader.writeUInt16LE( stamp.date, 12 );
		localHeader.writeUInt32LE( checksum, 14 );
		localHeader.writeUInt32LE( payload.length, 18 );
		localHeader.writeUInt32LE( content.length, 22 );
		localHeader.writeUInt16LE( nameBuffer.length, 26 );
		localHeader.writeUInt16LE( 0, 28 ); // Extra field length.

		locals.push( localHeader, nameBuffer, payload );

		const centralHeader = Buffer.alloc( 46 );
		centralHeader.writeUInt32LE( 0x02014b50, 0 );
		centralHeader.writeUInt16LE( 0x031e, 4 ); // Made by Unix, ZIP 3.0.
		centralHeader.writeUInt16LE( 20, 6 );
		centralHeader.writeUInt16LE( 0x0800, 8 );
		centralHeader.writeUInt16LE( method, 10 );
		centralHeader.writeUInt16LE( stamp.time, 12 );
		centralHeader.writeUInt16LE( stamp.date, 14 );
		centralHeader.writeUInt32LE( checksum, 16 );
		centralHeader.writeUInt32LE( payload.length, 20 );
		centralHeader.writeUInt32LE( content.length, 24 );
		centralHeader.writeUInt16LE( nameBuffer.length, 28 );
		centralHeader.writeUInt16LE( 0, 30 ); // Extra field length.
		centralHeader.writeUInt16LE( 0, 32 ); // Comment length.
		centralHeader.writeUInt16LE( 0, 34 ); // Disk number.
		centralHeader.writeUInt16LE( 0, 36 ); // Internal attributes.
		// Unix mode 0644 in the high word. The shift needs >>> 0: JS bit shifts
		// are signed, and 0o100644 << 16 overflows into a negative number.
		centralHeader.writeUInt32LE( ( 0o100644 << 16 ) >>> 0, 38 );
		centralHeader.writeUInt32LE( offset, 42 );

		central.push( centralHeader, nameBuffer );

		offset += localHeader.length + nameBuffer.length + payload.length;
	}

	if ( files.length > 0xffff || offset > 0xffffffff ) {
		throw new Error( 'Archive exceeds the limits of the classic ZIP format.' );
	}

	const centralBuffer = Buffer.concat( central );

	const end = Buffer.alloc( 22 );
	end.writeUInt32LE( 0x06054b50, 0 );
	end.writeUInt16LE( 0, 4 ); // This disk.
	end.writeUInt16LE( 0, 6 ); // Disk with the central directory.
	end.writeUInt16LE( files.length, 8 );
	end.writeUInt16LE( files.length, 10 );
	end.writeUInt32LE( centralBuffer.length, 12 );
	end.writeUInt32LE( offset, 16 );
	end.writeUInt16LE( 0, 20 ); // Comment length.

	if ( existsSync( output ) ) {
		rmSync( output );
	}

	writeFileSync( output, Buffer.concat( [ ...locals, centralBuffer, end ] ) );
}

/**
 * Verify that the finished archive uses POSIX separators.
 *
 * Reads the central directory of the ZIP and fails loudly on any entry name
 * containing a backslash. Cheap insurance against shipping an archive that only
 * unpacks correctly on Windows.
 *
 * @param {string} file Archive path.
 * @returns {number} Number of entries checked.
 */
function verifyArchive( file ) {
	const buffer = readFileSync( file );
	const signature = 0x02014b50; // Central directory file header.
	let entries = 0;

	for ( let i = 0; i < buffer.length - 46; i++ ) {
		if ( buffer.readUInt32LE( i ) !== signature ) {
			continue;
		}

		const nameLength = buffer.readUInt16LE( i + 28 );
		const name = buffer.toString( 'utf8', i + 46, i + 46 + nameLength );

		if ( name.includes( '\\' ) ) {
			throw new Error( `Archive entry uses a backslash separator: ${ name }` );
		}

		entries++;
		i += 45;
	}

	if ( entries === 0 ) {
		throw new Error( `No entries found in ${ file }; the archive is not a valid ZIP.` );
	}

	return entries;
}

const version = themeVersion();
const files = collect( themeDir );
const problems = audit( files );

if ( problems.length ) {
	console.error( 'Package audit failed:\n' );

	for ( const problem of problems ) {
		console.error( `  - ${ problem }` );
	}

	process.exit( 1 );
}

if ( ! existsSync( distDir ) ) {
	mkdirSync( distDir, { recursive: true } );
}

const themeZip = join( distDir, `basalt-${ version }.zip` );

zip(
	themeZip,
	files.map( ( path ) => ( { path, name: path } ) )
);

console.log( `Theme package: ${ themeZip } (${ verifyArchive( themeZip ) } entries, POSIX separators verified)` );

if ( process.argv.includes( '--market' ) ) {
	const bundleZip = join( distDir, `basalt-${ version }-themeforest.zip` );

	/*
	 * The layout Envato expects: the installable theme at the top level, the
	 * documentation in its own folder, the licence beside them.
	 */
	const contents = [ { path: themeZip, name: basename( themeZip ) } ];

	for ( const doc of collect( 'docs' ) ) {
		contents.push( { path: doc, name: doc.replace( /^docs\//, 'documentation/' ) } );
	}

	if ( existsSync( 'LICENSE.md' ) ) {
		contents.push( { path: 'LICENSE.md', name: 'LICENSE.md' } );
	}

	zip( bundleZip, contents );

	console.log( `Marketplace bundle: ${ bundleZip } (${ verifyArchive( bundleZip ) } entries)` );
	console.log( 'Add the licensing folder and the item description before uploading.' );
}
