<?php

class WWPE_Helpers {

    private $endpoint_url = '';

    function __construct() {
        $this->endpoint_url = wwpe_get_endpoint_url();

        add_action( 'wp_ajax_wwpe_get_problem_render_html', array( $this, 'wwpe_ajaxget_problem_render_html' ) );
        add_action( 'wp_ajax_nopriv_wwpe_get_problem_render_html', array( $this, 'wwpe_ajaxget_problem_render_html' ) );
    }

    /**
     * Load problem by source file path
     */
    function wwpe_get_problem_content_by_file_path( $path ) {
        $url = $this->endpoint_url . '/tap';

        $response = wp_remote_post( $url, array(
            'method'    => 'POST',
            'body'      => array(
                'sourceFilePath' => $path
            )
        ) );

        $status_code = wp_remote_retrieve_response_code( $response );

        if( ! is_wp_error( $response ) && $status_code === 200 )
            return wp_remote_retrieve_body($response);

        return null;
    }

    /**
     * Generate problem source base64
     */
    function wwpe_generate_problem_source( $problem_content ) {
        $uint8 = unpack( "C*", $problem_content );

        $str= '';
        foreach($uint8 as $key => $value ) {
            $str .= chr($value);
        }
        
        return base64_encode( $str );
    }

    /**
     * Get HTML for the iframe
     */
    function wwpe_get_problem_render_html( $path, $seed ) {
        if( empty( $path ) || empty( $seed ) ) 
            return;

        // Get problem content by source file path
        $problem_content = $this->wwpe_get_problem_content_by_file_path( $path );

        if( $problem_content == null) 
            return;

        // Remove \r from the problem content, because wrong 8-bit array of integers gets generated 
        $problem_content = str_replace( "\r", "", $problem_content );

        // Generate problem source code
        $problemSource = $this->wwpe_generate_problem_source( $problem_content );

        $response = wp_remote_post( $this->endpoint_url, array(
            'method'    => 'POST',
            'body'  => [
                'problemSeed'       => $seed,
                'format'            => 'html',
                'outputFormat'      => 'single',
                'problemSource'     => $problemSource
            ]
        ) );

        if( ! is_wp_error( $response ) ) {
            return array(
                'problemSource' => $problemSource,
                'problemSeed'   => $seed,
                'problemHtml'   => htmlentities( wp_remote_retrieve_body( $response ) )
            );
        }

        return null;
    }

    /**
     * AJAX method for getting a new problem html with random seed number.
     */
    function wwpe_ajaxget_problem_render_html() {
        // Check if problem source is provided
        if( ! isset( $_POST['problem_source'] ) || empty( $_POST['problem_source'] ) ) {
            wp_send_json_error( 'Missing problem source.', 500 );
            die();
        }

        // Get random seed number
        $seed = $this->wwpe_get_random_problem_seed();
        
        // Get current problem source
        $problem_source = $_POST['problem_source'];

        $response = wp_remote_post( $this->endpoint_url, array(
            'method'    => 'POST',
            'body'      => array(
                'problemSeed'   => $seed,
                'format'        => 'html',
                'outputFormat'  => 'single',
                'problemSource' => $problem_source
            )
        ) );

        if( ! is_wp_error( $response ) ) {
            wp_send_json( array(
                'success'       => true,
                'problemSource' => $problem_source,
                'problemSeed'   => $seed,
                'problemHtml'   => wp_remote_retrieve_body( $response )
            ) );
        } else {
            wp_send_json( $response->get_error_message(), 500 );
        }

    }

    /**
     * Generate random problem seed number between 0 and 9999
     */
    function wwpe_get_random_problem_seed() {
        return rand(0, 9999);
    }
}

$wwpe_helper = new WWPE_Helpers();