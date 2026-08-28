<?php
/**
 * The page head and the site header.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrap">

	<header
		class="site-header site-header--<?php echo esc_attr( (string) basalt_get_option( 'header_layout' ) ); ?><?php echo basalt_get_option( 'header_sticky' ) ? ' site-header--sticky' : ''; ?>"
		role="banner"
	>
		<div class="site-header__inner container">

			<?php basalt_site_branding(); ?>

			<?php if ( basalt_has_primary_navigation() ) : ?>
				<button
					type="button"
					class="nav-toggle"
					aria-expanded="false"
					aria-controls="primary-navigation"
				>
					<span class="nav-toggle__icon" aria-hidden="true"></span>
					<span class="nav-toggle__label"><?php esc_html_e( 'Menu', 'basalt' ); ?></span>
				</button>

				<nav
					id="primary-navigation"
					class="primary-nav"
					aria-label="<?php esc_attr_e( 'Primary', 'basalt' ); ?>"
				>
					<?php basalt_nav_menu( 'primary' ); ?>

					<?php if ( basalt_get_option( 'header_show_search' ) ) : ?>
						<div class="primary-nav__search">
							<?php get_search_form(); ?>
						</div>
					<?php endif; ?>
				</nav>
			<?php endif; ?>

		</div>
	</header>

	<?php
	/**
	 * Fires directly after the site header, before the breadcrumb.
	 *
	 * The place to inject a notification bar or a hero that has to sit outside
	 * the main landmark.
	 */
	do_action( 'basalt_after_header' );
	?>

	<?php
	/**
	 * Filter whether the breadcrumb bar is rendered.
	 *
	 * Templates that open with a full bleed hero switch this off, because a bar
	 * above the hero pushes the largest element below the fold and costs LCP.
	 *
	 * @param bool $show Whether to render the breadcrumb bar.
	 */
	if ( apply_filters( 'basalt_show_breadcrumbs', ! is_front_page() ) ) :
		?>
		<div class="breadcrumbs-bar">
			<div class="container">
				<?php basalt_breadcrumbs(); ?>
			</div>
		</div>
	<?php endif; ?>
