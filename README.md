# YouTube for WordPress Pro

A complete WordPress plugin for integrating YouTube content into your website. Display YouTube videos, create video archives, and manage your YouTube content directly from WordPress.

## Description

YouTube for WordPress Pro provides a comprehensive solution for integrating YouTube content into your WordPress site. The plugin combines intuitive Gutenberg blocks with powerful content management features, allowing you to showcase YouTube videos in various layouts while maintaining full control over your video library.

### Key Features

- **Simple YouTube Feed Block** - Display channel videos in a responsive carousel or grid layout
- **YouTube Live Block** - Show live streaming status and embed active livestreams
- **Video Custom Post Type** - Import and manage YouTube videos as WordPress posts
- **Bulk Video Import** - Automatically import videos from your YouTube channel with metadata
- **Custom Page Templates** - Pre-designed templates for video archives (grid and list views)
- **Playlist Management** - Organize videos using hierarchical playlist taxonomy
- **Search & Filtering** - Allow visitors to search and filter videos by playlist
- **Secure API Key Storage** - Encrypted API key management using Sodium encryption
- **Easy Digital Downloads Integration** - Built-in licensing system for updates and support

## Installation

1. Upload the `youtube-for-wordpress-pro` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **YT for WP** > **Settings** to configure your YouTube API key and channel ID
4. Start using the blocks in the Gutenberg editor or import your videos

## Configuration

### Getting Your YouTube API Key

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **YouTube Data API v3**
4. Create credentials (API Key)
5. Copy your API key

### Getting Your Channel ID

