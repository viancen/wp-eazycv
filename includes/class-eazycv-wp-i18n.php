<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://inforvision.nl
 * @since      1.0.0
 *
 * @package    Eazycv_Wp
 * @subpackage Eazycv_Wp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Eazycv_Wp
 * @subpackage Eazycv_Wp/includes
 * @author     Vincent <vincent@inforvision.nl>
 */
class Eazycv_Wp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'eazycv-wp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}


}
