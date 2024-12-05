<?php
/**
 * Template Name: Videos Page List View
 * Description: A custom page template to display posts from the 'yt-4-wp-video' CPT in a list view.
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
                <li class="video-item">
                    <a href="<?php the_permalink(); ?>">
                        <div class="video-thumbnail">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( plugins_url( '/assets/default-thumbnail.jpg', __FILE__ ) ); ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="video-content">
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

<style>
.videos-list-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.videos-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.video-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
    align-items: center;
}

.video-thumbnail img {
    max-width: 120px;
    height: auto;
    display: block;
}

.video-content h2 {
    font-size: 18px;
    margin: 0 0 5px;
}

.video-content p {
    margin: 0;
    color: #555;
    font-size: 14px;
}
</style>

<?php
get_footer();
