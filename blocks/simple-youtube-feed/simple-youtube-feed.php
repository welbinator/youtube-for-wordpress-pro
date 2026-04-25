<?php
/**
 * Simple YouTube Feed block registration and render callback.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWP\Blocks\SimpleYouTubeFeed;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using metadata from block.json.
 */
function register_simple_youtube_feed_block() {
	// Register the block.
	register_block_type_from_metadata(
		plugin_dir_path( __FILE__ ) . '../../build/simple-youtube-feed',
		array(
			'render_callback' => __NAMESPACE__ . '\\render_youtube_feed_block',
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\register_simple_youtube_feed_block' );

/**
 * Renders the block on the frontend.
 *
 * @param array  $attributes The block attributes.
 * @param string $content    The block content (unused).
 * @return string The block HTML.
 */
function render_youtube_feed_block( $attributes, $content ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by block render callback signature.
	// Get the API key and channel ID.
	$api_key            = \YouTubeForWP\Admin\Settings\get_api_key();
	$channels           = \YouTubeForWPPro\Channels\Channel_Manager::get_all();
	$default_channel_id = ! empty( $channels[0]['id'] ) ? $channels[0]['id'] : get_option( 'yt_for_wp_channel_id' );

	// Use block channel ID if set, otherwise use default.
	$channel_id = ! empty( $attributes['channelId'] ) ? $attributes['channelId'] : $default_channel_id;

	// Append custom class.
	$attributes['className'] = trim( ( $attributes['className'] ?? '' ) . ' youtube-feed-container' );

	// Localize the script with required data.
	wp_localize_script(
		'yt-for-wp-simple-youtube-feed-view',
		'YT_FOR_WP',
		array(
			'channelId' => $channel_id,
			'apiKey'    => $api_key,
			'restUrl'   => rest_url( 'yt-for-wp/v1/' ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'siteUrl'   => get_site_url(),
		)
	);

	$unique_id = uniqid( 'youtube-feed-' );

	// Start output buffering.
	ob_start();
	?>
	<div
		<?php echo esc_attr( get_block_wrapper_attributes() ); ?>
		id="<?php echo esc_attr( $unique_id ); ?>"
		data-layout="<?php echo esc_attr( $attributes['layout'] ?? 'grid' ); ?>"
		data-max-videos="<?php echo esc_attr( $attributes['maxVideos'] ?? 5 ); ?>"
		data-selected-playlist="<?php echo esc_attr( $attributes['selectedPlaylist'] ?? '' ); ?>"
		data-enable-search="<?php echo esc_attr( $attributes['enableSearch'] ? 'true' : 'false' ); ?>"
		data-enable-playlist-filter="<?php echo esc_attr( $attributes['enablePlaylistFilter'] ? 'true' : 'false' ); ?>"
		data-channel-id="<?php echo esc_attr( $channel_id ); ?>"
	></div>
	<?php
	return ob_get_clean();
}

/**
 * Fetches YouTube videos for the feed with backend caching.
 *
 * @param string $channel_id  The YouTube channel ID.
 * @param string $api_key     The YouTube API key.
 * @param int    $max_results Maximum number of videos to return.
 * @return array Video data or an empty array on failure.
 */
function fetch_youtube_feed_videos( $channel_id, $api_key, $max_results = 5 ) {
	// Sanitize and validate input.
	if ( empty( $channel_id ) || empty( $api_key ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube for WordPress Pro: Missing required parameters: channel_id or api_key.' );
		}
		return array();
	}

	// Create a unique cache key.
	$cache_key     = "yt_for_wp_videos_{$channel_id}_{$max_results}";
	$cached_videos = get_transient( $cache_key );

	if ( $cached_videos ) {
		// Return cached data if available.
		return $cached_videos;
	}

	// Construct the YouTube API URL.
	$api_url = add_query_arg(
		array(
			'key'        => $api_key,
			'channelId'  => $channel_id,
			'part'       => 'snippet',
			'type'       => 'video',
			'order'      => 'date',
			'maxResults' => $max_results,
		),
		'https://www.googleapis.com/youtube/v3/search'
	);

	// Fetch the data from YouTube API.
	$response = wp_remote_get( $api_url );

	if ( is_wp_error( $response ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube for WordPress Pro: API Request Error: ' . $response->get_error_message() );
		}
		return array();
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $data['error'] ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube for WordPress Pro: API Error: ' . wp_json_encode( $data['error'] ) );
		}
		return array();
	}

	// Map the API response to the expected structure.
	$videos = array_map(
		function ( $video ) {
			// Check for required keys to avoid PHP warnings.
			$video_id   = $video['id']['videoId'] ?? null;
			$snippet    = $video['snippet'] ?? array();
			$thumbnails = $snippet['thumbnails']['medium']['url'] ?? '';

			if ( ! $video_id || ! $snippet ) {
				return null;
			}

			return array(
				'id'          => $video_id,
				'title'       => $snippet['title'] ?? 'Untitled Video',
				'description' => $snippet['description'] ?? 'No description available.',
				'publishedAt' => $snippet['publishedAt'] ?? '',
				'thumbnail'   => $thumbnails,
			);
		},
		$data['items'] ?? array()
	);

	// Remove null entries caused by invalid data.
	$videos = array_filter( $videos );

	if ( empty( $videos ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube for WordPress Pro: No videos found or invalid API response.' );
		}
		return array();
	}

	// Cache the result for 1 hour.
	set_transient( $cache_key, $videos, HOUR_IN_SECONDS );

	return $videos;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'youtube-for-wordpress/v1',
			'/videos',
			array(
				'methods'             => 'GET',
				'callback'            => function ( \WP_REST_Request $request ) {
					$channels    = \YouTubeForWPPro\Channels\Channel_Manager::get_all();
					$default_id  = ! empty( $channels[0]['id'] ) ? $channels[0]['id'] : get_option( 'yt_for_wp_channel_id' );
					$channel_id  = $request->get_param( 'channelId' ) ? $request->get_param( 'channelId' ) : $default_id;
					$max_results = intval( $request->get_param( 'maxResults' ) ? $request->get_param( 'maxResults' ) : 5 );
					$api_key     = \YouTubeForWP\Admin\Settings\get_api_key();

					if ( ! $channel_id || ! $api_key ) {
						return new \WP_Error(
							'missing_parameters',
							__( 'Channel ID or API key is missing.', 'yt-for-wp-pro' ),
							array( 'status' => 400 )
						);
					}

					$videos = fetch_youtube_feed_videos( $channel_id, $api_key, $max_results );

					return rest_ensure_response( $videos );
				},
				'permission_callback' => '__return_true',
			)
		);
	}
);
