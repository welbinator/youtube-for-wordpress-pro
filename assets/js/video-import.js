document.addEventListener('DOMContentLoaded', () => {
    const importButton = document.getElementById('yt-for-wp-import-videos');

    if (importButton) {
        importButton.addEventListener('click', async () => {
            const limit       = document.getElementById('yt_for_wp_pro_video_import_limit').value || 0;
            const channelEl   = document.getElementById('yt_for_wp_channel');
            const postTypeEl  = document.getElementById('yt_for_wp_post_type');
            const playlistEl  = document.getElementById('yt_for_wp_playlist');
            const channelId   = channelEl ? channelEl.value : '';
            const postTypeSlug = postTypeEl ? postTypeEl.value : '';
            const playlistId  = playlistEl ? playlistEl.value : '';

            importButton.disabled = true;
            importButton.textContent = 'Importing...';

            try {
                const formData = new FormData();
                formData.append('action', 'yt_for_wp_pro_import_videos');
                formData.append('limit', limit);
                formData.append('channel_id', channelId);
                formData.append('post_type_slug', postTypeSlug);
                formData.append('playlist_id', playlistId);
                formData.append('_ajax_nonce', ytForWPSettings.nonce);

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    alert('Videos imported successfully!');
                } else {
                    console.error(result.data);
                    alert('An error occurred during the import process: ' + result.data);
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred. Please check the console for details.');
            } finally {
                importButton.disabled = false;
                importButton.textContent = 'Import Videos';
            }
        });
    }
});
