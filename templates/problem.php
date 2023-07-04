<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $args['problemId'] ) || empty( $args['problemId'] ) ) {
	return __( '<p><strong>WeBWorK Error:</strong> Missing Problem Id.', 'wwpe' );
}

global $wwpe_helper;

$problem_seed = isset( $args['problemSeed'] ) && ! empty( $args['problemSeed'] ) ? intval( $args['problemSeed'] ) : $wwpe_helper->get_random_problem_seed();
$allow_reseed = isset( $args['allowReseed'] ) ? (bool) $args['allowReseed'] : false;

$response = $wwpe_helper->get_problem_html( $args['problemId'], $problem_seed );

if( ! $response['success'] ) {
	return printf( __( '<p><strong>WeBWorK Error:</strong> %d - %s', 'wwpe' ), $response['code'], $response['html'] );
}

?>
<div class="wwpe-problem-wrapper">
	<?php if ( $allow_reseed ) : ?>
		<div class="wwpe-problem-content-button">
			<button type="button" id="wwpe-random-seed"><?php esc_html_e( 'Random Seed', 'wwpe' ); ?></button>
		</div>
	<?php endif; ?>
	<div class="wwpe-problem-content">
		<input type="hidden" id="problemId" value="<?php echo esc_attr( $response['problem_id'] ); ?>" />
		<input type="hidden" id="problemSeed" value="<?php echo esc_attr( $response['seed'] ); ?>" />
		<iframe
			id="renderer-problem"
			<?php echo defined( 'REST_REQUEST' ) && REST_REQUEST ? 'style="pointer-events: none;"' : ''; ?>
			<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
			srcdoc="<?php echo $response['html']; ?>"
			width="100%"
		></iframe>
	</div>
</div>
