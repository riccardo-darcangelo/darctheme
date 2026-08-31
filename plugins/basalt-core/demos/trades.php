<?php
/**
 * Demo: a trade hiring out equipment.
 *
 * The pages are made of patterns rather than of markup, so this file stays
 * short and the demo cannot drift from what the buyer finds in the inserter.
 * Where a page needs its own words, they are here.
 *
 * Catalog entries are declared even though the post type belongs to a separate
 * plugin. The runner skips any post whose type does not exist, so the demo
 * imports correctly with or without Basalt Catalog and does not have to ask.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Trades and equipment', 'basalt-core' ),
	'description' => __( 'A firm that hires out machines: a landing page, a catalog with technical data, three articles and the usual legal pages. The closest fit for construction, plant hire, workshops and anything sold on specifications.', 'basalt-core' ),

	'front_page' => 'home',
	'posts_page' => 'journal',
	'logo'       => 'mark',

	'options' => array(
		'blogdescription' => __( 'Hoists, lifts and site equipment for hire across Bavaria', 'basalt-core' ),
	),

	'settings' => array(
		/*
		 * The display settings panel is on, because it is a front end feature
		 * of the theme and a demo that hides it is not showing the product. The
		 * login screen is deliberately absent from this list: changing an admin
		 * screen is not something an import should do to somebody unasked.
		 */
		'preferences_enabled' => true,
		'entity_type'   => 'HomeAndConstructionBusiness',
		'entity_name'   => __( 'Augsburger Hebetechnik', 'basalt-core' ),
		'opening_hours' => 'Mo-Fr 07:00-18:00',
	),

	'images' => array(
		'mark'    => array(
			'width'  => 256,
			'height' => 256,
			'seed'   => 1,
			'title'  => __( 'Site mark', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, standing in for a logo', 'basalt-core' ),
		),
		'hero'    => array(
			'width'  => 1600,
			'height' => 1000,
			'seed'   => 3,
			'title'  => __( 'The fleet', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the fleet would go', 'basalt-core' ),
		),
		'social'  => array(
			'width'  => 1200,
			'height' => 630,
			'seed'   => 7,
			'title'  => __( 'Social preview', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns', 'basalt-core' ),
		),
		'yard'    => array(
			'width'  => 1600,
			'height' => 900,
			'seed'   => 11,
			'title'  => __( 'The yard', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the yard would go', 'basalt-core' ),
		),
		'catalog-a' => array(
			'width'  => 1200,
			'height' => 750,
			'seed'   => 21,
			'title'  => __( 'Hoist GL 200', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the machine would go', 'basalt-core' ),
		),
		'catalog-b' => array(
			'width'  => 1200,
			'height' => 750,
			'seed'   => 22,
			'title'  => __( 'Roof lift RL 250', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the machine would go', 'basalt-core' ),
		),
		'catalog-c' => array(
			'width'  => 1200,
			'height' => 750,
			'seed'   => 23,
			'title'  => __( 'Heavy hoist HH 500', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the machine would go', 'basalt-core' ),
		),
		'article' => array(
			'width'  => 1600,
			'height' => 900,
			'seed'   => 41,
			'title'  => __( 'Article image', 'basalt-core' ),
			'alt'    => __( 'Placeholder graphic: a pattern of basalt columns, where the article image would go', 'basalt-core' ),
		),
	),

	'terms' => array(
		array(
			'taxonomy' => 'category',
			'name'     => __( 'Equipment', 'basalt-core' ),
			'slug'     => 'equipment',
		),
		array(
			'taxonomy' => 'catalog_capacity',
			'name'     => __( 'Up to 200 kg', 'basalt-core' ),
			'slug'     => 'up-to-200',
		),
		array(
			'taxonomy' => 'catalog_capacity',
			'name'     => __( 'Above 300 kg', 'basalt-core' ),
			'slug'     => 'above-300',
		),
		array(
			'taxonomy' => 'catalog_use_case',
			'name'     => __( 'Facade work', 'basalt-core' ),
			'slug'     => 'facade',
		),
		array(
			'taxonomy' => 'catalog_use_case',
			'name'     => __( 'Roofing', 'basalt-core' ),
			'slug'     => 'roofing',
		),
	),

	'menu' => array(
		'title' => __( 'Main', 'basalt-core' ),
		'items' => array(
			array( 'label' => __( 'Home', 'basalt-core' ), 'page' => 'home' ),
			array( 'label' => __( 'Services', 'basalt-core' ), 'page' => 'services' ),
			array( 'label' => __( 'About', 'basalt-core' ), 'page' => 'about' ),
			array( 'label' => __( 'Journal', 'basalt-core' ), 'page' => 'journal' ),
			array( 'label' => __( 'Contact', 'basalt-core' ), 'page' => 'contact' ),
		),
	),

	'posts' => array(
		array(
			'type'     => 'page',
			'slug'     => 'home',
			'title'    => __( 'Home', 'basalt-core' ),
			'template' => 'page-landing',
			/*
			 * The hero is written out rather than referenced as a pattern, and
			 * it is the only page section here that is. A pattern is resolved
			 * when the page is rendered, long after the import has finished, so
			 * it cannot be handed the id of an image the import has just
			 * created. The hero is the one place where that costs something
			 * visible: every other section reads correctly without a picture,
			 * and this one does not.
			 */
			'content'  => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->'
				. '<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">'
				. '<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->'
				. '<div class="wp-block-columns alignwide are-vertically-aligned-center">'
				. '<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->'
				. '<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">'
				. '<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">' . __( 'Since 1998', 'basalt-core' ) . '</p><!-- /wp:paragraph -->'
				. '<!-- wp:heading {"level":1,"fontSize":"display"} --><h1 class="wp-block-heading has-display-font-size">' . __( 'Equipment that arrives on time and works on site', 'basalt-core' ) . '</h1><!-- /wp:heading -->'
				. '<!-- wp:paragraph {"className":"is-style-lead","textColor":"contrast-soft"} --><p class="is-style-lead has-contrast-soft-color has-text-color">' . __( 'Hoists, lifts and scaffolding equipment for hire. Delivered, set up and collected, anywhere in Bavaria, usually within two working days.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->'
				. '<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} --><div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">'
				. '<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{{url:contact}}">' . __( 'Request a quote', 'basalt-core' ) . '</a></div><!-- /wp:button -->'
				. '<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="{{url:services}}">' . __( 'See the range', 'basalt-core' ) . '</a></div><!-- /wp:button -->'
				. '</div><!-- /wp:buttons --></div><!-- /wp:column -->'
				. '<!-- wp:column {"verticalAlignment":"center"} --><div class="wp-block-column is-vertically-aligned-center">'
				. '<!-- wp:image {"aspectRatio":"16/10","scale":"cover","sizeSlug":"large","linkDestination":"none","style":{"border":{"radius":"16px"}}} -->'
				. '<figure class="wp-block-image size-large has-custom-border"><img src="{{image:hero}}" alt="' . esc_attr__( 'Placeholder graphic: a pattern of basalt columns, where a photograph of the fleet would go', 'basalt-core' ) . '" style="border-radius:16px;aspect-ratio:16/10;object-fit:cover"/></figure>'
				. '<!-- /wp:image --></div><!-- /wp:column -->'
				. '</div><!-- /wp:columns --></div><!-- /wp:group -->'
				. '<!-- wp:pattern {"slug":"basalt/stats-row"} /-->'
				. '<!-- wp:pattern {"slug":"basalt/service-cards"} /-->'
				. '<!-- wp:pattern {"slug":"basalt/steps-process"} /-->'
				. '<!-- wp:pattern {"slug":"basalt/testimonials-row"} /-->'
				. '<!-- wp:pattern {"slug":"basalt/faq-accordion"} /-->'
				. '<!-- wp:pattern {"slug":"basalt/cta-contact"} /-->',
		),
		array(
			'type'    => 'page',
			'slug'    => 'services',
			'title'   => __( 'Services', 'basalt-core' ),
			'excerpt' => __( 'What we hire out, how it works and what it costs.', 'basalt-core' ),
			'content' => '<!-- wp:pattern {"slug":"basalt/page-services"} /-->',
		),
		array(
			'type'    => 'page',
			'slug'    => 'about',
			'title'   => __( 'About', 'basalt-core' ),
			'excerpt' => __( 'Twenty eight years of hiring out site equipment from a yard in Augsburg.', 'basalt-core' ),
			'image'   => 'yard',
			'content' => '<!-- wp:pattern {"slug":"basalt/page-about"} /-->',
		),
		array(
			'type'    => 'page',
			'slug'    => 'contact',
			'title'   => __( 'Contact', 'basalt-core' ),
			'excerpt' => __( 'Where to find us, and what happens after an enquiry.', 'basalt-core' ),
			'content' => '<!-- wp:pattern {"slug":"basalt/page-contact"} /-->',
		),
		array(
			'type'    => 'page',
			'slug'    => 'journal',
			'title'   => __( 'Journal', 'basalt-core' ),
			'excerpt' => __( 'Notes from the workshop and the yard.', 'basalt-core' ),
			'content' => '',
		),
		array(
			'type'    => 'page',
			'slug'    => 'imprint',
			'title'   => __( 'Imprint', 'basalt-core' ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'The legally required details of who runs this site. Replace this with yours.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),
		array(
			'type'    => 'page',
			'slug'    => 'privacy',
			'title'   => __( 'Privacy', 'basalt-core' ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'What this site stores, why, and for how long. Replace this with yours.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),

		array(
			'type'    => 'post',
			'slug'    => 'choosing-a-hoist-by-load-not-by-price',
			'title'   => __( 'Choosing a hoist by load, not by price', 'basalt-core' ),
			'excerpt' => __( 'The cheapest machine that fits the load is rarely the cheapest machine for the job.', 'basalt-core' ),
			'image'   => 'article',
			'terms'   => array( 'category' => array( 'equipment' ) ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Load capacity is the first number everyone looks at and the last one that should decide the hire. A machine running at its limit all day wears faster, runs slower and fails sooner than a larger one at two thirds load.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->'
				. '<!-- wp:heading --><h2 class="wp-block-heading">' . __( 'Work out the real load', 'basalt-core' ) . '</h2><!-- /wp:heading -->'
				. '<!-- wp:paragraph --><p>' . __( 'Take the heaviest single item, add the carrier, then add a third. That last third is what stops the machine running at its limit.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),
		array(
			'type'    => 'post',
			'slug'    => 'what-we-check-between-hires',
			'title'   => __( 'What we check between hires', 'basalt-core' ),
			'excerpt' => __( 'Every machine goes through the workshop before it goes out again. Here is the list.', 'basalt-core' ),
			'image'   => 'article',
			'terms'   => array( 'category' => array( 'equipment' ) ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Between every hire a machine goes through the workshop. It is slower than the industry average and it is the reason our failure rate is a third of it.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->'
				. '<!-- wp:list {"className":"is-style-checklist"} --><ul class="wp-block-list is-style-checklist">'
				. '<!-- wp:list-item --><li>' . __( 'Rope and hook inspected over the full length', 'basalt-core' ) . '</li><!-- /wp:list-item -->'
				. '<!-- wp:list-item --><li>' . __( 'Brake tested under load', 'basalt-core' ) . '</li><!-- /wp:list-item -->'
				. '<!-- wp:list-item --><li>' . __( 'Limit switches triggered in both directions', 'basalt-core' ) . '</li><!-- /wp:list-item -->'
				. '</ul><!-- /wp:list -->',
		),
		array(
			'type'    => 'post',
			'slug'    => 'site-power-what-a-230-volt-machine-really-needs',
			'title'   => __( 'Site power: what a 230 volt machine really needs', 'basalt-core' ),
			'excerpt' => __( 'A machine rated at 230 volts still trips the site supply if the run is long enough.', 'basalt-core' ),
			'image'   => 'article',
			'terms'   => array( 'category' => array( 'equipment' ) ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'The rating on the plate assumes the supply is at the machine. On a site it rarely is, and sixty metres of extension is enough to drop the voltage below what the motor needs to start under load.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),

		/*
		 * Catalog entries. The post type belongs to Basalt Catalog, and the
		 * runner skips any post whose type is not registered, so these appear
		 * when that plugin is active and are quietly left out when it is not.
		 * The demo does not have to ask, and it does not half import.
		 */
		array(
			'type'    => 'catalog_item',
			'slug'    => 'hoist-gl-200',
			'title'   => __( 'Hoist GL 200', 'basalt-core' ),
			'excerpt' => __( 'The workhorse of the fleet. 200 kg to 30 metres on a single phase supply, light enough for two people to position.', 'basalt-core' ),
			'order'   => 1,
			'image'   => 'catalog-a',
			'terms'   => array(
				'catalog_capacity' => array( 'up-to-200' ),
				'catalog_use_case' => array( 'facade' ),
			),
			'meta'    => array(
				'_catalog_capacity'   => 200,
				'_catalog_max_height' => 30,
				'_catalog_speed'      => 30,
				'_catalog_power'      => '230 V',
				'_catalog_weight'     => 85,
			),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Delivered on a trailer, set up by our crew and handed over tested. Collection is included in the hire price.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),
		array(
			'type'    => 'catalog_item',
			'slug'    => 'roof-lift-rl-250',
			'title'   => __( 'Roof lift RL 250', 'basalt-core' ),
			'excerpt' => __( 'An inclined lift for tile and membrane. Sets up against the eaves in under twenty minutes.', 'basalt-core' ),
			'order'   => 2,
			'image'   => 'catalog-b',
			'terms'   => array(
				'catalog_capacity' => array( 'up-to-200' ),
				'catalog_use_case' => array( 'roofing' ),
			),
			'meta'    => array(
				'_catalog_capacity'   => 250,
				'_catalog_max_height' => 22,
				'_catalog_speed'      => 18,
				'_catalog_power'      => '230 V',
				'_catalog_weight'     => 190,
			),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Delivered on a trailer, set up by our crew and handed over tested. Collection is included in the hire price.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),
		array(
			'type'    => 'catalog_item',
			'slug'    => 'heavy-hoist-hh-500',
			'title'   => __( 'Heavy hoist HH 500', 'basalt-core' ),
			'excerpt' => __( 'Five hundred kilos to 45 metres. Needs a 400 volt supply and a crew of two to rig.', 'basalt-core' ),
			'order'   => 3,
			'image'   => 'catalog-c',
			'terms'   => array(
				'catalog_capacity' => array( 'above-300' ),
				'catalog_use_case' => array( 'facade' ),
			),
			'meta'    => array(
				'_catalog_capacity'   => 500,
				'_catalog_max_height' => 45,
				'_catalog_speed'      => 20,
				'_catalog_power'      => '400 V',
				'_catalog_weight'     => 310,
			),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Delivered on a trailer, set up by our crew and handed over tested. Collection is included in the hire price.', 'basalt-core' ) . '</p><!-- /wp:paragraph -->',
		),
	),
);
