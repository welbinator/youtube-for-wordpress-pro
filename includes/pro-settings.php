<?php

namespace YouTubeForWPPro\Settings;

/**
 * Render the Import Videos page.
 */
function render_import_videos_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Import Videos', 'yt-for-wp-pro'); ?></h1>
        <form method="post" id="yt-for-wp-import-form">
            <?php wp_nonce_field('yt-for-wp-import-videos', '_yt_for_wp_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="yt_for_wp_pro_video_import_limit"><?php esc_html_e('Number of Videos to Import', 'yt-for-wp-pro'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="number" 
                            name="yt_for_wp_pro_video_import_limit" 
                            id="yt_for_wp_pro_video_import_limit"
                            value="50" 
                            class="regular-text"
                        />
                        <p class="description">
                            <?php esc_html_e('Leave blank to import all available videos.', 'yt-for-wp-pro'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Start Import', 'yt-for-wp-pro'); ?>
                    </th>
                    <td>
                        <button type="button" class="button button-primary" id="yt-for-wp-import-videos">
                            <?php esc_html_e('Import Videos', 'yt-for-wp-pro'); ?>
                        </button>
                        <p class="description">
                            <?php esc_html_e('This will import videos from the configured YouTube channel as posts in the background.', 'yt-for-wp-pro'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}
