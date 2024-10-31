<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_base.php';

class WC_Gateway_NicheClear_Pix extends WC_Gateway_NicheClear_Base {

	protected function get_payment_processor_code(  ) {
		return 'PIX';
	}

	public function __construct() {

		parent::__construct();
		$this->id                 = 'nc_pix'; // Unique ID for the gateway.
		$this->icon               = ''; // URL of the icon that represents the gateway.
		$this->has_fields         = false; // If the gateway has custom form fields.
		$this->method_title       = 'PIX';
		$this->method_description = 'PIX payment processor';

		// Load settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get gateway settings.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->is_sandbox = $this->get_option( 'sandbox' ) == 'yes';
		$this->api_key     = NicheclearAPI_Common::get_api_key();

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [
			$this,
			'process_admin_options'
		] );
	}


}
