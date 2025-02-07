<?php

class WP_Link_Shortener_Activator {
	public static function activate() {

		// Init db worker, Create necessary db table
		$db_worker = new WP_Link_Shortener_DB_Worker();
		$db_worker->create_table();

		// Add default options or settings if required
		add_option( 'wp_link_shortener_db_version', '1.0.0' ); // todo: connect with main class
		add_option( 'wp_link_shortener_default_redirect', '301' );
	}
}
