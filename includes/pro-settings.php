<?php
/**
 * Pro settings page for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWPPro\Settings;

/**
 * Render the Import Videos page.
 */
function render_import_videos_page() {
	// Check if post type has been created.
	if ( ! \YouTubeForWPPro\VideoCPT\Video_Post_Type::is_created() ) {
		// Show setup form.
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Set Up Your Video Post Type', 'yt-for-wp-pro' ); ?></h1>
			<p><?php esc_html_e( 'Before importing videos, please configure your video post type settings. Once created, these cannot be changed.', 'yt-for-wp-pro' ); ?></p>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="yt_post_type_name"><?php esc_html_e( 'Post Type Name (Singular)', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							name="yt_post_type_name" 
							id="yt_post_type_name"
							value="Video" 
							class="regular-text"
							required
						/>
						<p class="description">
							<?php esc_html_e( 'Example: "Video", "Tutorial", "Episode"', 'yt-for-wp-pro' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="yt_post_type_slug"><?php esc_html_e( 'Post Type Slug', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							name="yt_post_type_slug" 
							id="yt_post_type_slug"
							value="video" 
							class="regular-text"
							required
						/>
						<p class="description">
							<?php esc_html_e( 'URL-friendly slug (20 characters max, lowercase, no spaces). This will be auto-generated from the name.', 'yt-for-wp-pro' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"></th>
					<td>
						<button type="button" class="button button-primary" id="yt-for-wp-create-post-type">
							<?php esc_html_e( 'Create Post Type', 'yt-for-wp-pro' ); ?>
						</button>
						<p class="description" style="color: #d63638; font-weight: bold; display: none;" id="yt-post-type-error"></p>
					</td>
				</tr>
			</table>
		</div>
		
		<script type="text/javascript">
			jQuery(function($) {
				$('#yt-for-wp-create-post-type').on('click', function() {
					var $btn = $(this);
					var slug = $('#yt_post_type_slug').val().trim();
					var name = $('#yt_post_type_name').val().trim();
					var $error = $('#yt-post-type-error');
					
					$error.hide();
					$btn.prop('disabled', true).text('Creating...');
					
					$.post(ajaxurl, {
						action: 'yt_for_wp_create_post_type',
						slug: slug,
						name: name,
						_ajax_nonce: ytForWPCreateNonce
					}, function(response) {
						if (response.success) {
							location.reload();
						} else {
							$error.text(response.data).show();
							$btn.prop('disabled', false).text('Create Post Type');
						}
					});
				});
				
				// Auto-generate slug from name.
				$('#yt_post_type_name').on('input', function() {
					var slug = $(this).val()
						.toLowerCase()
						.replace(/[^a-z0-9-]/g, '-')
						.replace(/-+/g, '-')
						.replace(/^-|-$/g, '')
						.substring(0, 20);
					$('#yt_post_type_slug').val(slug);
				});
			});
		</script>
		<?php
		return;
	}

	// Post type is created, show the import form.
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?></h1>
		<form method="post" id="yt-for-wp-import-form">
			<?php wp_nonce_field( 'yt-for-wp-import-videos', '_yt_for_wp_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="yt_for_wp_pro_video_import_limit"><?php esc_html_e( 'Number of Videos to Import', 'yt-for-wp-pro' ); ?></label>
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
							<?php esc_html_e( 'Leave blank to import all available videos.', 'yt-for-wp-pro' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Start Import', 'yt-for-wp-pro' ); ?>
					</th>
					<td>
						<button type="button" class="button button-primary" id="yt-for-wp-import-videos">
							<?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?>
						</button>
						<p class="description">
							<?php esc_html_e( 'This will import videos from the configured YouTube channel as posts in the background.', 'yt-for-wp-pro' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php
}
