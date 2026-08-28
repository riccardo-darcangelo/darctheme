#!/bin/sh
# Install WordPress and fill it with content that exercises every template and
# every structured-data path in the theme.
#
# Run inside the cli container:
#   docker compose -f dev/compose.yaml exec -T cli sh /seed/seed.sh
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
wp plugin activate basalt-catalog

# Start from a known state so the script is repeatable.
wp site empty --yes

wp rewrite structure '/%postname%/' --hard >/dev/null
wp option update blogdescription 'Hoists, lifts and site equipment for hire across Bavaria'

# ---------------------------------------------------------------- front page
#
# Note the alignfull on the section groups. A group with no alignment is
# constrained to the content measure, and a child cannot be wider than its
# parent, so alignwide columns inside an unaligned group are silently clamped.

cat > /tmp/home.html <<'HTML'
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%"><!-- wp:heading {"level":6,"className":"is-style-eyebrow"} -->
<h6 class="wp-block-heading is-style-eyebrow">Since 1998</h6>
<!-- /wp:heading -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size">Equipment that arrives on time and works on site</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large","textColor":"contrast-soft"} -->
<p class="has-contrast-soft-color has-text-color has-large-font-size">Hoists, lifts and scaffolding equipment for hire. Delivered, set up and collected, anywhere in Bavaria, usually within two working days.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact/">Request a quote</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/catalog/">See the range</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"style":{"dimensions":{"aspectRatio":"16/10"},"border":{"radius":"16px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-radius:16px;background:linear-gradient(135deg,#2a3a47 0%,#16191d 100%);aspect-ratio:16/10"></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading {"level":6,"className":"is-style-eyebrow"} -->
<h6 class="wp-block-heading is-style-eyebrow">Why us</h6>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Twenty eight years on building sites</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"contrast-soft"} -->
<p class="has-contrast-soft-color has-text-color">We run our own fleet and our own workshop, so a machine that fails on a Friday is replaced on the Friday.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-checklist"} -->
<ul class="wp-block-list is-style-checklist"><!-- wp:list-item -->
<li>Delivery within two working days across Bavaria</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Set up and tested by our own crew</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Replacement machine within 24 hours if one fails</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"className":"is-style-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-card"><!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size">Quick figures</h3>
<!-- /wp:heading -->

<!-- wp:table {"className":"is-style-specs"} -->
<figure class="wp-block-table is-style-specs"><table><tbody><tr><th scope="row">Machines in the fleet</th><td>140</td></tr><tr><th scope="row">Average delivery time</th><td>1.4 working days</td></tr><tr><th scope="row">Service area</th><td>Bavaria and Baden-Wuerttemberg</td></tr></tbody></table></figure>
<!-- /wp:table --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":6,"className":"is-style-eyebrow"} -->
<h6 class="wp-block-heading is-style-eyebrow">FAQ</h6>
<!-- /wp:heading -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Questions we get asked</h2>
<!-- /wp:heading -->

<!-- wp:details {"summary":"How quickly can you deliver?","className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary>How quickly can you deliver?</summary><!-- wp:paragraph -->
<p>Two working days for anywhere in Bavaria, often the next morning if the order reaches us before noon. Outside Bavaria it depends on the route.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"Which areas do you cover?","className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary>Which areas do you cover?</summary><!-- wp:paragraph -->
<p>All of Bavaria and the eastern half of Baden-Wuerttemberg. Augsburg, Munich, Ingolstadt, Ulm and Nuremberg are covered daily.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"What does it cost?","className":"is-style-faq"} -->
<details class="wp-block-details is-style-faq"><summary>What does it cost?</summary><!-- wp:paragraph -->
<p>A 200 kg hoist starts at 38 euro per day including delivery inside Augsburg. Longer hires and larger machines are quoted individually.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"primary","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading {"textAlign":"center","textColor":"base"} -->
<h2 class="wp-block-heading has-text-align-center has-base-color has-text-color">Tell us what you need</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size">Send us the project and we come back with a quote, usually the same working day.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)"><!-- wp:button {"backgroundColor":"base","textColor":"contrast"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-contrast-color has-base-background-color has-text-color has-background wp-element-button" href="/contact/">Send an enquiry</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
HTML

HOME_ID=$(wp post create /tmp/home.html --post_type=page --post_title='Home' --post_name=home --post_status=publish --porcelain)
wp post meta update "$HOME_ID" _wp_page_template 'templates/template-landing.php' >/dev/null
wp option update show_on_front page >/dev/null
wp option update page_on_front "$HOME_ID" >/dev/null

BLOG_ID=$(wp post create --post_type=page --post_title='Journal' --post_name=journal --post_status=publish \
	--post_excerpt='Notes from the yard: equipment, site safety and the occasional opinion.' --porcelain)
wp option update page_for_posts "$BLOG_ID" >/dev/null

# ---------------------------------------------------------------- other pages

cat > /tmp/about.html <<'HTML'
<!-- wp:paragraph -->
<p>We started in 1998 with two hoists and a flatbed. Today we run 140 machines out of a yard in Augsburg, and we still answer the phone ourselves.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How we work</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every machine goes through the workshop between hires. That is slower than the industry average and it is the reason our failure rate is a third of it.</p>
<!-- /wp:paragraph -->

<!-- wp:quote {"className":"is-style-testimonial"} -->
<blockquote class="wp-block-quote is-style-testimonial"><!-- wp:paragraph -->
<p>They replaced a failed hoist on a Saturday morning. Nobody else would have picked up the phone.</p>
<!-- /wp:paragraph --><cite>Site manager, Munich</cite></blockquote>
<!-- /wp:quote -->
HTML
wp post create /tmp/about.html --post_type=page --post_title='About' --post_name=about --post_status=publish \
	--post_excerpt='Twenty eight years of hiring out site equipment from a yard in Augsburg.' >/dev/null

cat > /tmp/contact.html <<'HTML'
<!-- wp:paragraph -->
<p>Tell us the site, the height and the load, and we will come back with a price the same working day.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Reach us</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-plain"} -->
<ul class="wp-block-list is-style-plain"><!-- wp:list-item -->
<li><a href="tel:+498211234567">+49 821 1234567</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="mailto:hire@example.test">hire@example.test</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Industriestrasse 12, 86153 Augsburg</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
HTML
wp post create /tmp/contact.html --post_type=page --post_title='Contact' --post_name=contact --post_status=publish \
	--post_excerpt='Phone, email and the address of the yard in Augsburg.' >/dev/null

wp post create --post_type=page --post_title='Imprint' --post_name=imprint --post_status=publish \
	--post_content='<!-- wp:paragraph --><p>Placeholder for the legally required imprint.</p><!-- /wp:paragraph -->' >/dev/null
wp post create --post_type=page --post_title='Privacy' --post_name=privacy --post_status=publish \
	--post_content='<!-- wp:paragraph --><p>Placeholder for the privacy notice.</p><!-- /wp:paragraph -->' >/dev/null

# ---------------------------------------------------------------- blog posts

wp post create --post_type=post --post_status=publish \
	--post_title='Choosing a hoist by load, not by price' \
	--post_excerpt='The cheapest machine that fits the load is rarely the cheapest machine for the job.' \
	--post_content='<!-- wp:paragraph --><p>Load capacity is the first number everyone looks at and the last one that should decide the hire. A 200 kg hoist running at its limit all day wears faster, runs slower and fails sooner than a 300 kg machine at two thirds load.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Work out the real load</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Take the heaviest single item, add the carrier, then add a third. That last third is what stops the machine running at its limit.</p><!-- /wp:paragraph -->' >/dev/null

wp post create --post_type=post --post_status=publish \
	--post_title='What we check between hires' \
	--post_excerpt='Every machine goes through the workshop before it goes out again. Here is the list.' \
	--post_content='<!-- wp:paragraph --><p>Between every hire a machine goes through the workshop. It is slower than the industry average and it is the reason our failure rate is a third of it.</p><!-- /wp:paragraph --><!-- wp:list {"className":"is-style-checklist"} --><ul class="wp-block-list is-style-checklist"><!-- wp:list-item --><li>Rope and hook inspected over the full length</li><!-- /wp:list-item --><!-- wp:list-item --><li>Brake tested under load</li><!-- /wp:list-item --><!-- wp:list-item --><li>Limit switches triggered in both directions</li><!-- /wp:list-item --></ul><!-- /wp:list -->' >/dev/null

wp post create --post_type=post --post_status=publish \
	--post_title='Site power: what a 230 volt machine really needs' \
	--post_excerpt='A machine rated at 230 volts still trips the site supply if the run is long enough.' \
	--post_content='<!-- wp:paragraph --><p>The rating on the plate assumes the supply is at the machine. On a site it rarely is, and 60 metres of extension is enough to drop the voltage below what the motor needs to start under load.</p><!-- /wp:paragraph -->' >/dev/null

wp term create category 'Equipment' --slug=equipment >/dev/null 2>&1 || true
for id in $(wp post list --post_type=post --format=ids); do
	wp post term set "$id" category equipment >/dev/null
done

# ------------------------------------------------------------ catalog items

add_item() {
	ID=$(wp post create --post_type=catalog_item --post_status=publish \
		--post_title="$1" --post_name="$2" --post_excerpt="$3" --menu_order="${11}" \
		--post_content="<!-- wp:paragraph --><p>$3</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class=\"wp-block-heading\">Where it fits</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Delivered on a trailer, set up by our crew and handed over tested. Collection is included in the hire price.</p><!-- /wp:paragraph -->" \
		--porcelain)

	wp post meta update "$ID" _catalog_capacity "$4" >/dev/null
	wp post meta update "$ID" _catalog_max_height "$5" >/dev/null
	wp post meta update "$ID" _catalog_speed "$6" >/dev/null
	wp post meta update "$ID" _catalog_power "$7" >/dev/null
	wp post meta update "$ID" _catalog_weight "$8" >/dev/null
	wp post term set "$ID" catalog_capacity "$9" >/dev/null
	wp post term set "$ID" catalog_use_case "${10}" >/dev/null
}

wp term create catalog_capacity 'Up to 200 kg' --slug=up-to-200 >/dev/null 2>&1 || true
wp term create catalog_capacity 'Up to 300 kg' --slug=up-to-300 >/dev/null 2>&1 || true
wp term create catalog_capacity 'Above 300 kg' --slug=above-300 >/dev/null 2>&1 || true
wp term create catalog_use_case 'Facade work' --slug=facade >/dev/null 2>&1 || true
wp term create catalog_use_case 'Roofing' --slug=roofing >/dev/null 2>&1 || true
wp term create catalog_use_case 'Interior fit-out' --slug=interior >/dev/null 2>&1 || true

add_item 'Hoist GL 200' 'hoist-gl-200' \
	'The workhorse of the fleet. 200 kg to 30 metres on a single phase supply, light enough for two people to position.' \
	200 30 30 '230 V' 85 'up-to-200' 'facade' 1
add_item 'Hoist GL 300 T' 'hoist-gl-300-t' \
	'Three hundred kilos and a wider carrier, for pallets of tile and stone that will not fit a standard cradle.' \
	300 40 24 '400 V' 140 'up-to-300' 'roofing' 2
add_item 'Roof lift RL 250' 'roof-lift-rl-250' \
	'An inclined lift for tile and membrane. Sets up against the eaves in under twenty minutes.' \
	250 22 18 '230 V' 190 'up-to-300' 'roofing' 3
add_item 'Interior lift IL 150' 'interior-lift-il-150' \
	'Fits through a standard door frame and runs off a normal socket. For fit-out work above the second floor.' \
	150 12 20 '230 V' 62 'up-to-200' 'interior' 4
add_item 'Heavy hoist HH 500' 'heavy-hoist-hh-500' \
	'Five hundred kilos to 45 metres. Needs a 400 volt supply and a crew of two to rig.' \
	500 45 20 '400 V' 310 'above-300' 'facade' 5
add_item 'Facade platform FP 400' 'facade-platform-fp-400' \
	'A powered platform rather than a hoist: crew and material go up together, which halves the trips.' \
	400 35 12 '400 V' 520 'above-300' 'facade' 6

# ------------------------------------------------------------------- menus

wp menu create "Main" >/dev/null 2>&1 || true
wp menu create "Legal" >/dev/null 2>&1 || true

wp menu item add-post main "$(wp post list --post_type=page --name=home --format=ids)" --title="Home" >/dev/null
wp menu item add-custom main "Catalog" "/catalog/" >/dev/null
wp menu item add-post main "$(wp post list --post_type=page --name=about --format=ids)" --title="About" >/dev/null
wp menu item add-post main "$(wp post list --post_type=page --name=journal --format=ids)" --title="Journal" >/dev/null
wp menu item add-post main "$(wp post list --post_type=page --name=contact --format=ids)" --title="Contact" >/dev/null

# A submenu, so the accessible dropdown is actually exercised.
PARENT=$(wp menu item list main --fields=db_id,title --format=csv | grep -i ',Catalog' | head -1 | cut -d, -f1)
for slug in facade roofing interior; do
	wp menu item add-term main catalog_use_case "$(wp term list catalog_use_case --slug=$slug --field=term_id)" --parent-id="$PARENT" >/dev/null
done

wp menu item add-post legal "$(wp post list --post_type=page --name=imprint --format=ids)" --title="Imprint" >/dev/null
wp menu item add-post legal "$(wp post list --post_type=page --name=privacy --format=ids)" --title="Privacy" >/dev/null

wp menu location assign main primary >/dev/null
wp menu location assign legal legal >/dev/null

# ----------------------------------------------------------------- widgets

wp widget add search sidebar-1 --title="Search" >/dev/null 2>&1 || true
wp widget add recent-posts sidebar-1 --title="Latest from the journal" >/dev/null 2>&1 || true
wp widget add categories sidebar-1 --title="Topics" >/dev/null 2>&1 || true
wp widget add text footer-1 --title="The yard" --text="Industriestrasse 12, 86153 Augsburg. Open Monday to Friday, 7am to 6pm." >/dev/null 2>&1 || true
wp widget add recent-posts footer-2 --title="Journal" >/dev/null 2>&1 || true

# --------------------------------------------------- theme options per theme
#
# WordPress stores theme mods per stylesheet, so switching from Basalt to
# Basalt Child drops every setting made here. Both are seeded so the demo looks
# the same whichever is active, and so the switch does not look broken.

seed_theme_mods() {
	wp theme mod set schema_entity_type 'HomeAndConstructionBusiness' >/dev/null
	wp theme mod set schema_entity_name 'Augsburger Hebetechnik' >/dev/null
	wp theme mod set schema_phone '+49 821 1234567' >/dev/null
	wp theme mod set schema_email 'hire@example.test' >/dev/null
	wp theme mod set schema_street 'Industriestrasse 12' >/dev/null
	wp theme mod set schema_postal_code '86153' >/dev/null
	wp theme mod set schema_city 'Augsburg' >/dev/null
	wp theme mod set schema_region 'Bayern' >/dev/null
	wp theme mod set schema_country 'DE' >/dev/null
	wp theme mod set schema_opening_hours 'Mo-Fr 07:00-18:00' >/dev/null
	wp theme mod set schema_profiles 'https://www.linkedin.com/company/example' >/dev/null
	wp theme mod set footer_copyright 'Copyright {year} Augsburger Hebetechnik' >/dev/null
}

seed_theme_mods

wp theme activate basalt-child >/dev/null
seed_theme_mods
wp menu location assign main primary >/dev/null
wp menu location assign legal legal >/dev/null

# Leave the parent theme active; the child is one click away in the admin.
wp theme activate basalt >/dev/null

wp rewrite flush --hard >/dev/null

echo ""
echo "Seeded. Open ${WP_URL}  (${WP_ADMIN} / ${WP_PASSWORD})"
echo "Switch to Basalt Child under Appearance > Themes to see the catalog extension."
