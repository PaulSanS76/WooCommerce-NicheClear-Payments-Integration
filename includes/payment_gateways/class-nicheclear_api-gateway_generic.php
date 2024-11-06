<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_base.php';

class WC_Gateway_NicheClear_Generic extends WC_Gateway_NicheClear_Base {
	public $payment_processor_code;
	protected function get_payment_processor_code(  ) {
		return $this->payment_processor_code;
	}

	public function __construct($payment_processor_code) {
		parent::__construct();

		$this->payment_processor_code = $payment_processor_code;
		$this->id                 = 'nc_' . strtolower($this->payment_processor_code); // Unique ID for the gateway.
		$this->icon               = ''; // URL of the icon that represents the gateway.
		$this->has_fields         = false; // If the gateway has custom form fields.
		$this->method_title       = $this->payment_processor_code;
		$this->method_description = "$this->payment_processor_code payment processor";

		// Get gateway settings.
		$this->title       = $this->get_option( 'title' ) ?? $this->payment_processor_code;
		$this->description = $this->get_option( 'description' ) ?? "Pay with $this->payment_processor_code";

		$settings = NicheclearAPI_Common::get_plugin_options();
		if ( $this->get_option( 'sandbox' ) == 'yes' ) {
			$this->is_sandbox = true;
			$this->api_key    = $settings['api_key_sandbox'];
		} else {
			$this->is_sandbox = false;
			$this->api_key    = $settings['api_key_prod'];
		}

		// Load settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get gateway settings.
/*		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->is_sandbox = $this->get_option( 'sandbox' ) == 'yes';
		$this->api_key     = NicheclearAPI_Common::get_api_key();*/

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [
			$this,
			'process_admin_options'
		] );
	}

}
