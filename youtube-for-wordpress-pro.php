<?php
/**
 * Plugin Name: YouTube for WordPress Pro
 * Plugin URI: https://jameswelbes.com/youtube-for-wordpress
 * Description: A complete toolkit for integrating YouTube functionalities into WordPress with premium features.
 * Version: 2.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: James Welbes
 * Author URI: https://jameswelbes.com
 * Text Domain: yt-for-wp-pro
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include plugin.php for is_plugin_active() and deactivate_plugins()
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check if the old free plugin is active and automatically deactivate it
$old_free_plugin = 'toolkit-integration-for-youtube/youtube-for-wordpress.php';
if (is_plugin_active($old_free_plugin)) {
    // Deactivate the old free plugin immediately
    deactivate_plugins($old_free_plugin);
    
    // Show a success notice
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e('YouTube for WordPress Pro activated successfully!', 'yt-for-wp-pro'); ?></strong><br>
                <?php esc_html_e('The free "Toolkit Integration for Youtube" plugin has been automatically deactivated as all its features are now included in the Pro version.', 'yt-for-wp-pro'); ?>
            </p>
        </div>
        <?php
    });
}

// Check if old plugin constants are already defined (in case it loaded before us)
if (defined('YOUTUBE_FOR_WP_ACTIVE') && defined('YT_FOR_WP_PATH') && YT_FOR_WP_PATH !== plugin_dir_path(__FILE__)) {
    // Old plugin already loaded this request, but we've deactivated it for next page load
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('Please refresh the page', 'yt-for-wp-pro'); ?></strong><br>
                <?php esc_html_e('The old free plugin has been deactivated. Please refresh this page to complete the activation of YouTube for WordPress Pro.', 'yt-for-wp-pro'); ?>
            </p>
        </div>
        <?php
    });
    return; // Stop loading this plugin until next request
}

// Define plugin constants for Pro version
define('YOUTUBEFORWORDPRESS_PRO', __FILE__);
define('YOUTUBE_FOR_WP_PRO_VERSION', '2.0.0');
define('YT_FOR_WP_PRO_PATH', plugin_dir_path(__FILE__));
define('YT_FOR_WP_PRO_URL', plugin_dir_url(__FILE__));

// Define constants for core functionality (formerly free plugin)
define('YOUTUBE_FOR_WP_VERSION', '2.0.0');
define('YT_FOR_WP_PATH', plugin_dir_path(__FILE__));
define('YT_FOR_WP_URL', plugin_dir_url(__FILE__));
define('YT_FOR_WP_MIN_WP_VERSION', '5.8');
define('YT_FOR_WP_MIN_PHP_VERSION', '7.4');

// Indicate that core functionality is active (for backward compatibility)
if (!defined('YOUTUBE_FOR_WP_ACTIVE')) {
    define('YOUTUBE_FOR_WP_ACTIVE', true);
}

/**
 * Check PHP and WordPress versions before activation, and deactivate old free plugin
 */
function yt_for_wp_pro_activation_check() {
    $errors = [];
    $notices = [];
    
    // Check if the old free plugin is active and deactivate it
    $old_free_plugin = 'toolkit-integration-for-youtube/youtube-for-wordpress.php';
    if (is_plugin_active($old_free_plugin)) {
        deactivate_plugins($old_free_plugin);
        $notices[] = esc_html__('The free "Toolkit Integration for Youtube" plugin has been automatically deactivated as its functionality is now included in YouTube for WordPress Pro.', 'yt-for-wp-pro');
    }
    
    if (version_compare(PHP_VERSION, YT_FOR_WP_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            esc_html__('YouTube for WordPress Pro requires PHP version %s or higher.', 'yt-for-wp-pro'),
            esc_html(YT_FOR_WP_MIN_PHP_VERSION)
        );
    }

    if (version_compare($GLOBALS['wp_version'], YT_FOR_WP_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            esc_html__('YouTube for WordPress Pro requires WordPress version %s or higher.', 'yt-for-wp-pro'),
            esc_html(YT_FOR_WP_MIN_WP_VERSION)
        );
    }

    if ($errors) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            implode('<br>', array_map('esc_html', $errors)),
            esc_html__('Plugin Activation Error', 'yt-for-wp-pro'),
            ['back_link' => true]
        );
    }

    // Display notice about deactivated free plugin
    if ($notices) {
        set_transient('yt_for_wp_pro_activation_notice', $notices, 30);
    }
}
register_activation_hook(__FILE__, 'yt_for_wp_pro_activation_check');

/**
 * Display admin notice after activation if old plugin was deactivated
 */
function yt_for_wp_pro_activation_notice() {
    $notices = get_transient('yt_for_wp_pro_activation_notice');
    if ($notices) {
        foreach ($notices as $notice) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
        }
        delete_transient('yt_for_wp_pro_activation_notice');
    }
}
add_action('admin_notices', 'yt_for_wp_pro_activation_notice');

