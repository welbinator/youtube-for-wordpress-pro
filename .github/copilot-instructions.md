# YouTube for WordPress Pro - AI Coding Instructions

## Architecture Overview

This is a **unified premium WordPress plugin** that combines core YouTube integration features with premium functionality. Previously split into free and Pro versions, it's now a single plugin with all features included.

### Core Components

**Unified Plugin Structure:**
- Entry point: `youtube-for-wordpress-pro.php`
- Version: 2.0.0+
- Combines core blocks (Simple YouTube Feed, YouTube Live) with premium features
- Single namespace architecture with mixed namespaces for different components

### Key Architectural Patterns

**Namespace Organization:**
```php
YouTubeForWP\                 // Core functionality namespace
├── Admin\Settings\           // Settings page and API key management
└── (block registration)      // Block initialization functions

YouTubeForWPPro\              // Premium features namespace
├── VideoCPT\                 // Custom post type functionality
├── Settings\                 // Pro admin settings pages
└── EDDLicensing\             // Easy Digital Downloads licensing

Global Namespace:             // Legacy and helper functions
├── ajax-handlers.php         // AJAX endpoints
└── functions.php             // Utility functions
```

**Constants Architecture:**
```php
// Pro plugin constants
YOUTUBE_FOR_WP_PRO_VERSION, YT_FOR_WP_PRO_PATH, YT_FOR_WP_PRO_URL

// Core functionality constants (backward compatibility)
YOUTUBE_FOR_WP_VERSION, YT_FOR_WP_PATH, YT_FOR_WP_URL
YOUTUBE_FOR_WP_ACTIVE         // Always true (for backward compatibility)
```

## Custom Post Type Architecture

The plugin registers a **Video CPT** (`yt-4-wp-video`) with a **Playlist taxonomy** (`yt-4-wp-playlist`):

```php
// CPT: yt-4-wp-video
// Taxonomy: yt-4-wp-playlist (hierarchical)
// Slug patterns: /yt-4-wp-video/, /yt-4-wp-playlist/
```

**Meta Fields Pattern:**
- `_yt_video_id` - YouTube video ID
- `_yt_video_url` - Embed URL format: `https://www.youtube.com/embed/{video_id}`
- `_yt_published_at` - Original YouTube publish date
- Featured images downloaded from YouTube thumbnails

## YouTube API Integration

**AJAX Video Import Flow** (`includes/ajax-handlers.php`):
1. Fetch channel playlists → Create/map taxonomy terms
2. Search channel videos → Get video IDs
3. Fetch video details → Get full metadata
4. For each video:
   - Check for duplicates via `_yt_video_id` meta query
   - Create CPT post with YouTube publish date
   - Download thumbnail as featured image
   - Check playlist membership for each playlist
   - Assign playlist terms

**API Credentials:** Retrieved from free plugin's options (`yt_for_wp_channel_id`, `yt_for_wp_api_key`)

## Build System & Asset Management

**Build Configuration:**
```bash
npm run build   # Production build using @wordpress/scripts + copy Swiper CSS
npm run start   # Development mode with watch
```

**Dual Build System:**

1. **Core Blocks** (`blocks/` → `build/simple-youtube-feed/`, `build/youtube-live/`)
   - Each block has its own build directory with `index.js`, `view.js`, `block.json`
   - Registered via `register_block_type_from_metadata()`
   - Uses Swiper for carousel functionality (copied to `build/css/`)

2. **Pro Extensions** (`src/` → `build/index.js`)
   - Entry point: `src/index.js` imports all Pro block extensions
   - `src/simple-youtube-feed/pro-edit.js` & `pro-view.js` - Search & playlist filters
   - `src/youtube-live/pro-edit.js` & `pro-view.js` - Additional live features
   - Single bundled file extends core blocks via WordPress hooks

**Asset Loading Pattern:**

*Core blocks (per block):*
```php
// Automatic via block.json in build directories
register_block_type_from_metadata(plugin_dir_path(__FILE__) . '../../build/simple-youtube-feed');
```

*Pro extensions (global):*
```php
$asset_file = include plugin_dir_path(__FILE__) . 'build/index.asset.php';
wp_enqueue_script('yt-for-wp-pro-block-editor', 'build/index.js', $asset_file['dependencies']);
```

