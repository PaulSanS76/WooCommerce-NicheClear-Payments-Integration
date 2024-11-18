<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/admin
 */

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/admin
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */
class NicheclearAPI_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nicheclear_api-admin.css', array(), $this->version, 'all' );

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
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nicheclear_api-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function plugin_add_settings_link( $links ) {
		$settings_link = "<a href='/wp-admin/admin.php?page=ncapi-settings'>" . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );

		return $links;
	}

	public function ncapi_add_settings_page() {
		add_options_page(
			"$this->plugin_name Settings",           // Page title
			"$this->plugin_name Settings",           // Menu title
			'manage_options',            // Capability
			'ncapi-settings',              // Menu slug
			[ $this, 'ncapi_render_settings_page' ]   // Callback function to render the page
		);
	}

	public function ncapi_register_settings() {
		register_setting( 'ncapi_settings', 'ncapi_key', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => ''
		] );

		register_setting( 'ncapi_settings', 'ncapi_signing_key', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => ''
		] );
	}

	/**
	 * Renders the plugin's Settings page
	 * @return void
	 */
	public function ncapi_render_settings_page() {
		include ABSPATH . "wp-content/plugins/nicheclear_api/admin/partials/nicheclear_api-admin-display.php";
	}

	/**
	 * Retrieve plugin options and payment methods.
	 *
	 * This method fetches the plugin settings and available payment methods,
	 * combining them into a single array. It then returns this array as a
	 * JSON response.
	 *
	 * @return void This function outputs a JSON response with the
	 * 'settings' and 'payment_methods' keys.
	 */
	public function get_plugin_options() {
		$opts            = NicheclearAPI_Common::get_plugin_options();
		$payment_methods = NicheclearAPI_DB_Manager::get_payment_method_options();
		wp_send_json_success( [ 'settings' => $opts, 'payment_methods' => $payment_methods ] );
	}

	/**
	 * Save plugin options securely and respond with a JSON object indicating success or failure.
	 *
	 * This function updates plugin settings
	 * and payment methods, and sends a JSON response back to the client.
	 *
	 * @return void JSON response indicating success or failure.
	 */
	public function save_plugin_options() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'ncapi_save_options_nonce' ) ) {
			$json_input = json_decode( file_get_contents( 'php://input' ), true );

			if ( isset( $json_input['options'] ) && is_array( $json_input['options'] ) ) {
				$new_options = $json_input['options'];
			} else {
				wp_send_json_error( 'Invalid settings data.' );

				return;
			}

			// Update options
			$settings = NicheclearAPI_DB_Manager::save_plugin_settings( $new_options['settings'] );
			NicheclearAPI_DB_Manager::save_plugin_payment_methods( $new_options['payment_methods'] );

			wp_send_json_success( $settings );
		} else {
			wp_send_json_error( [
				'message'  => 'Invalid nonce specified',
				'response' => 403,
			] );
		}

		wp_die();
	}

	/**
	 * Perform database cleanup to remove old payment records.
	 *
	 * This function calls NicheclearAPI_DB_Manager to clean outdated payment data
	 * and logs the number of records removed using NicheclearAPI_Common.
	 *
	 * @return void
	 */
	public function run_db_cleanup() {
		$cnt = NicheclearAPI_DB_Manager::clean_old_payment_data();
		NicheclearAPI_Common::error_log( "[DB Cleanup] Removed $cnt old payment records" );
	}

}
