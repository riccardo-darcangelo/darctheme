<?php
/**
 * Demo content.
 *
 * A demo is a declaration, not a script: a file in demos/ returns an array of
 * pages, posts, terms, images and settings, and one runner below creates them.
 * That is what lets a site add its own demo without writing an importer, and
 * it is why the demos themselves are twenty lines each.
 *
 * Three decisions worth knowing about before reading the code.
 *
 * Nothing is ever deleted. Every demo importer that offers to "replace" the
 * existing content is one misplaced click away from removing a site somebody
 * had already started. This one only ever creates, refuses to run twice, and
 * says what it made.
 *
 * The imagery is drawn here rather than shipped. A theme sold on a marketplace
 * cannot redistribute photography it does not hold the rights to, and demo
 * content is exactly where that goes wrong. Drawn graphics have no such
 * question hanging over them, they weigh nothing in the package, and they are
 * obviously placeholders, which is what a buyer wants to replace first.
 *
 * The pages are made of patterns. A demo page is a handful of
 * wp:pattern references, so the demo cannot drift from the patterns the buyer
 * sees in the inserter, and improving a pattern improves the demo.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/** Option holding what has been imported already. */
const BASALT_CORE_DEMO_OPTION = 'basalt_core_imported_demos';

/**
 * The demos available to import.
 *
 * @return array<string, array<string, mixed>>
 */
function basalt_core_demos(): array {
	static $demos = null;

	if ( null !== $demos ) {
		return $demos;
	}

	$demos = array();
	$dir   = BASALT_CORE_DIR . 'demos/';

	foreach ( (array) glob( $dir . '*.php' ) as $file ) {
		$slug = basename( (string) $file, '.php' );

		/*
		 * The slug comes from the file name and the file name comes from a
		 * glob of a directory inside the plugin, so it cannot be steered from
		 * outside. Constrained anyway, because it ends up in an option key and
		 * in a form value.
		 */
		if ( ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
			continue;
		}

		$demo = require $file;

		if ( is_array( $demo ) && ! empty( $demo['title'] ) ) {
			$demos[ $slug ] = $demo;
		}
	}

	/**
	 * Filter the available demos.
	 *
	 * @param array<string, array<string, mixed>> $demos Slug to declaration.
	 */
	return (array) apply_filters( 'basalt_core_demos', $demos );
}

/**
 * Which demos have been imported, and when.
 *
 * @return array<string, array<string, mixed>>
 */
function basalt_core_imported_demos(): array {
	$imported = get_option( BASALT_CORE_DEMO_OPTION, array() );

	return is_array( $imported ) ? $imported : array();
}

/**
 * Draw one placeholder image and add it to the media library.
 *
 * Seeded from a number in the declaration, so the same demo always produces
 * the same pictures and two people importing it see the same site.
 *
 * @param array<string, mixed> $spec Width, height, seed, title and alt.
 * @return array{id: int, created: bool} The attachment, and whether this call
 *                                    is what made it.
 */
