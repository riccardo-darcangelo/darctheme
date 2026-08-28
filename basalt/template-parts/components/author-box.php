<?php
/**
 * The author box below a single post.
 *
 * A visible author with a real biography is one of the few E-E-A-T signals a
 * theme can supply directly, which is why it is on by default and why the
 * biography is not truncated.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_author_id  = (int) get_the_author_meta( 'ID' );
$basalt_biography  = (string) get_the_author_meta( 'description', $basalt_author_id );

if ( '' === trim( $basalt_biography ) ) {
	return;
}
?>

<aside class="author-box" aria-label="<?php esc_attr_e( 'About the author', 'basalt' ); ?>">
	<div class="author-box__avatar">
		<?php echo get_avatar( $basalt_author_id, 96, '', '', array( 'class' => 'author-box__image' ) ); ?>
	</div>

	<div class="author-box__body">
		<p class="author-box__eyebrow"><?php esc_html_e( 'Written by', 'basalt' ); ?></p>

		<h2 class="author-box__name">
			<a href="<?php echo esc_url( get_author_posts_url( $basalt_author_id ) ); ?>" rel="author">
				<?php echo esc_html( get_the_author_meta( 'display_name', $basalt_author_id ) ); ?>
			</a>
		</h2>

		<p class="author-box__bio"><?php echo esc_html( $basalt_biography ); ?></p>

		<?php
		$basalt_author_url = (string) get_the_author_meta( 'user_url', $basalt_author_id );

		if ( $basalt_author_url ) :
			?>
			<p class="author-box__link">
				<a href="<?php echo esc_url( $basalt_author_url ); ?>" rel="nofollow noopener">
					<?php echo esc_html( wp_parse_url( $basalt_author_url, PHP_URL_HOST ) ?: $basalt_author_url ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</aside>
