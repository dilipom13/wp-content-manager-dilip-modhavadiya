<?php
/*
 * Plugin Name:       Wp Content Manager Dilip Modhavadiya
 * Plugin URI:        https://wordpress.com/plugins/wp-content-manager-dilip-modhavadiya/
 * Description:       Promo Blocks + Settings + Shortcode + REST API + admin-ajax (toggle)
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.5
 * Author:            Dilip WPWeb
 * Author URI:        https://profiles.wordpress.org/dilip2615/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://wordpress.com/wp-content-manager-dilip-modhavadiya/
 * Text Domain:       wp-content-manager-dilip-modhavadiya
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DCM_PLUGIN_FILE' ) ) {
	define( 'DCM_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'DCM_PLUGIN_DIR' ) ) {
	define( 'DCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'DCM_PLUGIN_URL' ) ) {
	define( 'DCM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'DCM_TEXTDOMAIN' ) ) {
	define( 'DCM_TEXTDOMAIN', 'wp-content-manager-dilip-modhavadiya' );
}

/**
 * Includes
 * Keep dependencies in correct order.
 */
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-settings.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-cpt.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-repository.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-assets.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-rest.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-ajax.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-shortcode.php';
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-plugin.php';

// Bonus Task: WP-CLI command to clear cache
require_once DCM_PLUGIN_DIR . 'includes/class-dcm-cli.php';

/**
 * Activation
 */
register_activation_hook( __FILE__, 'dcm_activate_plugin' );

/**
 * Deactivation hooks
 */
register_deactivation_hook( __FILE__, 'dcm_deactivate_plugin' );

/**
 * Uninstall hook (works only if you DON'T use uninstall.php).
 */
register_uninstall_hook( __FILE__, 'dcm_uninstall_plugin' );


function dcm_activate_plugin() {
	if ( class_exists( 'DCM_Install' ) ) {
		DCM_Install::activate();
	}
}

function dcm_deactivate_plugin() {
	// Cleanup (cache etc.)
	if ( class_exists( 'DCM_Install' ) ) {
		DCM_Install::deactivate();
	}
}

function dcm_uninstall_plugin() {
	delete_option( 'dcm_settings' );

	// Clear transient caches
	for ( $i = 1; $i <= 50; $i++ ) {
		delete_transient( 'dcm_promos_' . $i );
	}
	
	$ids = get_posts(
		array(
			'post_type'      => 'promo_block',
			'post_status'    => 'any',
			'numberposts'    => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( (int) $id, true );
	}
}


add_action(
	'plugins_loaded',
	function() {

		/**
		 * Load Textdomain
		 */
		load_plugin_textdomain(
			DCM_TEXTDOMAIN,
			false,
			dirname( plugin_basename( DCM_PLUGIN_FILE ) ) . '/languages'
		);

		/* 
		 * Boot plugin
		*/
		if ( class_exists( 'DCM_Plugin' ) ) {
			DCM_Plugin::instance();
		}
	}
);