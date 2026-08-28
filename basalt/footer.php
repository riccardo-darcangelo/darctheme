<?php
/**
 * The site footer and the closing document markup.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

$basalt_footer_widget_areas = array_values(
	array_filter(
		array( 'footer-1', 'footer-2', 'footer-3', 'footer-4' ),
		'is_active_sidebar'
	)
);
?>

	<?php
	/**
	 * Fires before the site footer.
	 */
	do_action( 'basalt_before_footer' );
	?>

	<footer class="site-footer" role="contentinfo">

		<?php if ( $basalt_footer_widget_areas ) : ?>
			<div class="site-footer__widgets">
				<div
					class="container site-footer__grid"
					style="--footer-columns: <?php echo esc_attr( (string) count( $basalt_footer_widget_areas ) ); ?>"
				>
					<?php foreach ( $basalt_footer_widget_areas as $basalt_area ) : ?>
						<div class="site-footer__column">
							<?php dynamic_sidebar( $basalt_area ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="site-footer__bar">
			<div class="container site-footer__bar-inner">
				<p class="site-footer__copyright">
					<?php echo wp_kses( basalt_get_copyright_text(), basalt_allowed_inline_html() ); ?>
				</p>

				<?php if ( basalt_get_option( 'footer_show_legal_menu' ) && has_nav_menu( 'legal' ) ) : ?>
					<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'basalt' ); ?>">
						<?php basalt_nav_menu( 'legal', array( 'depth' => 1 ) ); ?>
					</nav>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'social' ) ) : ?>
					<nav class="site-footer__social" aria-label="<?php esc_attr_e( 'Social links', 'basalt' ); ?>">
						<?php basalt_nav_menu( 'social', array( 'depth' => 1 ) ); ?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</footer>

	<button type="button" class="back-to-top" hidden>
		<span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'basalt' ); ?></span>
		<span class="back-to-top__icon" aria-hidden="true"></span>
	</button>

</div><!-- .site-wrap -->

<?php wp_footer(); ?>
</body>
</html>
