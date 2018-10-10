<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://inforvision.nl
 * @since             1.0.0
 * @package           Eazycv_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       eazycv-wp
 * Plugin URI:        https://eazycv.nl
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Vincent
 * Author URI:        https://inforvision.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eazycv-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPEAZYCV_NAME', 'WP EazyCV' );
define( 'WPEAZYCV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPEAZYCV_PUBLIC_DIR', plugin_dir_url( __FILE__ ) );

/*
 * autoloads for composer / mustache
 */
require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-eazycv-wp-activator.php
 */
function activate_eazycv_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-eazycv-wp-activator.php';
	Eazycv_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eazycv-wp-deactivator.php
 */
function deactivate_eazycv_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-eazycv-wp-deactivator.php';
	Eazycv_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_eazycv_wp' );
register_deactivation_hook( __FILE__, 'deactivate_eazycv_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-eazycv-api-client.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-eazycv-l10n.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-eazycv-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_eazycv_wp() {

	$plugin = new Eazycv_Wp();
	$plugin->run();

}

run_eazycv_wp();
