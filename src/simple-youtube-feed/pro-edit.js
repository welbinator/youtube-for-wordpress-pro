import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';

// Extend block attributes
const addProAttributes = (settings, name) => {
    if (name === 'yt-for-wp/simple-youtube-feed') {
        settings.attributes = {
            ...settings.attributes,
            enableSearch: { type: 'boolean', default: false },
            enablePlaylistFilter: { type: 'boolean', default: false },
            channelId: { type: 'string', default: '' },
            contentTypes: {
                type: 'object',
                default: { standard: true, short: true, live: true },
            }, // Add Content Types as a Pro attribute
        };
        console.log('Pro Attributes Added:', settings.attributes);
    }
    return settings;
};

// Add Pro controls to the Inspector panel
const withProControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name === 'yt-for-wp/simple-youtube-feed') {
            const { contentTypes = { standard: true, short: true, live: true } } = attributes;

            console.log('Current Attributes in Editor:', attributes);

            return (
                <>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <PanelBody title="Pro Features">
                            <TextControl
                                label="YouTube Channel ID (Pro)"
                                value={attributes.channelId}
                                onChange={(newChannelId) => {
                                    setAttributes({ channelId: newChannelId });
                                    console.log('Channel ID Updated:', newChannelId);
                                }}
                                help="Leave blank to use the default Channel ID from settings."
                            />
                            <ToggleControl
                                label="Enable User Search (Pro)"
                                checked={attributes.enableSearch}
                                onChange={(value) => {
                                    setAttributes({ enableSearch: value });
                                    console.log('Enable Search Updated:', value);
                                }}
                            />
                            <ToggleControl
                                label="Enable Playlist Filter (Pro)"
                                checked={attributes.enablePlaylistFilter}
                                onChange={(value) => {
                                    setAttributes({ enablePlaylistFilter: value });
                                    console.log('Enable Playlist Filter Updated:', value);
                                }}
                            />
                            <PanelBody title="Content Types">
                                <ToggleControl
                                    label="Standard"
                                    checked={contentTypes.standard}
                                    onChange={(checked) => {
                                        const updatedContentTypes = { ...contentTypes, standard: checked };
                                        setAttributes({ contentTypes: updatedContentTypes });
                                        console.log('Content Types Updated:', updatedContentTypes);
                                    }}
                                />
                                <ToggleControl
                                    label="Short"
                                    checked={contentTypes.short}
                                    onChange={(checked) => {
                                        const updatedContentTypes = { ...contentTypes, short: checked };
                                        setAttributes({ contentTypes: updatedContentTypes });
                                        console.log('Content Types Updated:', updatedContentTypes);
                                    }}
                                />
                                <ToggleControl
                                    label="Live"
                                    checked={contentTypes.live}
                                    onChange={(checked) => {
                                        const updatedContentTypes = { ...contentTypes, live: checked };
                                        setAttributes({ contentTypes: updatedContentTypes });
                                        console.log('Content Types Updated:', updatedContentTypes);
                                    }}
                                />
                            </PanelBody>
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
