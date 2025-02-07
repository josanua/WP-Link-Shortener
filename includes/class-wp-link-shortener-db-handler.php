<?php

// Perform setup tasks: Create DB tables, set default options, etc.
class WP_Link_Shortener_DB_Handler {
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
	           last_clicked DATETIME DEFAULT NULL,            -- Timestamp of the last click
	           ip_address VARCHAR(45) DEFAULT NULL,           -- User's IP address
	           user_agent TEXT DEFAULT NULL,                  -- User agent string
	           referer_data TEXT DEFAULT NULL,                -- Referer URL
	           created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, -- Timestamp of creation
	           updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL, -- Timestamp of update
	           PRIMARY KEY (id),
	           UNIQUE KEY short_url (short_url)
	       ) $this->charset_collate;";
	}

	/**
	 * Create the database table on first init.
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

	/**
	 * Save or update a link item in the database.
	 *
	 * @param   string  $item_name
	 * @param   string  $original_url
	 * @param   string  $short_url
	 */
	public function save_or_update_link( $item_name, $original_url, $short_url ) {
		global $wpdb;

		// verify by short_url entry value
		$existing_entry = $wpdb->get_row(
			$wpdb->prepare( "SELECT id FROM $this->table_name WHERE short_url = %s", $short_url )
		);

		if ( $existing_entry ) {
			$wpdb->update(
				$this->table_name,
				[
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'updated_at'   => current_time( 'mysql' ),
				],
				[ 'id' => $existing_entry->id ],
				[ '%s', '%s', '%s' ],
				[ '%d' ]
			);
		} else {
			$wpdb->insert(
				$this->table_name,
				[
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'short_url'    => $short_url,
					'created_at'   => current_time( 'mysql' ),
					'updated_at'   => current_time( 'mysql' ),
				],
				[ '%s', '%s', '%s', '%s', '%s' ]
			);
		}
	}

	public function get_total_items() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name" );
	}

	public function get_all_items_data() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM $this->table_name", ARRAY_A );

		//$results = $wpdb->get_results( "SELECT id, item_name, original_url, short_url, created_at FROM $this->table_name", ARRAY_A );
	}

	public function get_item_by_id( $id ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM $this->table_name WHERE id = $id", ARRAY_A );
	}

	public function delete_item_by_id( $id ) {
		global $wpdb;
		$wpdb->delete( $this->table_name, array( 'id' => $id ) );
	}

	public function insert_click_log( $data ) {
		global $wpdb;

		// Increment click count and update additional logging fields in a single query
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE $this->table_name
             SET click_count = click_count + 1,
                 ip_address = %s,
                 user_agent = %s,
                 referer = %s,
                 last_clicked = %s,
                 updated_at = %s
             WHERE id = %d",
				$data['ip_address'],    // User's IP address
				$data['user_agent'],    // User agent string
				$data['referer'],       // Referrer URL
				$data['timestamp'],     // Timestamp of the click
				$data['timestamp'],     // Timestamp of update
				$data['link_id']        // Link ID
			)
		);

		// Check if the query succeeded
		if ( $result === false ) {
			return new WP_Error( 'db_update_failed', 'Failed to update click log', $wpdb->last_error );
		}

		return true; // Successfully updated
	}


}
