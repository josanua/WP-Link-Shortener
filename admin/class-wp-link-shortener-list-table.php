<?php
// Necessary core file
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class WP_Link_Shortener_List_Table extends WP_List_Table {
	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'link_shortener_plugin';
		$results    = $wpdb->get_results( "SELECT id, item_name, original_url, short_url, created_at FROM {$table_name}", ARRAY_A );

		$this->items = $results;

		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		// setup pagination
		$per_page = 10; // Items per page
		$current_page = $this->get_pagenum();
		$offset = ($current_page - 1) * $per_page;

		// Total items for pagination
		$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items / $per_page),
		]);
	}

	/**
	 * Define table columns.
	 */
	public function get_columns() {
		return [
			'cb'           => '<input type="checkbox" />', // Checkbox for bulk actions
			'id'    => __( 'Id', 'wp-link-shortener' ),
			'item_name'    => __( 'Item Name', 'wp-link-shortener' ),
			'original_url' => __( 'Original URL', 'wp-link-shortener' ),
			'short_url'    => __( 'Short URL', 'wp-link-shortener' ),
			'created_at'   => __( 'Created At', 'wp-link-shortener' ),
			'click_count'  => __( 'Clicks', 'wp-link-shortener' ),
			'ip_address'   => __( 'IP Address', 'wp-link-shortener' ),
		];
	}

	/** Make columns sortable */
	public function get_sortable_columns() {
		return [
			'item_name'    => [ 'id', true ],
			'original_url' => [ 'original_url', false ],
			'short_url'    => [ 'short_url', false ],
			'created_at'   => [ 'created_at', false ],
			'click_count'  => [ 'click_count', false ],
		];
	}

	/**
	 * Default column behavior.
	 *
	 * @param   array   $item         Item data.
	 * @param   string  $column_name  Column name.
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/** Checkbox for bulk actions */
//	public function column_cb($item) {
//		return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
//	}
}
