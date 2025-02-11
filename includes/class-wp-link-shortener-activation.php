<?php

/**
 * Handles the activation process of the WP Link Shortener plugin.
 *
 * Responsible for setting up the necessary database tables and default options or settings
 * required for the plugin to function correctly upon activation.
 */
class WP_Link_Shortener_Activation {
	public static function activate() {

		// Init db worker, Create necessary db table
		if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
			// Trigger critical DB logic only when WordPress is ready.
			$db_handler = new WP_Link_Shortener_DB_Handler();
			$db_handler->create_table();
		}

		// Add default options or settings if required,
		// Set the plugin and database versions
		add_option( 'wp_link_shortener_plugin_version', WP_Link_Shortener::PLUGIN_VERSION );
		add_option( 'wp_link_shortener_db_version', WP_Link_Shortener::DB_VERSION );

		// Add other default options
		add_option( 'wp_link_shortener_default_redirect', '301' );
	}
}