function basalt_core_demo_image( array $spec ): array {
	if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagejpeg' ) ) {
		return array( 'id' => 0, 'created' => false );
	}

	$title = (string) ( $spec['title'] ?? 'Demo image' );

	/*
	 * An image with this title already in the library is reused rather than
	 * drawn again. Running the import a second time skips every post, because
	 * they exist, but it used to draw the pictures anyway and leave eight
	 * duplicates behind. Nothing in the interface offers a second run, but the
	 * function is callable and should not litter when it is called.
	 */
	$existing = get_posts(
		array(
			'title'                  => $title,
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'numberposts'            => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $existing ) {
		return array( 'id' => (int) $existing[0], 'created' => false );
	}

	$width  = max( 16, (int) ( $spec['width'] ?? 1200 ) );
	$height = max( 16, (int) ( $spec['height'] ?? 750 ) );
	$seed   = (int) ( $spec['seed'] ?? 1 );

	mt_srand( $seed );

	$image = imagecreatetruecolor( $width, $height );

	// Grounds from the theme palette, so the imagery cannot clash with it.
	$grounds = array(
		array( 42, 58, 71 ),
		array( 26, 37, 46 ),
		array( 22, 25, 29 ),
		array( 31, 61, 52 ),
	);
	$ground  = $grounds[ $seed % count( $grounds ) ];

	for ( $y = 0; $y < $height; $y++ ) {
		$fade = 1 - 0.5 * ( $y / max( 1, $height - 1 ) );

		imageline(
			$image,
			0,
			$y,
			$width,
			$y,
			imagecolorallocate(
				$image,
				(int) round( $ground[0] * $fade ),
				(int) round( $ground[1] * $fade ),
				(int) round( $ground[2] * $fade )
			)
		);
	}

	// Basalt columns seen end on: pointy top hexagons on a staggered grid.
	$column = static function ( $target, float $cx, float $cy, float $r, int $color ): void {
		$points = array();

		for ( $i = 0; $i < 6; $i++ ) {
			$angle    = deg2rad( 60 * $i - 30 );
			$points[] = (int) round( $cx + $r * cos( $angle ) );
			$points[] = (int) round( $cy + $r * sin( $angle ) );
		}

		imagefilledpolygon( $target, $points, $color );
	};

	$unit = max( 12, min( $width, $height ) / 6 );
	$step = $unit * sqrt( 3 );

	for ( $row = -1; $row * $unit * 1.5 < $height + $unit; $row++ ) {
		for ( $col = -1; $col * $step < $width + $step; $col++ ) {
			$lift = mt_rand( -14, 30 );

			$column(
				$image,
				$col * $step + ( 0 !== $row % 2 ? $step / 2 : 0 ),
				$row * $unit * 1.5,
				$unit * 0.97,
				imagecolorallocatealpha(
					$image,
					max( 0, min( 255, $ground[0] + $lift ) ),
					max( 0, min( 255, $ground[1] + $lift ) ),
					max( 0, min( 255, $ground[2] + $lift ) ),
					mt_rand( 55, 100 )
				)
			);
		}
	}

	// One column in the accent, so every image has a focal point.
	$column(
		$image,
		mt_rand( (int) ( $width * 0.2 ), (int) ( $width * 0.8 ) ),
		mt_rand( (int) ( $height * 0.25 ), (int) ( $height * 0.75 ) ),
		$unit * 0.97,
		imagecolorallocatealpha( $image, 194, 65, 12, 30 )
	);

	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		imagedestroy( $image );

		return array( 'id' => 0, 'created' => false );
	}

	$name = wp_unique_filename( $uploads['path'], sanitize_file_name( $title . '.jpg' ) );
	$path = trailingslashit( $uploads['path'] ) . $name;

	imagejpeg( $image, $path, 82 );
	imagedestroy( $image );

	$attachment = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$path
	);

	if ( is_wp_error( $attachment ) || ! $attachment ) {
		return array( 'id' => 0, 'created' => false );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $path ) );

	/*
	 * Alternative text on every one of them. A plugin that sells accessibility
	 * and then fills a library with images that have none teaches the buyer the
	 * wrong habit on their first day. The wording says what the picture is
	 * rather than pretending it is a photograph of something.
	 */
	update_post_meta( $attachment, '_wp_attachment_image_alt', (string) ( $spec['alt'] ?? '' ) );

	return array( 'id' => (int) $attachment, 'created' => true );
}

/**
 * Replace the tokens a demo may use in its content.
 *
 * Two of them: {{url:slug}} for a link to another page in the same demo, and
 * {{image:key}} for the URL of one of its images. Both exist because neither
 * value is known until the import is running.
 *
 * @param string             $content Raw content.
 * @param array<string, int> $pages   Slug to post ID.
 * @param array<string, int> $images  Key to attachment ID.
 * @return string
 */
