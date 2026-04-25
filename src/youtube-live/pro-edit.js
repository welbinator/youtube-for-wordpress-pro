import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

// Build channel options from ytForWPProEditor global (injected by PHP).
const getChannelOptions = () => {
    const channels = ( window.ytForWPProEditor && window.ytForWPProEditor.channels ) || [];
    const options = [ { label: '— Use default from settings —', value: '' } ];
    channels.forEach( ( ch ) => {
        options.push( { label: ch.name || ch.channel_id, value: ch.channel_id } );
    } );
    return options;
};

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
                            <SelectControl
                                label="YouTube Channel (Pro)"
                                value={attributes.channelId}
                                options={getChannelOptions()}
                                onChange={(value) => setAttributes({ channelId: value })}
                                help="Select a channel, or leave as default to use the channel from Settings."
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
