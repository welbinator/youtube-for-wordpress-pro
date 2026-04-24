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
		add_action( 'init', array( __CLASS__, 'maybe_migrate_legacy' ), 1 ); // Priority 1 = before register_post_type.
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
	}

	/**
	 * Check if the post type has been created.
	 *
	 * @return bool True if created, false otherwise.
	 */
	public static function is_created(): bool {
		return (bool) get_option( 'yt_for_wp_post_type_created', false );
	}

	/**
	 * Get the post type slug.
	 *
	 * @return string The post type slug.
	 */
	public static function get_slug(): string {
		return sanitize_key( get_option( 'yt_for_wp_post_type_slug', '' ) );
	}

	/**
	 * Get the post type name.
	 *
	 * @return string The post type name.
	 */
	public static function get_name(): string {
		return sanitize_text_field( get_option( 'yt_for_wp_post_type_name', 'Video' ) );
	}

	/**
	 * Migrate legacy installations to the new dynamic post type.
	 */
	public static function maybe_migrate_legacy() {
		if ( get_option( 'yt_for_wp_post_type_created' ) ) {
			return; // Already set up.
		}

		// Check if legacy posts exist.
		$legacy_posts = get_posts(
			array(
				'post_type'      => 'yt-4-wp-video',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $legacy_posts ) ) {
			update_option( 'yt_for_wp_post_type_slug', 'yt-4-wp-video' );
			update_option( 'yt_for_wp_post_type_name', 'Video' );
			update_option( 'yt_for_wp_post_type_created', true );
		}
	}

	/**
	 * Create the video post type with the given slug and name.
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
			return new \WP_Error( 'slug_reserved', __( 'That slug is reserved by WordPress. Please choose a different one.', 'yt-for-wp-pro' ) );
		}

		update_option( 'yt_for_wp_post_type_slug', $slug );
		update_option( 'yt_for_wp_post_type_name', $name );
		update_option( 'yt_for_wp_post_type_created', true );

		// Flush rewrite rules so the new slug works.
		flush_rewrite_rules();

		return true;
	}

	/**
	 * Register the Video custom post type.
	 */
	public static function register_post_type() {
		if ( ! self::is_created() ) {
			return;
		}

		$slug = self::get_slug();
		$name = self::get_name();

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

	/**
	 * Register the Playlist taxonomy.
	 */
	public static function register_taxonomy() {
		if ( ! self::is_created() ) {
			return;
		}

		$slug          = self::get_slug();
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
