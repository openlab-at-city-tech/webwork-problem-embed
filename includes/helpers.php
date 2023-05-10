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

		if ( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
			$content = $this->get_problem_by_problem_source_url( $problem_id );
		} else {
			$content = $this->get_problem_by_source_file_path( $problem_id, $seed );
		}

		if ( ! $content ) {
			return;
		}

		// Remove "\r" from the content, because it results with wrong 8-bit integer array
		$content = str_replace( "\r", '', $content );

		// Generate problem source from the problem content
		$source = $this->generate_problem_source( $content );

		// Fetch the HTML for the problem
		$response = $this->fetch_problem_html( $source, $seed );

		if ( ! is_wp_error( $response ) ) {
			return array(
				'source' => $source,
				'seed'   => $seed,
				'html'   => htmlentities( wp_remote_retrieve_body( $response ) ),
			);
		}
	}

	/**
	 * AJAX method for getting a new problem html with random seed number.
	 */
	public function ajax_get_problem_html() {
		// Check if problem source is provided
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['problem_source'] ) || empty( $_POST['problem_source'] ) ) {
			wp_send_json_error( 'Missing problem source.', 500 );
			die();
		}

		$source = $_POST['problem_source'];
		$seed   = $this->get_random_problem_seed();
		// phpcs:enable WordPress.Security.NonceVerification

		$response = $this->fetch_problem_html( $source, $seed );

		if ( ! is_wp_error( $response ) ) {
			wp_send_json(
				array(
					'success' => true,
					'source'  => $source,
					'seed'    => $seed,
					'html'    => wp_remote_retrieve_body( $response ),
				)
			);
		} else {
			wp_send_json( $response->get_error_message(), 500 );
		}
	}

	/**
	 * AJAX method for getting the problem attribution
	 */
	public function ajax_get_problem_attribution() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['problem_source'] ) || empty( $_POST['problem_source'] ) ) {
			wp_send_json_error( 'Missing problem source', 500 );
			die();
		}

		$seed     = $_POST['problem_seed'];
		$source   = $_POST['problem_source'];
		$response = wp_remote_post(
			$this->endpoint_url,
			array(
				'method' => 'POST',
				'body'   => array(
					'problemSource' => $source,
					'problemSeed'   => $seed,
					'format'        => 'json',
					'outputFormat'  => 'single',
					'includeTags'   => true,
				),
			)
		);
		// phpcs:enable WordPress.Security.NonceVerification

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_wp_error( $response ) ) {
			wp_send_json(
				array(
					'success' => true,
					'tags'    => isset( $body['tags'] ) ? $body['tags'] : [],
				)
			);
		} else {
			wp_send_json( $response->get_error_message(), 500 );
		}

	}

	/**
	 * Generate random problem seed number between 0 and 9999
	 */
	public function get_random_problem_seed() {
		return wp_rand( 0, 9999 );
	}

	/**
	 * Get problem content by source file path.
	 */
	private function get_problem_by_source_file_path( $path, $seed ) {
		$url = $this->endpoint_url . '/';

		// Request problem content
		$response = wp_remote_post(
			$url,
			[
				'method' => 'POST',
				'body'   => [
					'sourceFilePath' => $path,
					'problemSeed'    => $seed,
				],
			]
		);

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code && ! is_wp_error( $response ) ) {
			return wp_remote_retrieve_body( $response );
		}
	}

	/**
	 * Get problem content by problem source url.
	 */
	private function get_problem_by_problem_source_url( $url ) {
		$response = wp_remote_get( $url );

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code && ! is_wp_error( $response ) ) {
			return wp_remote_retrieve_body( $response );
		}
	}

	/**
	 * Generate problem source base64
	 */
	private function generate_problem_source( $content ) {
		// Unpack data from a binary string
		$uint8 = unpack( 'C*', $content );

		$result = '';
		foreach ( $uint8 as $key => $value ) {
			$result .= chr( $value );
		}

		// Encode result string with base64.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $content );
	}

	/**
	 * Fetch problem render HTML
	 */
	private function fetch_problem_html( $source, $seed ) {
		return wp_remote_post(
			$this->endpoint_url,
			[
				'method' => 'POST',
				'body'   => [
					'problemSource' => $source,
					'problemSeed'   => $seed,
					'format'        => 'html',
					'outputFormat'  => 'single',
				],
			]
		);
	}
}

$wwpe_helper = new WWPE_Helpers();
