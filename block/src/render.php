<?php

function wwpe_block_render( $atts ) {
    ob_start();

    global $wwpe_helper;

    $problemId = isset( $atts['problemId'] ) && ! empty( $atts['problemId'] ) ? $atts['problemId'] : null;
    $seed = isset( $atts['seed'] ) && ! empty( $atts['seed'] ) && is_numeric( $atts['seed'] ) ? intval( $atts['seed'] ) : $wwpe_helper->wwpe_get_random_problem_seed();
    $showButton = isset( $atts['showRandomSeedButton'] ) && ! empty( $atts['showRandomSeedButton']) ? $atts['showRandomSeedButton'] : false;

    $response = $wwpe_helper->wwpe_get_problem_render_html( $problemId, $seed );
    ?>
    <div class="wwpe-problem-wrapper">
        <?php if( $response == null ) : ?>
        <div class="wwpe-problem-error">
            <p>Missing or non existing WeBWorK ProblemId.</p>
        </div>
        <?php else : ?>
        <div class="wwpe-problem-content">
            <?php if( $showButton ) : ?>
            <div class="wwpe-problem-content-button">
                <button type="button" id="random-seed-button">Random Seed</button>
            </div>
            <?php endif; ?>
            <input type="hidden" id="problemId" value="<?php echo $problemId; ?>" />
            <input type="hidden" id="problemSeed" value="<?php echo $response['problemSeed']; ?>" />
            <input type="hidden" id="problemSource" value="<?php echo $response['problemSource']; ?>" />
            <iframe id="renderer-problem" <?php echo defined( 'REST_REQUEST' ) && REST_REQUEST ? 'style="pointer-events: none;"' : ''; ?> srcdoc="<?php echo $response['problemHtml']; ?>" scrolling="yes" width="100%"></iframe>
        </div>
        <?php endif; ?>
    </div>
    <?php
    $html = ob_get_clean();

    return $html;
}
