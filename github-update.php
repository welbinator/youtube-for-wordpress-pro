<?php
namespace YouTubeForWP\GitHubUpdater;

add_filter('pre_set_site_transient_update_plugins', __NAMESPACE__ . '\\check_for_updates');

function check_for_updates($transient) {
    $owner = 'welbinator';
    $repo  = 'youtube-for-wordpress';

    // Skip if this is not a plugin update check
    if ( empty($transient->checked) || ! is_object($transient) ) {
        return $transient;
    }

    // GitHub API URL for the latest release
    $api_url = "https://api.github.com/repos/$owner/$repo/releases/latest";

    // Fetch latest release from GitHub
    $response = wp_remote_get($api_url, [
        'headers' => [
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        ]
    ]);

    if ( is_wp_error($response) ) {
        return $transient;
    }

    $release = json_decode(wp_remote_retrieve_body($response), true);

    if ( ! isset($release['tag_name']) || empty($release['assets'][0]['browser_download_url']) ) {
        return $transient;
    }

    // Extract the latest version (strip 'v' prefix)
    $latest_version = ltrim($release['tag_name'], 'v');
    $download_url   = $release['assets'][0]['browser_download_url'];

	

    // Define the main plugin file once
    $plugin_file = __DIR__ . '/youtube-for-wordpress.php';
    $plugin_slug = plugin_basename($plugin_file);
    $plugin_data = get_plugin_data($plugin_file);
    $current_version = $plugin_data['Version'];
	
    if ( version_compare($latest_version, $current_version, '>') ) {
        $transient->response[$plugin_slug] = (object) [
            'slug'        => $plugin_slug,
            'new_version' => $latest_version,
            'package'     => $download_url,
            'url'         => $release['html_url'],
            'tested'      => get_bloginfo('version'),
            'requires'    => '6.0'
        ];
    }

    return $transient;
}
