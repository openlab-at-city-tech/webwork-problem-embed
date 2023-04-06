<?php
/**
 * Plugin Name:       WeBWorK Problem Embed
 * Plugin URI:        https://openlab.citytech.cuny.edu/
 * Description:       Add support for embedding WeBWorK problems
 * Version:           1.0.0
 * Author:            Boris Kuzmanov
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wwpe
 * Domain Path:       /languages
 */


function wwpe_block_init() {
    $asset_file = require_once plugin_dir_path( __FILE__ ) . '/block/build/index.asset.php';
    require_once plugin_dir_path( __FILE__ ) . '/block/src/render.php';
    
    // Register JS script
    wp_register_script(
        'wwpe-block',
        plugins_url( '/block/build/index.js', __FILE__ ),
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Register CSS
    wp_register_style(
        'wwpe-block',
        plugins_url( '/block/build/index.css', __FILE__ ),
        array(),
        $asset_file['version']
    );

    // Register block
    register_block_type(
        'wwpe/problem-embed', [
            'api_version'       => 2,
            'editor_script'     => 'wwpe-block',
            'editor_style'      => 'wwpe-block',
            'attributes'        => array(
                'problemId'  => [
                    'type'      => 'string',
                    'default'   => ''
                ],
                'showRandomSeedButton'      => [
                    'type'      => 'boolean',
                    'default'   => true
                ],
                'seed'    => [
                    'type'      => 'string',
                    'default'   => ''
                ]
            ),
            'render_callback'   => 'wwpe_block_render'
        ]
    );

    wp_enqueue_style(
		'wwpe-public',
		plugins_url( '/assets/public.css', __FILE__ ),
		array(),
		'1.0.0'
	);

    wp_register_script(
        'iframe-resizer',
        plugins_url( '/assets/iframe-resizer/js/iframeResizer.js', __FILE__ ),
        array(),
        '1.0.0'
    );

    wp_register_script(
        'wwpe-public',
        plugins_url( '/assets/public.js', __FILE__ ),
        array( 'jquery', 'iframe-resizer'),
        true
    );

    wp_localize_script( 'wwpe-public', 'wwpe', array(
        'endpoint_url'  => wwpe_get_endpoint_url(),
        'ajax_url'      => admin_url( 'admin-ajax.php' )
    ));

    wp_enqueue_script( 'wwpe-public' );
}
add_action( 'init', 'wwpe_block_init' );

require_once plugin_dir_path( __FILE__ ) . '/includes/options.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/helpers.php';

