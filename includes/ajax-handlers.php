<?php
add_action('wp_ajax_yt_for_wp_pro_import_videos', function () {
    check_ajax_referer('yt-for-wp-import-videos', '_ajax_nonce');

    $limit = intval($_POST['limit'] ?? 0);
    $channel_id = get_option('yt_for_wp_channel_id');
    $api_key = get_option('yt_for_wp_api_key');

    if (!$channel_id || !$api_key) {
        wp_send_json_error(__('API Key or Channel ID not configured.', 'yt-for-wp-pro'));
    }

    // Fetch playlists for the channel
    $playlists_url = add_query_arg([
        'part'       => 'snippet',
        'channelId'  => $channel_id,
        'maxResults' => 50,
        'key'        => $api_key,
    ], 'https://www.googleapis.com/youtube/v3/playlists');

    $playlists_response = wp_remote_get($playlists_url);

    if (is_wp_error($playlists_response)) {
        wp_send_json_error($playlists_response->get_error_message());
    }

    $playlists = json_decode(wp_remote_retrieve_body($playlists_response), true);

    if (empty($playlists['items'])) {
        wp_send_json_error(__('No playlists found.', 'yt-for-wp-pro'));
    }

    // Map playlists to taxonomy terms
    $playlist_map = [];
    foreach ($playlists['items'] as $playlist) {
        $playlist_name = sanitize_text_field($playlist['snippet']['title']);
        $playlist_id = sanitize_text_field($playlist['id']);

        $term = term_exists($playlist_name, 'yt-4-wp-playlist');
        if (!$term) {
            $term = wp_insert_term($playlist_name, 'yt-4-wp-playlist');
        }

        if (!is_wp_error($term)) {
            $playlist_map[$playlist_id] = $term['term_id'];
        }
    }

    // Fetch videos for the channel
    $videos_url = add_query_arg([
        'part'       => 'snippet',
        'channelId'  => $channel_id,
        'maxResults' => min($limit ?: 50, 50),
        'type'       => 'video',
        'order'      => 'date',
        'key'        => $api_key,
    ], 'https://www.googleapis.com/youtube/v3/search');

    $videos_response = wp_remote_get($videos_url);

    if (is_wp_error($videos_response)) {
        wp_send_json_error($videos_response->get_error_message());
    }

    $videos = json_decode(wp_remote_retrieve_body($videos_response), true);

    if (empty($videos['items'])) {
        wp_send_json_error(__('No videos found.', 'yt-for-wp-pro'));
    }

    // Get full details for each video
    $video_ids = array_map(fn($video) => $video['id']['videoId'], $videos['items']);
    $videos_details_url = add_query_arg([
        'part' => 'snippet',
        'id'   => implode(',', $video_ids),
        'key'  => $api_key,
    ], 'https://www.googleapis.com/youtube/v3/videos');

    $details_response = wp_remote_get($videos_details_url);

    if (is_wp_error($details_response)) {
        wp_send_json_error($details_response->get_error_message());
    }

    $details = json_decode(wp_remote_retrieve_body($details_response), true);

    if (empty($details['items'])) {
        wp_send_json_error(__('No video details found.', 'yt-for-wp-pro'));
    }

    foreach ($details['items'] as $video) {
        $video_id = $video['id'];
        $snippet = $video['snippet'] ?? null;

        if (!$video_id || !$snippet) {
            continue;
        }

        $video_url = sprintf('https://www.youtube.com/embed/%s', $video_id);


        // Insert the video post
        $post_id = wp_insert_post([
            'post_title'   => sanitize_text_field($snippet['title']),
            'post_content' => esc_html($snippet['description']),
            'post_status'  => 'publish',
            'post_type'    => 'yt-4-wp-video',
            'post_date'    => gmdate('Y-m-d H:i:s', strtotime($snippet['publishedAt'])),
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Set featured image (video thumbnail)
            $thumbnail_url = $snippet['thumbnails']['high']['url'];
            yt_for_wp_set_post_thumbnail_from_url($post_id, $thumbnail_url);

            // Add published date as custom field
            update_post_meta($post_id, '_yt_published_at', sanitize_text_field($snippet['publishedAt']));

            // Add video ID as custom field
            update_post_meta($post_id, '_yt_video_id', $video_id);

            // Add video URL as custom field
            update_post_meta($post_id, '_yt_video_url', esc_url_raw($video_url));

            // Check for playlist association
            foreach ($playlist_map as $playlist_id => $term_id) {
                $playlist_items_url = add_query_arg([
                    'part'       => 'snippet',
                    'playlistId' => $playlist_id,
                    'maxResults' => 50,
                    'key'        => $api_key,
                ], 'https://www.googleapis.com/youtube/v3/playlistItems');

                $playlist_items_response = wp_remote_get($playlist_items_url);

                if (is_wp_error($playlist_items_response)) {
                    continue;
                }

                $playlist_items = json_decode(wp_remote_retrieve_body($playlist_items_response), true);
                if (empty($playlist_items['items'])) {
                    continue;
                }

                foreach ($playlist_items['items'] as $item) {
                    if ($item['snippet']['resourceId']['videoId'] === $video_id) {
                        wp_set_post_terms($post_id, [$term_id], 'yt-4-wp-playlist', true);
                        break;
                    }
                }
            }
        }
    }

    wp_send_json_success(__('Videos imported successfully.', 'yt-for-wp-pro'));
});
