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

	private static ?self $instance = null;

	/**
	 * @var WP_Link_Shortener_DB_Handler
	 */
	private WP_Link_Shortener_DB_Handler $db_handler;

	/**
	 * @var WP_Link_Shortener_List_Table|null
	 */
	private ?WP_Link_Shortener_List_Table $list_table = null;

	/**
	 * Initialize the class.
	 * Only loads the instance for admin contexts.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Constructor.
	 * Private to enforce Singleton pattern.
	 */
	private function __construct() {
		$this->db_handler = new WP_Link_Shortener_DB_Handler();

		// Hook into admin initialization.
		add_action( 'admin_init', array( $this, 'call_list_table_handler' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'plugins_loaded', array( $this, 'call_statistic_handler' ) );
		add_action( 'admin_post_wp_link_shortener_save', array( $this, 'handle_form_submission' ) );
	}

	/**
	 * Registers the plugin's admin menu.
	 */
	public static function register_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'WP Link Shortener', 'wp-link-shortener' ),
			__( 'Link Shortener', 'wp-link-shortener' ),
			'manage_options',
			'wp-link-shortener',
			array( self::$instance, 'render_admin_page' )
		);
	}

	public function call_list_table_handler() {
		// Handle bulk actions
		$this->list_table = WP_Link_Shortener_List_Table::get_instance();
		$this->list_table->prepare_items();
	}

	/**
	 * Renders the admin page.
	 */
	public function render_admin_page() {
		?>
		<!-- todo: better to create globally and use utility classes -->
		<style>
			.mb-1 {
				margin-bottom: 1rem;
			}
			.mt-2 {
				margin-top: 2rem;
			}
		</style>
		<!-- end custom css -->
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Link Shortener', 'wp-link-shortener' ); ?></h1>
			<p>A WordPress plugin enabling authorized users to create, manage, and track short links</p>
			<span class="notice mb-1">In this release, to update an item, you must provide the current value of the Short URL field.</span>
			<?php

			// show notice-success in case of item updating
			if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
				echo '<div class="updated notice"><p>' . esc_html__( 'Data saved successfully.', 'wp-link-shortener' ) . '</p></div>';
			}

			// show notice-success in case of item deleting
			if ( isset( $_GET['deleted'] ) && intval( $_GET['deleted'] ) > 0 ) {
				printf(
					'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
					sprintf( __( '%d item(s) deleted successfully.', 'wp-link-shortener' ), intval( $_GET['deleted'] ) )
				);
			}
			?>
			<div class="mt-2 wp-link-shortener-add-link-item-form-wrapp">
				<?php $this->render_add_link_item_form(); ?>
			</div><!-- /.wp-link-shortener-add-link-item-form-wrapp -->

			<div class="mt-2 wp-link-shortener-list-table-wrapp">
				<form method="post">
					<?php $this->list_table->display(); ?>
				</form>
			</div><!-- /.wp-link-shortener-list-table-wrapp -->
		</div>
		<?php
	}

	/**
	 * Create add item form.
	 */
	public function render_add_link_item_form() {
		?>
		<h2>Add or Update Link Item</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wp_link_shortener_save">
			<?php wp_nonce_field( 'wp_link_shortener_nonce', '_wpnonce' ); ?>

			<div class="form-wrap add-link-item-form-wrapp">
				<div class="mb-1">
					<label for="wls_item_name">
						<strong>
							<?php esc_html_e( 'Item Name', 'wp-link-shortener' ); ?>
						</strong>
					</label>
					<input
							type="text"
							id="wls_item_name"
							name="wls_item_name"
							class="regular-text"
							required
					/>
				</div>
				<div class="mb-1">
					<label for="wls_original_url">
						<strong>
							<?php esc_html_e( 'Original URL', 'wp-link-shortener' ); ?>
						</strong>
					</label>

					<input
							type="url"
							id="wls_original_url"
							name="wls_original_url"
							class="regular-text"
							required
					/>
				</div>
				<div class="mb-1">
					<label for="wls_short_url">
						<strong>
							<?php esc_html_e( 'Short URL', 'wp-link-shortener' ); ?>
						</strong>
					</label>
					<input
							type="text"
							id="wls_short_url"
							name="wls_short_url"
							class="regular-text"
							required
					/>
				</div>
			</div>

			<button type="submit" class="button button-primary mb-1">
				<?php esc_html_e( 'Add/Update Short Link', 'wp-link-shortener' ); ?>
			</button>
		</form>
		<?php
	}

	/**
	 * Handle form submission and save data to the database.
	 */
	public function handle_form_submission() {

		// Check for valid nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wp_link_shortener_nonce' ) ) {
			wp_die( __( 'Invalid nonce', 'wp-link-shortener' ) );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not allowed to perform this action', 'wp-link-shortener' ) );
		}

		// Prepare input data
		$item_name    = isset( $_POST['wls_item_name'] ) ? sanitize_text_field( $_POST['wls_item_name'] ) : '';
		$original_url = isset( $_POST['wls_original_url'] ) ? esc_url_raw( $_POST['wls_original_url'] ) : '';
		$short_url    = isset( $_POST['wls_short_url'] ) ? sanitize_text_field( $_POST['wls_short_url'] ) : '';

		// Insert or update the data
		$this->db_handler->save_or_update_link( $item_name, $original_url, $short_url );

		// Redirect back to admin page with a success message
		wp_safe_redirect( admin_url( 'tools.php?page=wp-link-shortener&updated=true' ) );
		exit;
	}

	/**
	 * Handles the statistics tracking requests for a given URL and item ID.
	 * @return void This method does not return a value.
	 */
	public function call_statistic_handler() {
		if ( isset( $_GET['original_url'] ) && isset( $_GET['item_id'] ) ) {
			$statistics_handler = new WP_Link_Shortener_Statistics_Handler();
			$id                 = filter_input( INPUT_GET, 'item_id', FILTER_SANITIZE_NUMBER_INT );
			$original_url       = filter_input( INPUT_GET, 'original_url', FILTER_SANITIZE_URL );

			if ( $original_url && filter_var( $original_url, FILTER_VALIDATE_URL ) && $id ) {
				$statistics_handler->process_tracking_request();
			} else {
				error_log( 'Invalid URL or ID in the request.' );
			}
		}
	}
}
