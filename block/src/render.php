<?php

function wwpe_block_render( $atts ) {
	ob_start();

	wwpe_get_template(
		'problem.php',
		array(
			'problemId'                => $atts['problemId'] ?? null,
			'allowReseed'              => $atts['showRandomSeedButton'] ?? false,
			'problemSeed'              => $atts['seed'] ?? null,
			'showCorrectAnswersButton' => $atts['showCorrectAnswersButton'] ?? false,
		)
	);

	return ob_get_clean();
}
