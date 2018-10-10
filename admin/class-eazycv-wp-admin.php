<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://inforvision.nl
 * @since      1.0.0
 *
 * @package    Eazycv_Wp
 * @subpackage Eazycv_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eazycv_Wp
 * @subpackage Eazycv_Wp/admin
 * @author     Vincent <vincent@inforvision.nl>
 */
class Eazycv_Wp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	private $l10n;
	private $m;
	private $settings_defaults = [ 'instance-id' => null, 'instance-pass' => null ];
	private $settings = [];


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->l10n = new wpeazycv_l10n( get_option( 'wpeazycv-settings.language', 'en' ) ); // default language
		$this->m    = new Mustache_Engine( array(
			'loader'          => new Mustache_Loader_FilesystemLoader( dirname( dirname( __FILE__ ) ) . '/templates' ),
			'partials_loader' => new Mustache_Loader_FilesystemLoader( dirname( dirname( __FILE__ ) ) . '/templates/partials' ),
		) );


	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eazycv_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eazycv_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/eazycv-wp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eazycv_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eazycv_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eazycv-wp-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Adds a settings page link to a menu
	 *
	 * @link        https://codex.wordpress.org/Administration_Menus
	 * @since        1.0.0
	 * @return        void
	 */
	public function add_menu() {
		// add main menu item
		// add main menu item
		add_menu_page( WPEAZYCV_NAME, WPEAZYCV_NAME, 'manage_options', 'wpeazycv', array( $this, 'wpeazycv_about' ), 'dashicons-store', 2 );
		// makes first submenu item have another name than WPEAZYCV_NAME
		add_submenu_page( 'wpeazycv', $this->l10n->get( 'global.about-wpeazycv' ), $this->l10n->get( 'global.about-wpeazycv' ), 'manage_options', 'wpeazycv', array( $this, 'wpeazycv_about' ) );
		// add API settings page
		add_submenu_page( 'wpeazycv', $this->l10n->get( 'form-settings.settings-title' ), $this->l10n->get( 'form-settings.settings-title' ), 'manage_options', 'wpeazycv-setttings', array( $this, 'wpeazycv' ) );
		// add Licence information page
		add_submenu_page( 'wpeazycv', $this->l10n->get( 'licence-info.info-title' ), $this->l10n->get( 'licence-info.info-title' ), 'manage_options', 'wpeazycv-licence-info', array( $this, 'wpeazycv_licence_info' ) );
		// add Shortcode settings page
		add_submenu_page( 'wpeazycv', $this->l10n->get( 'form-shortcode.shortcode-title' ), $this->l10n->get( 'form-shortcode.shortcode-title' ), 'manage_options', 'wpeazycv-shortcode', array( $this, 'wpeazycv_shortcode' ) );
	}


	public function wpeazycv() {

		if ( isset( $_POST ) && isset( $_POST['wpeazycv_form_settings'] ) ) {
			$current_lang = get_option( 'wpeazycv-settings.language' );
			$this->set( $_POST );
			// make sure the change of language is visible to the user
			if ( $_POST['wpeazycv_form_settings']['language'] !== $current_lang ) {
				echo '<script type="">location.reload(true);</script>';
			}
		}

		// test the settings (saves the response to option wpeazycv-info
		$test = $this->test();

		// collect data before rendering the page
		$data = $this->l10n->add_globals( array(
			'page-title'    => WPEAZYCV_NAME,
			'nonce'         => wp_nonce_field( 'wpeazycv-form-settings-nonce', 'wpeazycv-form-settings-nonce', '/wp-admin/admin.php?page=wpeazycv', false ),
			'form-settings' => $this->l10n->get( 'form-settings' ),
			'form-info'     => $this->l10n->get( 'form-info' ),
			'data'          => array(
				'instance'  => $this->get(),
				'info'      => json_decode( get_option( 'wpeazycv-info', false ) ),
				'languages' => array(
					array(
						'key'      => 'en',
						'label'    => $this->l10n->get( 'global.en' ),
						'selected' => get_option( 'wpeazycv-settings.language' ) === 'en' ? 'selected' : ''
					),
					array(
						'key'      => 'nl',
						'label'    => $this->l10n->get( 'global.nl' ),
						'selected' => get_option( 'wpeazycv-settings.language' ) === 'nl' ? 'selected' : ''
					)
				)
			),
			'test'          => $test
		) );

		// render the settings page
		echo $this->m->render( 'settings-page', $data );
	}

	public function wpeazycv_licence_info() {
		$data = $this->l10n->add_globals( array(
			'page-title'   => WPEAZYCV_NAME,
			'licence-info' => $this->l10n->get( 'licence-info' ),
			'data'         => json_decode( get_option( 'wpeazycv-info', false ) )
		) );
		echo $this->m->render( 'licence-info', $data );
	}

	public function wpeazycv_shortcode() {
		$data = array(
			'page-title' => WPEAZYCV_NAME,
			'shortcode'  => $this->l10n->get( 'form-shortcode' )
		);
		echo $this->m->render( 'shortcode-page', $data );
	}

	public function wpeazycv_about() {
		$data = array(
			'page-title' => WPEAZYCV_NAME,
			'about'      => $this->l10n->get( 'about' )
		);
		echo $this->m->render( 'about-page', $data );
	}

	private function set_info( $data ) {
		if ( $data === null ) {
			update_option( 'wpeazycv-info', null );
		} else {
			$add_data = array(
				'last_test_success' => count( $data ) > 0 ? true : false,
				'last_test_date'    => gmdate( "d-m-Y \T H:i:s \Z", time() + date( "Z" ) )
			);
			$data     = $add_data + $data;
			$bools    = array( 'licence_type_candidate', 'licence_type_employee', 'status', 'last_test_success' );
			$info     = array();
			foreach ( $data as $key => $val ) {
				if ( in_array( $key, $bools ) ) {
					$val = $this->l10n->get( 'global.no' );
					if ( (boolean) $val ) {
						$val = $this->l10n->get( 'global.yes' );
					}
				}
				$key    = str_replace( '_', '-', $key );
				$info[] = array( 'label' => $this->l10n->get( 'licence-info.label-' . $key, 'label-' . $key ), 'value' => $val );
			}
			update_option( 'wpeazycv-info', json_encode( $info ) );
		}
	}

	private function test() {
		$api_client = new wpeazycv_api_client();
		$test_call  = $api_client->test();
		if ( $test_call['test'] === 'failed' ) {
			$this->set_info( null );

			return false;
		} else {
			$this->set_info( $test_call[0] );

			return true;
		}
	}

	private function get() {
		$settings = new stdClass();
		foreach ( $this->settings_defaults as $key => $val ) {
			$settings->{$key} = get_option( 'wpeazycv-settings.' . $key, $val );
		}

		return $settings;
	}

	public static function set( $data ) {
		$settings = new stdClass();
		if ( is_admin() && wp_verify_nonce( $_POST['wpeazycv-form-settings-nonce'], 'wpeazycv-form-settings-nonce' ) ) {
			if ( $data && is_array( $data ) ) {
				foreach ( $data['wpeazycv_form_settings'] as $key => $val ) {
					update_option( 'wpeazycv-settings.' . $key, sanitize_text_field( $val ) );
				}
			}
		}
	}

}
