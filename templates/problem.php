<?php
if ( ! defined( 'ABSPATH' ) ) 
    exit;

if( ! isset( $args['problemId'] ) || empty( $args['problemId'] ) )
    return _e( 'Missing `problemSourceURL` or `sourceFilePath`.', 'wwpe' );

global $wwpe_helper;

$problemSeed = isset( $args['problemSeed'] ) && ! empty( $args['problemSeed'] ) ? intval( $args['problemSeed'] ) : $wwpe_helper->get_random_problem_seed();
$allowReseed = isset( $args['allowReseed'] ) ? (bool)$args['allowReseed'] : false;

$response = $wwpe_helper->get_problem_html( $args['problemId'], $problemSeed );

if( $response == null)
    return _e( 'Invalid `problemSourceURL` or `sourceFilePath`.', 'wwpe' );
?>
<div class="wwpe-problem-wrapper">
    <?php if( $allowReseed ) : ?>
        <div class="wwpe-problem-content-button">
            <button type="button" id="wwpe-random-seed"><?php _e( 'Random Seed', 'wwpe' ); ?></button>
        </div>
    <?php endif; ?>
    <div class="wwpe-problem-content">
        <input type="hidden" id="problemId" value="<?php echo $args['problemId']; ?>" />
        <input type="hidden" id="problemSource" value="<?php echo $response['source']; ?>" />
        <input type="hidden" id="problemSeed" value="<?php echo $response['seed']; ?>" />
        <iframe 
            id="renderer-problem" 
            <?php echo defined( 'REST_REQUEST' ) && REST_REQUEST ? 'style="pointer-events: none;"' : ''; ?> 
            srcdoc="<?php echo $response['html']; ?>"
            width="100%"
        ></iframe>
    </div>
</div>