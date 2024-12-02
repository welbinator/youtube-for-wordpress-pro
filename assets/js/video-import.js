document.addEventListener('DOMContentLoaded', () => {
    const importButton = document.getElementById('yt-for-wp-import-videos');

    if (importButton) {
        importButton.addEventListener('click', async () => {
            const limit = document.getElementById('yt_for_wp_pro_video_import_limit').value || 0;

            importButton.disabled = true;
            importButton.textContent = 'Importing...';

            try {
                const formData = new FormData();
                formData.append('action', 'yt_for_wp_pro_import_videos');
                formData.append('limit', limit);
                formData.append('_ajax_nonce', ytForWPPro.nonce);

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    alert('Videos imported successfully!');
                } else {
                    console.error(result.data);
                    alert('An error occurred during the import process.');
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
