<?php

class WWPE_Helpers {

    private $endpoint_url = '';

    function __construct() {
        $this->endpoint_url = wwpe_get_endpoint_url();

        add_action( 'wp_ajax_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );
        add_action( 'wp_ajax_nopriv_wwpe_get_problem_render_html', array( $this, 'ajax_get_problem_html' ) );
    }

    /**
     * Get HTML for the iframe
     */
    public function get_problem_html( $problem_id, $seed ) {
        // Check if the problemSourceURL/sourceFilePath ends with .pg
        if( ! str_ends_with( $problem_id, '.pg' ) )
            return;

        if( filter_var( $problem_id, FILTER_VALIDATE_URL ) ) {
            $content = $this->get_problem_by_problem_source_url( $problem_id );
        } else {
            $content = $this->get_problem_by_source_file_path( $problem_id );
        }

        if( $content == null )
            return;

        // Remove "\r" from the content, because it results with wrong 8-bit integer array
        $content = str_replace( "\r", "", $content );

        // Generate problem source from the problem content
        $source = $this->generate_problem_source( $content );

        // Fetch the HTML for the problem
        $response = $this->fetch_problem_html( $source, $seed );

        if( ! is_wp_error( $response ) ) {
            return array(
                'source' => $source,
                'seed'   => $seed,
                'html'   => htmlentities( wp_remote_retrieve_body( $response ) )
            );
        }

        return;
    }

    /**
     * AJAX method for getting a new problem html with random seed number.
     */
    public function ajax_get_problem_html() {
        // Check if problem source is provided
        if( ! isset( $_POST['problem_source'] ) || empty( $_POST['problem_source'] ) ) {
            wp_send_json_error( 'Missing problem source.', 500 );
            die();
        }

        $source = $_POST['problem_source'];
        $seed = $this->get_random_problem_seed();

        $response = $this->fetch_problem_html( $source, $seed );

        if( ! is_wp_error( $response ) ) {
            wp_send_json( array(
                'success'   => true,
                'source'    => $source,
                'seed'      => $seed,
                'html'      => wp_remote_retrieve_body( $response )
            ) );
        } else {
            wp_send_json( $response->get_error_message(), 500 );
        }
    }

    /**
     * Generate random problem seed number between 0 and 9999
     */
    public function get_random_problem_seed() {
        return rand(0, 9999);
    }

    /**
     * Get problem content by source file path.
     */
    private function get_problem_by_source_file_path( $path ) {
        $url = $this->endpoint_url . '/tap';

        // Request problem content
        $response = wp_remote_post( $url, array(
            'method'    => 'POST',
            'body'      => array(
                'sourceFilePath' => $path
            )
        ) );

        $status_code = wp_remote_retrieve_response_code( $response );

        if( $status_code == 200 && ! is_wp_error( $response ) )
            return wp_remote_retrieve_body( $response );
    }

    /**
     * Get problem content by problem source url.
     */
    private function get_problem_by_problem_source_url( $url ) {
        $response = wp_remote_get( $url );

        $status_code = wp_remote_retrieve_response_code( $response );
        
        if( $status_code == 200 && ! is_wp_error( $response ) )
            return wp_remote_retrieve_body( $response );
    }

    /**
     * Generate problem source base64
     */
    private function generate_problem_source( $content ) {
        // Unpack data from a binary string
        $uint8 = unpack( "C*", $content );

        $result = '';
        foreach($uint8 as $key => $value ) {
            $result .= chr($value);
        }
        
        // Encode result string with base64
        return base64_encode( $content );
    }

    /**
     * Fetch problem render HTML
     */
    private function fetch_problem_html( $source, $seed ) {
        return wp_remote_post( $this->endpoint_url, array(
            'method'    => 'POST',
            'body'      => array(
                'problemSource' => $source,
                'problemSeed'   => $seed,
                'format'        => 'html',
                'outputFormat'  => 'single'
            )
        ) );
    }

}

$wwpe_helper = new WWPE_Helpers();