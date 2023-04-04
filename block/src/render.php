<?php

function wwpe_block_render( $atts ) {
    ob_start();
    ?>
    <div>
        <p>WWPE Block Render</p>
    </div>
    <?php
    $html = ob_get_clean();

    return $html;
}