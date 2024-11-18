<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 */

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

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
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */
class NicheclearAPI {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      NicheclearAPI_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'NICHECLEAR_API_VERSION' ) ) {
			$this->version = NICHECLEAR_API_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'NicheclearAPI';

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
	 * - Nicheclear_api_Loader. Orchestrates the hooks of the plugin.
	 * - Nicheclear_api_i18n. Defines internationalization functionality.
	 * - Nicheclear_api_Admin. Defines all hooks for the admin area.
	 * - Nicheclear_api_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nicheclear_api-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nicheclear_api-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-nicheclear_api-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-nicheclear_api-public.php';

		$this->loader = new NicheclearAPI_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Nicheclear_api_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new NicheclearAPI_i18n();

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

		$plugin_admin = new NicheclearAPI_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ncapi_add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ncapi_register_settings' );
		$this->loader->add_filter( "plugin_action_links_nicheclear_api/nicheclear_api.php", $plugin_admin, 'plugin_add_settings_link' );
		$this->loader->add_action( 'wp_ajax_get_plugin_options', $plugin_admin, 'get_plugin_options' );
		$this->loader->add_action( 'wp_ajax_save_plugin_options', $plugin_admin, 'save_plugin_options' );

		$this->loader->add_action( NicheclearAPI_Common::CRON_HOOK_DB_CLEANUP, $plugin_admin, 'run_db_cleanup' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new NicheclearAPI_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_checkout_js_css' );

		$this->loader->add_action( 'wp_ajax_ncapi_create_order', $plugin_public, 'ncapi_create_order' );
		$this->loader->add_action( 'wp_ajax_nopriv_ncapi_create_order', $plugin_public, 'ncapi_create_order' );

		$this->loader->add_action( 'wp_ajax_nc_pay_for_order', $plugin_public, 'nc_pay_for_order' );
		$this->loader->add_action( 'wp_ajax_nopriv_nc_pay_for_order', $plugin_public, 'nc_pay_for_order' );

		$this->loader->add_action( 'wp_ajax_ncapi_add_notice', $plugin_public, 'ncapi_add_notice' );
		$this->loader->add_action( 'wp_ajax_nopriv_ncapi_add_notice', $plugin_public, 'ncapi_add_notice' );

		$this->loader->add_action( 'woocommerce_review_order_after_payment', $plugin_public, 'inject_js_after_payment_methods' );
		$this->loader->add_action( 'woocommerce_pay_order_after_submit', $plugin_public, 'inject_js_after_payment_methods' );

		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-woo-manager.php';
		$woo_manager = new NicheclearAPI_WooManager();

		$this->loader->add_filter( 'woocommerce_payment_gateways', $woo_manager, 'add_nc_gateway_classes' );
		$this->loader->add_action( 'plugins_loaded', $woo_manager, 'init_nc_gateway_class' );

		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-webhooks.php';
		$webhooks_manager = new NicheclearAPI_Webhooks();

		$this->loader->add_action( 'woocommerce_api_ncapi_create_payment', $webhooks_manager, 'create_payment_webhook' );
		$this->loader->add_action( 'woocommerce_api_nc-payment-complete', $webhooks_manager, 'payment_complete' );

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
	 * @return    NicheclearAPI_Loader    Orchestrates the hooks of the plugin.
	 *@since     1.0.0
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
