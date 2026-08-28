# Development sandbox

A throwaway WordPress for looking at the theme and, more usefully, for testing
against a real install. Nothing in this folder ships: the release ZIP is built
from `basalt/` alone.

## Why it is here

Static checks do not find the interesting failures. Everything below was caught
by running the theme in a real WordPress, and none of it was visible to PHP
linting or to the static preview in `tools/make-preview.mjs`:

- a fatal error from passing an internal PHP function as a `register_post_meta`
  sanitize callback
- a self-referencing breadcrumb on a static front page
- missing canonicals on every archive
- sidebar headings at 48 pixels, because block based widgets do not use the
  registered `before_title` markup

If you change anything in `inc/seo.php`, the templates or the widget areas,
bring it up and look.

## Requirements

Docker. Nothing else; WP-CLI runs in a container.

## Use

```bash
docker compose -f dev/compose.yaml up -d
docker compose -f dev/compose.yaml exec -T cli sh /seed/seed.sh
```

Then open <http://localhost:8088>, log in as `admin` / `basalt-demo-pw`.

Or through npm, which does the same thing:

```bash
npm run dev:up
npm run dev:seed
```

Throw it away, including the database:

```bash
npm run dev:down
```

## What the seed builds

A site that touches every template and every structured-data path:

| Content | Exercises |
| --- | --- |
| Front page on the Landing template | Full width sections, block styles, FAQ blocks and the FAQPage data derived from them |
| Catalog with six items | Custom post type, two taxonomies, specification meta, the child theme's `Product` node |
| Three posts, one category | Blog index, single post, author box, category archive |
| Menu with a submenu | The accessible dropdown, keyboard handling, `aria-expanded` |
| Sidebar and footer widgets | Widget areas and both kinds of widget title markup |
| Imprint and privacy pages | Legal menu in the footer bar |

The parent theme is left active. Switch to Basalt Child under Appearance to see
the catalog extension: specification list, `Product` structured data and the
taxonomy that drives the breadcrumb.

Re-running the seed wipes the content first, so it always produces the same
result no matter what was clicked in the admin.

## Running WP-CLI

The `cli` container stays alive and shares the WordPress volume:

```bash
docker compose -f dev/compose.yaml exec -T cli wp plugin list
docker compose -f dev/compose.yaml exec -T cli wp theme activate basalt-child
docker compose -f dev/compose.yaml exec -T cli wp rewrite flush --hard
```

## Reading the error log

`WP_DEBUG_DISPLAY` is off and `WP_DEBUG_LOG` is on, so a notice cannot quietly
corrupt the markup you are inspecting. It lands here instead:

```bash
docker compose -f dev/compose.yaml exec -T cli cat /var/www/html/wp-content/debug.log
```

An empty or absent file is the pass condition.

## Notes

The theme, the child theme and the plugin are bind mounted from the repository,
so an edit shows up on the next reload. WordPress core and the database live in
named volumes, which is why `down -v` is a complete reset.

The WordPress image is deliberately unpinned. The point of this environment is
to catch what breaks on the current release; pin it only when reproducing a bug
against a specific version.

`seed.sh` must keep LF line endings. A CRLF copy fails inside the container
with `sh: : not found` on scattered lines, which is a confusing way to spend
twenty minutes. `.gitattributes` enforces it.
