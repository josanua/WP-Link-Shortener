<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WP_Link_Shortener_Admin
 *
 * Handles the admin area functionality for the WP Link Shortener plugin.
 */
class WP_Link_Shortener_Admin {

	/**
	 * Initializes the plugin functionality.
	 */
	public static function init() {
		$instance = new self();
	}

	/**
	 * Constructor.
	 *
	 * Registers admin hooks and initializes settings.
	 */
	public function __construct() {
		// Hook into admin initialization.
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Registers the plugin's admin menu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Link Shortener', 'wp-link-shortener' ),
			__( 'Link Shortener', 'wp-link-shortener' ),
			'manage_options',
			'wp-link-shortener',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Renders the admin page.
	 */
	public function render_admin_page() {

		$list_table = new WP_Link_Shortener_List_Table();
		$list_table->prepare_items();

		?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WP Link Shortener', 'wp-link-shortener' ); ?></h1>
			<?php $list_table->display(); ?>
        </div>
		<?php
	}

	/**
	 * Registers settings for the plugin.
	 */
	public function register_settings() {
		register_setting(
			'wp_link_shortener_options_group',
			'wp_link_shortener_options',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => [],
			]
		);

		add_settings_section(
			'wp_link_shortener_main_section',
			__( 'Main Settings', 'wp-link-shortener' ),
			null,
			'wp-link-shortener'
		);

		add_settings_field(
			'example_option',
			__( 'Example Option', 'wp-link-shortener' ),
			[ $this, 'render_example_option' ],
			'wp-link-shortener',
			'wp_link_shortener_main_section'
		);
	}

	/**
	 * Renders an example option field.
	 */
	public function render_example_option() {
		$options = get_option( 'wp_link_shortener_options' );
		?>
        <input
                type="text"
                name="wp_link_shortener_options[example_option]"
                value="<?php echo esc_attr( $options['example_option'] ?? '' ); ?>"
                class="regular-text"
        />
		<?php
	}

	/**
	 * Sanitizes the plugin settings.
	 *
	 * @param   array  $input  The input data to sanitize.
	 *
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = [];
		if ( isset( $input['example_option'] ) ) {
			$sanitized['example_option'] = sanitize_text_field( $input['example_option'] );
		}

		return $sanitized;
	}
}

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class WP_Link_Shortener_List_Table extends WP_List_Table {
	/**
	 * Prepare items for the table.
	 */
	public function prepare_items() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'link_shortener_plugin';
		$results    = $wpdb->get_results( "SELECT original_url, short_url, created_at FROM {$table_name}", ARRAY_A );

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
