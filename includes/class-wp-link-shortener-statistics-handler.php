<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Link_Shortener_Statistics_Handler {

	protected $db_handler;
	private $log_file;

	public function __construct() {
		// Specify the log file for debugging (optional)
		$this->log_file = WP_CONTENT_DIR . '/debug.log';

		// Instantiate the DB handler class
		$this->db_handler = new WP_Link_Shortener_DB_Handler();
	}

	/**
	 * Logs a click for a short link.
	 *
	 * @param int    $link_id The ID of the short link in the database.
	 * @param string $user_ip The IP address of the user clicking the short link.
	 * @param string $user_agent Information about the user's device/browser.
	 * @return bool True on success, false on failure.
	 */
	public function send_log_click_to_db( $link_id, $user_ip, $user_agent ) {
		// Prepare data for logging
		$data = [
			'link_id'    => $link_id,
			'ip_address' => $user_ip,
			'user_agent' => $user_agent,
			'timestamp'  => current_time( 'mysql' ), // Get current time in MySQL format
		];

		// Use the DB handler to insert the data
		return $this->db_handler->insert_click_log( $data );
	}


	/**
	 * Processes the requested tracking action.
	 */
	public function process_tracking_request() {

		// Retrieve and validate the `item_id`
		$item_id = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );

		// Retrieve and validate the `original_url`
		$original_url = filter_input( INPUT_GET, 'original_url', FILTER_SANITIZE_URL );

		if ( $original_url && filter_var( $original_url, FILTER_VALIDATE_URL ) && $item_id ) {

//			$this->send_log_click_to_db( $item_id );
			$this->log_message( 'Redirecting to: ' . $original_url . ' with Item ID: ' . $item_id );

			// Perform the redirection (if required)
			wp_redirect( $original_url );
			exit;
		}

		// Handle invalid URL case with a default response
		$this->log_message( 'Invalid URL passed for tracking.' );
		echo 'Invalid URL passed for tracking.';
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
}
