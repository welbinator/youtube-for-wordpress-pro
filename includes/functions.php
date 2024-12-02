<?php

/**
 * Download an image from a URL and set it as the featured image for a post.
 *
 * @param int $post_id The post ID.
 * @param string $image_url The image URL.
 * @return bool|int Attachment ID on success, false on failure.
 */
function yt_for_wp_set_post_thumbnail_from_url($post_id, $image_url) {
    // Check if the post ID and image URL are valid.
    if (empty($post_id) || empty($image_url)) {
        return false;
    }

    // Get the image data from the URL.
    $response = wp_remote_get($image_url);

    if (is_wp_error($response) || empty($response['body'])) {
        return false;
    }

    // Get the file name from the image URL.
    $image_name = basename(parse_url($image_url, PHP_URL_PATH));

    // Generate a unique filename by appending the post ID to prevent overwriting.
    $unique_filename = $post_id . '-' . $image_name;

    // Define the upload directory and file path.
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['path'] . '/' . $unique_filename;

    // Save the image file to the uploads directory.
    file_put_contents($file_path, $response['body']);

    // Check the file type.
    $file_type = wp_check_filetype($file_path, null);

    if (!$file_type['type']) {
        return false; // Invalid file type.
    }

    // Create the attachment array.
    $attachment = [
        'post_mime_type' => $file_type['type'],
        'post_title'     => sanitize_file_name($unique_filename),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    // Insert the attachment into the WordPress database.
    $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);

    if (is_wp_error($attachment_id)) {
        return false;
    }

    // Include the required file for generating metadata.
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate and save attachment metadata.
    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

    // Set the attachment as the featured image for the post.
    set_post_thumbnail($post_id, $attachment_id);

    return $attachment_id;
}

