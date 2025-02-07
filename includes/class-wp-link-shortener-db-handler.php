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
            -- last_clicked TIMESTAMP DEFAULT NULL       -- Timestamp of the last click
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY short_url (original_url(191))
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
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name" );

		return $total_items;
	}

	public function get_all_items_data() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM $this->table_name", ARRAY_A );

		//$results = $wpdb->get_results( "SELECT id, item_name, original_url, short_url, created_at FROM $this->table_name", ARRAY_A );

		return $results;
	}

	public function get_item_by_id( $id ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM $this->table_name WHERE id = $id", ARRAY_A );
	}

	public function delete_item_by_id( $id ) {
		global $wpdb;
		$wpdb->delete( $this->table_name, array( 'id' => $id ) );
	}

	public function insert_click_log($link_id) {
		global $wpdb;

		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE $this->table_name  SET click_count = click_count + 1, updated_at = %s WHERE id = %d",
				current_time( 'mysql' ),
				$link_id
			)
		);

		return $result;
	}
}
