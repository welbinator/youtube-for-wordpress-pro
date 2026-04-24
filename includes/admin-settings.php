<?php
/**
 * Admin settings and API key handler for YouTube for WordPress Pro.
 *
 * @package YouTubeForWordPressPro
 */

namespace YouTubeForWP\Admin\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles encryption and storage of the YouTube API key.
 */
class API_Key_Handler {
	/**
	 * Option name for storing the encrypted API key.
	 */
	private const ENCRYPTED_API_KEY_OPTION = 'yt_for_wp_encrypted_api_key';

	/**
	 * Option name for storing the encryption key.
	 */
	private const ENCRYPTION_KEY_OPTION = 'yt_for_wp_encryption_key';

	/**
	 * Generate a new encryption key.
	 *
	 * @return string|false Base64-encoded key or false on failure.
	 */
	private function generate_encryption_key() {
		try {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Used for binary key storage, not obfuscation.
			return base64_encode( random_bytes( SODIUM_CRYPTO_SECRETBOX_KEYBYTES ) );
		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'YouTube for WordPress Pro: Failed to generate encryption key: ' . $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Get or create the encryption key.
	 *
	 * @return string|false Raw binary key or false on failure.
	 */
	private function get_encryption_key() {
		$key = get_option( self::ENCRYPTION_KEY_OPTION );

		if ( ! $key ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			$key = $this->generate_encryption_key();
			if ( false === $key ) {
				return false;
			}
			add_option( self::ENCRYPTION_KEY_OPTION, $key, '', false );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Decoding binary key from storage, not obfuscation.
		return base64_decode( $key );
	}

	/**
	 * Encrypt the API key using libsodium.
	 *
	 * @param string $api_key The API key to encrypt.
	 * @return string|false Encrypted value or false on failure.
	 */
	public function encrypt_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return false;
		}

		if ( ! function_exists( 'sodium_crypto_secretbox' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'YouTube for WordPress Pro: Sodium encryption not available.' );
			}
			return false;
		}

		try {
			$key = $this->get_encryption_key();
			if ( false === $key ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( 'YouTube for WordPress Pro: Failed to get encryption key.' );
				}
				return false;
			}

			$nonce     = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$encrypted = sodium_crypto_secretbox( $api_key, $nonce, $key );

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Binary data encoded for DB storage, not obfuscation.
			return base64_encode( $nonce . $encrypted );
		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'YouTube for WordPress Pro: Encryption error: ' . $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Decrypt the API key.
	 *
	 * @param string $encrypted_api_key The encrypted API key to decrypt.
	 * @return string|false Decrypted API key or false on failure.
	 */
	public function decrypt_api_key( $encrypted_api_key ) {
		if ( empty( $encrypted_api_key ) ) {
			return false;
		}

		if ( ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'YouTube for WordPress Pro: Sodium decryption not available.' );
			}
			return false;
		}

		try {
			$key = $this->get_encryption_key();
			if ( false === $key ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( 'YouTube for WordPress Pro: Failed to get decryption key.' );
				}
				return false;
			}

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Decoding binary data from DB storage, not obfuscation.
			$decoded = base64_decode( $encrypted_api_key );
			if ( false === $decoded ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( 'YouTube for WordPress Pro: Failed to decode encrypted data.' );
				}
				return false;
			}

			$nonce     = substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$encrypted = substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

			$decrypted = sodium_crypto_secretbox_open( $encrypted, $nonce, $key );
			if ( false === $decrypted ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( 'YouTube for WordPress Pro: Decryption failed.' );
				}
				return false;
			}

			return $decrypted;
		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'YouTube for WordPress Pro: Decryption error: ' . $e->getMessage() );
			}
			return false;
		}
	}

	/**
	 * Save the API key (encrypted).
	 *
	 * @param string $api_key The API key to save.
	 * @return bool True on success, false on failure.
	 */
	public function save_api_key( $api_key ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( empty( $api_key ) ) {
			delete_option( self::ENCRYPTED_API_KEY_OPTION );
			return true;
		}

		$encrypted = $this->encrypt_api_key( $api_key );
		if ( false === $encrypted ) {
			return false;
		}

		return update_option( self::ENCRYPTED_API_KEY_OPTION, $encrypted, false );
	}

	/**
	 * Get the decrypted API key.
	 *
	 * @return string|false Decrypted API key or false if not set.
	 */
	public function get_decrypted_api_key() {
		$encrypted = get_option( self::ENCRYPTED_API_KEY_OPTION );
		if ( empty( $encrypted ) ) {
			return false;
		}

		return $this->decrypt_api_key( $encrypted );
	}
}

// Create a global instance.
$GLOBALS['yt_for_wp_api_handler'] = new API_Key_Handler();

/**
 * Get the decrypted YouTube API key.
 *
 * @return string|false The API key or false if not set.
 */
function get_api_key() {
	return $GLOBALS['yt_for_wp_api_handler']->get_decrypted_api_key();
}

/**
 * Register settings and add validation.
 */
