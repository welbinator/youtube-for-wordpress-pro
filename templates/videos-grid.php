<?php
/**
 * Template Name: Videos Page Grid View
 * Description: A custom page template to display posts from the 'yt-4-wp-video' CPT in a grid view.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get posts from the 'yt-4-wp-video' custom post type.
$args = array(
    'post_type'      => 'yt-4-wp-video',
    'posts_per_page' => 12, // Adjust this to show more/less posts.
    'post_status'    => 'publish',
);

$videos = new WP_Query( $args );

get_header(); ?>

<div class="videos-grid-container">
    <h1><?php the_title(); ?></h1>
    <div class="videos-grid">
        <?php if ( $videos->have_posts() ) : ?>
            <?php while ( $videos->have_posts() ) : $videos->the_post(); ?>
                <div class="video-item">
                    <a href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium' ); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url( plugins_url( '/assets/default-thumbnail.jpg', __FILE__ ) ); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <h2><?php the_title(); ?></h2>
                    </a>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'No videos found.', 'yt-4-wp' ); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.videos-grid-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
.videos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.video-item {
    border: 1px solid #ddd;
    padding: 15px;
    text-align: center;
}
.video-item img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto 10px;
}
.video-item h2 {
    font-size: 16px;
    margin: 0;
}
</style>

<?php
get_footer();
