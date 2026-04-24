<?php
/**
 * AJAX handlers for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

// Add channel.
add_action(
	'wp_ajax_yt_for_wp_add_channel',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		$channel_id = isset( $_POST['channel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['channel_id'] ) ) : '';
		$result     = \YouTubeForWPPro\Channels\Channel_Manager::add( $channel_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Return the updated channel list.
		wp_send_json_success( \YouTubeForWPPro\Channels\Channel_Manager::get_all() );
	}
);

// Remove channel.
add_action(
	'wp_ajax_yt_for_wp_remove_channel',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		$channel_id = isset( $_POST['channel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['channel_id'] ) ) : '';
		\YouTubeForWPPro\Channels\Channel_Manager::remove( $channel_id );

		wp_send_json_success( \YouTubeForWPPro\Channels\Channel_Manager::get_all() );
	}
);

// Update channel name.
add_action(
	'wp_ajax_yt_for_wp_update_channel_name',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		$channel_id = isset( $_POST['channel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['channel_id'] ) ) : '';
		$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$result     = \YouTubeForWPPro\Channels\Channel_Manager::update_name( $channel_id, $name );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( \YouTubeForWPPro\Channels\Channel_Manager::get_all() );
	}
);

// Add post type.
add_action(
	'wp_ajax_yt_for_wp_add_post_type',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$result = \YouTubeForWPPro\VideoCPT\Video_Post_Type::create( $slug, $name );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( \YouTubeForWPPro\VideoCPT\Video_Post_Type::get_all() );
	}
);

// Update post type name.
add_action(
	'wp_ajax_yt_for_wp_update_post_type_name',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$result = \YouTubeForWPPro\VideoCPT\Video_Post_Type::update_name( $slug, $name );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( \YouTubeForWPPro\VideoCPT\Video_Post_Type::get_all() );
	}
);

// Import videos.
add_action(
	'wp_ajax_yt_for_wp_pro_import_videos',
	function () {
		check_ajax_referer( 'yt-for-wp-settings', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'yt-for-wp-pro' ) );
		}

		if ( ! \YouTubeForWPPro\VideoCPT\Video_Post_Type::is_created() ) {
			wp_send_json_error( __( 'Please create your video post type before importing videos.', 'yt-for-wp-pro' ) );
		}

		$limit          = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 0;
		$channel_id     = isset( $_POST['channel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['channel_id'] ) ) : '';
		$post_type_slug = isset( $_POST['post_type_slug'] ) ? sanitize_key( wp_unslash( $_POST['post_type_slug'] ) ) : '';

		// Validate channel exists.
		$channels      = \YouTubeForWPPro\Channels\Channel_Manager::get_all();
		$channel_found = false;
		foreach ( $channels as $ch ) {
			if ( $ch['id'] === $channel_id ) {
				$channel_found = true;
				break;
			}
		}
		if ( ! $channel_found ) {
			wp_send_json_error( __( 'Selected channel not found.', 'yt-for-wp-pro' ) );
		}

		// Validate post type exists.
		$post_types = \YouTubeForWPPro\VideoCPT\Video_Post_Type::get_all();
		$pt_found   = false;
		foreach ( $post_types as $pt ) {
			if ( $pt['slug'] === $post_type_slug ) {
				$pt_found = true;
				break;
			}
		}
		if ( ! $pt_found ) {
			wp_send_json_error( __( 'Selected post type not found.', 'yt-for-wp-pro' ) );
		}

		$api_key = function_exists( 'YouTubeForWP\Admin\Settings\get_api_key' )
			? \YouTubeForWP\Admin\Settings\get_api_key()
			: get_option( 'yt_for_wp_api_key', '' );

		if ( ! $api_key ) {
			wp_send_json_error( __( 'API Key not configured.', 'yt-for-wp-pro' ) );
		}

		// Fetch playlists for the channel.
		$playlists_url = add_query_arg(
			array(
				'part'       => 'snippet',
				'channelId'  => $channel_id,
				'maxResults' => 50,
				'key'        => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/playlists'
		);

		$playlists_response = wp_remote_get( $playlists_url );

		if ( is_wp_error( $playlists_response ) ) {
			wp_send_json_error( $playlists_response->get_error_message() );
		}

		$playlists = json_decode( wp_remote_retrieve_body( $playlists_response ), true );

		if ( empty( $playlists['items'] ) ) {
			wp_send_json_error( __( 'No playlists found.', 'yt-for-wp-pro' ) );
		}

		// Map playlists to taxonomy terms.
		$taxonomy_slug = $post_type_slug . '-playlist';
		$playlist_map  = array();
		foreach ( $playlists['items'] as $playlist ) {
			$playlist_name = sanitize_text_field( $playlist['snippet']['title'] );
			$playlist_id   = sanitize_text_field( $playlist['id'] );

			$term = term_exists( $playlist_name, $taxonomy_slug );
			if ( ! $term ) {
				$term = wp_insert_term( $playlist_name, $taxonomy_slug );
			}

			if ( ! is_wp_error( $term ) ) {
				$playlist_map[ $playlist_id ] = $term['term_id'];
			}
		}

		// Fetch videos for the channel.
		$videos_url = add_query_arg(
			array(
				'part'       => 'snippet',
				'channelId'  => $channel_id,
				'maxResults' => min( $limit ? $limit : 50, 50 ),
				'type'       => 'video',
				'order'      => 'date',
				'key'        => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/search'
		);

		$videos_response = wp_remote_get( $videos_url );

		if ( is_wp_error( $videos_response ) ) {
			wp_send_json_error( $videos_response->get_error_message() );
		}

		$videos = json_decode( wp_remote_retrieve_body( $videos_response ), true );

		if ( empty( $videos['items'] ) ) {
			wp_send_json_error( __( 'No videos found.', 'yt-for-wp-pro' ) );
		}

		// Get full details for each video.
		$video_ids          = array_map(
			function ( $video ) {
				return $video['id']['videoId'];
			},
			$videos['items']
		);
		$videos_details_url = add_query_arg(
			array(
				'part' => 'snippet',
				'id'   => implode( ',', $video_ids ),
				'key'  => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/videos'
		);

		$details_response = wp_remote_get( $videos_details_url );

		if ( is_wp_error( $details_response ) ) {
			wp_send_json_error( $details_response->get_error_message() );
		}

		$details = json_decode( wp_remote_retrieve_body( $details_response ), true );

		if ( empty( $details['items'] ) ) {
			wp_send_json_error( __( 'No video details found.', 'yt-for-wp-pro' ) );
		}

		foreach ( $details['items'] as $video ) {
			$video_id = $video['id'];
			$snippet  = $video['snippet'] ?? null;

			if ( ! $video_id || ! $snippet ) {
				continue;
			}

			$video_url = sprintf( 'https://www.youtube.com/embed/%s', $video_id );

			// Skip if this video already exists.
			$existing = new \WP_Query(
				array(
					'post_type'      => $post_type_slug,
					'post_status'    => 'any',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for video lookup.
					'meta_query'     => array(
						array(
							'key'     => '_yt_video_id',
							'value'   => $video_id,
							'compare' => '=',
						),
					),
					'fields'         => 'ids',
					'posts_per_page' => 1,
				)
			);

			if ( $existing->have_posts() ) {
				continue; // Skip duplicate video.
			}
			wp_reset_postdata();

			// Insert the video post.
			$post_id = wp_insert_post(
				array(
					'post_title'   => sanitize_text_field( $snippet['title'] ),
					'post_content' => wp_kses_post( $snippet['description'] ),
					'post_status'  => 'publish',
					'post_type'    => $post_type_slug,
					'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $snippet['publishedAt'] ) ),
				)
			);

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				// Set featured image (video thumbnail).
				$thumbnail_url = $snippet['thumbnails']['high']['url'];
				if ( function_exists( 'yt_for_wp_set_post_thumbnail_from_url' ) ) {
					yt_for_wp_set_post_thumbnail_from_url( $post_id, $thumbnail_url );
				}

				// Add published date as custom field.
				update_post_meta( $post_id, '_yt_published_at', sanitize_text_field( $snippet['publishedAt'] ) );

				// Add video ID as custom field.
				update_post_meta( $post_id, '_yt_video_id', $video_id );

				// Add video URL as custom field.
				update_post_meta( $post_id, '_yt_video_url', esc_url_raw( $video_url ) );

				// Check for playlist association.
				foreach ( $playlist_map as $playlist_id => $term_id ) {
					$playlist_items_url = add_query_arg(
						array(
							'part'       => 'snippet',
							'playlistId' => $playlist_id,
							'maxResults' => 50,
							'key'        => $api_key,
						),
						'https://www.googleapis.com/youtube/v3/playlistItems'
					);

					$playlist_items_response = wp_remote_get( $playlist_items_url );

					if ( is_wp_error( $playlist_items_response ) ) {
						continue;
					}

					$playlist_items = json_decode( wp_remote_retrieve_body( $playlist_items_response ), true );
					if ( empty( $playlist_items['items'] ) ) {
						continue;
					}

					foreach ( $playlist_items['items'] as $item ) {
						if ( $item['snippet']['resourceId']['videoId'] === $video_id ) {
							wp_set_post_terms( $post_id, array( $term_id ), $taxonomy_slug, true );
							break;
						}
					}
				}
			}
		}

		wp_send_json_success( __( 'Videos imported successfully.', 'yt-for-wp-pro' ) );
	}
);