function basalt_core_demo_tokens( string $content, array $pages, array $images ): string {
	return (string) preg_replace_callback(
		'/\{\{(url|image):([a-z0-9-]+)\}\}/',
		static function ( array $match ) use ( $pages, $images ): string {
			if ( 'url' === $match[1] ) {
				return isset( $pages[ $match[2] ] ) ? (string) get_permalink( $pages[ $match[2] ] ) : '#';
			}

			return isset( $images[ $match[2] ] ) ? (string) wp_get_attachment_url( $images[ $match[2] ] ) : '';
		},
		$content
	);
}

/**
 * Import one demo.
 *
 * @param string $slug Demo slug.
 * @return array{created: array<string, int>, skipped: string[], notices: string[]}|WP_Error
 */
function basalt_core_import_demo( string $slug ) {
	$demos = basalt_core_demos();

	if ( ! isset( $demos[ $slug ] ) ) {
		return new WP_Error( 'basalt_core_unknown_demo', __( 'That demo does not exist.', 'basalt-core' ) );
	}

	$demo    = $demos[ $slug ];
	$created = array();
	$skipped = array();
	$notices = array();

	// Images first: pages refer to them.
	$images = array();
	$drawn  = 0;

	foreach ( (array) ( $demo['images'] ?? array() ) as $key => $spec ) {
		$image = basalt_core_demo_image( (array) $spec );

		if ( ! $image['id'] ) {
			$notices[] = __( 'Images could not be drawn, so the demo was imported without them. The GD extension is what draws them.', 'basalt-core' );
			break;
		}

		$images[ $key ] = $image['id'];

		if ( $image['created'] ) {
			++$drawn;
		}
	}

	// Counted, not measured: an image that was already there is not a new one.
	if ( $drawn ) {
		$created['images'] = $drawn;
	}

	// Terms before posts, so a post can be put in one.
	foreach ( (array) ( $demo['terms'] ?? array() ) as $term ) {
		$taxonomy = (string) ( $term['taxonomy'] ?? '' );

		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) || term_exists( (string) $term['slug'], $taxonomy ) ) {
			continue;
		}

		wp_insert_term( (string) $term['name'], $taxonomy, array( 'slug' => (string) $term['slug'] ) );
	}

	/*
	 * Two passes over the posts. The first creates them so that every slug has
	 * an ID, the second fills in the content, because a page that links to
	 * another page cannot know its address until that page exists.
	 */
	$pages = array();

	foreach ( (array) ( $demo['posts'] ?? array() ) as $post ) {
		$post_slug = (string) ( $post['slug'] ?? '' );
		$type      = (string) ( $post['type'] ?? 'page' );

		if ( ! $post_slug || ! post_type_exists( $type ) ) {
			continue;
		}

		/*
		 * Deliberately not get_page_by_path(). Its query matches the requested
		 * post type or an attachment, which is reasonable for resolving a URL
		 * and wrong for asking whether a post exists: a demo image titled
		 * "Hoist GL 200" gets the slug hoist-gl-200, and the catalog entry of
		 * the same name was then skipped as already present. Attachment slugs
		 * do not block post slugs, so there was nothing in the way; the check
		 * was simply answering a different question.
		 */
		$existing = get_posts(
			array(
				'name'                   => $post_slug,
				'post_type'              => $type,
				'post_status'            => 'any',
				'numberposts'            => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $existing ) {
			$skipped[]           = $post_slug;
			$pages[ $post_slug ] = (int) $existing[0];
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => $type,
				'post_status'  => 'publish',
				'post_title'   => (string) ( $post['title'] ?? '' ),
				'post_name'    => $post_slug,
				'post_excerpt' => (string) ( $post['excerpt'] ?? '' ),
				'menu_order'   => (int) ( $post['order'] ?? 0 ),
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			continue;
		}

		$pages[ $post_slug ] = (int) $id;
	}

	foreach ( (array) ( $demo['posts'] ?? array() ) as $post ) {
		$post_slug = (string) ( $post['slug'] ?? '' );

		if ( ! isset( $pages[ $post_slug ] ) || in_array( $post_slug, $skipped, true ) ) {
			continue;
		}

		$id = $pages[ $post_slug ];

		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => basalt_core_demo_tokens( (string) ( $post['content'] ?? '' ), $pages, $images ),
			)
		);

		if ( ! empty( $post['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', (string) $post['template'] );
		}

		if ( ! empty( $post['image'] ) && isset( $images[ $post['image'] ] ) ) {
			set_post_thumbnail( $id, $images[ $post['image'] ] );
		}

		foreach ( (array) ( $post['terms'] ?? array() ) as $taxonomy => $terms ) {
			if ( taxonomy_exists( (string) $taxonomy ) ) {
				wp_set_object_terms( $id, (array) $terms, (string) $taxonomy );
			}
		}

		foreach ( (array) ( $post['meta'] ?? array() ) as $meta_key => $meta_value ) {
			update_post_meta( $id, (string) $meta_key, $meta_value );
		}

		$created[ $post['type'] ?? 'page' ] = ( $created[ $post['type'] ?? 'page' ] ?? 0 ) + 1;
	}

	// A navigation, if the demo brings one and the site has none yet.
	if ( ! empty( $demo['menu']['items'] ) && ! get_posts( array( 'post_type' => 'wp_navigation', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
		$items = '';

		foreach ( (array) $demo['menu']['items'] as $item ) {
			$target = (string) ( $item['page'] ?? '' );
			$url    = isset( $pages[ $target ] ) ? get_permalink( $pages[ $target ] ) : home_url( '/' );

			$items .= sprintf(
				'<!-- wp:navigation-link {"label":"%1$s","url":"%2$s","kind":"custom","isTopLevelItem":true} /-->',
				esc_attr( (string) ( $item['label'] ?? '' ) ),
				esc_url( (string) $url )
			);
		}

		wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => (string) ( $demo['menu']['title'] ?? __( 'Main', 'basalt-core' ) ),
				'post_content' => $items,
			)
		);

		$created['menu'] = 1;
	}

	// The front page and the posts page, if the demo named them.
	if ( ! empty( $demo['front_page'] ) && isset( $pages[ $demo['front_page'] ] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages[ $demo['front_page'] ] );
	}

	if ( ! empty( $demo['posts_page'] ) && isset( $pages[ $demo['posts_page'] ] ) ) {
		update_option( 'page_for_posts', $pages[ $demo['posts_page'] ] );
	}

	foreach ( (array) ( $demo['options'] ?? array() ) as $option => $value ) {
		update_option( (string) $option, $value );
	}

	/*
	 * The plugin settings are merged rather than replaced. A site that has
	 * already filled in its address should not lose it to a demo, and the demo
	 * only means to fill in what is still empty.
	 */
	if ( ! empty( $demo['settings'] ) ) {
		$settings = basalt_core_sanitize( array_merge( basalt_core_defaults(), (array) $demo['settings'], array_filter( (array) get_option( 'basalt_core_settings', array() ) ) ) );

		update_option( 'basalt_core_settings', $settings );
	}

	if ( ! empty( $demo['logo'] ) && isset( $images[ $demo['logo'] ] ) ) {
		update_option( 'site_logo', $images[ $demo['logo'] ] );
	}

	$imported          = basalt_core_imported_demos();
	$imported[ $slug ] = array(
		'time'    => time(),
		'created' => $created,
	);

	update_option( BASALT_CORE_DEMO_OPTION, $imported );

	return array(
		'created' => $created,
		'skipped' => $skipped,
		'notices' => array_unique( $notices ),
	);
}

