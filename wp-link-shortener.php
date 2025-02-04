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
	private static ?self $instance = null;

	// Plugin Constants
	const VERSION = '1.0.0';
	const SLUG = 'wp-link-shortener';
	const PLUGIN_DIR = __DIR__;
	const INCLUDES_PATH = self::PLUGIN_DIR . '/includes';
	const ADMIN_PATH = self::PLUGIN_DIR . '/admin';
	const PUBLIC_PATH = self::PLUGIN_DIR . '/public';
	private string $plugin_url;

	// Singleton instance
	public static function instance(): self {
		return self::$instance ??= new self();
	}

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
		add_action( 'plugins_loaded', [ $this, 'initialize_plugin' ] );
		register_activation_hook( __FILE__, [ 'WP_Link_Shortener_Activator', 'activate' ] );
		register_deactivation_hook( __FILE__, [ 'WP_Link_Shortener_Deactivator', 'deactivate' ] );
	}

	// Load dependencies
	private function load_dependencies(): void {
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-activator.php';
		require_once self::INCLUDES_PATH . '/class-wp-link-shortener-deactivator.php';
	}

	// Load admin-specific dependencies
	private function load_admin_dependencies(): void {
		require_once self::ADMIN_PATH . '/class-wp-link-shortener-admin.php';
		WP_Link_Shortener_Admin::init();
	}

	// Load public-specific dependencies
	private function load_public_dependencies(): void {
		require_once self::PUBLIC_PATH . '/class-wp-link-shortener-public.php';
		WP_Link_Shortener_Public::init();
	}

	// Plugin initialization logic
	public function initialize_plugin(): void {
		load_plugin_textdomain( self::SLUG, false, self::SLUG . '/languages' );

		if ( is_admin() && file_exists( self::ADMIN_PATH . '/class-wp-link-shortener-admin.php' ) ) {
			$this->load_admin_dependencies();
		} elseif ( file_exists( self::PUBLIC_PATH . '/class-wp-link-shortener-public.php' ) ) {
			$this->load_public_dependencies();
		}
	}
}

// Initialize the plugin
WP_Link_Shortener::instance();
