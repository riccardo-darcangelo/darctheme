/**
 * Render a static preview of the theme from its real stylesheets.
 *
 * WordPress turns theme.json into CSS custom properties at runtime. Outside
 * WordPress those properties do not exist, so this script derives them from
 * theme.json with the same naming scheme and writes them into the preview.
 * The result uses the theme's actual CSS files, so what it shows is what the
 * theme renders, not a mockup drawn separately.
 *
 * Used for the theme screenshot and for checking layout work without a
 * WordPress install.
 *
 * Usage:
 *   node tools/make-preview.mjs
 */

import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const themeDir = 'basalt';
const outDir = join( 'dist', 'preview' );

const theme = JSON.parse( readFileSync( join( themeDir, 'theme.json' ), 'utf8' ) );

/**
 * Flatten settings.custom into --wp--custom--* properties.
 *
 * WordPress converts camelCase to kebab-case and joins nesting levels with a
 * double dash, which is what this mirrors.
 *
 * @param {object} value  Object to flatten.
 * @param {string} prefix Accumulated prefix.
 * @returns {string[]}
 */
function customProperties( value, prefix = '--wp--custom' ) {
	const lines = [];

	for ( const [ key, entry ] of Object.entries( value ) ) {
		const name = `${ prefix }--${ key.replace( /([a-z0-9])([A-Z])/g, '$1-$2' ).toLowerCase() }`;

		if ( entry && typeof entry === 'object' && ! Array.isArray( entry ) ) {
			lines.push( ...customProperties( entry, name ) );
		} else {
			lines.push( `\t${ name }: ${ entry };` );
		}
	}

	return lines;
}

/**
 * Resolve a fluid font size to a clamp() expression.
 *
 * @param {object} size Font size entry from theme.json.
 * @returns {string}
 */
function fontSize( size ) {
	if ( size.fluid && typeof size.fluid === 'object' ) {
		return `clamp(${ size.fluid.min }, ${ size.fluid.min } + 1.5vw, ${ size.fluid.max })`;
	}

	return size.size;
}

const settings = theme.settings ?? {};
const lines = [ ':root {' ];

for ( const color of settings.color?.palette ?? [] ) {
	lines.push( `\t--wp--preset--color--${ color.slug }: ${ color.color };` );
}

for ( const gradient of settings.color?.gradients ?? [] ) {
	lines.push( `\t--wp--preset--gradient--${ gradient.slug }: ${ gradient.gradient };` );
}

for ( const family of settings.typography?.fontFamilies ?? [] ) {
	lines.push( `\t--wp--preset--font-family--${ family.slug }: ${ family.fontFamily };` );
}

for ( const size of settings.typography?.fontSizes ?? [] ) {
	lines.push( `\t--wp--preset--font-size--${ size.slug }: ${ fontSize( size ) };` );
}

for ( const space of settings.spacing?.spacingSizes ?? [] ) {
	lines.push( `\t--wp--preset--spacing--${ space.slug }: ${ space.size };` );
}

for ( const shadow of settings.shadow?.presets ?? [] ) {
	lines.push( `\t--wp--preset--shadow--${ shadow.slug }: ${ shadow.shadow };` );
}

lines.push( `\t--wp--style--global--content-size: ${ settings.layout?.contentSize ?? '44rem' };` );
lines.push( `\t--wp--style--global--wide-size: ${ settings.layout?.wideSize ?? '80rem' };` );
lines.push( ...customProperties( settings.custom ?? {} ) );
lines.push( '}' );

// The element styles WordPress would generate from styles.elements.
const styles = theme.styles ?? {};

lines.push(
	'',
	'body {',
	`\tbackground-color: ${ styles.color?.background ?? '#fff' };`,
	`\tcolor: ${ styles.color?.text ?? '#000' };`,
	`\tfont-family: ${ styles.typography?.fontFamily ?? 'sans-serif' };`,
	`\tfont-size: ${ styles.typography?.fontSize ?? '1rem' };`,
	`\tline-height: ${ styles.typography?.lineHeight ?? '1.6' };`,
	'}',
	'',
	'h1, h2, h3, h4, h5, h6 {',
	`\tfont-weight: ${ styles.elements?.heading?.typography?.fontWeight ?? '700' };`,
	`\tline-height: ${ styles.elements?.heading?.typography?.lineHeight ?? '1.15' };`,
	`\tletter-spacing: ${ styles.elements?.heading?.typography?.letterSpacing ?? 'normal' };`,
	`\tcolor: ${ styles.elements?.heading?.color?.text ?? 'inherit' };`,
	'}'
);

for ( const level of [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ] ) {
	const size = styles.elements?.[ level ]?.typography?.fontSize;

	if ( size ) {
		lines.push( `${ level } { font-size: ${ size }; }` );
	}
}

/*
 * The link and button selectors must match what WordPress actually emits,
 * including the :root and :where() wrappers. Their specificity is what decides
 * whether a component rule in the theme wins or loses, so a preview that
 * simplifies them to "a { }" would hide exactly the bugs it exists to catch.
 */
lines.push(
	'',
	':root :where(a:where(:not(.wp-element-button))) {',
	`\tcolor: ${ styles.elements?.link?.color?.text ?? 'inherit' };`,
	`\ttext-decoration: ${ styles.elements?.link?.typography?.textDecoration ?? 'underline' };`,
	'}',
	'',
	':root :where(.wp-element-button, .wp-block-button__link) {',
	`\tbackground-color: ${ styles.elements?.button?.color?.background ?? 'transparent' };`,
	`\tcolor: ${ styles.elements?.button?.color?.text ?? 'inherit' };`,
	`\tborder-radius: ${ styles.elements?.button?.border?.radius ?? '0' };`,
	`\tfont-size: ${ styles.elements?.button?.typography?.fontSize ?? 'inherit' };`,
	`\tfont-weight: ${ styles.elements?.button?.typography?.fontWeight ?? 'inherit' };`,
	'\ttext-decoration: none;',
	'\tdisplay: inline-block;',
	`\tpadding: ${ styles.elements?.button?.spacing?.padding?.top ?? '0' } ${
		styles.elements?.button?.spacing?.padding?.right ?? '0'
	};`,
	'}'
);

