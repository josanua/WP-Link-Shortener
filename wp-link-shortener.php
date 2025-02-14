<?php
/**
 * Plugin Name: WP-Link-Shortener
 * Description: A custom plugin to create and manage shortened links in WordPress.
 * Version: 1.0.0
 * Author: Josanu Andrei
 * Author URI: https://wpforpro.com/about-me/
 * License: GPL2
 * Text Domain: wp-link-shortener
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Link_Shortener {

	// Singleton instance preparation
	private static ?self $instance = null;

	// Prevent the cloning of the instance
	private function __clone() {}

	// Singleton Instance
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	// Plugin Constants
	const PLUGIN_VERSION = '1.0.1';
	const DB_VERSION     = '1.0.0';
	const SLUG           = 'wp-link-shortener';
	const PLUGIN_DIR     = __DIR__;
	const INCLUDES_PATH  = self::PLUGIN_DIR . '/includes';
	const ADMIN_PATH     = self::PLUGIN_DIR . '/admin';

	private string $plugin_url;

	// Constructor
	private function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->define_constants();

		// Register hooks
		$this->register_hooks();
	}

	// Define/redefine constants
	private function define_constants(): void {
		defined( 'WP_LINK_SHORTENER_PATH' ) || define( 'WP_LINK_SHORTENER_PATH', self::PLUGIN_DIR );
		defined( 'WP_LINK_SHORTENER_URL' ) || define( 'WP_LINK_SHORTENER_URL', $this->plugin_url );
	}

	// Register all plugin hooks
	private function register_hooks(): void {
		if ( is_admin() ) {
			// Admin-only: Register hooks specific to WordPress admin
			add_action( 'init', array( $this, 'initialize_plugin' ) );
		}
	}

	// Plugin initialization
	public function initialize_plugin(): void {
		// This plugin will work only in the Admin interface
		if ( is_admin() ) {
			$this->load_dependencies();
			$this->initialize_admin_logic();

			if ( isset( $_GET['original_url'] ) && isset( $_GET['item_id'] ) ) {
				$statistics_handler = new WP_Link_Shortener_Statistics_Handler();
				$statistics_handler->process_tracking_request();
			}
		}
	}

	// Load all required dependencies
	private function load_dependencies(): void {
		// Shared includes (if any)
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-statistics-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-db-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-activation.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-deactivation.php';

		// Admin-specific classes
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-admin.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-list-table.php';
	}

	// Initialize admin-specific logic
	private function initialize_admin_logic(): void {
		// Register activation and deactivation hooks (admin-related logic)
		register_activation_hook( __FILE__, array( 'WP_Link_Shortener_Activation', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'WP_Link_Shortener_Deactivation', 'deactivate' ) );

		// Initialize the Admin features of the plugin
		WP_Link_Shortener_Admin::init();
	}
}

// Initialize the plugin (singleton instance)
WP_Link_Shortener::get_instance();
