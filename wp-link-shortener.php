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

	// Singleton instance
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	// Plugin Constants
	const VERSION       = '1.0.1';
	const SLUG          = 'wp-link-shortener';
	const PLUGIN_DIR    = __DIR__;
	const INCLUDES_PATH = self::PLUGIN_DIR . '/includes';
	const ADMIN_PATH    = self::PLUGIN_DIR . '/admin';
	const PUBLIC_PATH   = self::PLUGIN_DIR . '/public';
	private string $plugin_url;

	// Constructor
	private function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->define_constants();
		$this->register_hooks();
		$this->load_dependencies();
	}

	// Define/redefine constants
	private function define_constants(): void {
		defined( 'WP_LINK_SHORTENER_PATH' ) || define( 'WP_LINK_SHORTENER_PATH', self::PLUGIN_DIR );
		defined( 'WP_LINK_SHORTENER_URL' ) || define( 'WP_LINK_SHORTENER_URL', $this->plugin_url );
	}

	// Register plugin hooks
	private function register_hooks(): void {
		add_action( 'plugins_loaded', array( $this, 'initialize_plugin' ) );
		register_activation_hook( __FILE__, array( 'WP_Link_Shortener_Activator', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'WP_Link_Shortener_Deactivator', 'deactivate' ) );

		// Initialize the statistics handler when needed
		add_action(
			'init',
			function () {
				if ( isset( $_GET['original_url'] ) && isset( $_GET['item_id'] ) ) {
					$statistics_handler = new WP_Link_Shortener_Statistics_Handler();
					$statistics_handler->process_tracking_request();
				}
			}
		);
	}

	// Load dependencies
	private function load_dependencies(): void {
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-statistics-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-db-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-activator.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-deactivator.php';
	}

	// Load admin-specific dependencies
	private function load_admin_dependencies(): void {
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-admin.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-list-table.php';
		WP_Link_Shortener_Admin::init();
	}

	// Load public-specific dependencies
	//  private function load_public_dependencies(): void {
	//      require_once self::PUBLIC_PATH . '/class-wp-link-shortener-public.php';
	//      WP_Link_Shortener_Public::init();
	//  }

	// Plugin initialization logic
	public function initialize_plugin(): void {
		load_plugin_textdomain( self::SLUG, false, self::SLUG . '/languages' );

		if ( is_admin() && file_exists( self::ADMIN_PATH . '/class-wp-link-shortener-admin.php' ) ) {
			$this->load_admin_dependencies();
		}
		//      elseif ( file_exists( self::PUBLIC_PATH . '/class-wp-link-shortener-public.php' ) ) {
		//          $this->load_public_dependencies();
		//      }
	}
}

// Initialize the plugin
WP_Link_Shortener::get_instance();
