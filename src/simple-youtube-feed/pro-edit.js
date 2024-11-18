import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

// Extend block attributes
const addProAttributes = (settings, name) => {
    if (name === 'yt-for-wp/simple-youtube-feed') {
        settings.attributes = {
            ...settings.attributes,
            enableSearch: { type: 'boolean', default: false },
            enablePlaylistFilter: { type: 'boolean', default: false },
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
                        <PanelBody title="Pro Features">
                            <ToggleControl
                                label="Enable User Search (Pro)"
                                checked={attributes.enableSearch}
                                onChange={(value) => setAttributes({ enableSearch: value })}
                            />
                            <ToggleControl
                                label="Enable Playlist Filter (Pro)"
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
