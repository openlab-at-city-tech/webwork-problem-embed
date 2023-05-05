<?php

function wwpe_block_render( $atts ) {
    ob_start();
    
    wwpe_get_template( 'problem.php', array(
        'problemId'     => $atts['problemId'] ?? null,
        'allowReseed'   => $atts['showRandomSeedButton'] ?? false,
        'problemSeed'   => $atts['seed'] ?? null
    ) ); 

    return ob_get_clean();
}
