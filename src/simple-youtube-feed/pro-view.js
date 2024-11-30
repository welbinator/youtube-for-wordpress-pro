import { addAction } from '@wordpress/hooks';

// Define cache in the global scope of this module
const cache = {};

addAction('yt_for_wp_simple_feed_view', 'yt-for-wp-pro', async (container, attributes) => {
    const {
        channelId,
        layout,
        maxVideos,
        enableSearch = container.dataset.enableSearch === 'true',
        enablePlaylistFilter = container.dataset.enablePlaylistFilter === 'true',
    } = attributes;

    const renderVideos = YT_FOR_WP.renderVideos || function () {
        console.error('renderVideos function is not available.');
    };

    const fetchVideos = YT_FOR_WP.fetchVideos || async function () {
        console.error('fetchVideos function is not available.');
        return [];
    };

    const apiUrlBase = `https://www.googleapis.com/youtube/v3`;
    const apiKey = YT_FOR_WP.apiKey;

    const effectiveChannelId = channelId || YT_FOR_WP.channelId;

    let playlists = [];
    let nextPageToken = null;
    let playlistCache = {};

    // Function to fetch playlists
    async function fetchPlaylists(loadMore = false) {
        if (playlistCache[effectiveChannelId] && !loadMore) {
            return playlistCache[effectiveChannelId]; // Return cached playlists
        }

        let apiUrl = `${apiUrlBase}/playlists?part=snippet&channelId=${effectiveChannelId}&key=${apiKey}&maxResults=50`;
        if (nextPageToken && loadMore) {
            apiUrl += `&pageToken=${nextPageToken}`;
        }

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

            playlistCache[effectiveChannelId] = playlists; // Cache playlists
        } catch (error) {
            console.error('Error fetching playlists:', error);
        }
    }

    // Render search and filter UI
    function renderSearchAndFilterUI() {
        if (container.querySelector('.youtube-search-container')) {
            return;
        }

        const searchContainer = document.createElement('div');
        searchContainer.classList.add('youtube-search-container');

        // Playlist dropdown
        if (enablePlaylistFilter && playlists.length > 0) {
            const dropdown = document.createElement('select');
            dropdown.classList.add('youtube-playlist-dropdown');

            playlists.forEach(({ id, snippet }) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = snippet.title;
                dropdown.appendChild(option);
            });

            dropdown.addEventListener('change', async () => {
                try {
                    const playlistId = dropdown.value;
                    const searchQuery = container.querySelector('.youtube-search-bar')?.value.trim() || '';
                    const cacheKey = `${container.id}-${channelId}-${maxVideos}-${searchQuery}-${playlistId}`;

                    // Clear cache for this interaction
                    delete cache[cacheKey];

                    const videos = await fetchVideos(container, searchQuery, playlistId);
                    renderVideos(container, videos, layout);
                } catch (error) {
                    console.error('Error handling playlist dropdown change:', error);
                }
            });

            searchContainer.appendChild(dropdown);
        }

        // Search bar
        if (enableSearch) {
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
                try {
                    const searchQuery = searchBar.value.trim();
                    const playlistId = container.querySelector('.youtube-playlist-dropdown')?.value || '';
                    const cacheKey = `${container.id}-${channelId}-${maxVideos}-${searchQuery}-${playlistId}`;

                    // Clear cache for this interaction
                    delete cache[cacheKey];

                    const videos = await fetchVideos(container, searchQuery, playlistId);
                    renderVideos(container, videos, layout);
                } catch (error) {
                    console.error('Error handling search button click:', error);
                }
            });

            searchContainer.appendChild(searchBar);
            searchContainer.appendChild(searchButton);
        }

        if (searchContainer.children.length > 0) {
            container.prepend(searchContainer);
        }
    }

    // Initial fetch for playlists and render the UI
    try {
        if (container.getAttribute('data-initialized')) {
            console.log(`Container #${container.id} already initialized.`);
            return;
        }

        container.setAttribute('data-initialized', 'true');
        await fetchPlaylists();
        renderSearchAndFilterUI();

        if (!enablePlaylistFilter) {
            const videos = await fetchVideos(container);
            renderVideos(container, videos, layout);
        }
    } catch (error) {
        console.error('Error during initialization:', error);
    }
});