mkdirSync( outDir, { recursive: true } );
writeFileSync( join( outDir, 'tokens.css' ), `${ lines.join( '\n' ) }\n`, 'utf8' );

const page = `<!DOCTYPE html>
<html lang="en" class="is-ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Basalt preview</title>
<link rel="stylesheet" href="tokens.css">
<link rel="stylesheet" href="../../basalt/assets/css/base.css">
<link rel="stylesheet" href="../../basalt/assets/css/layout.css">
<link rel="stylesheet" href="../../basalt/assets/css/components.css">
<link rel="stylesheet" href="../../basalt/assets/css/blocks.css">
<style>
.preview-hero { display: grid; gap: var(--wp--preset--spacing--40); align-items: center; }
@media (min-width: 48rem) { .preview-hero { grid-template-columns: 1.1fr 0.9fr; gap: var(--wp--preset--spacing--50); } }
</style>
</head>
<body class="no-sidebar">
<div class="site-wrap">

	<header class="site-header site-header--split site-header--sticky">
		<div class="site-header__inner container">
			<div class="site-branding">
				<div class="site-branding__title"><a class="site-branding__link" href="#">Basalt</a></div>
			</div>
			<button type="button" class="nav-toggle" aria-expanded="false" aria-controls="primary-navigation"><span class="nav-toggle__icon" aria-hidden="true"></span><span class="nav-toggle__label">Menu</span></button>
			<nav id="primary-navigation" class="primary-nav" aria-label="Primary">
				<ul class="menu menu--primary">
					<li class="menu-item"><a class="menu-item__link" href="#" aria-current="page">Home</a></li>
					<li class="menu-item"><a class="menu-item__link" href="#">Equipment</a></li>
					<li class="menu-item"><a class="menu-item__link" href="#">Services</a></li>
					<li class="menu-item"><a class="menu-item__link" href="#">Journal</a></li>
					<li class="menu-item"><a class="menu-item__link" href="#">Contact</a></li>
				</ul>
			</nav>
		</div>
	</header>

	<main class="site-main" style="padding-block:0">
		<div class="wp-block-group alignfull has-surface-background-color has-background"
			style="background-color:var(--wp--preset--color--surface);padding-block:var(--wp--preset--spacing--60)">
			<div class="container">
				<div class="preview-hero">
					<div>
						<h6 class="wp-block-heading is-style-eyebrow" style="margin:0 0 .75rem">Since 1998</h6>
						<h1 style="margin:0 0 1rem">Equipment that arrives on time and works on site</h1>
						<p style="font-size:var(--wp--preset--font-size--large);color:var(--wp--preset--color--contrast-soft);margin:0 0 1.75rem">
							A hybrid WordPress theme with a theme.json design system, structured data built into the templates and no external requests.
						</p>
						<div style="display:flex;gap:.75rem;flex-wrap:wrap">
							<a class="button" href="#">Request a quote</a>
							<a class="button button--ghost" href="#">See the range</a>
						</div>
					</div>
					<div>
						<div style="aspect-ratio:16/10;border-radius:var(--wp--custom--radius--lg);background:
							linear-gradient(135deg, var(--wp--preset--color--primary) 0%, var(--wp--preset--color--contrast) 100%);
							box-shadow:var(--wp--preset--shadow--lg);display:grid;place-items:center">
							<span style="color:var(--wp--preset--color--base);opacity:.32;font-size:5rem;font-weight:700;letter-spacing:-.04em">B</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="container" style="padding-block:var(--wp--preset--spacing--50)">
			<h2 style="margin:0 0 var(--wp--preset--spacing--30)">What we offer</h2>
			<div class="entry-list entry-list--grid">
				${ [
					[ 'Design tokens', 'Colour, type and spacing come from theme.json and apply everywhere at once.' ],
					[ 'Structured data', 'Organization, breadcrumbs, articles and FAQ as one JSON-LD graph.' ],
					[ 'Accessible by default', 'Real buttons for submenus, visible focus, 44 pixel targets.' ],
				]
					.map(
						( [ title, text ] ) => `<article class="entry entry--card">
					<div class="wp-block-group is-style-card" style="block-size:100%">
						<div style="aspect-ratio:8/5;border-radius:var(--wp--custom--radius--md);background:var(--wp--preset--color--surface-strong);margin-block-end:1rem"></div>
						<h3 class="entry__title" style="margin:0 0 .5rem"><a class="entry__link" href="#">${ title }</a></h3>
						<p style="margin:0;color:var(--wp--preset--color--contrast-soft)">${ text }</p>
					</div>
				</article>`
					)
					.join( '\n\t\t\t\t' ) }
			</div>
		</div>
	</main>

	<footer class="site-footer">
		<div class="site-footer__bar">
			<div class="container site-footer__bar-inner">
				<p class="site-footer__copyright">© 2026 Basalt</p>
				<nav class="site-footer__legal" aria-label="Legal">
					<ul class="menu menu--legal">
						<li class="menu-item"><a href="#">Imprint</a></li>
						<li class="menu-item"><a href="#">Privacy</a></li>
					</ul>
				</nav>
			</div>
		</div>
	</footer>
</div>
</body>
</html>
`;

writeFileSync( join( outDir, 'index.html' ), page, 'utf8' );

console.log( `Preview written to ${ join( outDir, 'index.html' ) }` );
