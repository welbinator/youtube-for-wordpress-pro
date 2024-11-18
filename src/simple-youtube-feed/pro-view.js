import { addAction } from '@wordpress/hooks';

addAction('yt_for_wp_simple_feed_view', 'yt-for-wp-pro', async (container, { channelId, layout, maxVideos }) => {
    const apiUrlBase = `https://www.googleapis.com/youtube/v3`;
    const apiKey = YT_FOR_WP.apiKey;

    let playlists = [];
    let nextPageToken = null;

    // Function to fetch playlists
    async function fetchPlaylists(loadMore = false) {
        let apiUrl = `${apiUrlBase}/playlists?part=snippet&channelId=${channelId}&key=${apiKey}&maxResults=50`;
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

            if (data.items) {
                playlists = loadMore
                    ? [...playlists, ...data.items]
                    : [{ id: '', snippet: { title: 'All Videos' } }, ...data.items];
            }
            nextPageToken = data.nextPageToken || null;
            renderLoadMoreButton();
        } catch (error) {
            console.error('Error fetching playlists:', error);
        }
    }

    // Function to fetch videos
    async function fetchVideos(searchQuery = '', playlistId = '') {
        let apiUrl = `${apiUrlBase}/search?part=snippet&type=video&channelId=${channelId}&maxResults=${maxVideos}&key=${apiKey}`;
        if (playlistId) {
            apiUrl = `${apiUrlBase}/playlistItems?part=snippet&maxResults=${maxVideos}&playlistId=${playlistId}&key=${apiKey}`;
        }
        if (searchQuery) {
            apiUrl += `&q=${encodeURIComponent(searchQuery)}`;
        }

        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.error) {
                console.error('YouTube API Error:', data.error);
                return [];
            }

            return data.items || [];
        } catch (error) {
            console.error('Error fetching videos:', error);
            return [];
        }
    }

    // Render search and filter UI
    function renderSearchAndFilterUI() {
        const filterContainer = document.createElement('div');
        filterContainer.classList.add('youtube-filter-container');

        // Playlist dropdown
        if (playlists.length > 0) {
            const dropdown = document.createElement('select');
            dropdown.classList.add('youtube-playlist-dropdown');

            playlists.forEach(({ id, snippet }) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = snippet.title;
                dropdown.appendChild(option);
            });

            dropdown.addEventListener('change', async () => {
                const playlistId = dropdown.value;
                const searchQuery = document.querySelector('.youtube-search-bar')?.value.trim() || '';
                const videos = await fetchVideos(searchQuery, playlistId);
                renderVideos(container, videos, layout);
            });

            filterContainer.appendChild(dropdown);
        }

        // Search bar
        const searchContainer = document.createElement('div');
        searchContainer.classList.add('youtube-search-container');

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
            const keyword = searchBar.value.trim();
            const playlistId = document.querySelector('.youtube-playlist-dropdown')?.value || '';
            const videos = await fetchVideos(keyword, playlistId);
            renderVideos(container, videos, layout);
        });

        searchContainer.appendChild(searchBar);
        searchContainer.appendChild(searchButton);
        filterContainer.appendChild(searchContainer);

        container.appendChild(filterContainer);
    }

    // Render the "Load More" button
    function renderLoadMoreButton() {
        const filterContainer = container.querySelector('.youtube-filter-container');
        if (!filterContainer) return;

        const existingButton = filterContainer.querySelector('.load-more-button');
        if (existingButton) existingButton.remove();

        if (nextPageToken) {
            const loadMoreButton = document.createElement('button');
            loadMoreButton.textContent = 'Load More Playlists';
            loadMoreButton.classList.add('load-more-button');

            loadMoreButton.addEventListener('click', () => fetchPlaylists(true));
            filterContainer.appendChild(loadMoreButton);
        }
    }

    // Initial fetch for playlists and render the UI
    await fetchPlaylists();
    renderSearchAndFilterUI();
});
