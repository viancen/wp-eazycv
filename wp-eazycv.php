<?php
/**
 * EazyCV Wordpress implementation
 *
 * @link              https://eazycv.nl
 * @package           WP_EazyCV
 *
 * @wordpress-plugin
 * Plugin Name:       EazyCV
 * Plugin URI:        https://eazycv.nl/wordpress-plugin/
 * Description:       Deze plugin is om je EazyCV systeem aan te sluiten op je Wordpress website..
 * Version:           1.4.2
 * Author:            Inforvision BV
 * Author URI:        https://inforvision.nl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-eazycv
 * Domain Path:       /
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'Wp_EazyCV_VERSION', '1.4.2' );
define( 'Wp_EazyCV_DEBUG', false );

/**
 * auto update through github
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-eazycv-autoupdate.php';
if ( is_admin() ) {
	new WP_EazyCV_Updater( __FILE__, 'viancen', "wp-eazycv" );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-eazycv-activator.php
 */
function activate_wp_eazycv() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-eazycv-activator.php';
	Wp_EazyCV_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-eazycv-deactivator.php
 */
function deactivate_wp_eazycv() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-eazycv-deactivator.php';
	Wp_EazyCV_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_eazycv' );
register_deactivation_hook( __FILE__, 'deactivate_wp_eazycv' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-eazycv.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_eazycv() {
	$plugin = new wp_eazycv();
	$plugin->run();
}

run_wp_eazycv();