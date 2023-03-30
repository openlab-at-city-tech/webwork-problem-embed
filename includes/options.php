<?php

/**
 * Add menu item in "Settings"
 */
add_action( 'admin_menu', 'wwpe_admin_menu' );
function wwpe_admin_menu() {
    add_options_page( 'WeBWorK', 'WeBWorK', 'manage_options', __FILE__, 'wwpe_plugin_options_render' );
}

/**
 * Register settings
 */
add_action( 'admin_init', 'wwpe_admin_init' );
function wwpe_admin_init() {
    register_setting( 'wwpe_options', 'wwpe_endpoint', 'wwpe_endpoint_validate' );
}

/**
 * Render method for the WebWorK options page
 */
function wwpe_plugin_options_render() {
    ?>
    <div class="wrap">
        <h2>WeBWorK</h2>
        <form method="POST" action="options.php">
            <?php settings_fields( 'wwpe_options' ); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="wwpe_endpoint">WeBWorK Endpoint URL</label>
                        </th>
                        <td>
                            <input type="text" name="wwpe_endpoint" id="wwpe_endpoint" value="<?php echo wwpe_get_endpoint_url(); ?>" <?php disabled( defined( 'WWPE_ENDPOINT_URL' ), true ); ?> />
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Validate Endpoint URL value.
 */
function wwpe_endpoint_validate( $value ) {
    // TODO: Implement validation
    return $value;
}

/**
 * Get WeBWorK Endpoint URL
 */
function wwpe_get_endpoint_url() {
    return defined( 'WWPE_ENDPOINT_URL' ) ? WWPE_ENDPOINT_URL : get_option( 'wwpe_endpoint' );
}