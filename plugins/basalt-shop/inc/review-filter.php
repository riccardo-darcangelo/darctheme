<?php
/**
 * Reviews, filtered by the size the reviewer wore.
 *
 * On a shop where size is the whole question, the useful review is not the
 * most recent one. It is the one written by somebody the same size. Ten
 * reviews saying "lovely fabric" are worth less than the one that says "I wear
 * 75C and took the 80B".
 *
 * The filter is a row of links, not a script. Each one is the same page with a
 * query argument on it, so the filtered view has its own address, can be
 * shared, works with the back button, and works with no JavaScript at all. The
 * filtering itself happens in the comment query, which means it applies to the
 * classic review template and to the review block equally, and the pagination
 * that WooCommerce prints keeps working because it is the same query.
 *
 * @package BasaltShop
 */

defined( 'ABSPATH' ) || exit;

/** The query argument for a size. */
const BASALT_SHOP_SIZE_ARG = 'review-size';

/** The query argument for how the size came out. */
const BASALT_SHOP_FIT_ARG = 'review-fit';

/**
 * The size being filtered for, if any.
 *
 * @return string
 */
function basalt_shop_review_size_filter(): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a filter in a link, not an action.
	$value = isset( $_GET[ BASALT_SHOP_SIZE_ARG ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ BASALT_SHOP_SIZE_ARG ] ) ) : '';

	return mb_substr( $value, 0, 20 );
}

/**
 * The fit being filtered for, if any.
 *
 * @return string
 */
function basalt_shop_review_fit_filter(): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a filter in a link, not an action.
	$value = isset( $_GET[ BASALT_SHOP_FIT_ARG ] ) ? sanitize_key( wp_unslash( (string) $_GET[ BASALT_SHOP_FIT_ARG ] ) ) : '';

	return array_key_exists( $value, basalt_shop_fit_scale() ) ? $value : '';
}

/**
 * Apply the filter to the review query.
 *
 * Narrow on purpose: only on a product page, only for the query that is asking
 * for this product's comments. A count query or a query somewhere else in the
 * page is left alone, otherwise the number next to the tab stops matching what
 * the tab contains.
 *
 * @param WP_Comment_Query $query The query.
 * @return void
 */
function basalt_shop_filter_reviews( $query ): void {
	if ( ! basalt_shop_active() || ! is_product() || is_admin() ) {
		return;
	}

	$size = basalt_shop_review_size_filter();
	$fit  = basalt_shop_review_fit_filter();

	if ( '' === $size && '' === $fit ) {
		return;
	}

	$post_id = (int) get_the_ID();
	$asked   = (int) ( $query->query_vars['post_id'] ?? 0 );

	if ( $asked !== $post_id || ! empty( $query->query_vars['count'] ) ) {
		return;
	}

	$meta = (array) ( $query->query_vars['meta_query'] ?? array() );

	if ( '' !== $size ) {
		$meta[] = array(
			'key'   => BASALT_SHOP_FIT_META['worn'],
			'value' => $size,
		);
	}

	if ( '' !== $fit ) {
		$meta[] = array(
			'key'   => BASALT_SHOP_FIT_META['fit'],
			'value' => $fit,
		);
	}

	$query->query_vars['meta_query'] = $meta;
}
add_action( 'pre_get_comments', 'basalt_shop_filter_reviews' );

/**
 * The sizes people have said they wear, with how often.
 *
 * Read from the reviews rather than from the product's attributes, because
 * the question is which sizes have been written about, and an attribute that
 * nobody has reviewed has nothing to show.
 *
 * @param int $post_id The product.
 * @return array<string, int> Size to number of reviews, in reading order.
 */
function basalt_shop_reviewed_sizes( int $post_id ): array {
	$cached = get_transient( 'basalt_shop_sizes_' . $post_id );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$comments = get_comments(
		array(
			'post_id'    => $post_id,
			'status'     => 'approve',
			'type'       => 'review',
			'number'     => 300,
			'meta_key'   => BASALT_SHOP_FIT_META['worn'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- cached below, and a product has tens of reviews rather than thousands.
			'meta_compare' => 'EXISTS',
		)
	);

	$sizes = array();

	foreach ( $comments as $comment ) {
		$size = (string) get_comment_meta( (int) $comment->comment_ID, BASALT_SHOP_FIT_META['worn'], true );

		if ( '' === $size ) {
			continue;
		}

		$sizes[ $size ] = ( $sizes[ $size ] ?? 0 ) + 1;
	}

	uksort( $sizes, 'strnatcasecmp' );

	set_transient( 'basalt_shop_sizes_' . $post_id, $sizes, DAY_IN_SECONDS );

	return $sizes;
}

/**
 * Throw the size list away when a review is written or moderated.
 *
 * @param int|string $comment_id The comment, or its new status.
 * @param mixed      $second     The comment when the hook passes two.
 * @return void
 */
function basalt_shop_flush_sizes( $comment_id, $second = null ): void {
	$comment = $second instanceof WP_Comment ? $second : get_comment( is_numeric( $comment_id ) ? (int) $comment_id : 0 );

	if ( $comment instanceof WP_Comment ) {
		delete_transient( 'basalt_shop_sizes_' . (int) $comment->comment_post_ID );
	}
}
add_action( 'comment_post', 'basalt_shop_flush_sizes' );
add_action( 'edit_comment', 'basalt_shop_flush_sizes' );
add_action( 'deleted_comment', 'basalt_shop_flush_sizes' );
add_action( 'wp_set_comment_status', 'basalt_shop_flush_sizes' );

/**
 * How many approved reviews the product has in total.
 *
 * The total in the sentence has to be every review, not only the ones that
 * named a size, or "showing 2 of 8" quietly excludes the reviews that said
 * nothing about size and the two numbers stop adding up for the reader.
 *
 * @param int $post_id The product.
 * @return int
 */
function basalt_shop_review_total( int $post_id ): int {
	return (int) get_comments(
		array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'type'    => 'review',
			'count'   => true,
		)
	);
}

