<?php

class WP_Link_Shortener_Activator {
	public static function activate() {
		// Create or update database tables
		global $wpdb;
		$table_name      = $wpdb->prefix . 'link_shortener';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			short_url VARCHAR(255) NOT NULL,
			original_url TEXT NOT NULL,
			clicks BIGINT(20) DEFAULT 0 NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY short_url (short_url)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Add default options or settings if required
		add_option( 'wp_link_shortener_version', '1.0.0' );
		add_option( 'wp_link_shortener_default_redirect', '301' );
	}
}