/**
 * Register the screen.
 *
 * @return void
 */
function basalt_core_demo_page(): void {
	add_management_page(
		__( 'Basalt demo content', 'basalt-core' ),
		__( 'Basalt demos', 'basalt-core' ),
		'import',
		'basalt-core-demos',
		'basalt_core_render_demo_page'
	);
}
add_action( 'admin_menu', 'basalt_core_demo_page' );

/**
 * Handle the import request.
 *
 * @return void
 */
function basalt_core_handle_demo_import(): void {
	if ( ! isset( $_POST['basalt_core_demo'] ) ) {
		return;
	}

	/*
	 * The import capability rather than manage_options. Creating content is
	 * what this does, and that is the capability WordPress uses for it.
	 */
	if ( ! current_user_can( 'import' ) ) {
		wp_die( esc_html__( 'You are not allowed to import content.', 'basalt-core' ), '', array( 'response' => 403 ) );
	}

	$slug = sanitize_key( wp_unslash( $_POST['basalt_core_demo'] ) );

	check_admin_referer( 'basalt-core-import-' . $slug );

	$result = basalt_core_import_demo( $slug );
	$args   = array( 'page' => 'basalt-core-demos' );

	if ( is_wp_error( $result ) ) {
		$args['basalt-error'] = 1;
	} else {
		$args['basalt-imported'] = $slug;
	}

	wp_safe_redirect( add_query_arg( $args, admin_url( 'tools.php' ) ) );
	exit;
}
add_action( 'admin_init', 'basalt_core_handle_demo_import' );

