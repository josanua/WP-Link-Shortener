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

// add_action( 'admin_head', 'dolly_css' ); for css styles.

//function wporg_debug() {
//	echo '<p>' . current_action() . '</p>';
//}
//add_action( 'all', 'wporg_debug' );
class WP_Link_Shortener {

	// Singleton instance preparation
	private static ?self $instance = null;

	// Prevent the cloning of the instance
	private function __clone() {}

	// Empty unserialize method to prevent unserializing of the *Singleton* instance.
	public function __wakeup() {}

	// Singleton Instance
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	// Plugin Constants
	const PLUGIN_VERSION = '1.0.1';
	const DB_VERSION     = '1.0.0';
	const PLUGIN_SLUG    = 'wp-link-shortener';
	const PLUGIN_DIR     = __DIR__;
	const INCLUDES_PATH  = self::PLUGIN_DIR . '/includes';
	const ADMIN_PATH     = self::PLUGIN_DIR . '/admin';

	private string $plugin_url;

	// Constructor
	private function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->define_constants();
		$this->load_dependencies();
		$this->initialize_plugin();
	}

	// Define/redefine constants
	public function define_constants(): void {
		defined( 'WP_LINK_SHORTENER_PATH' ) || define( 'WP_LINK_SHORTENER_PATH', self::PLUGIN_DIR );
		defined( 'WP_LINK_SHORTENER_URL' ) || define( 'WP_LINK_SHORTENER_URL', $this->plugin_url );
	}

	// Load all required dependencies
	public function load_dependencies(): void {
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-db-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-activation.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-admin.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-list-table.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-statistics-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-deactivation.php';
	}

	public function initialize_plugin(): void {
		$this->initialize_admin_logic();
	}

	// Initialize admin-specific logic
	public function initialize_admin_logic(): void {
		// Initialize the Admin features of the plugin
		WP_Link_Shortener_Admin::get_instance();
	}
}

// Initialize the plugin (singleton instance)
WP_Link_Shortener::get_instance();

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_wp_link_shortener() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-link-shortener-activation.php';
	WP_Link_Shortener_Activation::activate();
}

// Register the activation hook
register_activation_hook( __FILE__, 'activate_wp_link_shortener' );
