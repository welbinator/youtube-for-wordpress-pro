<?php
/**
 * Template Name: Videos Page Grid View
 * Description: A custom page template to display posts from the 'yt-4-wp-video' CPT in a grid view with a modal for YouTube videos.
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
                <?php $video_url = get_post_meta( get_the_ID(), '_yt_video_url', true ); ?>
                <div class="video-grid-item">
                    <a href="#" class="video-thumbnail" data-video-url="<?php echo esc_url( $video_url ); ?>">
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
            <p><?php esc_html_e( 'No videos found.', 'yt-for-wp-pro' ); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Structure -->
<div id="video-modal" class="video-modal">
    <div class="video-modal-content">
        <span id="video-modal-close" class="video-modal-close">&times;</span>
        <iframe id="video-iframe" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>


<?php
get_footer();
