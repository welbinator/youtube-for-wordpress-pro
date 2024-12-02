<?php

add_action('wp_ajax_yt_for_wp_pro_import_videos', function () {
    check_ajax_referer('yt-for-wp-import-videos', '_ajax_nonce');

    $limit = intval($_POST['limit'] ?? 0);
    $channel_id = get_option('yt_for_wp_channel_id');
    $api_key = get_option('yt_for_wp_api_key');

    if (!$channel_id || !$api_key) {
        wp_send_json_error(__('API Key or Channel ID not configured.', 'yt-for-wp-pro'));
    }

    $api_url = add_query_arg([
        'part' => 'snippet',
        'channelId' => $channel_id,
        'maxResults' => min($limit ?: 50, 50),
        'type' => 'video',
        'key' => $api_key,
    ], 'https://www.googleapis.com/youtube/v3/search');

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $videos = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($videos['items'])) {
        wp_send_json_error(__('No videos found.', 'yt-for-wp-pro'));
    }

    foreach ($videos['items'] as $video) {
        $video_id = $video['id']['videoId'] ?? null;
        $snippet = $video['snippet'] ?? null;

        if (!$video_id || !$snippet) {
            continue;
        }

        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($snippet['title']),
            'post_content' => sprintf(
                '<iframe src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe><p>%s</p>',
                esc_attr($video_id),
                esc_html($snippet['description'])
            ),
            'post_status'  => 'publish',
            'post_type'    => 'yt-4-wp-video',
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Set featured image (video thumbnail)
            $thumbnail_url = $snippet['thumbnails']['high']['url'];
            yt_for_wp_set_post_thumbnail_from_url($post_id, $thumbnail_url);

            // Add published date as custom field
            update_post_meta($post_id, '_yt_published_at', sanitize_text_field($snippet['publishedAt']));
        }
    }

    wp_send_json_success(__('Videos imported successfully.', 'yt-for-wp-pro'));
});