/**
 * Load text domain for internationalization
 */
function yt_for_wp_pro_load_textdomain() {
    load_plugin_textdomain('yt-for-wp-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'yt_for_wp_pro_load_textdomain');

// Include EDD Licensing
if (file_exists(plugin_dir_path(__FILE__) . 'EDD_Licensing.php')) {
    require plugin_dir_path(__FILE__) . 'EDD_Licensing.php';
}

// Include core functionality (formerly free plugin features)
if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/admin-settings.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/admin-settings.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'blocks/simple-youtube-feed/simple-youtube-feed.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'blocks/simple-youtube-feed/simple-youtube-feed.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'blocks/youtube-live/youtube-live.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'blocks/youtube-live/youtube-live.php';
}

// Include Pro features
if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/simple-youtube-feed/pro-save.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/simple-youtube-feed/pro-save.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/youtube-live/pro-save.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/youtube-live/pro-save.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/pro-settings.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/pro-settings.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/ajax-handlers.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/ajax-handlers.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/functions.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/functions.php';
}

if (file_exists(YT_FOR_WP_PRO_PATH . 'includes/pro-features/class-video-post-type.php')) {
    require_once YT_FOR_WP_PRO_PATH . 'includes/pro-features/class-video-post-type.php';
}

// Include GitHub updater if available
if (file_exists(YT_FOR_WP_PRO_PATH . 'github-update.php')) {
    include YT_FOR_WP_PRO_PATH . 'github-update.php';
}

// Initialize the Video Post Type
if (class_exists('YouTubeForWPPro\VideoCPT\Video_Post_Type')) {
    \YouTubeForWPPro\VideoCPT\Video_Post_Type::init();
}

// Initialize Pro features
add_action('plugins_loaded', function () {
    do_action('yt_for_wp_pro_loaded');
});

// Register core admin menu
add_action('admin_menu', function() {
    // Only add menu if the render function exists
    if (function_exists('YouTubeForWP\Admin\Settings\render_settings_page')) {
        add_menu_page(
            __('YT for WP', 'yt-for-wp-pro'),
            __('YT for WP', 'yt-for-wp-pro'),
            'manage_options',
            'youtube-for-wordpress-settings',
            'YouTubeForWP\Admin\Settings\render_settings_page',
            'dashicons-video-alt3',
            20
        );
    }
});

// Enqueue core block assets (formerly from free plugin)
add_action('enqueue_block_assets', function() {
    // Check if the get_api_key function exists
    $api_key = function_exists('YouTubeForWP\Admin\Settings\get_api_key') 
        ? \YouTubeForWP\Admin\Settings\get_api_key() 
        : '';
    $channel_id = get_option('yt_for_wp_channel_id', '');
    $localize_data = [
        'channelId' => $channel_id,
        'apiKey'    => $api_key,
        'restUrl'   => rest_url('youtube-for-wordpress/v1/'),
        'nonce'     => wp_create_nonce('wp_rest'),
    ];

    // Enqueue front-end assets
    if (!is_admin()) {
        // Conditionally enqueue Swiper CSS
        if (!wp_style_is('swiper-css', 'enqueued')) {
            wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', [], '8.4.7');
        }

        // Conditionally enqueue Swiper JS
        if (!wp_script_is('swiper-js', 'enqueued')) {
            wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', [], '8.4.7', true);
        }

        // Enqueue plugin script with Swiper dependency
        wp_enqueue_script(
            'youtube-for-wordpress-simple-youtube-feed-view',
            plugins_url('build/simple-youtube-feed/view.js', __FILE__),
            ['swiper-js'],
            YOUTUBE_FOR_WP_PRO_VERSION,
            true
        );
        wp_localize_script('youtube-for-wordpress-simple-youtube-feed-view', 'YT_FOR_WP', $localize_data);
    } else {
        // Enqueue editor assets
        wp_enqueue_script(
            'youtube-for-wordpress-simple-youtube-feed-editor',
            plugins_url('build/simple-youtube-feed/index.js', __FILE__),
            ['wp-blocks', 'wp-element', 'wp-editor'],
            YOUTUBE_FOR_WP_PRO_VERSION,
            true
        );
        wp_localize_script('youtube-for-wordpress-simple-youtube-feed-editor', 'YT_FOR_WP', $localize_data);
    }
});

add_action('enqueue_block_editor_assets', function () {
    // Include the asset file generated by the build process
    $asset_file = include plugin_dir_path(__FILE__) . 'build/index.asset.php';

    wp_enqueue_script(
        'yt-for-wp-pro-block-editor',
        plugins_url('build/index.js', __FILE__), // Single bundled file
        $asset_file['dependencies'], // Properly extracted dependencies
        $asset_file['version'],      // Properly extracted version
        true
    );
});

