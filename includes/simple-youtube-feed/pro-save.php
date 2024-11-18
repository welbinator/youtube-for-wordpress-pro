<?php

add_filter('render_block_yt-for-wp/simple-youtube-feed', function($block_content, $block) {
    if (!isset($block['attrs'])) {
        return $block_content;
    }

    $attributes = $block['attrs'];

    // Add Pro-specific data attributes
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($block_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $container = $dom->getElementsByTagName('div')->item(0);
    if ($container) {
        if (isset($attributes['enableSearch']) && $attributes['enableSearch']) {
            $container->setAttribute('data-enable-search', 'true');
        }
        if (isset($attributes['enablePlaylistFilter']) && $attributes['enablePlaylistFilter']) {
            $container->setAttribute('data-enable-playlist-filter', 'true');
        }
    }

    return $dom->saveHTML();
}, 10, 2);
