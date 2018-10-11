<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://eazycv.nl
 * @since      1.0.0
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    wp_eazycv
 * @subpackage wp_eazycv/includes
 * @author     Inforvision BV <info@inforvision.nl>
 */
class wp_eazycv {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_EazyCV_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $wp_eazycv The string used to uniquely identify this plugin.
	 */
	protected $wp_eazycv;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;


	/**
	 * @var null
	 */
	public $eazyCV = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'Wp_EazyCV_VERSION' ) ) {
			$this->version = Wp_EazyCV_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->wp_eazycv = 'wp-eazycv';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->setup_eazycv_connection();


	}


	private function setup_eazycv_connection() {
		$optionKey      = get_option( 'wp_eazycv_apikey' );
		$optionInstance = get_option( 'wp_eazycv_instance' );
		if ( ! empty( $optionKey ) && ! empty( $optionInstance ) ) {
			$this->eazyCV = new EazycvClient( $optionKey, $optionInstance );
		}
	}

	public function check_api_connection() {
		if ( $this->eazyCV ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_EazyCV_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_EazyCV_i18n. Defines internationalization functionality.
	 * - Wp_EazyCV_Admin. Defines all hooks for the admin area.
	 * - Wp_EazyCV_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-eazycv-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-eazycv-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-eazycv-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-eazycv-public.php';

		/**
		 * View classses
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'views/job-view.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'views/job-search.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'views/apply.php';

		/**
		 * EazyCV client
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';



		$this->loader = new Wp_EazyCV_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_EazyCV_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_EazyCV_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wp_EazyCV_Admin( $this->get_wp_eazycv(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_setting' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_EazyCV_Public( $this->get_wp_eazycv(), $this->get_version() );

		$plugin_public->setup_rewrites();

		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'check_custom_pages' );
		$this->loader->add_action( 'init', $plugin_public, 'setup_shortcodes' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
		//$this->loader->check_custom_pages();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_wp_eazycv() {
		return $this->wp_eazycv;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_EazyCV_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
