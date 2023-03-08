<?php get_header(); ?>

<div class="search-result-page">
    <?php
    $s=get_search_query();
    $args = array(
                    's' =>$s
                );
        // The Query
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
            _e("<h2 style='font-weight:bold;color:#000'>Search Results for: ".get_query_var('s')."</h2>");
            ?> <ul> <?php
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                        ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail( $post->ID ) ): ?>
                                <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
                                <img src="<?php echo $image[0]; ?>" alt="the_title();">
                            <?php endif; ?>
                            <div class="search-result-info">
                                <?php the_title(); ?>
                            </div>
                        </a>
                    </li>
                    <?php
                }
            ?> </ul> <?php
        }else{
    ?>
            <h2 style='font-weight:bold;color:#000'>Nothing Found</h2>
            <div class="alert alert-info">
            <p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
            </div>
    <?php } ?>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>