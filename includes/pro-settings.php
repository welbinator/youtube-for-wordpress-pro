<?php
/**
 * Pro settings pages for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWPPro\Settings;

/**
 * Render the Channels management page.
 */
function render_channels_page() {
	$channels = \YouTubeForWPPro\Channels\Channel_Manager::get_all();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'YouTube Channels', 'yt-for-wp-pro' ); ?></h1>
		<p><?php esc_html_e( 'Add and manage YouTube channels for importing videos.', 'yt-for-wp-pro' ); ?></p>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Channel Name', 'yt-for-wp-pro' ); ?></th>
					<th><?php esc_html_e( 'Channel ID', 'yt-for-wp-pro' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'yt-for-wp-pro' ); ?></th>
				</tr>
			</thead>
			<tbody id="yt-channels-list">
				<?php if ( empty( $channels ) ) : ?>
					<tr id="no-channels-row">
						<td colspan="3"><?php esc_html_e( 'No channels added yet.', 'yt-for-wp-pro' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $channels as $channel ) : ?>
						<tr data-channel-id="<?php echo esc_attr( $channel['id'] ); ?>">
							<td>
								<span class="channel-name"><?php echo esc_html( $channel['name'] ); ?></span>
								<input type="text" class="channel-name-input" value="<?php echo esc_attr( $channel['name'] ); ?>" style="display:none;" />
							</td>
							<td><code><?php echo esc_html( $channel['id'] ); ?></code></td>
							<td>
								<button type="button" class="button button-small yt-rename-channel"><?php esc_html_e( 'Rename', 'yt-for-wp-pro' ); ?></button>
								<button type="button" class="button button-small yt-remove-channel"><?php esc_html_e( 'Remove', 'yt-for-wp-pro' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Add New Channel', 'yt-for-wp-pro' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="yt_channel_id"><?php esc_html_e( 'YouTube Channel ID', 'yt-for-wp-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="yt_channel_id" id="yt_channel_id" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Enter the YouTube channel ID. The channel name will be automatically fetched from YouTube.', 'yt-for-wp-pro' ); ?></p>
					<p class="description error" id="yt-channel-error" style="color: #d63638; font-weight: bold; display: none;"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button type="button" class="button button-primary" id="yt-add-channel"><?php esc_html_e( 'Add Channel', 'yt-for-wp-pro' ); ?></button>
				</td>
			</tr>
		</table>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			// Add channel
			$('#yt-add-channel').on('click', function() {
				var $btn = $(this);
				var channelId = $('#yt_channel_id').val().trim();
				var $error = $('#yt-channel-error');

				$error.hide();
				if (!channelId) {
					$error.text('<?php echo esc_js( __( 'Please enter a channel ID.', 'yt-for-wp-pro' ) ); ?>').show();
					return;
				}

				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Adding...', 'yt-for-wp-pro' ) ); ?>');

				$.post(ajaxurl, {
					action: 'yt_for_wp_add_channel',
					channel_id: channelId,
					_ajax_nonce: ytForWPSettings.nonce
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						$error.text(response.data).show();
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Add Channel', 'yt-for-wp-pro' ) ); ?>');
					}
				});
			});

			// Remove channel
			$(document).on('click', '.yt-remove-channel', function() {
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to remove this channel?', 'yt-for-wp-pro' ) ); ?>')) {
					return;
				}

				var $row = $(this).closest('tr');
				var channelId = $row.data('channel-id');

				$.post(ajaxurl, {
					action: 'yt_for_wp_remove_channel',
					channel_id: channelId,
					_ajax_nonce: ytForWPSettings.nonce
				}, function(response) {
					if (response.success) {
						location.reload();
					}
				});
			});

			// Rename channel
			$(document).on('click', '.yt-rename-channel', function() {
				var $row = $(this).closest('tr');
				var $nameSpan = $row.find('.channel-name');
				var $nameInput = $row.find('.channel-name-input');
				var $btn = $(this);

				if ($nameInput.is(':visible')) {
					// Save
					var channelId = $row.data('channel-id');
					var newName = $nameInput.val().trim();

					$.post(ajaxurl, {
						action: 'yt_for_wp_update_channel_name',
						channel_id: channelId,
						name: newName,
						_ajax_nonce: ytForWPSettings.nonce
					}, function(response) {
						if (response.success) {
							$nameSpan.text(newName).show();
							$nameInput.hide();
							$btn.text('<?php echo esc_js( __( 'Rename', 'yt-for-wp-pro' ) ); ?>');
						}
					});
				} else {
					// Edit
					$nameSpan.hide();
					$nameInput.show().focus();
					$btn.text('<?php echo esc_js( __( 'Save', 'yt-for-wp-pro' ) ); ?>');
				}
			});
		});
	</script>
	<?php
}

