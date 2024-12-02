<?php
get_header(); 

// Get video meta
$video_url = get_post_meta(get_the_ID(), '_yt_video_url', true);
$published_at = get_post_meta(get_the_ID(), '_yt_published_at', true);
$playlist_terms = wp_get_post_terms(get_the_ID(), 'yt-4-wp-playlist');

// Get featured image
$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

// Format date
$published_date = date('F j, Y', strtotime($published_at));

// Get playlist information
$playlist_name = $playlist_terms ? $playlist_terms[0]->name : '';
$playlist_link = $playlist_terms ? get_term_link($playlist_terms[0]) : '#';

// Extract YouTube video ID
preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video_url, $matches);
$video_id = $matches[1] ?? '';

// Generate embed URL
$embed_url = $video_id ? "https://www.youtube.com/embed/$video_id" : '';
?>

<div class="container">
    <h1 class="video-title"><?php the_title(); ?></h1>
    
    <?php if ($embed_url): ?>
        <div class="video-container">
            <iframe 
                src="<?php echo esc_url($embed_url); ?>" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>
    <?php else: ?>
        <p class="video-error">Video not available.</p>
    <?php endif; ?>
    
    <div class="video-meta">
        <p class="published-date">Published on <?php echo esc_html($published_date); ?></p>
        <?php if ($playlist_name): ?>
            <a href="<?php echo esc_url($playlist_link); ?>" class="playlist-link"><?php echo esc_html($playlist_name); ?></a>
        <?php endif; ?>
    </div>
    
    <div class="video-description">
        <h2>Description</h2>
        <p><?php the_content(); ?></p>
    </div>
</div>

<?php get_footer(); ?>
