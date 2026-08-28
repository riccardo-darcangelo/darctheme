# Basalt

A fast, accessible and SEO-first hybrid WordPress theme. Classic PHP templates
for exact control over markup and structured data, a `theme.json` design system
for everything visual. No build step, no dependencies, no external requests.

Formerly *DarcTheme*. Version 2.0.0 is a rewrite; the previous theme is kept
under `legacy/` for reference and is no longer maintained.

## Repository layout

| Path | Contents |
| --- | --- |
| `basalt/` | The theme. This folder is what ships. |
| `examples/basalt-child/` | Child theme starter: token overrides, custom post type templates, structured data mapping. |
| `examples/basalt-catalog/` | Companion plugin registering a catalog post type, taxonomies and specification fields. |
| `docs/` | Buyer documentation, shipped with the marketplace bundle. |
| `dev/` | Throwaway WordPress in Docker, with a seed script for demo content. |
| `tools/` | POT generation, static preview, packaging. Node only, no dependencies. |
| `legacy/` | The pre-2.0 theme. Not maintained. |

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

**Hybrid, not full site editing.** Templates are PHP so the heading order,
landmarks and structured data are determined in code rather than assembled in
an editor. `block-template-parts` support is deliberately off: it would create
a second, editable source of header and footer markup next to `header.php` and
`footer.php`, and two sources for one region is how heading order gets lost.

**No cascade layers in the theme CSS.** WordPress prints the CSS generated from
`theme.json` unlayered, and an unlayered rule beats every layered rule
regardless of specificity. Component styles inside `@layer` therefore lose every
collision with `theme.json`. See the note at the top of
`basalt/assets/css/base.css`.

**Data structure belongs in a plugin.** Post types, taxonomies and custom
fields are registered by `examples/basalt-catalog/`, never by the theme.
A theme that registers them takes the customer's content with it when they
switch.

**The theme stands down for SEO plugins.** Meta tags, structured data and
breadcrumbs are emitted only when no SEO plugin owns them. Detection lives in
`basalt/inc/integrations/seo-plugins.php`.

## Licence

GPL-2.0-or-later. See `LICENSE.md`.