/**
 * Render the screen.
 *
 * @return void
 */
function basalt_core_render_demo_page(): void {
	if ( ! current_user_can( 'import' ) ) {
		return;
	}

	$demos    = basalt_core_demos();
	$imported = basalt_core_imported_demos();
	$done     = isset( $_GET['basalt-imported'] ) ? sanitize_key( wp_unslash( $_GET['basalt-imported'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Basalt demo content', 'basalt-core' ); ?></h1>

		<?php if ( $done && isset( $demos[ $done ] ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: %s: demo name. */
						esc_html__( '%s was imported. Nothing that was already here was changed.', 'basalt-core' ),
						esc_html( (string) $demos[ $done ]['title'] )
					);
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Look at the site', 'basalt-core' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'A demo fills an empty site with pages, posts and pictures so that you can see how the pieces fit together, and then edit them into your own. The pages are built from the theme patterns, so what you get is what is in the inserter.', 'basalt-core' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Nothing is ever deleted.', 'basalt-core' ); ?></strong>
			<?php esc_html_e( 'Importing only creates. A page whose address is already taken is left alone and reported, so a demo cannot overwrite work you have already done.', 'basalt-core' ); ?>
		</p>

		<?php if ( ! $demos ) : ?>
			<p><em><?php esc_html_e( 'No demos are installed.', 'basalt-core' ); ?></em></p>
		<?php endif; ?>

		<div class="basalt-demos">
		<?php foreach ( $demos as $slug => $demo ) : ?>
			<div class="card" style="max-width:40rem;">
				<h2><?php echo esc_html( (string) $demo['title'] ); ?></h2>
				<p><?php echo esc_html( (string) ( $demo['description'] ?? '' ) ); ?></p>

				<?php if ( isset( $imported[ $slug ] ) ) : ?>
					<p>
						<em>
							<?php
							printf(
								/* translators: %s: date the demo was imported. */
								esc_html__( 'Imported on %s. Importing it again would create a second copy of anything you have since renamed, so it is not offered twice.', 'basalt-core' ),
								esc_html( wp_date( (string) get_option( 'date_format' ), (int) $imported[ $slug ]['time'] ) )
							);
							?>
						</em>
					</p>
				<?php else : ?>
					<form method="post">
						<?php wp_nonce_field( 'basalt-core-import-' . $slug ); ?>
						<input type="hidden" name="basalt_core_demo" value="<?php echo esc_attr( $slug ); ?>">
						<p>
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Import this demo', 'basalt-core' ); ?>
							</button>
						</p>
					</form>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}
