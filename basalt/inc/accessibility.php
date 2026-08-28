<?php
/**
 * Accessibility helpers.
 *
 * Basalt targets WCAG 2.2 AA. The rules that belong in CSS (focus visibility,
 * target size, reduced motion, contrast) live in assets/css/base.css; this file
 * covers what has to happen in PHP.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip link, rendered as the first focusable element on the page.
 *
 * @return void
 */
function basalt_skip_link(): void {
	printf(
		'<a class="skip-link" href="#content">%s</a>',
		esc_html__( 'Skip to content', 'basalt' )
	);
}
add_action( 'wp_body_open', 'basalt_skip_link', 1 );

/**
 * Give the search form a unique label and id.
 *
 * A page can contain more than one search form, for example in the header and
 * in the 404 template. Duplicate ids break the label association, so each form
 * gets its own generated id.
 *
 * @param string $form Search form markup.
 * @return string
 */
function basalt_search_form( $form ) {
	static $instance = 0;

	++$instance;

	$id     = 'search-field-' . $instance;
	$action = esc_url( home_url( '/' ) );
	$value  = esc_attr( get_search_query() );

	return sprintf(
		'<form role="search" method="get" class="search-form" action="%1$s">
			<label class="search-form__label" for="%2$s">%3$s</label>
			<div class="search-form__row">
				<input type="search" id="%2$s" class="search-form__field" placeholder="%4$s" value="%5$s" name="s" autocomplete="off" />
				<button type="submit" class="search-form__submit button">%6$s</button>
			</div>
		</form>',
		$action,
		esc_attr( $id ),
		esc_html__( 'Search this site', 'basalt' ),
		esc_attr__( 'Search…', 'basalt' ),
		$value,
		esc_html__( 'Search', 'basalt' )
	);
}
add_filter( 'get_search_form', 'basalt_search_form' );

/**
 * Remove the title attribute WordPress puts on the archive widget links.
 *
 * A title attribute duplicated from the link text is announced twice by some
 * screen readers and is invisible to keyboard users.
 *
 * @param string $link_html Widget link markup.
 * @return string
 */
function basalt_strip_widget_title_attribute( $link_html ) {
	return (string) preg_replace( '/\stitle=("|\')(.*?)\1/', '', (string) $link_html );
}
add_filter( 'get_archives_link', 'basalt_strip_widget_title_attribute' );

/**
 * Give the "read more" link a description of what it leads to.
 *
 * @param string $link The more link markup.
 * @return string
 */
function basalt_more_link( $link ) {
	$screen_reader = sprintf(
		'<span class="screen-reader-text"> %s</span>',
		esc_html(
			sprintf(
				/* translators: %s: post title. */
				__( 'about %s', 'basalt' ),
				wp_strip_all_tags( get_the_title() )
			)
		)
	);

	return (string) str_replace( '</a>', $screen_reader . '</a>', (string) $link );
}
add_filter( 'the_content_more_link', 'basalt_more_link' );

/**
 * Make the comment reply link announce which comment it replies to.
 *
 * @param string $link Reply link markup.
 * @return string
 */
function basalt_comment_reply_link( $link ) {
	return (string) str_replace( 'class=\'comment-reply-link', 'class=\'comment-reply-link button button--ghost', (string) $link );
}
add_filter( 'comment_reply_link', 'basalt_comment_reply_link' );

/**
 * Set the document language direction hint used by the CSS logical properties.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function basalt_direction_body_class( $classes ) {
	$classes   = (array) $classes;
	$classes[] = is_rtl() ? 'is-rtl' : 'is-ltr';

	return $classes;
}
add_filter( 'body_class', 'basalt_direction_body_class' );
