import { addAction } from '@wordpress/hooks';

addAction('yt_for_wp_simple_feed_view', 'yt-for-wp-pro', async (container, attributes) => {
    const {
        channelId,
        layout,
        maxVideos,
        enableSearch = container.dataset.enableSearch === 'true',
        enablePlaylistFilter = container.dataset.enablePlaylistFilter === 'true',
        contentTypes = JSON.parse(container.dataset.contentTypes || '["standard", "short", "live"]'), // Ensure proper JSON parsing
    } = attributes;

    console.log('Pro Attributes:', {
        channelId,
        layout,
        maxVideos,
        enableSearch,
        enablePlaylistFilter,
        contentTypes,
    });

    // Prevent multiple initializations
    if (container.dataset.initialized === 'true') {
        console.log(`Container ${container.id} already initialized. Skipping.`);
        return;
    }
    container.dataset.initialized = 'true';

    // Ensure the required functions are available
    const renderVideos = YT_FOR_WP.renderVideos || function () {
        console.error('renderVideos function is not available.');
    };

    const fetchVideos = YT_FOR_WP.fetchVideos || async function () {
        console.error('fetchVideos function is not available.');
        return [];
    };

    let playlists = [];
    let nextPageToken = null;

    // Function to fetch playlists
    async function fetchPlaylists(loadMore = false) {
        const apiUrl = `https://www.googleapis.com/youtube/v3/playlists?part=snippet&channelId=${channelId}&key=${YT_FOR_WP.apiKey}&maxResults=50`;

        console.log('Fetching playlists from API:', apiUrl);

        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.error) {
                console.error('YouTube API Error:', data.error);
                return;
            }

            playlists = loadMore
                ? [...playlists, ...data.items]
                : [{ id: '', snippet: { title: 'All Videos' } }, ...data.items];

            nextPageToken = data.nextPageToken || null;
            console.log('Fetched Playlists:', playlists);
        } catch (error) {
            console.error('Error fetching playlists:', error);
        }
    }

    // Render search and filter UI
    function renderSearchAndFilterUI() {
        console.log('Rendering Search and Filter UI...');
        if (container.querySelector('.youtube-search-container')) {
            console.log('Search and Filter UI already exists, skipping...');
            return;
        }

        const searchContainer = document.createElement('div');
        searchContainer.classList.add('youtube-search-container');

        // Playlist dropdown
        if (enablePlaylistFilter && playlists.length > 0) {
            console.log('Rendering Playlist Filter Dropdown...');
            const dropdown = document.createElement('select');
            dropdown.classList.add('youtube-playlist-dropdown');

            playlists.forEach(({ id, snippet }) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = snippet.title;
                dropdown.appendChild(option);
            });

            dropdown.addEventListener('change', async () => {
                console.log('Playlist dropdown changed.');
                const playlistId = dropdown.value;
                const searchQuery = container.querySelector('.youtube-search-bar')?.value.trim() || '';
                const videos = await fetchVideos(container, searchQuery, playlistId);
                const filteredVideos = filterVideosByType(videos);
                renderVideos(container, filteredVideos, layout);
            });

            searchContainer.appendChild(dropdown);
        }

        // Search bar
        if (enableSearch) {
            console.log('Rendering Search Bar...');
            const searchBar = document.createElement('input');
            searchBar.type = 'text';
            searchBar.placeholder = 'Search videos';
            searchBar.classList.add('youtube-search-bar');

            const searchButton = document.createElement('button');
            searchButton.textContent = 'Search';
            searchButton.classList.add('youtube-search-button');

            searchBar.addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchButton.click();
                }
            });

            searchButton.addEventListener('click', async () => {
                console.log('Search button clicked.');
                const keyword = searchBar.value.trim();
                const playlistId = container.querySelector('.youtube-playlist-dropdown')?.value || '';
                const videos = await fetchVideos(container, keyword, playlistId);
                const filteredVideos = filterVideosByType(videos);
                renderVideos(container, filteredVideos, layout);
            });

            searchContainer.appendChild(searchBar);
            searchContainer.appendChild(searchButton);
        }

        if (searchContainer.children.length > 0) {
            console.log('Appending Search and Filter UI...');
            container.prepend(searchContainer);
        }
    }

    // Filter videos by selected content types
    function filterVideosByType(videos) {
        console.log('Filtering Videos by Type:', contentTypes);
        const filteredVideos = videos.filter((video) => {
            const videoType = getVideoType(video);
            console.log('Video Type:', videoType, 'Included:', contentTypes.includes(videoType));
            return contentTypes.includes(videoType);
        });
        console.log('Filtered Videos:', filteredVideos);
        return filteredVideos;
    }

    // Determine the type of a video
    function getVideoType(video) {
        if (video.snippet.liveBroadcastContent === 'live') {
            return 'live';
        }
        if (video.snippet.categoryId === '22') { // Example category ID for Shorts
            return 'short';
        }
        return 'standard';
    }

    // Initial fetch for playlists and render the UI
    try {
        console.log('Initializing playlists and videos...');
        await fetchPlaylists();
        renderSearchAndFilterUI();

        const videos = await fetchVideos(container);
        const filteredVideos = filterVideosByType(videos); // Apply content type filtering
        renderVideos(container, filteredVideos, layout);
    } catch (error) {
        console.error('Error during initialization:', error);
    }
});
