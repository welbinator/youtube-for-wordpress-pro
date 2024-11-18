import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

const addProAttributes = (settings, name) => {
    if (name === 'yt-for-wp/youtube-live') {
        settings.attributes = {
            ...settings.attributes,
            channelId: { type: 'string', default: '' },
        };
    }
    return settings;
};

const withProControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { attributes, setAttributes, name } = props;

        if (name === 'yt-for-wp/youtube-live') {
            return (
                <>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <PanelBody title="Pro Features">
                            <TextControl
                                label="YouTube Channel ID (Pro)"
                                value={attributes.channelId}
                                onChange={(value) => setAttributes({ channelId: value })}
                                help="Leave blank to use the default Channel ID from settings."
                            />
                        </PanelBody>
                    </InspectorControls>
                </>
            );
        }

        return <BlockEdit {...props} />;
    };
}, 'withProControls');

addFilter('blocks.registerBlockType', 'yt-for-wp-pro/add-pro-attributes', addProAttributes);
addFilter('editor.BlockEdit', 'yt-for-wp-pro/add-pro-controls', withProControls);
