<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://eazycv.nl
 * @since      1.0.0
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/public
 * @author     Inforvision BV <info@inforvision.nl>
 */
class Wp_EazyCV_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wp_eazycv The ID of this plugin.
	 */
	private $wp_eazycv;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The api connection of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	public $api = null;
	public $eazyCvLicence = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wp_eazycv The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $wp_eazycv, $version ) {

		$this->wp_eazycv = $wp_eazycv;
		$this->version   = $version;


		$this->setup_api();

	}


	/**
	 * EazyCV api connection
	 *
	 * @return bool
	 * @throws Eazycv_Error
	 */
	private function setup_api() {
		//check connection
		$optionKey      = get_option( 'wp_eazycv_apikey' );
		$optionInstance = get_option( 'wp_eazycv_instance' );
		if ( ! empty( $optionKey ) && ! empty( $optionInstance ) ) {
			$this->api = new EazycvClient( $optionKey, $optionInstance );

			try {
				$this->eazyCvLicence = $this->api->get( 'licence' );
			} catch ( Exception $wEx ) {
				return false;
			}
		}
	}

	/**
	 * make url work
	 */
	public function setup_rewrites() {
		add_filter( 'query_vars', 'add_jobid_var', 0, 1 );
		function add_jobid_var( $vars ) {
			$vars[] = 'JobID';

			return $vars;
		}


		add_action( 'init', 'add_jobid_rule' );
		function add_jobid_rule( $jobPage ) {
			$jobPage = get_option( 'wp_eazycv_jobpage' );

			if ( $jobPage ) {
				add_rewrite_rule(
					'^vacature-voorbeeld/([^/]*)/?',
					'index.php?pagename=' . get_option( 'wp_eazycv_jobpage' ) . '&JobID=$matches[1]',
					'top'
				);
			}
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_EazyCV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_EazyCV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->wp_eazycv, plugin_dir_url( __FILE__ ) . 'css/wp-eazycv-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_EazyCV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_EazyCV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->wp_eazycv, plugin_dir_url( __FILE__ ) . 'js/wp-eazycv-public.js', array( 'jquery' ), $this->version, false );

	}

}
