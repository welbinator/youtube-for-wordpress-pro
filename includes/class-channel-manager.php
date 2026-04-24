<?php
/**
 * Channel manager for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWPPro\Channels;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Channel Manager class.
 */
class Channel_Manager {

	/**
	 * Get all saved channels.
	 *
	 * @return array Array of ['id' => string, 'name' => string]
	 */
	public static function get_all(): array {
		$channels = get_option( 'yt_for_wp_channels', array() );
		return is_array( $channels ) ? $channels : array();
	}

	/**
	 * Add a channel. Looks up the name from YouTube API if possible.
	 *
	 * @param string $channel_id YouTube channel ID.
	 * @return bool|\WP_Error
	 */
	public static function add( string $channel_id ) {
		$channel_id = sanitize_text_field( $channel_id );
		if ( empty( $channel_id ) ) {
			return new \WP_Error( 'invalid_input', __( 'Channel ID is required.', 'yt-for-wp-pro' ) );
		}

		// Check for duplicate.
		foreach ( self::get_all() as $ch ) {
			if ( $ch['id'] === $channel_id ) {
				return new \WP_Error( 'duplicate', __( 'That channel has already been added.', 'yt-for-wp-pro' ) );
			}
		}

		// Try to look up channel name via YouTube API.
		$name = self::fetch_channel_name( $channel_id );
		if ( is_wp_error( $name ) ) {
			// If lookup fails, use the channel ID as the name.
			$name = $channel_id;
		}

		$channels   = self::get_all();
		$channels[] = array(
			'id'   => $channel_id,
			'name' => $name,
		);
		update_option( 'yt_for_wp_channels', $channels );
		return true;
	}

	/**
	 * Remove a channel by ID.
	 *
	 * @param string $channel_id The channel ID to remove.
	 */
	public static function remove( string $channel_id ): void {
		$channel_id = sanitize_text_field( $channel_id );
		$channels   = array_filter(
			self::get_all(),
			function ( $ch ) use ( $channel_id ) {
				return $ch['id'] !== $channel_id;
			}
		);
		update_option( 'yt_for_wp_channels', array_values( $channels ) );
	}

	/**
	 * Update a channel's display name.
	 *
	 * @param string $channel_id The channel ID.
	 * @param string $name The new name.
	 * @return bool|\WP_Error
	 */
	public static function update_name( string $channel_id, string $name ) {
		$name = sanitize_text_field( $name );
		if ( empty( $name ) ) {
			return new \WP_Error( 'invalid_input', __( 'Channel name cannot be empty.', 'yt-for-wp-pro' ) );
		}
		$channels = self::get_all();
		foreach ( $channels as &$ch ) {
			if ( $ch['id'] === $channel_id ) {
				$ch['name'] = $name;
				update_option( 'yt_for_wp_channels', $channels );
				return true;
			}
		}
		return new \WP_Error( 'not_found', __( 'Channel not found.', 'yt-for-wp-pro' ) );
	}

	/**
	 * Fetch the channel name from the YouTube API.
	 *
	 * @param string $channel_id The channel ID.
	 * @return string|\WP_Error
	 */
	public static function fetch_channel_name( string $channel_id ) {
		$api_key = \YouTubeForWP\Admin\Settings\get_api_key();

		if ( ! $api_key ) {
			return new \WP_Error( 'no_api_key', __( 'No API key configured.', 'yt-for-wp-pro' ) );
		}

		$url = add_query_arg(
			array(
				'part' => 'snippet',
				'id'   => $channel_id,
				'key'  => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/channels'
		);

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['items'][0]['snippet']['title'] ) ) {
			return new \WP_Error( 'not_found', __( 'Channel not found or API key invalid.', 'yt-for-wp-pro' ) );
		}

		return sanitize_text_field( $data['items'][0]['snippet']['title'] );
	}

	/**
	 * Resolve a YouTube channel URL or handle to a channel ID and name.
	 *
	 * Supports:
	 * - https://www.youtube.com/channel/UCxxxxxx
	 * - https://www.youtube.com/@HandleName
	 * - https://www.youtube.com/c/CustomName
	 * - https://www.youtube.com/user/Username
	 * - @HandleName (bare handle)
	 * - UCxxxxxx (bare channel ID)
	 *
	 * @param string $input URL, handle, or channel ID.
	 * @return array|\WP_Error Array with 'id' and 'name', or WP_Error.
	 */
	public static function resolve_url( string $input ) {
		$input = trim( $input );

		$api_key = \YouTubeForWP\Admin\Settings\get_api_key();

		if ( ! $api_key ) {
			return new \WP_Error( 'no_api_key', __( 'Please configure your YouTube API key in Settings before adding channels.', 'yt-for-wp-pro' ) );
		}

		// Extract from URL if it looks like one.
		if ( strpos( $input, 'youtube.com' ) !== false ) {
			$parsed = wp_parse_url( $input );
			$path   = isset( $parsed['path'] ) ? trim( $parsed['path'], '/' ) : '';

			// youtube.com/channel/UCxxxxxx.
			if ( preg_match( '#^channel/(UC[\w-]{22})#', $path, $m ) ) {
				$channel_id = $m[1];
				$name       = self::fetch_channel_name( $channel_id );
				return is_wp_error( $name )
					? array(
						'id'   => $channel_id,
						'name' => $channel_id,
					)
					: array(
						'id'   => $channel_id,
						'name' => $name,
					);
			}

			// youtube.com/@Handle.
			if ( preg_match( '#^@([\w.-]+)#', $path, $m ) ) {
				$input = '@' . $m[1];
			}

			// youtube.com/c/Name or youtube.com/user/Name.
			if ( preg_match( '#^(?:c|user)/([\w.-]+)#', $path, $m ) ) {
				$input = $m[1];
			}
		}

		// Bare channel ID (starts with UC and is 24 chars).
		if ( preg_match( '/^UC[\w-]{22}$/', $input ) ) {
			$name = self::fetch_channel_name( $input );
			return is_wp_error( $name )
				? array(
					'id'   => $input,
					'name' => $input,
				)
				: array(
					'id'   => $input,
					'name' => $name,
				);
		}

		// Handle (@HandleName or bare handle name).
		$handle = ltrim( $input, '@' );
		$url    = add_query_arg(
			array(
				'part'      => 'snippet',
				'forHandle' => '@' . $handle,
				'key'       => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/channels'
		);

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! empty( $data['items'][0] ) ) {
			return array(
				'id'   => sanitize_text_field( $data['items'][0]['id'] ),
				'name' => sanitize_text_field( $data['items'][0]['snippet']['title'] ),
			);
		}

		// Fallback: search by name.
		$search_url = add_query_arg(
			array(
				'part' => 'snippet',
				'type' => 'channel',
				'q'    => $handle,
				'key'  => $api_key,
			),
			'https://www.googleapis.com/youtube/v3/search'
		);

		$search_response = wp_remote_get( $search_url );
		if ( is_wp_error( $search_response ) ) {
			return $search_response;
		}

		$search_data = json_decode( wp_remote_retrieve_body( $search_response ), true );
		if ( ! empty( $search_data['items'][0]['id']['channelId'] ) ) {
			return array(
				'id'   => sanitize_text_field( $search_data['items'][0]['id']['channelId'] ),
				'name' => sanitize_text_field( $search_data['items'][0]['snippet']['channelTitle'] ),
			);
		}

		return new \WP_Error( 'not_found', __( 'Could not find a YouTube channel for that URL or handle.', 'yt-for-wp-pro' ) );
	}
}
