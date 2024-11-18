import { addAction } from '@wordpress/hooks';

addAction('yt_for_wp_youtube_live_view', 'yt-for-wp-pro', (container, attributes) => {
    const { channelId } = attributes;

    if (channelId) {
        container.dataset.channelId = channelId;
    }
});
