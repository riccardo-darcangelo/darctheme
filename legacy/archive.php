<?php get_header(); ?>

<div class="page-container section transition-2 active-trans transition">
    <h1><?php the_title(); ?></h1>

    <div class="content">
        <div class="container">
            <?php if(have_posts()) : while(have_posts()) : the_post(); ?>
            <?php if(has_post_thumbnail()) : ?>
                <a href="<?php the_permalink(); ?>"><img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>"></a>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>"><h2><?php the_title(); ?></h2></a>
            <?php the_excerpt(); ?>
            <?php endwhile; else: endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>