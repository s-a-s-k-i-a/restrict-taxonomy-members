<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.saskialund.de
 * @since      0.0.1
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
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
 * @since      0.0.1
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 * @author     Saskia Lund <hello@saskialund.de>
 */
class Rt_Members {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Rt_Members_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		if(is_admin()){
			add_action( 'admin_init', array( $this, 'activation_redirect' ) );
		}

		$this->plugin_name = 'rt-members';
		$this->version = RT_MEMBERS_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rt_Members_Loader. Orchestrates the hooks of the plugin.
	 * - Rt_Members_i18n. Defines internationalization functionality.
	 * - Rt_Members_Admin. Defines all hooks for the admin area.
	 * - Rt_Members_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rt-members-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rt-members-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rt-members-admin.php';

		/**
		 * The class responsible for showing the settings page, we only load it if in the admin area.
		 */
		if(is_admin()){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-view-rt-members.php';
		}


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rt-members-public.php';

		$this->loader = new Rt_Members_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rt_Members_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rt_Members_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rt_Members_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		/* $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_membership_meta_boxes' );
		$this->loader->add_action( 'members_role_added', $plugin_admin, 'save_membership' );
		$this->loader->add_action( 'members_role_updated', $plugin_admin, 'save_membership' ); 
		$this->loader->add_action( 'rt_members_expire_check', $plugin_admin, 'expire_check', 11 ); */
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_single_access_settings' );

		// add the taxonomy inputs
		$this->loader->add_action( 'category_add_form_fields', $plugin_admin, 'taxonomy_meta_box' );
		$this->loader->add_action( 'category_edit_form_fields', $plugin_admin, 'taxonomy_meta_box' );
		
		// save the taxonomy settings
		$this->loader->add_action( 'edit_category', $plugin_admin, 'save_term_meta' );
		$this->loader->add_action( 'create_category', $plugin_admin, 'save_term_meta' );
		
		// add our settings page to the members screen
		$this->loader->add_action( 'members_register_settings_views', $plugin_admin, 'register_settings_page', 5 );


		//$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'expire_check', 11 ); // for testing expire check only
		

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rt_Members_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_filter( 'members_can_user_view_post', $plugin_public, 'can_user_view_post', 10, 3 );

		$this->loader->add_filter( 'members_post_error_message', $plugin_public, 'term_error_message', 11, 3 );

		

		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Rt_Members_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Redirect to the settings page on activation.
	 *
	 * @since 0.0.1
	 */
	public function activation_redirect() {
		// Bail if no activation redirect
		if ( !get_transient( '_rt_members_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_rt_members_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'options-general.php?page=members-settings&view=rt-members' ) );
		exit;
	}
	

}
