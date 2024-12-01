<?php

namespace YouTubeForWPPro\VideoCPT;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Video_Post_Type {
    /**
     * Initialize the class.
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomy']);
    }

    /**
     * Register the Video custom post type.
     */
    public static function register_post_type() {
        $labels = [
            'name'                  => __('Videos', 'yt-for-wp-pro'),
            'singular_name'         => __('Video', 'yt-for-wp-pro'),
            'menu_name'             => __('Videos', 'yt-for-wp-pro'),
            'add_new'               => __('Add New Video', 'yt-for-wp-pro'),
            'add_new_item'          => __('Add New Video', 'yt-for-wp-pro'),
            'edit_item'             => __('Edit Video', 'yt-for-wp-pro'),
            'new_item'              => __('New Video', 'yt-for-wp-pro'),
            'view_item'             => __('View Video', 'yt-for-wp-pro'),
            'search_items'          => __('Search Videos', 'yt-for-wp-pro'),
            'not_found'             => __('No videos found', 'yt-for-wp-pro'),
            'not_found_in_trash'    => __('No videos found in trash', 'yt-for-wp-pro'),
        ];

        $args = [
            'labels'                => $labels,
            'public'                => true,
            'has_archive'           => true,
            'show_in_rest'          => true,
            'supports'              => ['title', 'editor', 'thumbnail'],
            'menu_icon'             => 'dashicons-video-alt3',
            'rewrite'               => ['slug' => 'yt-4-wp-video'],
            'capability_type'       => 'post',
        ];

        register_post_type('yt-4-wp-video', $args);
    }

    /**
     * Register the Playlist taxonomy.
     */
    public static function register_taxonomy() {
        $labels = [
            'name'                       => __('Playlists', 'yt-for-wp-pro'),
            'singular_name'              => __('Playlist', 'yt-for-wp-pro'),
            'search_items'               => __('Search Playlists', 'yt-for-wp-pro'),
            'all_items'                  => __('All Playlists', 'yt-for-wp-pro'),
            'edit_item'                  => __('Edit Playlist', 'yt-for-wp-pro'),
            'view_item'                  => __('View Playlist', 'yt-for-wp-pro'),
            'update_item'                => __('Update Playlist', 'yt-for-wp-pro'),
            'add_new_item'               => __('Add New Playlist', 'yt-for-wp-pro'),
            'new_item_name'              => __('New Playlist Name', 'yt-for-wp-pro'),
            'menu_name'                  => __('Playlists', 'yt-for-wp-pro'),
        ];

        $args = [
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'yt-4-wp-playlist'],
        ];

        register_taxonomy('yt-4-wp-playlist', ['yt-4-wp-video'], $args);
    }
}
