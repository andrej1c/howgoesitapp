<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/includes
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
 * @package    How_Goes_It
 * @subpackage How_Goes_It/includes
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      How_Goes_It_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'how-goes-it';

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
	 * - How_Goes_It_Loader. Orchestrates the hooks of the plugin.
	 * - How_Goes_It_i18n. Defines internationalization functionality.
	 * - How_Goes_It_Admin. Defines all hooks for the admin area.
	 * - How_Goes_It_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-how-goes-it-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-how-goes-it-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-how-goes-it-admin.php';

		/**
		 * The class responsible for executing registration action.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-how-goes-it-registration.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-how-goes-it-public.php';

		/**
		 * The class responsible for defining shortcodes and running them.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-how-goes-it-shortcodes.php';

		/**
		 * The class responsible for managing user actions like login, logout and registration.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-how-goes-it-user-actions.php';

		$this->loader = new How_Goes_It_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the How_Goes_It_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new How_Goes_It_i18n();

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

		$plugin_admin = new How_Goes_It_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$plugin_admin_reg = new How_Goes_It_Admin_Registration( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_post_nopriv_hgi_create_user', $plugin_admin_reg, 'hgi_register_user_action' );
		$this->loader->add_action( 'admin_post_nopriv_hgi_validate_user', $plugin_admin_reg, 'hgi_validate_user_action' );
		$this->loader->add_action( 'wp_authenticate_user', $plugin_admin_reg, 'hgi_validate_user_on_login' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new How_Goes_It_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$plugin_shortcodes = new How_Goes_It_Public_Shortcodes( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_shortcodes, 'init_shortcodes' );

		$plugin_user_actions = new How_Goes_It_Public_User_Actions( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'login_redirect', $plugin_user_actions, 'cs_redirect_from_wp_login' );
		$this->loader->add_action( 'login_redirect', $plugin_user_actions, 'redirect_after_login', 10, 3 );
		$this->loader->add_action( 'wp_logout', $plugin_user_actions, 'cs_logout_redirect' );
		$this->loader->add_action( 'init', $plugin_user_actions, 'cs_disable_admin_bar' );
		$this->loader->add_action( 'login_form_register', $plugin_user_actions, 'cs_do_register_user', 10, 0 );
		$this->loader->add_action( 'login_form_register', $plugin_user_actions, 'cs_redirect_from_wp_register', 10, 0 );

		$this->loader->add_filter( 'authenticate', $plugin_user_actions, 'cs_maybe_redirect_at_authentication', 101, 3 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    How_Goes_It_Loader    Orchestrates the hooks of the plugin.
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
