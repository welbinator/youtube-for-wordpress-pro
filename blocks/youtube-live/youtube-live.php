<?php

namespace YouTubeForWP\Blocks\YouTubeLive;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the block using metadata from `block.json`.
 */
function register_simple_youtube_live_block() {
    register_block_type_from_metadata( plugin_dir_path( __FILE__ ) . '../../build/youtube-live' );
}
add_action('init', __NAMESPACE__ . '\\register_simple_youtube_live_block');



