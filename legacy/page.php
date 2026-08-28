<?php get_header(); ?>

<div class="page-container section transition-2 active-trans transition">
    <h1><?php the_title(); ?></h1>

    <div class="content">
        <div class="container">
            <?php if(have_posts()) : while(have_posts()) : the_post(); ?>
            <?php the_content() ?>
            <?php endwhile; else: endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>