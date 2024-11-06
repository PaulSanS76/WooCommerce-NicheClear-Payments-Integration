<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/public
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';

class NicheclearAPI_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nicheclear_api-public.css', array(), $this->version, 'all' );

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
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nicheclear_api-public.js', array( 'jquery' ), $this->version, false );

	}

	public function enqueue_checkout_custom_js_css() {
		if ( is_checkout() ) {
			$active_payment_methods = NicheclearAPI_DB_Manager::get_active_methods_titles();

			wp_enqueue_script( 'checkout-js', plugins_url( 'js/nicheclear_api-checkout.js', __FILE__ ), [ 'jquery' ], $this->version, true );
			wp_localize_script( 'checkout-js', 'ncapi_checkout_vars', array(
				'payment_method'         => 'nc_blik',
				'button_text'            => 'Pay via BLIK',
				'active_payment_methods' => $active_payment_methods,
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
			) );

			wp_enqueue_style( 'checkout-css', plugin_dir_url( __FILE__ ) . 'css/nicheclear_api-checkout.css', array(), $this->version, 'all' );

		}
	}

	public function inject_content_after_payment_methods() {
		?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css">
        <div id="ncapi-payment-method-section" style="display:none;">
            <div class="messages"></div>

            <button type="button" class="button alt wp-element-button" id="pay_ncapi">
                Pay via PIX
            </button>
        </div>
		<?php
	}

	public function ncapi_create_order() {
		try {
			// Initialize WooCommerce checkout instance
			$checkout = WC()->checkout();

			// Process the checkout using the POST data, which includes all the necessary checkout fields
			$checkout->process_checkout();

			// Assuming everything is fine, respond with a success and redirect URL
			wp_send_json_success( [
//				'redirect_url' => $order->get_checkout_order_received_url(),
				'OK' => true
			] );

		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => $e->getMessage()
			) );
		}
	}

}
