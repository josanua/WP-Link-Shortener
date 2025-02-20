<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handles database operations for the WP Link Shortener plugin.
 */
class WP_Link_Shortener_DB_Handler {
	private string $table_name;
	private string $charset_collate;
	private $wpdb;  // Declare $wpdb as a class property

	// Extracted constant for table name suffix
	private const TABLE_SUFFIX = 'link_shortener_plugin';

	/**
	 * Constructor to initialize necessary properties.
	 */
	public function __construct() {
		global $wpdb; // Initialize global $wpdb

		if ( ! isset( $wpdb ) ) {
			error_log( 'Object $wpdb is ' . ( isset( $wpdb ) ? 'set' : 'not set' ) );
		}
		error_log('construct');
		$this->wpdb            = $wpdb; // Assign $wpdb to the class property.
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
	           created_at DATETIME NOT NULL, -- Timestamp of creation
	           updated_at DATETIME NOT NULL, -- Timestamp of update
	           PRIMARY KEY (id),
	           UNIQUE KEY short_url (short_url)
	       ) $this->charset_collate;";
	}

	/**
	 * Create the database table on first init.
	 */
	public function create_table() {
		// Check if the table exists.
		$table_exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$this->table_name
			)
		);

		if ( $table_exists === $this->table_name ) {
			return; // Table already exists, do nothing.
		}

		$sql = $this->create_table_schema();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		error_log( 'create_table' );
		dbDelta( $sql );
	}


	/**
	 * Save or update a link item in the database.
	 *
	 * @param string $item_name
	 * @param string $original_url
	 * @param string $short_url
	 */
	public function save_or_update_link( string $item_name, string $original_url, string $short_url ) {

		// verify by short_url entry value
		$existing_entry = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT id FROM $this->table_name WHERE short_url = %s", $short_url )
		);

		if ( $existing_entry ) {
			$this->wpdb->update(
				$this->table_name,
				array(
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'updated_at'   => current_time( 'mysql' ),
				),
				array( 'id' => $existing_entry->id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$this->wpdb->insert(
				$this->table_name,
				array(
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'short_url'    => $short_url,
					'created_at'   => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%s' )
			);
		}
	}

	/**
	 * Retrieve total number of items.
	 *
	 * @return int
	 */
	public function get_total_items(): int {
		return (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name" );
	}

	/**
	 * Fetch all items data.
	 *
	 * @return array
	 */
	public function get_all_items_data(): array {
		return $this->wpdb->get_results( "SELECT * FROM $this->table_name" ,ARRAY_A);
	}

	/**
	 * Pagination with customizable order and limits.
	 * todo: for future use
	 */
	public function get_paginated_items( string $order_by = 'id', string $order = 'asc', int $offset = 0, int $per_page = 10 ) {
		// Sanitize inputs
		$order_by = esc_sql( $order_by );
		$order    = in_array( strtolower( $order ), array( 'asc', 'desc' ), true ) ? strtoupper( $order ) : 'ASC';

		$query = $this->wpdb->prepare(
			"SELECT * FROM $this->table_name ORDER BY $order_by $order LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);

		return $this->wpdb->get_results( $query, ARRAY_A );
	}



	/**
	 * Fetch a single item by ID.
	 *
	 * @param int $id
	 *
	 * @return array|null
	 */
	public function get_item_by_id( int $id ): ?array {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE id = %d",
				$id
			),
			ARRAY_A
		);
	}

	/**
	 * Delete an item from the database.
	 *
	 * @param int $id
	 *
	 * @return int|false
	 */
	public function delete_item( int $id ) {
		return $this->wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Insert click logs and update link details.
	 *
	 * @param array $data
	 *
	 * @return true|WP_Error
	 */
	public function insert_click_log( array $data ) {
		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"UPDATE $this->table_name
	             SET click_count = click_count + 1,
	                 ip_address = %s,
	                 user_agent = %s,
	                 referer_data = %s,
	                 last_clicked = %s
	             WHERE id = %d",
				$data['ip_address'],    // User's IP address
				$data['user_agent'],    // User agent string
				$data['referer'],       // Referrer URL
				$data['last_clicked'],  // Timestamp of the click
				$data['id']             // Link ID
			)
		);

		if ( false === $result ) {
			return new WP_Error( 'db_update_failed', 'Failed to update click log', $this->wpdb->last_error );
		}

		return true;
	}
}