/**
 * Render the Post Types management page.
 */
function render_post_types_page() {
	$types = \YouTubeForWPPro\VideoCPT\Video_Post_Type::get_all();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Video Post Types', 'yt-for-wp-pro' ); ?></h1>
		<p><?php esc_html_e( 'Create and manage custom post types for your imported YouTube videos.', 'yt-for-wp-pro' ); ?></p>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Post Type Name', 'yt-for-wp-pro' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'yt-for-wp-pro' ); ?></th>
					<th><?php esc_html_e( 'Taxonomy Slug', 'yt-for-wp-pro' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'yt-for-wp-pro' ); ?></th>
				</tr>
			</thead>
			<tbody id="yt-post-types-list">
				<?php if ( empty( $types ) ) : ?>
					<tr id="no-types-row">
						<td colspan="4"><?php esc_html_e( 'No post types created yet.', 'yt-for-wp-pro' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $types as $type ) : ?>
						<tr data-post-type-slug="<?php echo esc_attr( $type['slug'] ); ?>">
							<td>
								<span class="type-name"><?php echo esc_html( $type['name'] ); ?></span>
								<input type="text" class="type-name-input" value="<?php echo esc_attr( $type['name'] ); ?>" style="display:none;" />
							</td>
							<td><code><?php echo esc_html( $type['slug'] ); ?></code></td>
							<td><code><?php echo esc_html( $type['slug'] . '-playlist' ); ?></code></td>
							<td>
								<button type="button" class="button button-small yt-rename-type"><?php esc_html_e( 'Rename', 'yt-for-wp-pro' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Add New Post Type', 'yt-for-wp-pro' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="yt_post_type_name"><?php esc_html_e( 'Post Type Name (Singular)', 'yt-for-wp-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="yt_post_type_name" id="yt_post_type_name" value="Video" class="regular-text" required />
					<p class="description"><?php esc_html_e( 'Example: "Video", "Tutorial", "Episode"', 'yt-for-wp-pro' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="yt_post_type_slug"><?php esc_html_e( 'Post Type Slug', 'yt-for-wp-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="yt_post_type_slug" id="yt_post_type_slug" value="video" class="regular-text" required />
					<p class="description"><?php esc_html_e( 'URL-friendly slug (20 characters max, lowercase, no spaces). This will be auto-generated from the name.', 'yt-for-wp-pro' ); ?></p>
					<p class="description error" id="yt-post-type-error" style="color: #d63638; font-weight: bold; display: none;"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button type="button" class="button button-primary" id="yt-create-post-type"><?php esc_html_e( 'Create Post Type', 'yt-for-wp-pro' ); ?></button>
				</td>
			</tr>
		</table>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			// Auto-generate slug from name
			$('#yt_post_type_name').on('input', function() {
				var slug = $(this).val()
					.toLowerCase()
					.replace(/[^a-z0-9-]/g, '-')
					.replace(/-+/g, '-')
					.replace(/^-|-$/g, '')
					.substring(0, 20);
				$('#yt_post_type_slug').val(slug);
			});

			// Create post type
			$('#yt-create-post-type').on('click', function() {
				var $btn = $(this);
				var slug = $('#yt_post_type_slug').val().trim();
				var name = $('#yt_post_type_name').val().trim();
				var $error = $('#yt-post-type-error');

				$error.hide();
				if (!slug || !name) {
					$error.text('<?php echo esc_js( __( 'Please enter both name and slug.', 'yt-for-wp-pro' ) ); ?>').show();
					return;
				}

				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Creating...', 'yt-for-wp-pro' ) ); ?>');

				$.post(ajaxurl, {
					action: 'yt_for_wp_add_post_type',
					slug: slug,
					name: name,
					_ajax_nonce: ytForWPSettings.nonce
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						$error.text(response.data).show();
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Create Post Type', 'yt-for-wp-pro' ) ); ?>');
					}
				});
			});

			// Rename post type
			$(document).on('click', '.yt-rename-type', function() {
				var $row = $(this).closest('tr');
				var $nameSpan = $row.find('.type-name');
				var $nameInput = $row.find('.type-name-input');
				var $btn = $(this);

				if ($nameInput.is(':visible')) {
					// Save
					var slug = $row.data('post-type-slug');
					var newName = $nameInput.val().trim();

					$.post(ajaxurl, {
						action: 'yt_for_wp_update_post_type_name',
						slug: slug,
						name: newName,
						_ajax_nonce: ytForWPSettings.nonce
					}, function(response) {
						if (response.success) {
							$nameSpan.text(newName).show();
							$nameInput.hide();
							$btn.text('<?php echo esc_js( __( 'Rename', 'yt-for-wp-pro' ) ); ?>');
						}
					});
				} else {
					// Edit
					$nameSpan.hide();
					$nameInput.show().focus();
					$btn.text('<?php echo esc_js( __( 'Save', 'yt-for-wp-pro' ) ); ?>');
				}
			});
		});
	</script>
	<?php
}

