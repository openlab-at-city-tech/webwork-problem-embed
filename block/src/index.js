import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

registerBlockType( 'wwpe/problem-embed', {
    title: 'WeBWorK Problem Embed',
    icon: 'superhero',
    category: 'embed',
    attributes: {
        problemId: {
            type: 'string',
            default: '',
        },
        showRandomSeedButton: {
            type: 'boolean',
            default: true
        },
        seed: {
            type: 'string',
            default: ''
        }
    },
    edit: Edit,
    save: () => null
} );