=== Basalt ===

Contributors: Riccardo D'Arcangelo
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 3.0.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, portfolio, business, one-column, block-patterns, block-styles, custom-colors, custom-logo, custom-menu, editor-style, featured-images, rtl-language-support, sticky-post, threaded-comments, translation-ready, wide-blocks, accessibility-ready

A fast, accessible, SEO-first WordPress block theme.

== Description ==

Basalt is a block theme with five complete style variations that switch the
whole look in one click, including a high contrast variation where every text
and control colour clears WCAG AAA. The design tokens are defined once in
theme.json and apply to the front end, the editor, the patterns and the block
styles at once.

Structured data, meta tags, breadcrumbs and the accessibility corrections for
core blocks come from the bundled Basalt Core plugin, so a site keeps them when
it changes theme.

What it does out of the box:

* Semantic HTML5 with one H1 per view and a correct heading order
* Schema.org JSON-LD as a single @graph: Organization or LocalBusiness,
  WebSite, WebPage, BreadcrumbList, BlogPosting and FAQPage
* FAQ structured data generated from ordinary core/details blocks
* Open Graph and Twitter Card tags, with a fallback sharing image
* Accessible navigation with real buttons for submenus, focus management
  and Escape handling
* WCAG 2.2 AA targets: visible focus, 44 pixel controls, reduced motion,
  logical properties for RTL without a second stylesheet
* Core block styles loaded per block, no emoji polyfill, no external
  requests, no web fonts, no jQuery
* Speculative loading tuned so carts, checkouts and nonce URLs are excluded
* Breadcrumbs, and every SEO output stands down automatically when Rank Math,
  Yoast, SEOPress, AIOSEO, Slim SEO or Squirrly is active

== Installation ==

1. Go to Appearance > Themes and choose Add New.
2. Upload basalt.zip and click Install Now, then Activate.
3. Open Appearance > Basalt and follow the three steps.

== Frequently Asked Questions ==

= Do I need an SEO plugin? =

No. Basalt emits meta tags, structured data and breadcrumbs on its own. If you
do install one, the theme detects it and stops emitting its own, so nothing is
duplicated.

= How do I change the colours and the type scale? =

Under Appearance > Editor > Styles, or in theme.json from a child theme for a
change that survives a reset. Both the front end and the block editor follow.
Editing CSS by hand is only needed for layout work.

= Where do I put custom CSS? =

Use a child theme's style.css, or Appearance > Customize > Additional CSS.
The theme's own style.css contains no rules on purpose, so a child theme's
stylesheet always loads last.

= Does it work with page builders? =

Yes, the page templates are plain containers. For full width builder layouts
use the "Landing page" template, which removes the title, the breadcrumb bar
and the content measure.

= Can I use it as a parent theme? =

Yes. Every module in inc/ is loaded through the basalt_modules filter, every
output is filterable, and the templates use get_template_part(), so a child
theme can replace a single part instead of a whole template.

== Changelog ==

= 3.0.0 =
* Converted to a block theme. Templates and template parts are HTML in
  templates/ and parts/, editable in the site editor.
* Added five style variations, including High contrast where every text and
  control colour clears WCAG AAA and the focus ring widens to 3px, and
  Boutique with serif headings and a rose accent for shops and studios.
* Added a header with shop details (hours, a phone link, directions) and five
  patterns for a business with a door: a row of reassurances, collection
  tiles, a word from the owner, visit the shop, and a complete shop front
  page. Covers gained a Tile style that makes the whole block one link.
* Moved structured data, meta tags, breadcrumbs and the accessibility
  corrections for core blocks into the bundled Basalt Core plugin, so a site
  keeps them across a theme change. Settings moved from theme mods to options
  for the same reason.
* Breadcrumbs are now a server rendered block, reading from the same data as
  the BreadcrumbList structured data.
* The theme ships no JavaScript.
* Accessibility: eyebrow labels are paragraphs rather than headings, the posts
  page has an H1, submenu toggles meet the WCAG 2.2 target size, and
  navigation and search landmarks carry accessible names.
* Audited with axe-core across nine templates at three widths: zero
  violations, no horizontal overflow.

= 2.0.0 =
* Rewritten from the ground up.
* Added theme.json v3 with colour, type, spacing and shadow tokens.
* Added Schema.org JSON-LD graph with automatic FAQPage detection.
* Added Open Graph and Twitter Card output with SEO plugin detection.
* Added accessible navigation walker with submenu buttons.
* Added block styles, pattern categories and seven block patterns.
* Added WooCommerce support with conditionally loaded styles.
* Removed the bundled plugin installer that downloaded ZIP files over cURL.
* Removed all hardcoded agency content and the external icon font request.

= 1.0.0 =
* Initial release.

== Copyright ==

Basalt WordPress Theme, (C) 2026 Riccardo D'Arcangelo
Basalt is distributed under the terms of the GNU GPL v2 or later.

This theme bundles no third party code, no fonts and no images. It makes no
external requests at runtime.
