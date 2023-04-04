import { ServerSideRender, __experimentalInputControl as InputControl, ToggleControl, __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { useBlockProps } from "@wordpress/block-editor";
import { useState, useEffect } from '@wordpress/element';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected }) {
    const [ problemId, setProblemId ] = useState('');
    const [ showRandomSeedButton, setRandomSeedButton ] = useState(true);
    const [ seedNumber, setSeedNumber ] = useState('');

    useEffect(() => {
        setProblemId(attributes.problemId);
        setRandomSeedButton(attributes.showRandomSeedButton);
        setSeedNumber(attributes.seed);
    });

    function onChangeProblemId(value) {
        setProblemId(value);
        setAttributes({ problemId: value });
    }

    function onChangeShowRandomSeedButton(value) {
        setRandomSeedButton(value);
        setAttributes({ showRandomSeedButton: value });
    }

    function onChangeSeedNumber(value) {
        setSeedNumber(value);
        setAttributes({ seed: value });
    }

    return (
        <div { ...useBlockProps() }>
            {( isSelected ) ? (
                <div>
                    <div className="wwpe-editor-field-group">
                        <InputControl
                            label="Problem Id"
                            value={ problemId }
                            onChange={ onChangeProblemId }
                        />
                    </div>
                    <div className="wwpe-editor-field-group">
                        <InputControl
                            label="Seed"
                            value={ seedNumber }
                            onChange={ onChangeSeedNumber }
                        />
                    </div>
                    <div className="wwpe-editor-field-group">
                        <ToggleControl
                            label="Display Random Seed Button?"
                            checked={ showRandomSeedButton }
                            onChange={ onChangeShowRandomSeedButton }
                        />
                    </div>
                </div>
            ) : (
                <ServerSideRender
                    block="wwpe/problem-embed"
                    attributes={ attributes }
                />
            )}
        </div>
    )
}