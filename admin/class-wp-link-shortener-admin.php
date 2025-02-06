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
//		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Handle form submission
		add_action( 'admin_post_wp_link_shortener_save', [ $this, 'handle_form_submission' ] );

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
            <span class="notice mb-1">At the moment, to update an item, you need to enter the existing value of the Short URL field.</span>
	        <?php
                if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
                    echo '<div class="updated notice"><p>' . esc_html__( 'Data saved successfully.', 'wp-link-shortener' ) . '</p></div>';
                }
	        ?>
            <div class="mt-2">
			    <?php $this->render_add_link_item_form(); ?>
            </div>
			<?php $list_table->display(); ?>
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
                    <label for="wls_item_name" class="bold strong b">
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

		// Work with DB
		global $wpdb;
		$table_name = $wpdb->prefix . 'link_shortener_plugin';

		// Insert or update the data
		$existing_entry = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table_name WHERE short_url = %s", $short_url ) );

        // Update or Save new entry
		if ( $existing_entry ) {
			// Update existing entry
			$wpdb->update(
				$table_name,
				[
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'updated_at'   => current_time( 'mysql' )
				],
				[ 'id' => $existing_entry->id ],
				[ '%s', '%s', '%s' ],
				[ '%d' ]
			);
		} else {
			// Insert new entry
			$wpdb->insert(
				$table_name,
				[
					'item_name'    => $item_name,
					'original_url' => $original_url,
					'short_url'    => $short_url,
					'created_at'   => current_time( 'mysql' ),
					'updated_at'   => current_time( 'mysql' )
				],
				[ '%s', '%s', '%s', '%s', '%s' ]
			);
		}

		// Redirect back to admin page with a success message
		wp_safe_redirect( admin_url( 'tools.php?page=wp-link-shortener&updated=true' ) );
		exit;
	}

	/**
	 * Registers settings for the plugin.
	 */
//	public function register_settings() {
//		register_setting(
//			'wp_link_shortener_options_group',
//			'wp_link_shortener_options',
//			[
//				'type'              => 'array',
//				'sanitize_callback' => [ $this, 'sanitize_settings' ],
//				'default'           => [],
//			]
//		);
//
//		add_settings_section(
//			'wp_link_shortener_main_section',
//			__( 'Main Settings', 'wp-link-shortener' ),
//			null,
//			'wp-link-shortener'
//		);
//	}

	/**
	 * Sanitizes the plugin settings.
	 *
	 * @param   array  $input  The input data to sanitize.
	 *
	 * @return array Sanitized settings.
	 */
//	public function sanitize_settings( $input ) {
//		$sanitized = [];
//		if ( isset( $input['example_option'] ) ) {
//			$sanitized['example_option'] = sanitize_text_field( $input['example_option'] );
//		}
//
//		return $sanitized;
//	}
}
