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
	public function get_problem_html( $problem_id, $seed, $opts = [] ) {
		$r = array_merge(
			[
				'show_correct_answers_button' => false,
			],
			$opts
		);

		$fetch_args = [
			'show_correct_answers_button' => $r['show_correct_answers_button'],
		];

		$response = $this->fetch_problem_html( $problem_id, $seed, $fetch_args );

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

		$fetch_args = [
			'show_correct_answers_button' => ! empty( $_POST['show_correct_answers_button'] ),
		];

		// phpcs:enable WordPress.Security.NonceVerification
		$response = $this->fetch_problem_html( $problem_id, $seed, $fetch_args );

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
	private function fetch_problem_html( $problem_id, $problem_seed, $opts = [] ) {
		$r = array_merge(
			[
				'show_correct_answers_button' => false,
			],
			$opts
		);

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
				'problemSeed'              => $problem_seed,
				'outputFormat'             => 'single',
				'format'                   => 'json',
				'includeTags'              => true,
				'showCorrectAnswersButton' => (bool) $r['show_correct_answers_button'],
				'userID'                   => $this->get_user_id(),
			),
		);

		if ( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$args['body']['problemSourceURL'] = $problem_id;
		} else {
			$args['body']['sourceFilePath'] = $problem_id;
		}

		$args = apply_filters( 'webwork_problem_embed_renderer_request_args', $args );

		$response = wp_remote_post( $this->endpoint_url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'code'    => $response->get_error_code(),
				'body'    => $response->get_error_message(),
			);
		}

		$body = json_decode( $response['body'], true );

		$allowed_tags = [ 'Author', 'Institution' ];
		$tags         = [];
		if ( isset( $body['tags'] ) && is_array( $body['tags'] ) ) {
			foreach ( $body['tags'] as $tag_name => $tag_value ) {
				if ( in_array( $tag_name, $allowed_tags, true ) ) {
					$tags[ $tag_name ] = $tag_value;
				}
			}
		}

		return array(
			'success' => 200 === $response['response']['code'],
			'code'    => $response['response']['code'],
			'body'    => 200 !== $response['response']['code'] ? $response['response']['message'] : $body['renderedHTML'],
			'tags'    => $tags,
		);
	}

	/**
	 * Get an ID fo the current user, to pass to the problem renderer.
	 *
	 * Renderer expects an email address.
	 *
	 * @return string
	 */
	private function get_user_id() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user = wp_get_current_user();

		return $user->user_email;
	}
}

$wwpe_helper = new WWPE_Helpers();
