<?php

add_filter('render_block_yt-for-wp/youtube-live', function ($block_content, $block) {
    if (!isset($block['attrs'])) {
        return $block_content;
    }

    $attributes = $block['attrs'];

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($block_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $container = $dom->getElementsByTagName('div')->item(0);
    if ($container && !empty($attributes['channelId'])) {
        $container->setAttribute('data-channel-id', $attributes['channelId']);
    }

    return $dom->saveHTML();
}, 10, 2);
