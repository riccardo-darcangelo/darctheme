=== Basalt ===

Contributors: Riccardo D'Arcangelo
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 2.0.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, portfolio, business, one-column, two-columns, block-patterns, block-styles, custom-colors, custom-logo, custom-menu, editor-style, featured-images, full-width-template, right-sidebar, rtl-language-support, sticky-post, threaded-comments, translation-ready, wide-blocks, accessibility-ready

A fast, accessible and SEO-first hybrid WordPress theme.

== Description ==

Basalt combines classic PHP templates with a theme.json design system. The
templates give exact control over markup, headings and structured data; the
design tokens are edited in the site editor under Styles and apply to the
front end, the editor, the patterns and the block styles at once.

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

In the site editor under Appearance > Editor > Styles. Everything comes from
theme.json, and both the front end and the editor follow. Editing CSS is only
needed for layout work.

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

= 2.0.0 =
* Rewritten from the ground up as a hybrid theme.
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
