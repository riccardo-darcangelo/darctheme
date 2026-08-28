<?php
/**
 * 404.
 *
 * A dead end is a chance to keep the visit alive: search, the most recent
 * posts and the main pages, rather than an apology.
 *
 * @package Basalt
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">
	<main id="content" class="site-main site-main--narrow" tabindex="-1">

		<header class="page-header">
			<p class="page-header__eyebrow"><?php esc_html_e( 'Error 404', 'basalt' ); ?></p>
			<h1 class="page-header__title"><?php esc_html_e( 'This page does not exist', 'basalt' ); ?></h1>
			<p class="page-header__description">
				<?php esc_html_e( 'The address may have changed, or the page may have been removed. Try a search or one of the links below.', 'basalt' ); ?>
			</p>
		</header>

		<div class="error-404__search">
			<?php get_search_form(); ?>
		</div>

		<?php
		$basalt_recent = new WP_Query(
			array(
				'posts_per_page'      => 4,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
			)
		);

		if ( $basalt_recent->have_posts() ) :
			?>
			<section class="error-404__section">
				<h2 class="error-404__heading"><?php esc_html_e( 'Recent posts', 'basalt' ); ?></h2>
				<ul class="link-list">
					<?php
					while ( $basalt_recent->have_posts() ) :
						$basalt_recent->the_post();
						?>
						<li class="link-list__item">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
					<?php endwhile; ?>
				</ul>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>

		<?php
		$basalt_pages = get_pages(
			array(
				'number'      => 6,
				'sort_column' => 'menu_order, post_title',
				'parent'      => 0,
			)
		);

		if ( $basalt_pages ) :
			?>
			<section class="error-404__section">
				<h2 class="error-404__heading"><?php esc_html_e( 'Main pages', 'basalt' ); ?></h2>
				<ul class="link-list">
					<?php foreach ( $basalt_pages as $basalt_page ) : ?>
						<li class="link-list__item">
							<a href="<?php echo esc_url( (string) get_permalink( $basalt_page ) ); ?>">
								<?php echo esc_html( get_the_title( $basalt_page ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

	</main>
</div>

<?php
get_footer();