function register_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Register the settings section.
	add_settings_section(
		'yt_for_wp_main_section',
		__( 'Main Settings', 'yt-for-wp-pro' ),
		'__return_false',
		'yt-for-wp-settings'
	);

	// Register API Key field.
	add_settings_field(
		'yt_for_wp_api_key',
		__( 'YouTube API Key', 'yt-for-wp-pro' ),
		function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$api_key = get_api_key();
			?>
			<input
				type="password"
				name="yt_for_wp_api_key"
				value="<?php echo esc_attr( $api_key ? $api_key : '' ); ?>"
				class="regular-text"
			/>
			<p class="description">
				<?php esc_html_e( 'Your YouTube API key will be stored securely using encryption.', 'yt-for-wp-pro' ); ?>
			</p>
			<?php
		},
		'yt-for-wp-settings',
		'yt_for_wp_main_section'
	);

	// Register Channel ID field.
	add_settings_field(
		'yt_for_wp_channel_id',
		__( 'YouTube Channel ID', 'yt-for-wp-pro' ),
		function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$channel_id = get_option( 'yt_for_wp_channel_id', '' );
			?>
			<input
				type="text"
				name="yt_for_wp_channel_id"
				value="<?php echo esc_attr( $channel_id ); ?>"
				class="regular-text"
			/>
			<?php
		},
		'yt-for-wp-settings',
		'yt_for_wp_main_section'
	);

	// Register the API key setting.
	register_setting(
		'yt_for_wp_settings',
		'yt_for_wp_api_key',
		array(
			'sanitize_callback' => function ( $input ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					add_settings_error(
						'yt_for_wp_api_key',
						'invalid_permissions',
						__( 'You do not have permission to modify these settings.', 'yt-for-wp-pro' ),
						'error'
					);
					return get_api_key();
				}

				if ( empty( $input ) ) {
					delete_option( 'yt_for_wp_encrypted_api_key' );
					return '';
				}

				$sanitized_input = sanitize_text_field( $input );
				$saved           = $GLOBALS['yt_for_wp_api_handler']->save_api_key( $sanitized_input );

				if ( ! $saved ) {
					add_settings_error(
						'yt_for_wp_api_key',
						'encryption_failed',
						__( 'Failed to securely store the API key. Please try again.', 'yt-for-wp-pro' ),
						'error'
					);
				}

				return $sanitized_input;
			},
			'show_in_rest'      => false,
		)
	);

	// Register the channel ID setting.
	register_setting(
		'yt_for_wp_settings',
		'yt_for_wp_channel_id',
		array(
			'sanitize_callback' => function ( $input ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return get_option( 'yt_for_wp_channel_id' );
				}
				return sanitize_text_field( $input );
			},
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );

/**
 * Render the settings page content.
 */
function render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'You do not have sufficient permissions to access this page.', 'yt-for-wp-pro' )
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'YouTube for WordPress Settings', 'yt-for-wp-pro' ); ?></h1>

		<?php
		settings_errors( 'yt_for_wp_api_key' );

		$api_key        = get_api_key();
		$status_message = empty( $api_key )
			? __( 'Enter your YouTube API key here.', 'yt-for-wp-pro' )
			: validate_api_key( $api_key );

		echo '<p id="api-key-status">' . esc_html( $status_message ) . '</p>';
		?>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'yt_for_wp_settings' );
			do_settings_sections( 'yt-for-wp-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Validate the API key by making a test request to the YouTube API.
 *
 * @param string $api_key The API key to validate.
 * @return string Validation status message.
 */
function validate_api_key( $api_key ) {
	if ( empty( $api_key ) ) {
		return __( 'API Key not set.', 'yt-for-wp-pro' );
	}

	$channel_id = get_option( 'yt_for_wp_channel_id' );

	$args = array(
		'headers' => array(
			'Referer' => get_site_url(),
			'Origin'  => get_site_url(),
		),
	);

	if ( empty( $channel_id ) ) {
		$response = wp_remote_get(
			add_query_arg(
				array(
					'part'       => 'snippet',
					'key'        => $api_key,
					'maxResults' => 1,
					'type'       => 'video',
				),
				'https://www.googleapis.com/youtube/v3/search'
			),
			$args
		);
	} else {
		$response = wp_remote_get(
			add_query_arg(
				array(
					'part'       => 'snippet',
					'channelId'  => $channel_id,
					'maxResults' => 1,
					'type'       => 'video',
					'key'        => $api_key,
				),
				'https://www.googleapis.com/youtube/v3/search'
			),
			$args
		);
	}

	if ( is_wp_error( $response ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube API validation error: ' . $response->get_error_message() );
		}
		return __( 'API Key validation failed.', 'yt-for-wp-pro' );
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( isset( $data['error'] ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'YouTube API error: ' . wp_json_encode( $data['error'] ) );
		}

		if ( isset( $data['error']['errors'][0]['reason'] ) ) {
			$reason = $data['error']['errors'][0]['reason'];

			if ( 'quotaExceeded' === $reason ) {
				return __( 'API quota exceeded. Key may still be valid.', 'yt-for-wp-pro' );
			}

			if ( in_array( $reason, array( 'badRequest', 'invalid', 'authError' ), true ) ) {
				return __( 'API Key Invalid.', 'yt-for-wp-pro' );
			}

			if ( 'API_KEY_HTTP_REFERRER_BLOCKED' === $reason ) {
				return __( 'API Key is valid but restricted to specific domains. Please check your API key settings in Google Cloud Console.', 'yt-for-wp-pro' );
			}
		}

		return sprintf(
			/* translators: %s: Error message from the YouTube API. */
			__( 'API Error: %s', 'yt-for-wp-pro' ),
			isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Unknown error.', 'yt-for-wp-pro' )
		);
	}

	return __( 'API Key Valid.', 'yt-for-wp-pro' );
}
