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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WP_Link_Shortener
 *
 * A WordPress plugin designed to provide a link shortener functionality.
 * Implements the singleton pattern to ensure only one instance of the class is created.
 */
class WP_Link_Shortener {

	/** Singleton Instance */
	private static ?self $instance = null;

	/** Plugin Constants */
	const VERSION       = '1.0.1';
	const DB_VERSION    = '1.0.0';
	const SLUG          = 'wp-link-shortener';
	const INCLUDES_PATH = __DIR__ . '/includes';
	const ADMIN_PATH    = __DIR__ . '/admin';

	/** Plugin Properties */
	private string $plugin_url;

	/**
	 * Singleton: Prevent clone and unserialization
	 */
	private function __clone() {}
	public function __wakeup() {}

	/**
	 * Singleton: Get Instance
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Constructor: Initialize plugin
	 */
	private function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->define_constants();
		$this->load_core_dependencies();
		$this->register_activation_hook();
		$this->init_plugin(); // Call Init of Admin Logic
	}

	/**
	 * Define Plugin Constants
	 */
	public function define_constants(): void {
		defined( 'WP_LINK_SHORTENER_PATH' ) || define( 'WP_LINK_SHORTENER_PATH', __DIR__ );
		defined( 'WP_LINK_SHORTENER_URL' ) || define( 'WP_LINK_SHORTENER_URL', $this->plugin_url );
	}

	/**
	 * Register the plugin activation hook
	 */
	private function register_activation_hook(): void {
		register_activation_hook( __FILE__, array( self::class, 'activate_plugin' ) );
	}

	/**
	 * Activation logic for the plugin
	 */
	public static function activate_plugin(): void {
		WP_Link_Shortener_Activation::activate();
	}

	/**
	 * Load Dependencies
	 */
	public function load_core_dependencies(): void {
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-db-handler.php';
		      require_once self::INCLUDES_PATH . '/class-wp-link-shortener-activation.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-admin.php';
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-list-table.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-statistics-handler.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-deactivation.php';
	}

	public function init_plugin(): void {
		$this->init_admin_logic();
	}

	public function init_admin_logic(): void {
		// Initialize the Admin features of the plugin
		WP_Link_Shortener_Admin::get_instance();
	}
}

WP_Link_Shortener::get_instance();
