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
		add_menu_page(
			__( 'Link Shortener', 'wp-link-shortener' ),
			__( 'Link Shortener', 'wp-link-shortener' ),
			'manage_options',
			'wp-link-shortener',
			[ $this, 'render_admin_page' ],
			'dashicons-admin-links',
			25
		);
	}

	/**
	 * Renders the admin page.
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Link Shortener', 'wp-link-shortener' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp_link_shortener_options_group' );
				do_settings_sections( 'wp-link-shortener' );
				submit_button();
				?>
			</form>
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