1. Go to your [YouTube Studio](https://studio.youtube.com/)
2. Click on **Settings** > **Channel** > **Advanced settings**
3. Copy your Channel ID
4. Alternatively, go to your channel page and copy the ID from the URL

### Plugin Setup

1. Go to **YT for WP** > **Settings** in your WordPress admin
2. Paste your YouTube API Key
3. Paste your Channel ID
4. Click **Save Changes**

The API key will be encrypted and securely stored in your WordPress database.

## Features & Usage

### 1. Simple YouTube Feed Block

The Simple YouTube Feed block displays videos from your YouTube channel in a beautiful, responsive layout.

**Features:**
- Multiple layout options (carousel, grid)
- Customizable number of videos to display
- Search functionality (Pro)
- Playlist filtering (Pro)
- Responsive design with Swiper carousel
- Thumbnail images with video titles
- Click to watch in modal or new page

**Usage:**
1. Add the **Simple YouTube Feed** block to any post or page
2. Configure the block settings in the right sidebar:
   - Number of videos to display
   - Enable/disable search (Pro feature)
   - Enable/disable playlist filter (Pro feature)
   - Select layout style
3. The block will automatically fetch and display videos from your configured channel

**Block Settings:**
- **Videos to Show**: Control how many videos appear
- **Enable Search**: Allow visitors to search your videos (Pro)
- **Enable Playlist Filter**: Let visitors filter by playlist (Pro)
- **Layout**: Choose carousel or grid display

### 2. YouTube Live Block

Display your channel's live streaming status and automatically embed active livestreams.

**Features:**
- Automatically detects live streams
- Shows "LIVE" indicator when streaming
- Embeds live video player
- Displays offline message when not streaming
- Customizable messaging

**Usage:**
1. Add the **YouTube Live** block to any post or page
2. The block automatically checks your channel for live streams
3. When you go live on YouTube, the block updates automatically
4. Viewers can watch your livestream directly on your WordPress site

### 3. Video Custom Post Type

Import and manage YouTube videos as WordPress posts with full metadata.

**Features:**
- Custom post type: `yt-4-wp-video`
- Hierarchical playlist taxonomy: `yt-4-wp-playlist`
- Automatic thumbnail import as featured images
- Stores video ID, URL, and publish date
- SEO-friendly URLs: `/yt-4-wp-video/video-title/`
- Full WordPress editor support

**Video Meta Fields:**
- `_yt_video_id` - YouTube video ID
- `_yt_video_url` - Embed URL format
- `_yt_published_at` - Original YouTube publish date

### 4. Bulk Video Import

Automatically import videos from your YouTube channel into WordPress.

**Features:**
- Import up to 50 videos at once
- Automatically creates posts with video metadata
- Downloads and sets featured images from YouTube thumbnails
- Maps videos to playlist taxonomy terms
- Prevents duplicate imports
- Preserves original YouTube publish dates
- Runs in background via AJAX

**Usage:**
1. Go to **YT for WP** > **Import Videos**
2. Set the number of videos to import (default: 50)
3. Click **Import Videos**
4. Wait for the import process to complete
5. View your imported videos in the **Videos** menu

**Import Process:**
1. Fetches all playlists from your channel
2. Creates taxonomy terms for each playlist
3. Retrieves video details from YouTube API
4. Checks for existing videos to prevent duplicates
5. Creates WordPress posts with full metadata
6. Downloads thumbnails and sets as featured images
7. Assigns videos to appropriate playlist terms

### 5. Custom Page Templates

Create dedicated video archive pages with pre-designed templates.

**Available Templates:**
- **Videos Page Grid View** - Displays videos in a responsive grid layout
- **Videos Page List View** - Shows videos in a vertical list format

**Usage:**
1. Create a new page in WordPress
2. In the **Page Attributes** panel, select **Template**
3. Choose either "Videos Page Grid View" or "Videos Page List View"
4. Publish the page
5. The template will automatically display all imported videos

**Template Features:**
- Responsive design
- Modal video player
- Automatic thumbnail display
- Video titles and metadata
- Click to play functionality
- Custom CSS and JavaScript included

### 6. Playlist Management

Organize your videos using the hierarchical playlist taxonomy.

**Features:**
- Hierarchical structure (parent/child playlists)
- Automatic creation during import
- Manual assignment to videos
- Filter videos by playlist
- SEO-friendly URLs: `/yt-4-wp-playlist/playlist-name/`

**Usage:**
1. Go to **Videos** > **Playlists**
2. View automatically imported playlists
3. Create new playlists manually
4. Assign videos to playlists
5. Create hierarchical relationships

### 7. License Management

Manage your plugin license for updates and support (EDD integration).

**Usage:**
1. Go to **YT for WP** > **License**
2. Enter your license key
3. Click **Activate License**
4. Receive automatic updates when available

## Technical Details

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- YouTube Data API v3 access
- Modern browser with JavaScript enabled

### Build System

The plugin uses `@wordpress/scripts` for building assets:

```bash
npm install          # Install dependencies
npm run start        # Development mode with watch
npm run build        # Production build
```

### File Structure

```
youtube-for-wordpress-pro/
├── blocks/                          # Core block definitions
│   ├── simple-youtube-feed/        # Feed block PHP registration
│   └── youtube-live/               # Live block PHP registration
├── build/                          # Compiled assets
│   ├── simple-youtube-feed/        # Feed block assets
│   ├── youtube-live/               # Live block assets
│   ├── css/                        # Swiper CSS
│   └── index.js                    # Pro extensions bundle
├── includes/                       # PHP functionality
│   ├── admin-settings.php          # Settings page & API key encryption
│   ├── ajax-handlers.php           # Video import AJAX endpoint
│   ├── functions.php               # Helper functions
│   └── pro-features/               # Pro-specific features
│       └── class-video-post-type.php
├── src/                            # Source JavaScript files
│   ├── index.js                    # Entry point for Pro extensions
│   ├── simple-youtube-feed/        # Pro feed enhancements
│   └── youtube-live/               # Pro live enhancements
├── templates/                      # Custom page templates
│   ├── videos-grid.php
│   ├── videos-list.php
│   └── single-video.php
├── assets/                         # Static assets
│   ├── css/
│   └── js/
└── youtube-for-wordpress-pro.php   # Main plugin file
```

### Hooks & Filters

**Actions:**
- `yt_for_wp_pro_loaded` - Fires when Pro features are initialized
- `init` - Used for CPT and taxonomy registration

**Filters:**
- `theme_page_templates` - Adds custom page templates
- `template_include` - Loads custom page templates

### API Integration

The plugin uses the YouTube Data API v3:
- **Playlists endpoint**: Fetches channel playlists
- **Search endpoint**: Retrieves channel videos
- **Videos endpoint**: Gets detailed video metadata
- **PlaylistItems endpoint**: Maps videos to playlists

All API calls use encrypted API keys stored via Sodium encryption.

## Frequently Asked Questions

### Do I need a YouTube account?

Yes, you need a YouTube channel to use this plugin. The plugin fetches and displays videos from your configured YouTube channel.

### Is the YouTube API free?

Yes, the YouTube Data API v3 is free for most use cases. Google provides a generous daily quota that should be sufficient for most websites.

### Can I display videos from multiple channels?

Currently, the plugin is designed to work with a single YouTube channel per WordPress installation.

### Will imported videos update automatically?

No, imported videos are static WordPress posts. To get new videos, run the import feature again. The plugin prevents duplicate imports.

### Can I customize the video display?

Yes, you can customize the appearance using CSS. The plugin uses standard WordPress classes and provides custom CSS classes for targeting specific elements.

### What happens if I deactivate the plugin?

The Video custom post type and imported videos will remain in your database but won't be visible until you reactivate the plugin.

## Support

For support, bug reports, or feature requests, please visit:
- Website: [https://jameswelbes.com/youtube-for-wordpress](https://jameswelbes.com/youtube-for-wordpress)
- Documentation: [Coming soon]

## Changelog

### 2.0.0 - November 27, 2025

**Major Release: Unified Plugin**

This release merges the free "Toolkit Integration for Youtube" plugin with the Pro version into a single, unified plugin containing all features.

**Added:**
- ✨ Integrated core Simple YouTube Feed block functionality
- ✨ Integrated core YouTube Live block functionality
- ✨ Added encrypted API key storage with Sodium encryption
- ✨ Added core admin settings page
- ✨ Included all dependencies (Swiper, Tailwind CSS, etc.)
- ✨ Added backward compatibility constants for smooth migration

**Changed:**
- 🔄 Removed dependency on separate free plugin
- 🔄 Updated plugin architecture to unified namespace structure
- 🔄 Bumped version to 2.0.0 to reflect major architectural change
- 🔄 Merged build configurations and package dependencies
- 🔄 Updated documentation to reflect unified structure

**Technical:**
- Combined `YouTubeForWP` and `YouTubeForWPPro` namespaces
- Dual constants support: `YT_FOR_WP_*` and `YT_FOR_WP_PRO_*`
- Single plugin file now bootstraps all features
- Build system includes both core blocks and Pro extensions
- Maintains EDD licensing for updates and support

**Migration Notes:**
- Users upgrading from v1.x should deactivate the free "Toolkit Integration for Youtube" plugin
- All settings and data are preserved during upgrade
- The plugin now includes all features previously split between free and Pro versions

---

### 1.0.6 - Prior Release

**Changed:**
- 🐛 Bug fixes and improvements
- 🔄 Enhanced compatibility with latest WordPress version

### 1.0.0 - Initial Release

**Added:**
- ✨ Video Custom Post Type with Playlist taxonomy
- ✨ Bulk video import from YouTube
- ✨ Custom page templates (grid and list views)
- ✨ Pro block extensions (search and playlist filtering)
- ✨ EDD licensing integration
- ✨ Modal video player
- ✨ Automatic thumbnail importing

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 James Welbes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

**Author:** James Welbes  
**Website:** [https://jameswelbes.com](https://jameswelbes.com)  
**Plugin URI:** [https://jameswelbes.com/youtube-for-wordpress](https://jameswelbes.com/youtube-for-wordpress)

**Dependencies:**
- WordPress Gutenberg Editor
- YouTube Data API v3
- Swiper.js for carousel functionality
- @wordpress/scripts for build tooling

---

Made with ❤️ for the WordPress community
