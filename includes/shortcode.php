<?php

/**
 * Shortcode callback.
 *
 * Usage: [webwork_problem problemId="url_or_file_path.pg" allowReseed="1/0" problemSeed="1234"]
 */
function wwpe_problem_shortcode( $atts ) {
	ob_start();

	wwpe_get_template(
		'problem.php',
		array(
			'problemId'                => $atts['problemid'] ?? null,
			'allowReseed'              => $atts['allowreseed'] ?? false,
			'problemSeed'              => $atts['problemseed'] ?? null,
			'showCorrectAnswersButton' => $atts['showCorrectAnswersButton'] ?? false,
		)
	);

	return ob_get_clean();
}
add_shortcode( 'webwork_problem', 'wwpe_problem_shortcode' );