add_action('wp_enqueue_scripts', function () {
    // Include the asset file generated by the build process
    $asset_file = include plugin_dir_path(__FILE__) . 'build/index.asset.php';

    wp_enqueue_script(
        'yt-for-wp-pro-view',
        plugins_url('build/index.js', __FILE__), // Same bundled file
        $asset_file['dependencies'], // Properly extracted dependencies
        $asset_file['version'],      // Properly extracted version
        true
    );

    // Get the current page template slug
    $template_slug = get_page_template_slug();

    // Enqueue CSS for the grid or list view templates
    if (in_array($template_slug, ['templates/videos-grid.php', 'templates/videos-list.php'], true)) {
        wp_enqueue_style(
            'yt-for-wp-pro-video-templates-css',
            YT_FOR_WP_PRO_URL . 'assets/css/video-templates.css',
            [],
            YOUTUBE_FOR_WP_PRO_VERSION
        );

        // Enqueue the JavaScript for modal functionality
        wp_enqueue_script(
            'yt-for-wp-pro-video-templates-js',
            YT_FOR_WP_PRO_URL . 'assets/js/video-templates.js',
            [],
            YOUTUBE_FOR_WP_PRO_VERSION,
            true
        );
    }
});



add_action('admin_enqueue_scripts', function ($hook_suffix) {
    // Only enqueue on the Import Videos page
    if ('yt-for-wp_page_yt-for-wp-import-videos' !== $hook_suffix) {
        return;
    }

    wp_enqueue_script(
        'yt-for-wp-pro-video-import',
        YT_FOR_WP_PRO_URL . 'assets/js/video-import.js',
        ['jquery'],
        YOUTUBE_FOR_WP_PRO_VERSION,
        true
    );
    

    wp_localize_script('yt-for-wp-pro-video-import', 'ytForWPPro', [
        'nonce' => wp_create_nonce('yt-for-wp-import-videos'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
});



/**
 * Adds the plugin license page to the admin menu.
 *
 * @return void
 */
// if (function_exists('gutenberg_market_licensing')) {
// 	return;
// } else {
    function license_page() {
        error_log("license page");
    
        add_settings_section(
            'youtubeforwordpress_pro_license',
            __( 'License' ),
            'YouTubeForWPPro\EDDLicensing\license_key_settings_section',
            YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE
        );
    
        add_settings_field(
            'youtubeforwordpress_pro_license_key',
            '<label for="youtubeforwordpress_pro_license_key">' . __( 'License Key' ) . '</label>',
            'YouTubeForWPPro\EDDLicensing\license_key_settings_field',
            YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE,
            'youtubeforwordpress_pro_license',
        );
    
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'License Options' ); ?></h2>
            <form method="post" action="options.php">
    
                <?php
                do_settings_sections( YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE );
                settings_fields( 'youtubeforwordpress_pro_license' );
                submit_button();
                ?>
    
            </form>
        <?php
    }

    /**
 * Adds Pro-specific admin menu items.
 */

add_action('admin_menu', function() {
    

   
    add_submenu_page(
        'youtube-for-wordpress-settings', // Ensure parent menu is the same
        __('License', 'yt-for-wp-pro'),
        __('License', 'yt-for-wp-pro'),
        'manage_options',
        'youtubeforwordpresspro-license',
        'YouTubeForWPPro\license_page' // Directly call the license page function
    );
    add_submenu_page(
        'youtube-for-wordpress-settings', // Parent slug (main menu item)
        __('Import Videos', 'yt-for-wp-pro'), // Page title
        __('Import Videos', 'yt-for-wp-pro'), // Menu title
        'manage_options', // Capability
        'yt-for-wp-import-videos', // Menu slug
        'YouTubeForWPPro\Settings\render_import_videos_page' // Callback function
    );

}, 20);


// Register templates in the theme dropdown.
add_filter( 'theme_page_templates', __NAMESPACE__ . '\\add_custom_page_templates' );
add_filter( 'template_include', __NAMESPACE__ . '\\load_custom_page_template' );

/**
 * Add custom page templates to the page template dropdown.
 *
 * @param array $templates List of page templates.
 * @return array Modified list of templates.
 */
function add_custom_page_templates( $templates ) {
    $templates['templates/videos-grid.php'] = __( 'Videos Page Grid View', 'yt-for-wp-pro' );
    $templates['templates/videos-list.php'] = __( 'Videos Page List View', 'yt-for-wp-pro' );
    return $templates;
}

/**
 * Load the custom page template.
 *
 * @param string $template Path to the current template.
 * @return string Path to the new template if applicable.
 */
function load_custom_page_template( $template ) {
    if ( is_page() ) {
        $template_slug = get_page_template_slug();
        $plugin_template = YT_FOR_WP_PRO_PATH . $template_slug;

        if ( $template_slug && file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }

    return $template;
}

