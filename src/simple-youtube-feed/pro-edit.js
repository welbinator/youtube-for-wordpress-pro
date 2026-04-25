import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';

// Build channel options from ytForWPProEditor global (injected by PHP).
const getChannelOptions = () => {
    const channels = ( window.ytForWPProEditor && window.ytForWPProEditor.channels ) || [];
    const options = [ { label: '— Use default from settings —', value: '' } ];
    channels.forEach( ( ch ) => {
        options.push( { label: ch.name || ch.id, value: ch.id } );
    } );
    return options;
};

// Extend block attributes
const addProAttributes = (settings, name) => {
    if (name === 'yt-for-wp/simple-youtube-feed') {
        settings.attributes = {
            ...settings.attributes,
            enableSearch: { type: 'boolean', default: false },
            enablePlaylistFilter: { type: 'boolean', default: false },
            channelId: { type: 'string', default: '' },
        };
    }
    return settings;
};

// Add Pro controls to the Inspector panel
const withProControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name === 'yt-for-wp/simple-youtube-feed') {
            return (
                <>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <PanelBody title="Advanced Features">
                            <SelectControl
                                label="YouTube Channel"
                                value={attributes.channelId}
                                options={getChannelOptions()}
                                onChange={(newChannelId) => setAttributes({ channelId: newChannelId })}
                                help="Select a channel, or leave as default to use the channel from Settings."
                            />
                            <ToggleControl
                                label="Enable User Search"
                                checked={attributes.enableSearch}
                                onChange={(value) => setAttributes({ enableSearch: value })}
                            />
                            <ToggleControl
                                label="Enable Playlist Filter"
                                checked={attributes.enablePlaylistFilter}
                                onChange={(value) => setAttributes({ enablePlaylistFilter: value })}
                            />
                        </PanelBody>
                    </InspectorControls>
                </>
            );
        }

        return <BlockEdit {...props} />;
    };
}, 'withProControls');

// Apply filters to extend the block
addFilter('blocks.registerBlockType', 'yt-for-wp-pro/add-pro-attributes', addProAttributes);
addFilter('editor.BlockEdit', 'yt-for-wp-pro/add-pro-controls', withProControls);
