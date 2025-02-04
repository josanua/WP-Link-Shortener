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
	const PLUGIN_PATH = __DIR__;
	private static string $PLUGIN_URL;

	// Constructor
	private function __construct() {
		self::$PLUGIN_URL = plugin_dir_url( __FILE__ );
		$this->define_constants();
		$this->register_hooks();
	}

	// Singleton instance
	public static function instance(): self {
		return self::$instance ??= new self();
	}

	// Define/redefine constants here
	private function define_constants(): void {
		defined( 'WP_LINK_SHORTENER_PATH' ) || define( 'WP_LINK_SHORTENER_PATH', self::PLUGIN_PATH );
		defined( 'WP_LINK_SHORTENER_URL' ) || define( 'WP_LINK_SHORTENER_URL', self::$PLUGIN_URL );
	}

	// Register plugin hooks for activation, deactivation, and initialization.
	private function register_hooks(): void {
		add_action( 'plugins_loaded', [ $this, 'initialize_plugin' ] );
		register_activation_hook( __FILE__, [ 'WP_Link_Shortener_Activator', 'activate' ] );
		register_deactivation_hook( __FILE__, [ 'WP_Link_Shortener_Deactivator', 'deactivate' ] );
	}

	// Plugin initialization logic
	public function initialize_plugin(): void {
		load_plugin_textdomain( self::SLUG, false, self::SLUG . '/languages' );

		if ( is_admin() ) {
			require_once self::PLUGIN_PATH . '/admin/class-wp-link-shortener-admin.php';
			WP_Link_Shortener_Admin::init();
		} else {
			require_once self::PLUGIN_PATH . '/public/class-wp-link-shortener-public.php';
			WP_Link_Shortener_Public::init();
		}
	}
}

// Initialize the plugin.
WP_Link_Shortener::instance();