*External dependencies:*
- Swiper CSS/JS loaded conditionally from CDN on frontend
- Swiper is a dependency for the YouTube Feed carousel view

## EDD Licensing System

**Constants to Update** (in `EDD_Licensing.php`):
- `YOUTUBEFORWORDPRESSPRO_STORE_URL` - License server
- `YOUTUBEFORWORDPRESSPRO_ITEM_ID` - Product ID (currently 226)
- `YOUTUBEFORWORDPRESSPRO_ITEM_NAME` - Product name

**Options:**
- `youtubeforwordpress_pro_license_key` - License key
- `youtubeforwordpress_pro_license_status` - Validation state

**Updater:** Uses `EDD_SL_Plugin_Updater` class for automatic updates from license server.

## Admin Pages & Settings

**Page Registration:**
- Import Videos page: Hook suffix `yt-for-wp_page_yt-for-wp-import-videos`
- Admin scripts conditionally loaded based on `$hook_suffix`

**Settings Structure:**
- Extends free plugin's settings menu
- Uses WordPress Settings API with option groups
- License settings: `youtubeforwordpress_pro_license` option group

## Template System

**Custom Page Templates:**
- `templates/videos-grid.php` - Grid layout for videos archive
- `templates/videos-list.php` - List layout for videos archive
- `templates/single-video.php` - Single video display

**Conditional Asset Loading:**
```php
if (in_array($template_slug, ['templates/videos-grid.php', 'templates/videos-list.php'], true)) {
    wp_enqueue_style('yt-for-wp-pro-video-templates-css');
    wp_enqueue_script('yt-for-wp-pro-video-templates-js');
}
```

## Core Features Integration

**Admin Settings:**
- Settings page callback: `YouTubeForWP\Admin\Settings\render_settings_page()`
- API Key encryption using Sodium (`API_Key_Handler` class)
- Stored options: `yt_for_wp_encrypted_api_key`, `yt_for_wp_encryption_key`, `yt_for_wp_channel_id`
- Helper: `YouTubeForWP\Admin\Settings\get_api_key()` for decrypted access

**Block Registration:**
- Core blocks registered via `blocks/simple-youtube-feed/simple-youtube-feed.php`
- Pro extensions loaded via hooks in `src/` (uses `addFilter` from `@wordpress/hooks`)
- Pro features add controls without modifying core block code

## Development Workflow

**Version Updates:**
Update in four places:
1. Plugin header comment in `youtube-for-wordpress-pro.php`
2. `YOUTUBE_FOR_WP_PRO_VERSION` constant (Pro version)
3. `YOUTUBE_FOR_WP_VERSION` constant (Core version - keep in sync)
4. `package.json` version

**Building Assets:**
```bash
npm install                    # Install all dependencies including Swiper
npm run build                  # Build all blocks + copy Swiper CSS
npm run start                  # Watch mode for development
```

**Testing Video Import:**
- Requires configured API key and channel ID in free plugin
- Uses nonce: `yt-for-wp-import-videos`
- AJAX action: `yt_for_wp_pro_import_videos`
- Returns WP_Query to check duplicates before importing

**Path Constants:**
- `YT_FOR_WP_PRO_PATH` / `YT_FOR_WP_PATH` - Absolute filesystem path (aliased)
- `YT_FOR_WP_PRO_URL` / `YT_FOR_WP_URL` - Plugin URL for assets (aliased)
- `YOUTUBEFORWORDPRESS_PRO` - Main plugin file reference

**Migration Notes:**
- Plugin now includes all core and Pro features in one
- Backward compatibility maintained via `YOUTUBE_FOR_WP_ACTIVE` constant
- Old free plugin (`toolkit-integration-for-youtube`) no longer needed
- No dependency checks - all features available immediately on activation

## Common Patterns

**Image Download Helper:**
```php
yt_for_wp_set_post_thumbnail_from_url($post_id, $thumbnail_url);
// Generates unique filename: {post_id}-{original_name}
// Auto-generates attachment metadata
```

**Plugin Dependency Check:**
```php
if (!is_free_version_active()) {
    return; // Exit early if free plugin not active
}
```

**AJAX Security:**
```php
check_ajax_referer('yt-for-wp-import-videos', '_ajax_nonce');
wp_send_json_success($message); // or wp_send_json_error()
```
