import { __experimentalInputControl as InputControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from "@wordpress/block-editor";
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected }) {
    const [ problemId, setProblemId ] = useState('');
    const [ showRandomSeedButton, setRandomSeedButton ] = useState(true);
    const [ showCorrectAnswersButton, setShowCorrectAnswersButton ] = useState(false);
    const [ seedNumber, setSeedNumber ] = useState('');

    useEffect(() => {
        setProblemId(attributes.problemId);
        setRandomSeedButton(attributes.showRandomSeedButton);
        setShowCorrectAnswersButton(attributes.showCorrectAnswersButton);
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

    function onChangeShowCorrectAnswersButton( value ) {
        setShowCorrectAnswersButton( value );
        setAttributes({ showCorrectAnswersButton: value });
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
                            label={ __( "Problem Id", 'wwpe' ) }
                            value={ problemId }
                            onChange={ onChangeProblemId }
                        />
                    </div>
                    <div className="wwpe-editor-field-group">
                        <InputControl
                            label={ __( "Seed", 'wwpe' ) }
                            value={ seedNumber }
                            onChange={ onChangeSeedNumber }
                        />
                    </div>
                    <div className="wwpe-editor-field-group">
                        <ToggleControl
                            label={ __( "Display 'Try Another' Button?", 'wwpe' ) }
                            checked={ showRandomSeedButton }
                            onChange={ onChangeShowRandomSeedButton }
														help={ __( "The 'Try Another' button allows the regeneration of problem using a new, random seed value.", 'wwpe' ) }
                        />
                    </div>
                    <div className="wwpe-editor-field-group">
                        <ToggleControl
                            label={ __( "Allow students to request solutions?", 'wwpe' ) }
                            checked={ showCorrectAnswersButton }
                            onChange={ onChangeShowCorrectAnswersButton }
                        />
                    </div>
                </div>
            ) : (
                <ServerSideRender
                    block="wwpe/problem-embed"
                    attributes={ attributes }
                />
            ) }
        </div>
    )
}
