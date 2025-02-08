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
	 * @var WP_Link_Shortener_DB_Handler
	 */
	private $db_worker;


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
		// Initialize the DB worker
		$this->db_worker = new WP_Link_Shortener_DB_Handler();

		// Hook into admin initialization.
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_post_wp_link_shortener_save', [ $this, 'handle_form_submission' ] );
	}

	/**
	 * Registers the plugin's admin menu.
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'WP Link Shortener', 'wp-link-shortener' ),
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
					<?php $list_table->display(); ?>
				</form>
			</div><!-- /.wp-link-shortener-list-table-wrapp -->
        </div>
		<?php
	}

	/**
	 * Create add item form.
	 */
	public function render_add_link_item_form() { ?>
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
		$this->db_worker->save_or_update_link( $item_name, $original_url, $short_url );

		// Redirect back to admin page with a success message
		wp_safe_redirect( admin_url( 'tools.php?page=wp-link-shortener&updated=true' ) );
		exit;
	}
}
