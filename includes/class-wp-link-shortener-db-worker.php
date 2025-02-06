<?php

// Perform setup tasks: Create DB tables, set default options, etc.
class WP_Link_Shortener_DB_Worker {
	private $table_name;
	private $charset_collate;

	// Extracted constant for table name suffix
	private const TABLE_SUFFIX = 'link_shortener_plugin';

	/**
	 * Constructor to initialize necessary properties.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name      = $wpdb->prefix . self::TABLE_SUFFIX;
		$this->charset_collate = $wpdb->get_charset_collate();
	}

	/**
	 * Get the SQL schema for creating the table.
	 *
	 * @return string SQL query to create the database table.
	 */
	private function create_table_schema(): string {
		return "CREATE TABLE $this->table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            item_name VARCHAR(255) NOT NULL,               -- General item name
            original_url TEXT NOT NULL,                    -- Original URL being shortened
            short_url VARCHAR(255) NOT NULL,               -- Short link slug
            click_count BIGINT(20) DEFAULT 0 NOT NULL,     -- Number of clicks
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY short_url (original_url(191))
        ) $this->charset_collate;";
	}

	/**
	 * Create the database table.
	 */
	public function create_table() {
		global $wpdb;

		// Check if the table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$this->table_name'" ) === $this->table_name ) {
			return; // Table already exists, do nothing
		}

		$sql = $this->create_table_schema();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
