# Basalt Child

A working starting point for a project built on Basalt. It is not a demo: every
file here does something a real project needs.

## What it shows

| File | What it demonstrates |
| --- | --- |
| `theme.json` | Overriding design tokens. Only the palette and one custom value are redefined; everything else is inherited. This file is the customization surface, since a classic theme has no site editor. |
| `functions.php` | Rendering data from a plugin, filtering the archive query, choosing an archive layout, and hooking into `basalt_after_header`. |
| `inc/schema.php` | Adding a `Product` node and an `ItemList` node to the parent's Schema.org graph, and deciding which taxonomy drives the breadcrumb. |
| `style.css` | CSS for the child's own components, using the parent's design tokens. |

## Setup

1. Copy this folder into `wp-content/themes/` and rename it. Change
   `Theme Name` and `Text Domain` in `style.css`; leave `Template: basalt`.
2. Copy `../basalt-catalog` into `wp-content/plugins/` and activate it. It
   registers the `catalog_item` post type, its taxonomies and its
   specification fields.
3. Activate the child theme. Without the plugin the child theme still works;
   the catalog features simply do not appear.

## The division of labour

Content structure lives in the plugin: post types, taxonomies, custom fields.
Presentation lives in the theme: templates, styles, structured data mapping.

That line is not bureaucracy. If the post type lived in the theme, switching
theme would make every catalog entry vanish from the admin and every catalog
URL return a 404. The customer would reasonably conclude their data was
deleted. Marketplaces reject themes for this, and it is the right structure
regardless of where a theme is sold.

## Renaming for a real project

In `basalt-catalog.php`:

- `BASALT_CATALOG_POST_TYPE` and the labels in `basalt_catalog_register()`
- the `rewrite` slug, which becomes the URL segment
- `basalt_catalog_taxonomies()` and `basalt_catalog_specs()`

In the child theme, only `catalog_use_case` in `inc/schema.php` refers to a
taxonomy by name.

Renaming a post type after content exists means the existing posts keep the old
`post_type` value in the database and disappear from the admin, so decide on the
names before entering content.
