<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Link_Shortener_Statistics {

	private $log_file;

	public function __construct() {
		$this->log_file = WP_CONTENT_DIR . '/debug.log';
	}

	/**
	 * Processes the requested tracking action.
	 */
	public function process_tracking_request() {
		// Retrieve and validate the `original_url`
		$original_url = filter_input( INPUT_GET, 'original_url', FILTER_SANITIZE_URL );

		if ( $original_url && filter_var( $original_url, FILTER_VALIDATE_URL ) ) {
			$this->log_message( 'Redirecting to: ' . $original_url );

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
