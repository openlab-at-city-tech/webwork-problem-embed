<?php

class WWPE_Helpers {
	private $endpoint_url = '';

	public function __construct() {
		$this->endpoint_url = wwpe_get_endpoint_url();

		add_action( 'wp_ajax_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );
		add_action( 'wp_ajax_nopriv_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );
	}

	/**
	 * Get HTML for the iframe
	 */
	public function get_problem_html( $problem_id, $seed ) {
		$response = $this->fetch_problem_html( $problem_id, $seed );

		return array(
			'success'    => $response['success'],
			'html'       => htmlentities( $response['body'] ),
			'tags'       => $response['tags'],
			'code'       => $response['code'],
			'problem_id' => $problem_id,
			'seed'       => $seed,
		);
	}

	/**
	 * AJAX method for getting a new problem html with random seed number.
	 */
	public function ajax_get_problem_html() {
		// phpcs:disable WordPress.Security.NonceVerification
		// If Problem Id is not provided, abort and return error
		if ( ! isset( $_POST['problem_id'] ) || empty( $_POST['problem_id'] ) ) {
			wp_send_json_error( __( 'Missing Problem Id.', 'wwpe' ), 400 );
			die();
		}

		$problem_id = $_POST['problem_id'];
		$seed       = $this->get_random_problem_seed();

		// phpcs:enable WordPress.Security.NonceVerification
		$response = $this->fetch_problem_html( $problem_id, $seed );

		wp_send_json(
			array(
				'success'    => $response['success'],
				'code'       => $response['code'],
				'html'       => $response['body'],
				'problem_id' => $problem_id,
				'seed'       => $seed,
			)
		);
	}

	/**
	 * Generate random problem seed number between 0 and 9999
	 */
	public function get_random_problem_seed() {
		return wp_rand( 0, 9999 );
	}

	/**
	 * Fetch problem render HTML
	 */
	private function fetch_problem_html( $problem_id, $problem_seed ) {
		// Check if the problemSourceURL/sourceFilePath ends with .pg
		if ( ! str_ends_with( $problem_id, '.pg' ) ) {
			return array(
				'success' => false,
				'code'    => 400,
				'body'    => __( 'Invalid Problem Id.', 'wwpe' ),
			);
		}

		// Construct API request arguments
		$args = array(
			'method' => 'POST',
			'body'   => array(
				'problemSeed'  => $problem_seed,
				'outputFormat' => 'single',
				'format'       => 'json',
				'includeTags'  => true,
			),
		);

		if ( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$args['body']['problemSourceURL'] = $problem_id;
		} else {
			$args['body']['sourceFilePath'] = $problem_id;
		}

		$response = wp_remote_post( $this->endpoint_url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'code'    => $response->get_error_code(),
				'body'    => $response->get_error_message(),
			);
		}

		$body = json_decode( $response['body'], true );

		return array(
			'success' => 200 === $response['response']['code'],
			'code'    => $response['response']['code'],
			'body'    => 200 !== $response['response']['code'] ? $response['response']['message'] : $body['renderedHTML'],
			'tags'    => isset( $body['tags'] ) ? $body['tags'] : [],
		);
	}

}

$wwpe_helper = new WWPE_Helpers();
