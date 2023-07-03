<?php

class WWPE_Helpers {
	private $endpoint_url = '';

	public function __construct() {
		$this->endpoint_url = wwpe_get_endpoint_url();

		add_action( 'wp_ajax_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );
		add_action( 'wp_ajax_nopriv_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );

		add_action( 'wp_ajax_wwpe_get_problem_attribution', array( $this, 'ajax_get_problem_attribution' ) );
		add_action( 'wp_ajax_nopriv_wwpe_get_problem_attribution', array( $this, 'ajax_get_problem_attribution' ) );
	}

	/**
	 * Get HTML for the iframe
	 */
	public function get_problem_html( $problem_id, $seed ) {
		// Check if the problemSourceURL/sourceFilePath ends with .pg
		if ( ! str_ends_with( $problem_id, '.pg' ) ) {
			return;
		}

		// Construct API request
		$args = array(
			'method'	=> 'POST',
			'body'		=> array(
				'problemSeed'		=> $seed,
				'outputFormat'		=> 'single',
				'format'			=> 'html'
			)
		);

		if( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$args['body']['problemSourceURL'] = $problem_id;
		} else {
			$args['body']['sourceFilePath'] = $problem_id;
		}

		$response = $this->fetch_problem_html( $args );

		return array(
			'problemId'	=> $problem_id,
			'seed'		=> $seed,
			'html'		=> htmlentities( $response )
		);
	}

	/**
	 * AJAX method for getting a new problem html with random seed number.
	 */
	public function ajax_get_problem_html() {
		// Check if problem source is provided
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['problem_id'] ) || empty( $_POST['problem_id'] ) ) {
			wp_send_json_error( 'Missing Problem Id.', 500 );
			die();
		}

		$problem_id = $_POST['problem_id'];
		$seed = $this->get_random_problem_seed();

		// phpcs:enable WordPress.Security.NonceVerification

		// Construct API request
		$args = array(
			'method'	=> 'POST',
			'body'		=> array(
				'problemSeed'		=> $seed,
				'outputFormat'		=> 'single',
				'format'			=> 'html'
			)
		);

		if( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$args['body']['problemSourceURL'] = $problem_id;
		} else {
			$args['body']['sourceFilePath'] = $problem_id;
		}

		$response = $this->fetch_problem_html( $args );

		if( ! empty( $response ) ) {
			wp_send_json( array(
				'success'	=> true,
				'problem_id'	=> $problem_id,
				'seed'			=> $seed,
				'html'			=> $response
			) );
		}
	}

	/**
	 * AJAX method for getting the problem attribution
	 */
	public function ajax_get_problem_attribution() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['problem_id'] ) || empty( $_POST['problem_id'] ) ) {
			wp_send_json_error( 'Missing Problem Id.', 500 );
			die();
		}

		$problem_id = $_POST['problem_id'];
		$seed = $_POST['problem_seed'];

		// phpcs:enable WordPress.Security.NonceVerification
		$args = array(
			'method'	=> 'POST',
			'body'		=> array(
				'problemSeed'		=> $seed,
				'outputFormat'		=> 'single',
				'format'			=> 'json',
				'includeTags'		=> true
			)
		);

		if( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$args['body']['problemSourceURL'] = $problem_id;
		} else {
			$args['body']['sourceFilePath'] = $problem_id;
		}

		$response = $this->fetch_problem_html( $args );	
	
		if( ! empty( $response ) ) {
			$response = json_decode( $response, true);

			wp_send_json( array(
				'success'	=> true,
				'tags'		=> isset( $response['tags'] ) ? $response['tags'] : []
			) );
		}

		wp_send_json( 'Error retrieving problem tags.', 500 );
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
	private function fetch_problem_html( $args ) {
		return wp_remote_post( $this->endpoint_url, $args );
	}

}

$wwpe_helper = new WWPE_Helpers();
