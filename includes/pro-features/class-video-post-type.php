<?php
/**
 * Video custom post type for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWPPro\VideoCPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Video Post Type class.
 */
class Video_Post_Type {
	/**
	 * Initialize the class.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
	}

	/**
	 * Get all registered post types.
	 *
	 * @return array Array of ['slug' => string, 'name' => string]
	 */
	public static function get_all(): array {
		$types = get_option( 'yt_for_wp_post_types', array() );
		return is_array( $types ) ? $types : array();
	}

	/**
	 * Check if any post types have been created.
	 *
	 * @return bool True if created, false otherwise.
	 */
	public static function is_created(): bool {
		return ! empty( self::get_all() );
	}

	/**
	 * Get the first post type slug (for backward compat).
	 *
	 * @return string The post type slug.
	 */
	public static function get_slug(): string {
		$types = self::get_all();
		return ! empty( $types ) ? sanitize_key( $types[0]['slug'] ) : '';
	}

	/**
	 * Get the first post type name (for backward compat).
	 *
	 * @return string The post type name.
	 */
	public static function get_name(): string {
		$types = self::get_all();
		return ! empty( $types ) ? sanitize_text_field( $types[0]['name'] ) : 'Video';
	}

	/**
	 * Create a new post type and add it to the array.
	 *
	 * @param string $slug The post type slug.
	 * @param string $name The post type name.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public static function create( string $slug, string $name ) {
		$slug = sanitize_key( $slug );
		$name = sanitize_text_field( $name );

		if ( empty( $slug ) || empty( $name ) ) {
			return new \WP_Error( 'invalid_input', __( 'Post type name and slug are required.', 'yt-for-wp-pro' ) );
		}

		if ( strlen( $slug ) > 20 ) {
			return new \WP_Error( 'slug_too_long', __( 'Post type slug must be 20 characters or fewer.', 'yt-for-wp-pro' ) );
		}

		// Check for duplicate slug in our own list.
		foreach ( self::get_all() as $existing ) {
			if ( $existing['slug'] === $slug ) {
				return new \WP_Error( 'slug_exists', __( 'You already have a post type with that slug.', 'yt-for-wp-pro' ) );
			}
		}

		// Check against already registered post types.
		$registered = get_post_types();
		if ( isset( $registered[ $slug ] ) ) {
			return new \WP_Error(
				'slug_exists',
				sprintf(
					/* translators: %s: post type slug */
					__( 'A post type with the slug "%s" already exists.', 'yt-for-wp-pro' ),
					$slug
				)
			);
		}

		// Reserved WordPress slugs.
		$reserved = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'action', 'author', 'order', 'theme' );
		if ( in_array( $slug, $reserved, true ) ) {
			return new \WP_Error( 'slug_reserved', __( 'That slug is reserved by WordPress.', 'yt-for-wp-pro' ) );
		}

		$types   = self::get_all();
		$types[] = array(
			'slug' => $slug,
			'name' => $name,
		);
		update_option( 'yt_for_wp_post_types', $types );

		// Flush rewrite rules so the new slug works.
		flush_rewrite_rules();

		return true;
	}

	/**
	 * Update a post type's display name (slug is immutable).
	 *
	 * @param string $slug The post type slug.
	 * @param string $name The new name.
	 * @return bool|\WP_Error
	 */
	public static function update_name( string $slug, string $name ) {
		$slug = sanitize_key( $slug );
		$name = sanitize_text_field( $name );
		if ( empty( $name ) ) {
			return new \WP_Error( 'invalid_input', __( 'Post type name cannot be empty.', 'yt-for-wp-pro' ) );
		}
		$types = self::get_all();
		foreach ( $types as &$type ) {
			if ( $type['slug'] === $slug ) {
				$type['name'] = $name;
				update_option( 'yt_for_wp_post_types', $types );
				return true;
			}
		}
		return new \WP_Error( 'not_found', __( 'Post type not found.', 'yt-for-wp-pro' ) );
	}

	/**
	 * Register the Video custom post type.
	 */
	public static function register_post_type() {
		$types = self::get_all();
		if ( empty( $types ) ) {
			return;
		}

		foreach ( $types as $type ) {
			$slug = sanitize_key( $type['slug'] );
			$name = sanitize_text_field( $type['name'] );

			$labels = array(
				'name'               => sprintf(
					/* translators: %s: plural post type name */
					__( '%ss', 'yt-for-wp-pro' ),
					$name
				),
				'singular_name'      => $name,
				'menu_name'          => sprintf(
					/* translators: %s: plural post type name */
					__( '%ss', 'yt-for-wp-pro' ),
					$name
				),
				'add_new'            => sprintf(
					/* translators: %s: singular post type name */
					__( 'Add New %s', 'yt-for-wp-pro' ),
					$name
				),
				'add_new_item'       => sprintf(
					/* translators: %s: singular post type name */
					__( 'Add New %s', 'yt-for-wp-pro' ),
					$name
				),
				'edit_item'          => sprintf(
					/* translators: %s: singular post type name */
					__( 'Edit %s', 'yt-for-wp-pro' ),
					$name
				),
				'new_item'           => sprintf(
					/* translators: %s: singular post type name */
					__( 'New %s', 'yt-for-wp-pro' ),
					$name
				),
				'view_item'          => sprintf(
					/* translators: %s: singular post type name */
					__( 'View %s', 'yt-for-wp-pro' ),
					$name
				),
				'search_items'       => sprintf(
					/* translators: %s: plural post type name */
					__( 'Search %ss', 'yt-for-wp-pro' ),
					$name
				),
				'not_found'          => sprintf(
					/* translators: %s: lowercase plural post type name */
					__( 'No %ss found', 'yt-for-wp-pro' ),
					strtolower( $name )
				),
				'not_found_in_trash' => sprintf(
					/* translators: %s: lowercase plural post type name */
					__( 'No %ss found in trash', 'yt-for-wp-pro' ),
					strtolower( $name )
				),
			);

			$args = array(
				'labels'          => $labels,
				'public'          => true,
				'has_archive'     => true,
				'show_in_rest'    => true,
				'supports'        => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon'       => 'dashicons-video-alt3',
				'rewrite'         => array( 'slug' => $slug ),
				'capability_type' => 'post',
			);

			register_post_type( $slug, $args );
		}
	}

	/**
	 * Register the Playlist taxonomy.
	 */
	public static function register_taxonomy() {
		$types = self::get_all();
		if ( empty( $types ) ) {
			return;
		}

		foreach ( $types as $type ) {
			$slug          = sanitize_key( $type['slug'] );
			$taxonomy_slug = $slug . '-playlist';

			$labels = array(
				'name'          => __( 'Playlists', 'yt-for-wp-pro' ),
				'singular_name' => __( 'Playlist', 'yt-for-wp-pro' ),
				'search_items'  => __( 'Search Playlists', 'yt-for-wp-pro' ),
				'all_items'     => __( 'All Playlists', 'yt-for-wp-pro' ),
				'edit_item'     => __( 'Edit Playlist', 'yt-for-wp-pro' ),
				'view_item'     => __( 'View Playlist', 'yt-for-wp-pro' ),
				'update_item'   => __( 'Update Playlist', 'yt-for-wp-pro' ),
				'add_new_item'  => __( 'Add New Playlist', 'yt-for-wp-pro' ),
				'new_item_name' => __( 'New Playlist Name', 'yt-for-wp-pro' ),
				'menu_name'     => __( 'Playlists', 'yt-for-wp-pro' ),
			);

			$args = array(
				'labels'       => $labels,
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $taxonomy_slug ),
			);

			register_taxonomy( $taxonomy_slug, array( $slug ), $args );
		}
	}
}
