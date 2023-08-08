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

$show_correct_answers_button = ! empty( $args['showCorrectAnswersButton'] );

$fetch_args = [
	'show_correct_answers_button' => $show_correct_answers_button,
];

$response = $wwpe_helper->get_problem_html( $args['problemId'], $problem_seed, $fetch_args );

if ( ! $response['success'] ) {
	/* phpcs:disable */
	/* translators: Fetching problem error message */
	return printf( __( '<p><strong>WeBWorK Error:</strong> %1$d - %2$s', 'wwpe' ), $response['code'], $response['html'] );
	/* phpcs:enable */
}

$block_id = uniqid();
?>
<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
<div class="wwpe-problem-wrapper" data-id="<?php echo $block_id; ?>">
	<?php if ( $allow_reseed ) : ?>
		<div class="wwpe-problem-content-button">
			<button type="button" class="wwpe-random-seed"><?php esc_html_e( 'Try Another', 'wwpe' ); ?></button>
		</div>
	<?php endif; ?>

	<input type="hidden" id="show-correct-answers-button-<?php echo esc_attr( $block_id ); ?>" value="<?php echo (int) $show_correct_answers_button; ?>" />

	<div class="wwpe-problem-content">
		<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
		<input type="hidden" id="problemId-<?php echo $block_id; ?>" value="<?php echo esc_attr( $response['problem_id'] ); ?>" />
		<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
		<input type="hidden" id="problemSeed-<?php echo $block_id; ?>" value="<?php echo esc_attr( $response['seed'] ); ?>" />
		<iframe
			<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
			data-id="<?php echo $block_id; ?>"
			class="renderer-problem"
			<?php echo defined( 'REST_REQUEST' ) && REST_REQUEST ? 'style="pointer-events: none;"' : ''; ?>
			<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
			srcdoc="<?php echo $response['html']; ?>"
			width="100%"
		></iframe>
		<?php if ( isset( $response['tags'] ) && ! empty( $response['tags'] ) ) { ?>
		<table>
			<?php foreach ( $response['tags'] as $key => $value ) { ?>
			<tr>
				<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
				<td><?php echo $key; ?></td>
				<td>
					<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
					<?php echo is_array( $value ) ? join( ', ', $value ) : $value; ?>
				</td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>
	</div>
</div>
