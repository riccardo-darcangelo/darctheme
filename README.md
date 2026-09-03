# Basalt

A fast, accessible, SEO-first WordPress block theme, with the structured data
and accessibility layer in a companion plugin so a customer keeps it when they
change theme.

Five style variations switch the whole look in one click, including a high
contrast variation where every text and control colour clears WCAG AAA. No build
step, no dependencies, no external requests, and the theme itself ships no
JavaScript at all.

Formerly *DarcTheme*. Version 3.0.0 converted the theme from classic templates
to a block theme.

## Repository layout

| Path | Contents |
| --- | --- |
| `basalt/` | The theme. A block theme; this folder is what ships as the theme. |
| `plugins/basalt-core/` | Companion plugin: Schema.org graph, meta tags, robots.txt and AI crawler policy, llms.txt, per page indexing switch, breadcrumb block, accessibility corrections for core blocks. |
| `plugins/basalt-catalog/` | Companion plugin registering a catalog post type, taxonomies and specification fields. |
| `plugins/basalt-forms/` | Companion plugin: one accessible contact and appointment form block, server validated, no JavaScript, submissions kept in the admin. |
| `plugins/basalt-security/` | Companion plugin: login page at an address of your choosing, brute force lockout, guarded password reset and registration, password quality, a small request filter, security headers and the hardening WordPress leaves off. |
| `plugins/basalt-shop/` | Companion plugin for WooCommerce: products sold in store only get an appointment button instead of a cart button, and an InStoreOnly offer. |
| `examples/basalt-child/` | Child theme starter: token overrides, custom post type templates, structured data mapping. |
| `docs/` | Buyer documentation, shipped with the marketplace bundle. |
| `dev/` | Throwaway WordPress in Docker, with a seed script for demo content. |
| `tools/` | POT generation, static preview, packaging. Node only, no dependencies. |

## Requirements

WordPress 6.6, PHP 8.1, Node 20 for the tooling, Docker for the sandbox. The
theme itself needs no Node, no Composer and no plugins.

## Running it

```bash
npm run dev:up
npm run dev:seed
```

<http://localhost:8088>, `admin` / `basalt-demo-pw`. `npm run dev:down` removes
everything including the database.

Use it. Static checks do not find the interesting failures: a fatal error from
an internal PHP function used as a `register_post_meta` sanitize callback, a
self-referencing breadcrumb on a static front page, missing canonicals on every
archive, sidebar headings at 48 pixels because block based widgets ignore the
registered `before_title` markup. All four were caught here and none of them
were visible to linting or to the static preview. See `dev/README.md`.

## Working on the theme

Regenerate the translation template after changing any string:

```bash
npm run pot
```

Render a static preview from the real stylesheets, with the `theme.json` tokens
resolved the way WordPress resolves them:

```bash
npm run preview
```

Serve it, then open `http://localhost:4321/dist/preview/`:

```bash
npm run serve
```

The preview reproduces the selectors WordPress generates from `theme.json`,
including the `:root :where(...)` wrappers. That matters: their specificity is
what decides whether a component rule wins, so a simplified preview would hide
exactly the bugs it exists to catch.

Build the distributable ZIP, with the pre-flight audit:

```bash
npm run package
```

Build the marketplace bundle as well:

```bash
npm run package:market
```

Check coding standards, if PHP_CodeSniffer with WPCS is installed:

```bash
npm run lint:php
```

## Architecture notes

**A block theme, because customizability needs a surface.** The 2.x theme had
`theme.json` but classic templates, and WordPress offers the site editor and its
Styles panel only to block themes: `wp_is_block_theme()` returned false, so
there was no visual way to change a single colour. Style variations, the
mechanism behind the one-click looks, are unreachable from a classic theme.

**Markup control survived the move.** It was the reason to stay classic, and it
is preserved where it matters. The breadcrumb is a server rendered block, so it
still emits a nav landmark, an ordered list and `aria-current` from the same
data as the BreadcrumbList JSON-LD. Where a core block emits markup that fails
an audit, a `render_block` filter corrects it rather than a template fork.

**No cascade layers in the theme CSS.** WordPress prints the CSS generated from
`theme.json` unlayered, and an unlayered rule beats every layered rule
regardless of specificity. Component styles inside `@layer` therefore lose every
collision with `theme.json`. See the note at the top of
`basalt/assets/css/base.css`.

**Data structure and site identity belong in plugins.** Post types, taxonomies
and custom fields are registered by `plugins/basalt-catalog/`; the Schema.org
graph, meta tags, breadcrumbs and the accessibility corrections for core blocks
live in `plugins/basalt-core/`. A theme that owns any of it takes the customer's
content and their search presence with it when they switch.

The settings are options rather than theme mods, and that fixes a real defect:
WordPress stores theme mods per stylesheet, so under 2.x switching from the
parent to the child theme silently wiped every business detail.

**Security is mostly what is not there.** The theme and its plugins were gone
through against the OWASP Top Ten: no direct database queries, no unescaped
output, no remote requests, no uploads, no bundled third party code. What is
actively added is rate limits on every public form, a capability and a nonce on
every admin action, and answers that do not confirm which accounts exist. The
full table is in `docs/index.html`.

**Crawling policy is a setting, not an opinion.** The AI crawler switch ships
on "allow": a theme that quietly blocks crawlers on activation makes a decision
that belongs to the site owner. The middle position, which lets the crawlers
that cite a source in and keeps the ones that only collect training data out,
is the one most small sites want, and the two lists are filterable through
`basalt_core_training_crawlers` and `basalt_core_citation_crawlers`.

**The plugin stands down for SEO plugins.** Meta tags, structured data and
breadcrumbs are emitted only when no SEO plugin owns them. Detection lives in
`plugins/basalt-core/inc/seo-plugins.php`.

## Licence

GPL-2.0-or-later. See `LICENSE.md`.
