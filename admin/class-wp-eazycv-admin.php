<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://eazycv.nl
 * @since      1.0.0
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wp_eazycv
 * @subpackage wp_eazycv/admin
 * @author     Inforvision BV <info@inforvision.nl>
 */
class Wp_EazyCV_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wp_eazycv The ID of this plugin.
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

	/**
	 * The options name to be used in this plugin
	 *
	 * @since    1.0.0
	 * @access    private
	 * @var    string $option_name Option name of this plugin
	 */
	private $option_name = 'wp_eazycv';

	public $plugin_settings_tabs = array();

	public $eazyCvApi = null;
	public $eazyCvLicence = null;
	public $eazyCvException= null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wp_eazycv The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->plugin_settings_tabs['general'] = 'General';
		$this->plugin_settings_tabs['jobs']    = 'Jobs';

		//check connection
		$optionKey      = get_option( 'wp_eazycv_apikey' );
		$optionInstance = get_option( 'wp_eazycv_instance' );
		if ( ! empty( $optionKey ) && ! empty( $optionInstance ) ) {
			$this->eazyCvApi = new EazycvClient( $optionKey, $optionInstance );

			try {
				$this->eazyCvLicence = $this->eazyCvApi->get( 'licence' );
			} catch ( Exception $wEx ) {
			    $this->eazyCvException = $wEx->getMessage();
			}
		}

	}

	/**
	 *
	 * Add an options page under the Settings submenu
	 *
	 * @since  1.0.0
	 */
	public function add_options_menu() {

		add_menu_page(
			__( 'EazyCV', $this->plugin_name ),
			__( 'EazyCV', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' ),
			'https://eazycv.nl/wp-content/uploads/2018/10/eazycv-icon-16.png', 2 );
		//add_options_page( __('EazyCV', $this->plugin_name), __('EazyCV', $this->plugin_name), 'manage_options', $this->plugin_name,);

	}

	/**
	 * Settings - Validates saved options
	 *
	 * @since        1.0.0
	 *
	 * @param        array $input array of submitted plugin options
	 *
	 * @return        array                        array of validated plugin options
	 */
	public function settings_sanitize( $input ) {
		// Initialize the new array that will hold the sanitize values
		$new_input = array();
		if ( isset( $input ) ) {
			// Loop through the input and sanitize each of the values
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = sanitize_text_field( $val );
			}
		}

		return $new_input;
	} // sanitize()

	/**
	 * Renders Settings Tabs
	 *
	 * @since        1.0.0
	 * @return        mixed            The settings field
	 */
	function filter_wp_api_render_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_name . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}

	/**
	 * Plugin Settings Link on plugin page
	 *
	 * @since        1.0.0
	 * @return        mixed            The settings field
	 */
	function add_settings_link( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=filter-wp-eazycv' ) . '">Settings</a>',
		);

		return array_merge( $links, $mylinks );
	}

	/**
	 * Callback function for the admin settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/filter-wp-eazycv-admin-display.php';
	}

	/**
	 * Returns plugin for settings page
	 *
	 * @since        1.0.0
	 * @return        string    $plugin_name       The name of this plugin
	 */
	public function get_plugin() {
		return $this->plugin_name;
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
		 * defined in Wp_EazyCV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_EazyCV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-eazycv-admin.css', array(), $this->version, 'all' );

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
		 * defined in Wp_EazyCV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_EazyCV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-eazycv-admin.js', array( 'jquery' ), $this->version, false );

	}

	private function register_job_options() {
		add_settings_section( $this->plugin_name . "-job_section", null, null, $this->plugin_name . "-job-options" );
		add_settings_field( $this->option_name . "_jobpage", __( "Job Page" ), array( $this, "list_pages" ), $this->plugin_name . "-job-options", $this->plugin_name . "-job_section", array( 'field' => 'jobpage' ) );
		add_settings_field( $this->option_name . "_jobsearch_page", __( "Job Search Page" ), array( $this, "list_pages" ), $this->plugin_name . "-job-options", $this->plugin_name . "-job_section", array( 'field' => 'jobsearch_page' ) );
		register_setting( $this->plugin_name . "-job_section", $this->option_name . "_jobpage" );
		register_setting( $this->plugin_name . "-job_section", $this->option_name . "_jobsearch_page" );

	}


	private function register_general_options() {
		//section name, display name, callback to print description of section, page to which section is attached.
		add_settings_section( $this->plugin_name . "-general_section", null, null, $this->plugin_name . "-general-options" );

		//setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
		//last field section is optional.
		add_settings_field( $this->option_name . "_instance", __( "Instantie" ), array( $this, "display_form_element" ), $this->plugin_name . "-general-options", $this->plugin_name . "-general_section", array( 'field' => 'instance' ) );
		add_settings_field( $this->option_name . "_apikey", __( "API Key" ), array( $this, "display_form_element" ), $this->plugin_name . "-general-options", $this->plugin_name . "-general_section", array( 'field' => 'apikey' ) );

		//section name, form element name, callback for sanitization
		register_setting( $this->plugin_name . "-general_section", $this->option_name . "_instance" );
		register_setting( $this->plugin_name . "-general_section", $this->option_name . "_apikey" );
	}

	/**
	 * Register all related settings of this plugin
	 *
	 * @since  1.0.0
	 */
	public function register_setting() {

		$this->register_general_options();
		$this->register_job_options();

	}


	/**
	 * Register all related settings of this plugin
	 *
	 * @since  1.0.0
	 */
	public function test_api_connection() {


	}


	/**
	 * @param $fields
	 */
	public function display_form_element( $fields ) {
		//id and name of form element should be same as the setting name.
		?>
        <input type="text" class="eazycv-admin-input"
               name="<?php echo $this->option_name . "_" . $fields['field']; ?>"
               id="<?php echo $this->option_name . "_" . $fields['field']; ?>"
               value="<?php echo get_option( $this->option_name . "_" . $fields['field'] ); ?>"/>
		<?php
	}


	/**
	 * @param $fields
	 */
	public function list_pages( $fields ) {
		flush_rewrite_rules( true );
		//id and name of form element should be same as the setting name.
		$args  = array(
			'sort_order'   => 'asc',
			'sort_column'  => 'post_title',
			'hierarchical' => 1,
			'exclude'      => '',
			'include'      => '',
			'meta_key'     => '',
			'meta_value'   => '',
			'authors'      => '',
			'child_of'     => 0,
			'exclude_tree' => '',
			'number'       => '',
			'offset'       => 0,
			'post_type'    => 'page',
			'post_status'  => 'publish'
		);
		$pages = get_pages( $args );

		?>
        <select type="text" class="eazycv-admin-select"
                name="<?php echo $this->option_name . "_" . $fields['field']; ?>"
                id="<?php echo $this->option_name . "_" . $fields['field']; ?>"
        >
            <option value=""></option>
			<?php
			foreach ( $pages as $page ) { ?>
                <option <?php if ( get_option( $this->option_name . "_" . $fields['field'] ) == $page->post_name ) {
					echo 'selected';
				} ?>
                        value="<?php echo $page->post_name ?>"><?php echo $page->post_title ?> (<?php echo $page->post_name ?>)
                </option>

			<?php } ?>
        </select>
		<?php
	}

}
