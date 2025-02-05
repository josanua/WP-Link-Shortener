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
        <div class="wrap">
            <h1><?php esc_html_e( 'WP Link Shortener', 'wp-link-shortener' ); ?></h1>
            <p>A WordPress plugin enabling authorized users to create, manage, and track short links</p>
			<?php $this->render_add_link_item_form(); ?>
			<?php $list_table->display(); ?>
        </div>
		<?php
	}

	/**
	 * Create add item form.
	 */
	public function render_add_link_item_form() {
		?>
        <!-- todo: better to create globbaly and use utility classes -->
        <style>
            .mb-1 {
                margin-bottom: 1rem;
            }
        </style>
        <!-- end custom css -->

        <h2>Add Link Item</h2>
        <form method="post" action="">
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
                            name="wp_link_shortener_options[wls_item_name]"
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
                            name="wp_link_shortener_options[wls_original_url]"
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
                            name="wp_link_shortener_options[wls_short_url]"
                            class="regular-text"
                            required
                    />
                </div>
            </div>

            <button type="submit" class="button button-primary">
		        <?php esc_html_e( 'Add Short Link', 'wp-link-shortener' ); ?>
            </button>
        </form>
		<?php
	}

	/**
	 * Handle form submission.
	 */
	public function handle_form_submission() {
		// Check if the nonce is valid
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wp_link_shortener_nonce' ) ) {
			wp_die( __( 'Invalid nonce', 'wp-link-shortener' ) );
		}

		// Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
	        wp_die( __( 'You are not allowed to perform this action', 'wp-link-shortener' ) );
        }


		// Redirect back with a success message
		wp_safe_redirect( admin_url( 'tools.php?page=wp-link-shortener&updated=true' ) );
		exit;
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
	}

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
