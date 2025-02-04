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

	private static $instance = null;

	const VERSION = '1.0.0';
	const SLUG = 'wp-link-shortener';

	private function __construct() {
		define( 'WP_LINK_SHORTENER_PATH', plugin_dir_path( __FILE__ ) );
		define( 'WP_LINK_SHORTENER_URL', plugin_dir_url( __FILE__ ) );

		// Autoload classes if needed.
		require_once WP_LINK_SHORTENER_PATH . 'includes/class-wp-link-shortener-autoloader.php';

		// Include the activation/deactivation files.
		require_once WP_LINK_SHORTENER_PATH . 'includes/class-wp-link-shortener-activator.php';
		require_once WP_LINK_SHORTENER_PATH . 'includes/class-wp-link-shortener-deactivator.php';

		// Hook to initialize the plugin.
		add_action( 'plugins_loaded', [ $this, 'init' ] );

		// Activation and deactivation hooks.
		register_activation_hook( __FILE__, [ __CLASS__, 'activate' ] );
		register_deactivation_hook( __FILE__, [ __CLASS__, 'deactivate' ] );
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// Initialization
	public function init() {
		// Load Text Domain for translations.
		load_plugin_textdomain( self::SLUG, false, self::SLUG . '/languages' );

		// Perform plugin-related initialization tasks.
		if ( is_admin() ) {
			require_once WP_LINK_SHORTENER_PATH . 'admin/class-wp-link-shortener-admin.php';
			WP_Link_Shortener_Admin::init();
		} else {
			require_once WP_LINK_SHORTENER_PATH . 'public/class-wp-link-shortener-public.php';
			WP_Link_Shortener_Public::init();
		}
	}

	public static function activate() {
		// Tasks to perform on activation, such as creating database tables.
		// do_action( 'wp_link_shortener_activation' );
		WP_Link_Shortener_Activator::activate();

	}

	public static function deactivate() {
		// Cleanup tasks to perform when the plugin is deactivated.
		// do_action( 'wp_link_shortener_deactivation' );
		WP_Link_Shortener_Deactivator::deactivate();
	}
}

// Initialize the plugin.
WP_Link_Shortener::instance();
