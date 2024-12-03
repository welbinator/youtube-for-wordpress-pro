<?php

namespace YouTubeForWPPro\EDDLicensing;

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'YOUTUBEFORWORDPRESSPRO_STORE_URL', 'https://youtubeforwordpress.com' ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
define( 'YOUTUBEFORWORDPRESSPRO_ITEM_ID', 226 ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the name of the product in Easy Digital Downloads
define( 'YOUTUBEFORWORDPRESSPRO_ITEM_NAME', 'YouTube for WordPress' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
// the name of the settings page for the license input to be displayed
define( 'YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE', 'youtubeforwordpresspro-license' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist
	include __DIR__ . '/EDD_SL_Plugin_Updater.php';
}

// retrieve our license key from the DB
$license_key = trim( get_option( 'youtubeforwordpress_pro_license_key' ) );
// setup the updater
$edd_updater = new \EDD_SL_Plugin_Updater(
	YOUTUBEFORWORDPRESSPRO_STORE_URL,
	YOUTUBEFORWORDPRESS_PRO,
	array(
		'version' => YOUTUBE_FOR_WP_PRO_VERSION,     // current version number
		'license' => $license_key,    // license key (used get_option above to retrieve from DB)
		'item_id' => YOUTUBEFORWORDPRESSPRO_ITEM_ID,   // id of this plugin
		'author'  => 'James Welbes',  // author of this plugin
		'beta'    => false,                // set to true if you wish customers to receive update notifications of beta releases
	)
);

/**
 * Adds content to the settings section.
 *
 * @return void
 */
function license_key_settings_section() {
	esc_html_e( 'This is where you enter your license key.' );
}

/**
 * Outputs the license key settings field.
 *
 * @return void
 */
function license_key_settings_field() {
	$license = get_option( 'youtubeforwordpress_pro_license_key' );
	$status  = get_option( 'youtubeforwordpress_pro_license_status' );

	?>
	<p class="description"><?php esc_html_e( 'Enter your license key.' ); ?></p>
	<?php
	printf(
		'<input type="text" class="regular-text" id="youtubeforwordpress_pro_license_key" name="youtubeforwordpress_pro_license_key" value="%s" />',
		esc_attr( $license )
	);
	$button = array(
		'name'  => 'youtubeforwordpresspro_edd_license_deactivate',
		'label' => __( 'Deactivate License' ),
	);
	if ( 'valid' !== $status ) {
		$button = array(
			'name'  => 'youtubeforwordpresspro_edd_license_activate',
			'label' => __( 'Activate License' ),
		);
	}
	wp_nonce_field( 'youtubeforwordpresspro_nonce', 'youtubeforwordpresspro_nonce' );
	?>
	<input type="submit" class="button-secondary" name="<?php echo esc_attr( $button['name'] ); ?>" value="<?php echo esc_attr( $button['label'] ); ?>"/>
	<?php
}

/**
 * Registers the license key setting in the options table.
 *
 * @return void
 */
function register_option() {
    register_setting(
        'youtubeforwordpress_pro_license', // Option group
        'youtubeforwordpress_pro_license_key', // Option name
        __NAMESPACE__ . '\edd_sanitize_license' // Sanitize callback
    );
}
add_action('admin_init', __NAMESPACE__ . '\register_option');


/**
 * Sanitizes the license key.
 *
 * @param string $new The license key.
 * @return string
 */
function edd_sanitize_license( $new ) {
	$old = get_option( 'youtubeforwordpress_pro_license_key' );
	if ( $old && $old !== $new ) {
		delete_option( 'youtubeforwordpress_pro_license_status' ); // new license has been entered, so must reactivate
	}

	return sanitize_text_field( $new );
}

/**
 * Activates the license key.
 *
 * @return void
 */
function activate_license() {

	// listen for our activate button to be clicked
	if ( ! isset( $_POST['youtubeforwordpresspro_edd_license_activate'] ) ) {
		return;
	}

	// run a quick security check
	if ( ! check_admin_referer( 'youtubeforwordpresspro_nonce', 'youtubeforwordpresspro_nonce' ) ) {
		return; // get out if we didn't click the Activate button
	}

	// retrieve the license from the database
	$license = trim( get_option( 'youtubeforwordpress_pro_license_key' ) );
	if ( ! $license ) {
		$license = ! empty( $_POST['youtubeforwordpress_pro_license_key'] ) ? sanitize_text_field( $_POST['youtubeforwordpress_pro_license_key'] ) : '';
	}
	if ( ! $license ) {
		return;
	}

	// data to send in our API request
	$api_params = array(
		'edd_action'  => 'activate_license',
		'license'     => $license,
		'item_id'     => YOUTUBEFORWORDPRESSPRO_ITEM_ID,
		'item_name'   => rawurlencode( YOUTUBEFORWORDPRESSPRO_ITEM_NAME ), // the name of our product in EDD
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		YOUTUBEFORWORDPRESSPRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

		// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$message = __( 'An error occurred, please try again.' );
		}
	} else {

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( false === $license_data->success ) {

			switch ( $license_data->error ) {

				case 'expired':
					$message = sprintf(
						/* translators: the license key expiration date */
						__( 'Your license key expired on %s.', 'yt-for-wp-pro' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;

				case 'disabled':
				case 'revoked':
					$message = __( 'Your license key has been disabled.', 'yt-for-wp-pro' );
					break;

				case 'missing':
					$message = __( 'Invalid license.', 'yt-for-wp-pro' );
					break;

				case 'invalid':
				case 'site_inactive':
					$message = __( 'Your license is not active for this URL.', 'yt-for-wp-pro' );
					break;

				case 'item_name_mismatch':
					/* translators: the plugin name */
					$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'yt-for-wp-pro' ), YOUTUBEFORWORDPRESSPRO_ITEM_NAME );
					break;

				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.', 'yt-for-wp-pro' );
					break;

				default:
					$message = __( 'An error occurred, please try again.', 'yt-for-wp-pro' );
					break;
			}
		}
	}

		// Check if anything passed on a message constituting a failure
	if ( ! empty( $message ) ) {
		$redirect = add_query_arg(
			array(
				'page'          => YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE,
				'sl_activation' => 'false',
				'message'       => rawurlencode( $message ),
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit();
	}

	// $license_data->license will be either "valid" or "invalid"
	if ( 'valid' === $license_data->license ) {
		update_option( 'youtubeforwordpress_pro_license_key', $license );
	}
	update_option( 'youtubeforwordpress_pro_license_status', $license_data->license );
	wp_safe_redirect( admin_url( 'admin.php?page=' . YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE ) );
	exit();
}
add_action( 'admin_init', __NAMESPACE__ . '\activate_license' );

/**
 * Deactivates the license key.
 * This will decrease the site count.
 *
 * @return void
 */
function deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['youtubeforwordpresspro_edd_license_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'youtubeforwordpresspro_nonce', 'youtubeforwordpresspro_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'youtubeforwordpress_pro_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => YOUTUBEFORWORDPRESSPRO_ITEM_ID,
			'item_name'   => rawurlencode( YOUTUBEFORWORDPRESSPRO_ITEM_NAME ), // the name of our product in EDD
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			YOUTUBEFORWORDPRESSPRO_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$redirect = add_query_arg(
				array(
					'page'          => YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE,
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'youtubeforwordpress_pro_license_status' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . YOUTUBEFORWORDPRESSPRO_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action( 'admin_init', __NAMESPACE__ . '\deactivate_license' );

/**
 * Checks if a license key is still valid.
 * The updater does this for you, so this is only needed if you want
 * to do something custom.
 *
 * @return void
 */
function check_license() {

	$license = trim( get_option( 'youtubeforwordpress_pro_license_key' ) );

	$api_params = array(
		'edd_action'  => 'check_license',
		'license'     => $license,
		'item_id'     => YOUTUBEFORWORDPRESSPRO_ITEM_ID,
		'item_name'   => rawurlencode( YOUTUBEFORWORDPRESSPRO_ITEM_NAME ),
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		YOUTUBEFORWORDPRESSPRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 'valid' === $license_data->license ) {
		echo 'valid';
		exit;
		// this license is still valid
	} else {
		echo 'invalid';
		exit;
		// this license is no longer valid
	}
}