/**
 * One link in the row.
 *
 * @param string $label   What it says.
 * @param array  $args    Query arguments to set, null to remove.
 * @param bool   $current Whether this is the view being shown.
 * @param int    $count   How many reviews are behind it, zero to hide the number.
 * @return string
 */
function basalt_shop_filter_chip( string $label, array $args, bool $current, int $count = 0 ): string {
	$url = remove_query_arg( array( BASALT_SHOP_SIZE_ARG, BASALT_SHOP_FIT_ARG, 'cpage' ), (string) get_permalink() );
	$url = $args ? add_query_arg( $args, $url ) : $url;

	return sprintf(
		'<li><a class="basalt-review-filter__chip%1$s" href="%2$s#reviews"%3$s>%4$s%5$s</a></li>',
		$current ? ' is-current' : '',
		esc_url( $url ),
		$current ? ' aria-current="true"' : '',
		esc_html( $label ),
		$count ? sprintf( ' <span class="basalt-review-filter__count">%d</span>', $count ) : ''
	);
}

/**
 * Render the filter block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $content    Inner content.
 * @param WP_Block|null        $block      The block instance.
 * @return string
 */
function basalt_shop_review_filter_block( $attributes, $content = '', $block = null ): string {
	$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );

	if ( ! basalt_shop_active() || ! $post_id ) {
		return '';
	}

	$attributes = wp_parse_args(
		(array) $attributes,
		array(
			'showFit'   => true,
			'sizeLabel' => __( 'Size worn', 'basalt-shop' ),
			'fitLabel'  => __( 'How it came out', 'basalt-shop' ),
		)
	);

	$sizes = basalt_shop_reviewed_sizes( $post_id );

	// Nothing to choose between: one size, or nobody has said.
	if ( count( $sizes ) < 2 ) {
		return '';
	}

	$size    = basalt_shop_review_size_filter();
	$fit     = basalt_shop_review_fit_filter();
	$chips   = basalt_shop_filter_chip( __( 'All', 'basalt-shop' ), array(), '' === $size, 0 );
	$reviews = basalt_shop_review_total( $post_id );

	foreach ( $sizes as $value => $count ) {
		$chips .= basalt_shop_filter_chip(
			(string) $value,
			array( BASALT_SHOP_SIZE_ARG => $value ),
			(string) $value === $size,
			$count
		);
	}

	$out = sprintf(
		'<div class="basalt-review-filter__row"><span class="basalt-review-filter__label">%1$s</span><ul class="basalt-review-filter__chips">%2$s</ul></div>',
		esc_html( (string) $attributes['sizeLabel'] ),
		$chips // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above.
	);

	if ( $attributes['showFit'] ) {
		$fits = basalt_shop_filter_chip( __( 'All', 'basalt-shop' ), array(), '' === $fit, 0 );

		foreach ( basalt_shop_fit_scale() as $key => $label ) {
			$fits .= basalt_shop_filter_chip( (string) $label, array( BASALT_SHOP_FIT_ARG => $key ), $key === $fit, 0 );
		}

		$out .= sprintf(
			'<div class="basalt-review-filter__row"><span class="basalt-review-filter__label">%1$s</span><ul class="basalt-review-filter__chips">%2$s</ul></div>',
			esc_html( (string) $attributes['fitLabel'] ),
			$fits // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above.
		);
	}

	if ( '' !== $size || '' !== $fit ) {
		$shown = (int) get_comments(
			array(
				'post_id' => $post_id,
				'status'  => 'approve',
				'type'    => 'review',
				'count'   => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one count on a page that is already querying these comments.
				'meta_query' => array_values(
					array_filter(
						array(
							'' !== $size ? array( 'key' => BASALT_SHOP_FIT_META['worn'], 'value' => $size ) : null,
							'' !== $fit ? array( 'key' => BASALT_SHOP_FIT_META['fit'], 'value' => $fit ) : null,
						)
					)
				),
			)
		);

		$out .= sprintf(
			'<p class="basalt-review-filter__state">%1$s <a href="%2$s#reviews">%3$s</a></p>',
			esc_html(
				sprintf(
					/* translators: 1: number of reviews shown, 2: number of reviews in total */
					_n( 'Showing %1$d of %2$d review.', 'Showing %1$d of %2$d reviews.', $reviews, 'basalt-shop' ),
					$shown,
					$reviews
				)
			),
			esc_url( remove_query_arg( array( BASALT_SHOP_SIZE_ARG, BASALT_SHOP_FIT_ARG, 'cpage' ), (string) get_permalink() ) ),
			esc_html__( 'Show all', 'basalt-shop' )
		);
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes( array( 'class' => 'basalt-review-filter' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the function.
		$out // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above.
	);
}
