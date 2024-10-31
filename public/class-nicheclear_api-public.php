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
			wp_enqueue_script( 'checkout-js', plugins_url( 'js/nicheclear_api-checkout.js', __FILE__ ), [ 'jquery' ], $this->version, true );
			wp_localize_script( 'checkout-js', 'checkout_vars', array(
				'payment_method' => 'test', // Replace 'test' with your payment method's ID
				'button_text'    => 'Pay via PIX',
				'ajax_url' => admin_url('admin-ajax.php'),
			) );

			wp_enqueue_style( 'checkout-css', plugin_dir_url( __FILE__ ) . 'css/nicheclear_api-checkout.css', array(), $this->version, 'all' );

		}
	}

	public function inject_content_after_payment_methods() {
		?>
        <div id="ncapi-payment-method-section" style="display:none;">
            <div class="messages"></div>

            <button type="button" class="button alt wp-element-button" id="pay_ncapi">
                Pay via PIX
            </button>
        </div>
		<?php
	}

	public function ncapi_create_order(  ) {
		try {
			// Initialize WooCommerce checkout instance
			$checkout = WC()->checkout();

			// Process the checkout using the POST data, which includes all the necessary checkout fields
			$checkout->process_checkout();

			// Load the order to finalize any custom actions or payment handling
//			$order = wc_get_order($order_id);

			// Here, you can add custom logic to process payment or handle order status if necessary
			// Example:
			// $payment_result = your_custom_payment_processing_function($order);
			// if ($payment_result['success']) {
			//     $order->payment_complete();
			// } else {
			//     $order->update_status('failed', $payment_result['message']);
			//     wp_send_json_error(['message' => $payment_result['message']]);
			// }

			// Assuming everything is fine, respond with a success and redirect URL
			wp_send_json_success( [
//				'redirect_url' => $order->get_checkout_order_received_url(),
                'OK'=>true
			] );

		} catch (Exception $e) {
			// If there's an error during checkout processing, handle it gracefully
			wp_send_json_error(array(
				'message' => $e->getMessage()
			));
		}
    }

}
