<?php
/**
 * The heading block above an archive or blog listing.
 *
 * Emits exactly one H1 per view. On the posts page that is the page title, on
 * a term archive the term name, and the term description sits below it as the
 * introduction search engines read.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_title       = '';
$basalt_eyebrow     = '';
$basalt_description = '';

if ( is_home() && ! is_front_page() ) {
	$basalt_blog_page = (int) get_option( 'page_for_posts' );
	$basalt_title     = $basalt_blog_page ? get_the_title( $basalt_blog_page ) : __( 'Blog', 'basalt' );

	if ( $basalt_blog_page ) {
		$basalt_description = (string) get_post_field( 'post_excerpt', $basalt_blog_page );
	}
} elseif ( is_archive() ) {
	$basalt_title       = get_the_archive_title();
	$basalt_description = (string) get_the_archive_description();

	if ( is_category() ) {
		$basalt_eyebrow = __( 'Category', 'basalt' );
	} elseif ( is_tag() ) {
		$basalt_eyebrow = __( 'Tag', 'basalt' );
	} elseif ( is_author() ) {
		$basalt_eyebrow = __( 'Author', 'basalt' );
	} elseif ( is_tax() ) {
		$basalt_term = get_queried_object();

		if ( $basalt_term instanceof WP_Term ) {
			$basalt_taxonomy = get_taxonomy( $basalt_term->taxonomy );

			if ( $basalt_taxonomy ) {
				$basalt_eyebrow = $basalt_taxonomy->labels->singular_name;
			}
		}
	}
}

if ( '' === $basalt_title ) {
	return;
}
?>

<header class="page-header">
	<?php if ( $basalt_eyebrow ) : ?>
		<p class="page-header__eyebrow"><?php echo esc_html( $basalt_eyebrow ); ?></p>
	<?php endif; ?>

	<h1 class="page-header__title"><?php echo esc_html( $basalt_title ); ?></h1>

	<?php if ( $basalt_description ) : ?>
		<div class="page-header__description"><?php echo wp_kses_post( wpautop( $basalt_description ) ); ?></div>
	<?php endif; ?>
</header>
