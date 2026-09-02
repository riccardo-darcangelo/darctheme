#!/bin/sh
# Install WordPress and fill it with the demo content that ships in Basalt Core.
#
# Run inside the cli container:
#   docker compose -f dev/compose.yaml exec -T cli sh /seed/seed.sh
#
# The content itself is not in this file. It used to be, five hundred lines of
# it, and it was a second implementation of something the product already had to
# do: the demo importer in Basalt Core builds the same site from the same
# declaration. Two implementations of one demo drift apart, and the one that
# drifts is always the one the buyer gets, because the one in dev/ is the one
# being looked at every day.
#
# So this script now does the part a buyer never does, which is install
# WordPress and put it into a known state, and then calls the importer. Every
# seed is a test of the code that ships.
#
# Safe to re-run: the content is wiped first, so the result is always the same
# regardless of what was clicked in the admin beforehand.
#
# Line endings matter. This file must stay LF; a CRLF copy fails inside the
# container with "sh: : not found" on random lines. .gitattributes enforces it.

set -e

WP_URL="${WP_URL:-http://localhost:8088}"
WP_ADMIN="${WP_ADMIN:-admin}"
WP_PASSWORD="${WP_PASSWORD:-basalt-demo-pw}"
WP_DEMO="${WP_DEMO:-trades}"
export WP_DEMO

# ---------------------------------------------------------------- install

if ! wp core is-installed 2>/dev/null; then
	wp core install \
		--url="$WP_URL" \
		--title="Basalt Demo" \
		--admin_user="$WP_ADMIN" \
		--admin_password="$WP_PASSWORD" \
		--admin_email="demo@example.test" \
		--skip-email
fi

wp theme activate basalt
wp plugin activate basalt-core basalt-catalog basalt-forms basalt-shop

# Theme development mode, so a new pattern file or a changed design token is
# picked up on the next request. Without it WordPress caches the list of pattern
# files and the parsed theme.json, and an edit to either appears to do nothing.
wp config set WP_DEVELOPMENT_MODE theme --type=constant >/dev/null

# Start from a known state so the script is repeatable.
wp site empty --yes

# Templates and template parts saved from the site editor live in the database
# and shadow the theme files. Left behind, a re-seed keeps serving the previous
# version of the header, and edits to the theme appear to do nothing.
STALE=$(wp post list --post_type=wp_template,wp_template_part --format=ids)
if [ -n "$STALE" ]; then
	wp post delete $STALE --force >/dev/null
fi

# The importer refuses to run a demo it has already run, which is right for a
# buyer and wrong for a script whose whole job is to rebuild the site.
wp option delete basalt_core_imported_demos >/dev/null 2>&1 || true

wp rewrite structure '/%postname%/' --hard >/dev/null

# ------------------------------------------------------------------- content

wp eval '
$result = basalt_core_import_demo( getenv( "WP_DEMO" ) ?: "trades" );

if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}

WP_CLI::log( "Imported: " . wp_json_encode( $result["created"] ) );

foreach ( $result["notices"] as $notice ) {
	WP_CLI::warning( $notice );
}
'

# ------------------------------------------------------------- sandbox only

# The demo deliberately leaves the login screen alone: changing an admin screen
# is not something an import should do to somebody unasked. The sandbox turns it
# on, because looking at it is what the sandbox is for.
LOGO_ID=$(wp option get site_logo)

wp option patch update basalt_core_settings login_enabled 1 >/dev/null
wp option patch update basalt_core_settings login_generic_errors 1 >/dev/null
wp option patch update basalt_core_settings login_logo "$LOGO_ID" >/dev/null
wp option patch update basalt_core_settings login_logo_width 128 >/dev/null
wp option patch update basalt_core_settings login_background '#1f3d34' >/dev/null
wp option patch update basalt_core_settings login_form_background '#ffffff' >/dev/null
wp option patch update basalt_core_settings login_accent '#c2410c' >/dev/null

wp rewrite flush --hard >/dev/null

echo ""
echo "Seeded. Open ${WP_URL}  (${WP_ADMIN} / ${WP_PASSWORD})"
echo "This is the same content a buyer gets from Tools > Basalt demos."
echo "Styles: Appearance > Editor > Styles has five variations, including High contrast and Boutique."
echo "Switch to Basalt Child under Appearance > Themes to see the catalog extension."
