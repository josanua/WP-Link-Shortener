<?php

/**
 * Plugin Name: WP-Link-Shortener
 * Description: A custom plugin to create and manage shortened links in WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL2
 * Text Domain: wp-link-shortener
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants for the plugin.
define( 'WP_LINK_SHORTENER_VERSION', '1.0.0' );
define( 'WP_LINK_SHORTENER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_LINK_SHORTENER_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_LINK_SHORTENER_SLUG', 'wp-link-shortener' );

// Autoload classes if needed.
require_once WP_LINK_SHORTENER_PATH . 'includes/class-wp-link-shortener-autoloader.php';

/**
 * Initialize the plugin.
 */
function wp_link_shortener_init() {
	// Load Text Domain for translations.
	load_plugin_textdomain( 'wp-link-shortener', false, WP_LINK_SHORTENER_SLUG . '/languages' );

	// Perform plugin-related initialization tasks.
	if ( is_admin() ) {
		require_once WP_LINK_SHORTENER_PATH . 'admin/class-wp-link-shortener-admin.php';
		WP_Link_Shortener_Admin::init();
	} else {
		require_once WP_LINK_SHORTENER_PATH . 'public/class-wp-link-shortener-public.php';
		WP_Link_Shortener_Public::init();
	}
}

add_action( 'plugins_loaded', 'wp_link_shortener_init' );

// Activation hook.
register_activation_hook( __FILE__, 'wp_link_shortener_activate' );
function wp_link_shortener_activate() {
	// Tasks to perform on activation, such as creating database tables.
	do_action( 'wp_link_shortener_activation' );
}

// Deactivation hook.
register_deactivation_hook( __FILE__, 'wp_link_shortener_deactivate' );
function wp_link_shortener_deactivate() {
	// Cleanup tasks to perform when the plugin is deactivated.
	do_action( 'wp_link_shortener_deactivation' );
}