<?php
/**
 * GitHub-based auto-updater for YouTube for WordPress Pro.
 *
 * Checks GitHub Releases for newer versions and handles WordPress update
 * integration. Includes SHA-256 integrity verification — fails closed if no
 * .sha256 asset is present in the release.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWP\GitHubUpdater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\\check_for_updates' );
add_filter( 'upgrader_pre_download', __NAMESPACE__ . '\\verify_package_integrity', 10, 3 );

/**
 * Check GitHub Releases for a newer version of the plugin.
 *
 * @param object $transient The update_plugins transient.
 * @return object Modified transient.
 */
function check_for_updates( $transient ) {
	$owner = 'welbinator';
	$repo  = 'youtube-for-wordpress-pro';

	// Skip if this is not a plugin update check.
	if ( empty( $transient->checked ) || ! is_object( $transient ) ) {
		return $transient;
	}

	// GitHub API URL for the latest release.
	$api_url = "https://api.github.com/repos/{$owner}/{$repo}/releases/latest";

	$response = wp_remote_get(
		$api_url,
		array(
			'headers' => array(
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
			),
			'timeout' => 10,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $transient;
	}

	$release = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! isset( $release['tag_name'] ) || empty( $release['assets'] ) ) {
		return $transient;
	}

	// Find the zip asset and the .sha256 asset.
	$zip_url    = '';
	$sha256_url = '';
	foreach ( $release['assets'] as $asset ) {
		if ( isset( $asset['browser_download_url'] ) ) {
			if ( str_ends_with( $asset['name'], '.zip' ) && '' === $zip_url ) {
				$zip_url = $asset['browser_download_url'];
			}
			if ( str_ends_with( $asset['name'], '.sha256' ) && '' === $sha256_url ) {
				$sha256_url = $asset['browser_download_url'];
			}
		}
	}

	// Fail closed — no .sha256 asset means no update offered.
	if ( '' === $sha256_url || '' === $zip_url ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[YouTube for WordPress Pro] Update blocked: no .sha256 or .zip asset found for release ' . $release['tag_name'] . '.' );
		}
		return $transient;
	}

	$latest_version = ltrim( $release['tag_name'], 'v' );

	// Load plugin data — admin-only function, check it exists.
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin_file     = __DIR__ . '/youtube-for-wordpress-pro.php';
	$plugin_slug     = plugin_basename( $plugin_file );
	$plugin_data     = get_plugin_data( $plugin_file );
	$current_version = $plugin_data['Version'];

	if ( version_compare( $latest_version, $current_version, '>' ) ) {
		$transient->response[ $plugin_slug ] = (object) array(
			'slug'        => $plugin_slug,
			'new_version' => $latest_version,
			'package'     => $zip_url,
			'url'         => $release['html_url'],
			'tested'      => get_bloginfo( 'version' ),
			'requires'    => '6.0',
			// Store sha256_url so verify_package_integrity() can fetch it.
			'sha256_url'  => esc_url_raw( $sha256_url ),
		);
	}

	return $transient;
}

/**
 * Verify the downloaded zip matches the SHA-256 hash before WordPress installs it.
 *
 * Hooked to upgrader_pre_download. Returns a WP_Error to abort the update
 * if the hash is missing or does not match.
 *
 * @param bool|WP_Error $reply      Whether to abort the download (false = proceed).
 * @param string        $package    The package URL.
 * @param object        $upgrader   The WP_Upgrader instance.
 * @return bool|WP_Error False to proceed, WP_Error to abort.
 */
function verify_package_integrity( $reply, $package, $upgrader ) {
	// Only act on our plugin's package.
	if ( ! isset( $upgrader->skin->plugin_info ) ) {
		return $reply;
	}

	$plugin_file = __DIR__ . '/youtube-for-wordpress-pro.php';
	$plugin_slug = plugin_basename( $plugin_file );

	// Find our update object.
	$updates = get_site_transient( 'update_plugins' );
	$release = $updates->response[ $plugin_slug ] ?? null;

	if ( ! $release || empty( $release->sha256_url ) ) {
		return new \WP_Error(
			'no_sha256',
			__( 'YouTube for WordPress Pro update aborted: no integrity checksum available for this release.', 'yt-for-wp-pro' )
		);
	}

	// Download the hash file.
	$hash_response = wp_remote_get(
		$release->sha256_url,
		array(
			'timeout'    => 10,
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ),
		)
	);

	if ( is_wp_error( $hash_response ) || 200 !== (int) wp_remote_retrieve_response_code( $hash_response ) ) {
		return new \WP_Error(
			'sha256_fetch_failed',
			__( 'YouTube for WordPress Pro update aborted: could not retrieve integrity checksum.', 'yt-for-wp-pro' )
		);
	}

	$expected_hash = trim( wp_remote_retrieve_body( $hash_response ) );
	// .sha256 files often contain "hash  filename" — extract just the hash.
	if ( str_contains( $expected_hash, ' ' ) ) {
		$expected_hash = explode( ' ', $expected_hash )[0];
	}

	// Download the zip to a temp file and hash it.
	$tmp_response = wp_remote_get(
		$package,
		array(
			'timeout'  => 60,
			'stream'   => true,
			'filename' => wp_tempnam( 'yt-for-wp-pro-update' ),
		)
	);

	if ( is_wp_error( $tmp_response ) ) {
		return $tmp_response;
	}

	$tmp_file    = wp_remote_retrieve_body( $tmp_response );
	$actual_hash = hash_file( 'sha256', $tmp_file );

	if ( ! hash_equals( $expected_hash, $actual_hash ) ) {
		// Clean up.
		@unlink( $tmp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- temp file cleanup.
		return new \WP_Error(
			'sha256_mismatch',
			__( 'YouTube for WordPress Pro update aborted: integrity check failed. The downloaded file does not match the expected hash. This may indicate a corrupted download or tampered release asset.', 'yt-for-wp-pro' )
		);
	}

	// Hash verified — let WordPress proceed with the temp file.
	// We return the temp file path so WordPress can use the already-downloaded zip.
	return $tmp_file;
}
