<?php

/**
 * Add menu item in "Settings"
 */
add_action( 'admin_menu', 'wwpe_admin_menu' );
function wwpe_admin_menu() {
	add_options_page( 'WeBWorK', 'WeBWorK', 'manage_options', 'wwpe', 'wwpe_plugin_options_render' );
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
							<input type="text" name="wwpe_endpoint" id="wwpe_endpoint" value="<?php echo esc_url( wwpe_get_endpoint_url() ); ?>" <?php disabled( defined( 'WWPE_ENDPOINT_URL' ), true ); ?> />
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
	$response = wp_remote_post(
		$value,
		[
			'method'      => 'POST',
			'body'        => [
				'problemSeed'   => 1234,
				'format'        => 'html',
				'outputFormat'  => 'single',
				'problemSource' => wwpe_get_test_problem_source(),
			],
			'data_format' => 'body',
		]
	);

	$status_code = intval( wp_remote_retrieve_response_code( $response ) );

	if ( 200 !== $status_code ) {
		/* translators: endpoint URL */
		$message = sprintf( __( 'WeBWorK Problem Endpoint is not valid (%s). Reverted to previous value.', 'wwpe' ), $value );
		add_settings_error( 'wwpe_messages', 'wwpe_message', $message );
		return wwpe_get_endpoint_url();
	}

	return $value;
}

/**
 * Get WeBWorK Endpoint URL
 */
function wwpe_get_endpoint_url() {
	return defined( 'WWPE_ENDPOINT_URL' ) ? WWPE_ENDPOINT_URL : get_option( 'wwpe_endpoint' );
}

/**
 * WeBWorK test problem source
 */
function wwpe_get_test_problem_source() {
	return 'IyNERVNDUklQVElPTgojIyBUYWdnZWQgYnkgampoMmIKCiMjIERCc3ViamVjdChXZUJXb3JLKQojIyBEQmNoYXB0ZXIoV2VCV29ySyB0dXRvcmlhbCkKIyMgREJzZWN0aW9uKE1BQSB0dXRvcmlhbCkKIyMgRGF0ZSg4LzMwLzA3KQojIyBTdGF0aWMoMSkKIyMgS0VZV09SRFMoJ3NhbXBsZScpCgpET0NVTUVOVCgpOyAgICAgICAKbG9hZE1hY3JvcygKICAiUEdzdGFuZGFyZC5wbCIsCiAgIlBHY2hvaWNlbWFjcm9zLnBsIiwKICAiUEdjb3Vyc2UucGwiCik7CiAgICAgICAgICAgCkJFR0lOX1RFWFQKQ29tcGxldGUgdGhlIHNlbnRlbmNlOiAkUEFSClx7IGFuc19ydWxlKDIwKSBcfSAgd29ybGQhCkVORF9URVhUCgpBTlMoc3RyX2NtcCggIkhlbGxvIiApICk7ICAjIGhlcmUgaXMgdGhlIGFuc3dlciwgYSBzdHJpbmcuCgoKCgpFTkRET0NVTUVOVCgpOyAKICAgCg==';
}
