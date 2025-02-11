<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Link_Shortener_Statistics_Handler {

	protected WP_Link_Shortener_DB_Handler $db_handler;
	private string $log_file;
	private bool $activate_debug_mode = true;

	public function __construct() {
		// Specify the log file (optional)
		if ($this->activate_debug_mode) {
			$this->log_file = WP_CONTENT_DIR . '/debug.log';
		}

		// Instantiate the DB handler class
		$this->db_handler = new WP_Link_Shortener_DB_Handler();
	}

	/**
	 * Logs a click for a short link.
	 *
	 * @param   int          $id          The ID of the short link in the database.
	 * @param   string|null  $user_ip     The IP address of the user clicking the short link.
	 * @param   string       $user_agent  Information about the user's device/browser.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function send_log_click_stats_to_db( int $id, ?string $user_ip = null, string $user_agent = 'No-data', $referer = 'No-data' ) {

		// Increment the click count for the given link ID
		// Prepare data for logging the individual click details
		$data = [
			'id'    => $id,                     // The unique ID of the link
			'ip_address' => $user_ip,           // The visitor's IP address
			'user_agent' => $user_agent,        // The browser or device info
			'referer'    => $referer,           // The referring URL or "No referer"
			'last_clicked'  => current_time( 'mysql' ),   // The timestamp
		];

		$result = $this->db_handler->insert_click_log( $data );

		// Stop execution if the click count update fails
		if ( ! $result ) {
			// Optionally log the failure
			if ( $this->activate_debug_mode ) {
				$this->log_message( "Failed to write stats data: $id" );
			}

			return false;
		} else {
			$this->log_message( "Write DB result data id=: $id" );
			$this->log_message( "IP =: $user_ip" );
			$this->log_message( "user_agent =: $user_agent" );
			$this->log_message( "referer =: $referer" );
		}

		return $result;
	}


	/**
	 * Processes the requested tracking action.
	 */
	public function process_tracking_request() {

		// Retrieve and validate the `id` and `original_url` items
		$id = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
		$original_url = filter_input( INPUT_GET, 'original_url', FILTER_SANITIZE_URL );

		// Check fields
		if ( $original_url && filter_var( $original_url, FILTER_VALIDATE_URL ) && $id ) {

			// Retrieve users data
			$user_ip = $this->get_user_ip();
			$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'No-data';
			$referer = $_SERVER['HTTP_REFERER'] ?? 'No-data';

			// send data to db and trigger click counter
			$this->send_log_click_stats_to_db( $id, $user_ip, $user_agent, $referer );

			if ($this->activate_debug_mode) {
				$this->log_message( 'Redirecting to: ' . $original_url . ' with Item ID: ' . $id );
			}

			// Perform the redirection (if required)
			wp_redirect( $original_url );
			exit;
		}

		// Handle invalid URL case with a default response
		if ($this->activate_debug_mode) {
			$this->log_message( 'plugin:wp-link-shortener: Invalid URL passed for tracking.' );
		}
		exit;
	}

	/**
	 * Logs messages to the debug file.
	 *
	 * @param string $message
	 */
	private function log_message( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			@file_put_contents(
				$this->log_file,
				date( 'Y-m-d H:i:s' ) . ' - ' . $message . PHP_EOL,
				FILE_APPEND
			);
		}
	}

	/**
	 * Retrieves the IP address of the user from the server variables.
	 * It checks for the IP in the following order:
	 * - HTTP_CLIENT_IP
	 * - HTTP_X_FORWARDED_FOR (takes the first value in a comma-separated list)
	 * - REMOTE_ADDR
	 * Validates the retrieved IP address and returns it, or returns 'Unknown' if the validation fails.
	 *
	 * @return string The validated user IP address or 'Unknown' if validation fails.
	 */
	private function get_user_ip(): string {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return filter_var($ip, FILTER_VALIDATE_IP) ?: 'Unknown';
	}
}