/**
 * Render the Import Videos page.
 */
function render_import_videos_page() {
	$channels   = \YouTubeForWPPro\Channels\Channel_Manager::get_all();
	$post_types = \YouTubeForWPPro\VideoCPT\Video_Post_Type::get_all();

	// Check prerequisites.
	if ( empty( $channels ) ) {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?></h1>
			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: link to channels page */
							__( 'Please <a href="%s">add a YouTube channel</a> first.', 'yt-for-wp-pro' ),
							admin_url( 'admin.php?page=yt-for-wp-channels' )
						)
					);
					?>
				</p>
			</div>
		</div>
		<?php
		return;
	}

	if ( empty( $post_types ) ) {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?></h1>
			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: link to post types page */
							__( 'Please <a href="%s">create a video post type</a> first.', 'yt-for-wp-pro' ),
							admin_url( 'admin.php?page=yt-for-wp-post-types' )
						)
					);
					?>
				</p>
			</div>
		</div>
		<?php
		return;
	}

	// All good, show the import form.
	$first_type = $post_types[0];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?></h1>

		<div style="background:#fff; border:1px solid #c3c4c7; padding:12px 16px; margin-bottom:20px; display:inline-block;" id="yt-post-type-info">
			<strong><?php esc_html_e( 'Selected Post Type:', 'yt-for-wp-pro' ); ?></strong>
			<span id="yt-selected-type-name"><?php echo esc_html( $first_type['name'] ); ?></span>
			&nbsp;&nbsp;
			<strong><?php esc_html_e( 'Slug:', 'yt-for-wp-pro' ); ?></strong>
			<code id="yt-selected-type-slug"><?php echo esc_html( $first_type['slug'] ); ?></code>
			&nbsp;&nbsp;
			<strong><?php esc_html_e( 'Taxonomy:', 'yt-for-wp-pro' ); ?></strong>
			<code id="yt-selected-type-taxonomy"><?php echo esc_html( $first_type['slug'] . '-playlist' ); ?></code>
		</div>

		<form method="post" id="yt-for-wp-import-form">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="yt_for_wp_channel"><?php esc_html_e( 'YouTube Channel', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<select name="yt_for_wp_channel" id="yt_for_wp_channel" class="regular-text">
							<?php foreach ( $channels as $channel ) : ?>
								<option value="<?php echo esc_attr( $channel['id'] ); ?>">
									<?php echo esc_html( $channel['name'] ); ?> (<?php echo esc_html( $channel['id'] ); ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="yt_for_wp_playlist"><?php esc_html_e( 'Playlist', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<select name="yt_for_wp_playlist" id="yt_for_wp_playlist" class="regular-text">
							<option value=""><?php esc_html_e( 'All Videos', 'yt-for-wp-pro' ); ?></option>
						</select>
						<span id="yt-playlist-loading" style="display:none; margin-left:8px;"><?php esc_html_e( 'Loading playlists...', 'yt-for-wp-pro' ); ?></span>
						<p class="description"><?php esc_html_e( 'Choose a specific playlist or import all videos from the channel.', 'yt-for-wp-pro' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="yt_for_wp_post_type"><?php esc_html_e( 'Post Type', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<select name="yt_for_wp_post_type" id="yt_for_wp_post_type" class="regular-text">
							<?php foreach ( $post_types as $type ) : ?>
								<option value="<?php echo esc_attr( $type['slug'] ); ?>">
									<?php echo esc_html( $type['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="yt_for_wp_pro_video_import_limit"><?php esc_html_e( 'Number of Videos to Import', 'yt-for-wp-pro' ); ?></label>
					</th>
					<td>
						<input type="number" name="yt_for_wp_pro_video_import_limit" id="yt_for_wp_pro_video_import_limit" value="50" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Leave blank to import all available videos.', 'yt-for-wp-pro' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Start Import', 'yt-for-wp-pro' ); ?></th>
					<td>
						<button type="button" class="button button-primary" id="yt-for-wp-import-videos">
							<?php esc_html_e( 'Import Videos', 'yt-for-wp-pro' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'This will import videos from the selected YouTube channel as posts in the background.', 'yt-for-wp-pro' ); ?></p>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			var postTypes = <?php echo wp_json_encode( $post_types ); ?>;

			// Update info bar when post type changes.
			$('#yt_for_wp_post_type').on('change', function() {
				var slug = $(this).val();
				var type = postTypes.find(function(t) { return t.slug === slug; });
				if (type) {
					$('#yt-selected-type-name').text(type.name);
					$('#yt-selected-type-slug').text(type.slug);
					$('#yt-selected-type-taxonomy').text(type.slug + '-playlist');
				}
			});

			// Fetch playlists when channel changes.
			function loadPlaylists(channelId) {
				var $select = $('#yt_for_wp_playlist');
				var $loading = $('#yt-playlist-loading');
				$select.prop('disabled', true);
				$loading.show();
				$.post(ajaxurl, {
					action: 'yt_for_wp_get_playlists',
					channel_id: channelId,
					_ajax_nonce: ytForWPSettings.nonce
				}, function(response) {
					$loading.hide();
					$select.prop('disabled', false);
					$select.find('option:not(:first)').remove();
					if (response.success && response.data.length) {
						$.each(response.data, function(i, playlist) {
							$select.append($('<option>', { value: playlist.id, text: playlist.title }));
						});
					}
				});
			}

			// Load playlists for the initially selected channel.
			var initialChannel = $('#yt_for_wp_channel').val();
			if (initialChannel) { loadPlaylists(initialChannel); }

			// Reload when channel changes.
			$('#yt_for_wp_channel').on('change', function() {
				loadPlaylists($(this).val());
			});
		});
	</script>
	<?php
}
