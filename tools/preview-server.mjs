/**
 * Minimal static file server for the theme preview.
 *
 * Serves the repository root so dist/preview/index.html can reach the real
 * stylesheets in basalt/assets/css. Development only; nothing here ships.
 *
 * Usage:
 *   node tools/preview-server.mjs [port]
 */

import { createServer } from 'node:http';
import { createReadStream, existsSync, statSync } from 'node:fs';
import { extname, join, normalize, resolve } from 'node:path';

const port = Number( process.argv[ 2 ] ?? 4321 );
const root = resolve( '.' );

const TYPES = {
	'.html': 'text/html; charset=utf-8',
	'.css': 'text/css; charset=utf-8',
	'.js': 'text/javascript; charset=utf-8',
	'.json': 'application/json; charset=utf-8',
	'.png': 'image/png',
	'.jpg': 'image/jpeg',
	'.svg': 'image/svg+xml',
	'.woff2': 'font/woff2',
};

createServer( ( request, response ) => {
	const url = decodeURIComponent( ( request.url ?? '/' ).split( '?' )[ 0 ] );
	let target = resolve( join( root, normalize( url ) ) );

	// Never serve outside the repository root.
	if ( ! target.startsWith( root ) ) {
		response.writeHead( 403 ).end( 'Forbidden' );
		return;
	}

	if ( existsSync( target ) && statSync( target ).isDirectory() ) {
		target = join( target, 'index.html' );
	}

	if ( ! existsSync( target ) ) {
		response.writeHead( 404, { 'content-type': 'text/plain' } ).end( 'Not found' );
		return;
	}

	response.writeHead( 200, {
		'content-type': TYPES[ extname( target ) ] ?? 'application/octet-stream',
		'cache-control': 'no-store',
	} );

	createReadStream( target ).pipe( response );
} ).listen( port, () => {
	console.log( `Preview server on http://localhost:${ port }/dist/preview/` );
} );
