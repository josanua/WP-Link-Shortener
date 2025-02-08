<?php
// Necessary core file
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

// Designed to manage custom plugin data for the WP Link Shortener.
class WP_Link_Shortener_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct([
			'singular' => 'link_shortener_item',  // Singular name
			'plural'   => 'link_shortener_items', // Plural name (and for nonce using)
			'ajax'     => false,                  // No AJAX support
		]);
	}

	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {

		// Init db worker
		$db_worker = new WP_Link_Shortener_DB_Handler();

		// Get all data
		// ! In the case of a large dataset, fetching all records at once could lead to performance issues.
		$results = $db_worker->get_all_items_data();

		// todo: Create another method which will Use the `offset` and `per_page` in database query to fetch data only relevant to the current pag
		// $results = $db_worker->get_items( $offset, $per_page ); // Example function
		// $results      = $db_worker->get_paginated_items( $orderby, $order, $offset, $per_page );
		// $total_items  = $db_worker->get_total_items();

		// Assign modified results to items
		$this->items = $results;

		// Handle bulk actions
		$this->process_bulk_action();

		// Data preparation
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		// setup pagination
		$per_page     = 10; // Items per page
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Total items for pagination
		$total_items = $db_worker->get_total_items();

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}


	protected function process_bulk_action() {
		// Check if the bulk action is "delete"
		if ( 'delete' === $this->current_action() ) {

			var_dump( $_POST);

			// Verify the nonce for security
			if (!empty($_POST['_wpnonce'])) {
				$nonce_action = 'bulk-' . $this->_args['plural']; // 'plural' needs to match what is passed in the constructor

				if (!wp_verify_nonce($_POST['_wpnonce'], $nonce_action)) {
					wp_die(__('Nonce verification failed.', 'wp-link-shortener')); // Kill the process if nonce verification fails
				}
			}


			// Get the selected IDs
			if ( isset( $_POST['id'] ) && is_array( $_POST['id'] ) ) {
				$ids_to_delete = array_map( 'intval', $_POST['id'] );

				// Use the database handler to delete the items
//				$db_worker = new WP_Link_Shortener_DB_Handler();
//				foreach ( $ids_to_delete as $id ) {
//					$db_worker->delete_item( $id );
//				}

				// Redirect to avoid resubmission
				wp_redirect( add_query_arg( [
					'page' => 'wp-link-shortener',
					'deleted' => count( $ids_to_delete )
				], admin_url( 'tools.php' ) ) );
				exit;
			}
		}
	}


	/**
	 * Setup table columns.
	 */
	public function get_columns() {
		return [
			'cb'           => '<input type="checkbox" />', // Checkbox for bulk actions
			'id'           => __( 'Id', 'wp-link-shortener' ),
			'item_name'    => __( 'Item Name', 'wp-link-shortener' ),
			'original_url' => __( 'Original URL', 'wp-link-shortener' ),
			'short_url'    => __( 'Short URL', 'wp-link-shortener' ),
			'click_count'  => __( 'Clicks', 'wp-link-shortener' ),
			'last_clicked' => __( 'Last click', 'wp-link-shortener' ),
			'ip_address'   => __( 'IP Address', 'wp-link-shortener' ),
			'user_agent'   => __( 'Browser', 'wp-link-shortener' ),
			'referer_data' => __( 'Referer', 'wp-link-shortener' ),
			'created_at'   => __( 'Created At', 'wp-link-shortener' ),
			'updated_at'   => __( 'Updated At', 'wp-link-shortener' ),
		];
	}

	/** Make columns sortable */
	public function get_sortable_columns() {

		// Sorting logic is missing todo: create sorting logic
//		$orderby = !empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';
//		$order   = !empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';
//		$results = $db_worker->get_items_sorted( $orderby, $order, $offset, $per_page );


		return [
			'item_name'    => [ 'id', true ],
			'original_url' => [ 'original_url', false ],
			'short_url'    => [ 'short_url', false ],
//			'created_at'   => [ 'created_at', false ],
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
			$short_url    = esc_url( $item['short_url'] );
			$original_url = esc_url( $item['original_url'] );

			// Create link for a tracking endpoint that logs clicks, from here statistics handler starting to work
//			'tracking_nonce' => wp_create_nonce( 'track_statistics_nonce' ) todo: analyze if it needed to create nonce here
//			check_admin_referer( 'track_statistics_nonce' );

			$tracking_url = add_query_arg( [
				'page'         => 'wp-link-shortener',       // Admin page slug
				'action'       => 'track_statistics',        // Custom action name
				'item_id'      => $item['id'],               // Item 'id' for easily/correctly finding in DB
				'original_url' => urlencode( $original_url ) // URL tracking parameter
			], admin_url( 'tools.php' ) );              // Point to 'tools.php' since that’s where your plugin lives

			return sprintf(
				'<a href="%s">%s</a>',
				esc_url( $tracking_url ),
				$short_url
			);
		}

		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}


	/** Checkbox for bulk actions */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			esc_attr( $item['id'] )
		);
	}

	public function get_bulk_actions() {
		return [
			'delete' => __( 'Delete', 'wp-link-shortener' ),
		];
	}

}
