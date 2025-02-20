<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handles the activation process of the WP Link Shortener plugin.
 *
 * Responsible for setting up the necessary database tables and default options or settings
 * required for the plugin to function correctly upon activation.
 */
class WP_Link_Shortener_Activation {

	/** Create necessary db table */
	public static function activate() {
		if ( self::can_activate() ) {
			$db_worker = new WP_Link_Shortener_DB_Handler();
			$db_worker->create_table();

			self::set_default_options();
		}
	}

	/**
	 * Checks if the activation process is permissible.
	 *
	 * @return bool
	 */
	private static function can_activate(): bool {
		return is_admin() && current_user_can( 'activate_plugins' );
	}

	/** Sets default plugin options. */
	private static function set_default_options(): void {
		add_option( 'wp_link_shortener_plugin_version', WP_Link_Shortener::VERSION );
		add_option( 'wp_link_shortener_db_version', WP_Link_Shortener::DB_VERSION );
		add_option( 'wp_link_shortener_default_redirect', '301' ); // Default redirect
	}
}
