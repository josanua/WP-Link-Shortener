<?php
// Necessary core file
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class WP_Link_Shortener_List_Table extends WP_List_Table {
	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {

		// Init db worker
		$db_worker = new WP_Link_Shortener_DB_Worker();

		// Get all data
		$results    = $db_worker->get_all_items_data();

		// Assign modified results to items
		$this->items = $results;

		// Data preparation
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		// setup pagination
		$per_page = 10; // Items per page
		$current_page = $this->get_pagenum();
		$offset = ($current_page - 1) * $per_page;

		// Total items for pagination
		$total_items = $db_worker->get_total_items();

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
	 * Change default column behavior.
	 *
	 * @param   array   $item         Item data.
	 * @param   string  $column_name  Column name.
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {

		// Prepare the 'short_url' markup
		if ( 'short_url' === $column_name && isset( $item['short_url'] ) ) {
			$short_url = esc_url( $item['short_url'] );
			$original_url = esc_url( $item['original_url'] );

			// Create link for a tracking endpoint that logs clicks
			$tracking_url = add_query_arg( [
				'page'         => 'wp-link-shortener',   // Admin page slug
				'action'       => 'track_statistics',   // Custom action name
				'original_url' => urlencode( $original_url ) // URL tracking parameter
			], admin_url( 'tools.php' ) ); // Point to 'tools.php' since that’s where your plugin lives



			return sprintf(
				'<a href="%s">%s</a>',
				$tracking_url,
				$short_url
			);
		}

		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}


	/** Checkbox for bulk actions */
//	public function column_cb($item) {
//		return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
//	}
}
