<?php
/**
 * What gets indexed, and what does not.
 *
 * Four things an SEO plugin is usually installed for, without the plugin:
 *
 * - A switch on a single post or page to keep it out of search results, which
 *   also keeps it out of the sitemap and out of llms.txt.
 * - Attachment pages, which are a page per uploaded image with no content on
 *   it, sent to where the image actually belongs.
 * - Date and author archives, which on most small sites are the same posts
 *   listed a third time.
 * - The verification tags Search Console and Bing ask for, so nobody has to
 *   paste a snippet into a theme file that the next update overwrites.
 *
 * All of it stands down when a dedicated SEO plugin is active.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/** The per-post switch. */
const BASALT_CORE_NOINDEX_META = '_basalt_noindex';

/**
 * Register the meta so it travels with an export and is readable by the API.
 *
 * @return void
 */
function basalt_core_register_noindex_meta(): void {
	foreach ( get_post_types( array( 'public' => true ) ) as $type ) {
		register_post_meta(
			$type,
			BASALT_CORE_NOINDEX_META,
			array(
				'type'          => 'boolean',
				'single'        => true,
				'show_in_rest'  => true,
				'default'       => false,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}
}
add_action( 'init', 'basalt_core_register_noindex_meta', 20 );

/**
 * The box in the editor.
 *
 * A classic meta box on purpose: the block editor still renders those, and it
 * saves shipping a build step and a script for one checkbox.
 *
 * @return void
 */
function basalt_core_noindex_box(): void {
	if ( basalt_core_seo_plugin_handles( 'meta' ) ) {
		return;
	}

	foreach ( get_post_types( array( 'public' => true ) ) as $type ) {
		if ( 'attachment' === $type ) {
			continue;
		}

		add_meta_box(
			'basalt-core-noindex',
			__( 'Search engines', 'basalt-core' ),
			'basalt_core_render_noindex_box',
			$type,
			'side',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'basalt_core_noindex_box' );

/**
 * Render it.
 *
 * @param WP_Post $post The post being edited.
 * @return void
 */
function basalt_core_render_noindex_box( $post ): void {
	wp_nonce_field( 'basalt_core_noindex', 'basalt_core_noindex_nonce' );
	?>
	<p>
		<label>
			<input type="checkbox" name="basalt_core_noindex" value="1" <?php checked( (bool) get_post_meta( $post->ID, BASALT_CORE_NOINDEX_META, true ) ); ?> />
			<?php esc_html_e( 'Hide from search engines', 'basalt-core' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Adds a noindex tag and takes the page out of the sitemap and out of llms.txt. The page stays reachable for anybody with the link.', 'basalt-core' ); ?>
	</p>
	<?php
}

/**
 * Save it.
 *
 * @param int $post_id The post.
 * @return void
 */
function basalt_core_save_noindex( $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// The box is only on the classic form; a REST save carries the meta itself.
	if ( ! isset( $_POST['basalt_core_noindex_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( (string) $_POST['basalt_core_noindex_nonce'] ) ), 'basalt_core_noindex' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( empty( $_POST['basalt_core_noindex'] ) ) {
		delete_post_meta( $post_id, BASALT_CORE_NOINDEX_META );
	} else {
		update_post_meta( $post_id, BASALT_CORE_NOINDEX_META, 1 );
	}
}
add_action( 'save_post', 'basalt_core_save_noindex' );

/**
 * Apply the switch, and the archive settings, to the robots tag.
 *
 * Runs after basalt_core_robots so the preview directives it added are taken
 * off again on a page that is now noindex.
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function basalt_core_indexing_robots( $robots ) {
	if ( basalt_core_seo_plugin_handles( 'meta' ) ) {
		return $robots;
	}

	$robots  = (array) $robots;
	$noindex = false;

	if ( is_singular() && get_post_meta( (int) get_the_ID(), BASALT_CORE_NOINDEX_META, true ) ) {
		$noindex = true;
	}

	if ( basalt_core_get( 'noindex_date' ) && ( is_date() || is_time() ) ) {
		$noindex = true;
	}

	if ( basalt_core_get( 'noindex_author' ) && is_author() ) {
		$noindex = true;
	}

	if ( ! $noindex ) {
		return $robots;
	}

	$robots['noindex'] = true;
	$robots['follow']  = true;

	unset( $robots['index'], $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview'] );

	return $robots;
}
add_filter( 'wp_robots', 'basalt_core_indexing_robots', 25 );

/**
 * Keep hidden posts out of the sitemap.
 *
 * @param array<string, mixed> $args      Query arguments.
 * @param string               $post_type The post type.
 * @return array<string, mixed>
 */
function basalt_core_sitemap_skip_noindex( $args, $post_type = '' ) {
	if ( basalt_core_seo_plugin_handles( 'sitemap' ) ) {
		return $args;
	}

	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- the sitemap is generated rarely and cached by the host.
	$args['meta_query'] = array(
		'relation' => 'OR',
		array(
			'key'     => BASALT_CORE_NOINDEX_META,
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'   => BASALT_CORE_NOINDEX_META,
			'value' => '0',
		),
	);

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'basalt_core_sitemap_skip_noindex', 10, 2 );

/**
 * Send an attachment page where the image belongs.
 *
 * An attachment page is a page with a title, an image and nothing else. Every
 * upload makes one, they compete with the post that uses the image, and no
 * visitor ever wants to land on one.
 *
 * @return void
 */
function basalt_core_redirect_attachments(): void {
	if ( ! is_attachment() || ! basalt_core_get( 'redirect_attachments' ) ) {
		return;
	}

	$post = get_post();

	if ( ! $post ) {
		return;
	}

	$target = $post->post_parent ? get_permalink( $post->post_parent ) : wp_get_attachment_url( $post->ID );

	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'basalt_core_redirect_attachments', 5 );

/**
 * The verification tags.
 *
 * @return void
 */
function basalt_core_verification_tags(): void {
	if ( basalt_core_seo_plugin_handles( 'meta' ) || ! is_front_page() ) {
		return;
	}

	foreach ( array( 'google-site-verification' => 'verify_google', 'msvalidate.01' => 'verify_bing' ) as $name => $key ) {
		$value = trim( (string) basalt_core_get( $key ) );

		if ( '' === $value ) {
			continue;
		}

		printf( '<meta name="%1$s" content="%2$s" />' . "\n", esc_attr( $name ), esc_attr( $value ) );
	}
}
add_action( 'wp_head', 'basalt_core_verification_tags', 4 );
