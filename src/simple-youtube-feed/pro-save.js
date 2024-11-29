import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const {
        layout,
        maxVideos,
        selectedPlaylist,
        enableSearch,
        enablePlaylistFilter,
        channelId,
        contentTypes,
    } = attributes;

    return (
        <div
            {...useBlockProps.save()}
            data-layout={layout}
            data-max-videos={maxVideos}
            data-selected-playlist={selectedPlaylist}
            data-enable-search={enableSearch}
            data-enable-playlist-filter={enablePlaylistFilter}
            data-channel-id={channelId}
            data-content-types={JSON.stringify(contentTypes)} // Serialize contentTypes
        ></div>
    );
}
