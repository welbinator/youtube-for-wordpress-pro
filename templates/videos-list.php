<?php
/**
 * Template Name: Videos Page List View
 * Description: A custom page template to display posts from the 'yt-4-wp-video' CPT in a list view with a modal for YouTube videos.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get posts from the 'yt-4-wp-video' custom post type.
$args = array(
    'post_type'      => 'yt-4-wp-video',
    'posts_per_page' => 10, // Adjust this to show more/less posts.
    'post_status'    => 'publish',
);

$videos = new WP_Query( $args );

get_header(); ?>

<div class="videos-list-container">
    <h1><?php the_title(); ?></h1>
    <?php if ( $videos->have_posts() ) : ?>
        <ul class="videos-list">
            <?php while ( $videos->have_posts() ) : $videos->the_post(); ?>
                <?php $video_id = get_post_meta( get_the_ID(), '_yt_video_id', true ); ?>
                <li class="video-list-item">
                    <a href="#" class="video-list-thumbnail" data-video-url="https://www.youtube.com/embed/<?php echo esc_attr( $video_id ); ?>">
                        <div class="video-list-thumbnail">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( plugins_url( '/assets/default-thumbnail.jpg', __FILE__ ) ); ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="video-list-content">
                            <h2><?php the_title(); ?></h2>
                            <p><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                        </div>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No videos found.', 'yt-for-wp-pro' ); ?></p>
    <?php endif; ?>
</div>

<!-- Modal Structure -->
<div id="video-modal" class="video-modal">
    <div class="video-modal-content">
        <span id="video-modal-close" class="video-modal-close">&times;</span>
        <iframe id="video-iframe" src="" frameborder="0" allow="autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
    </div>
</div>

<?php
get_footer();
