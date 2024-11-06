<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';

class NicheclearAPI_WooManager {
	public function add_test_gateway_class( $gateways ) {

		$ncapi_listed_methods = NicheclearAPI_DB_Manager::get_listed_payment_methods();
		foreach ( $ncapi_listed_methods as $gateway ) {
			$obj        = new WC_Gateway_NicheClear_Generic( $gateway );
			$gateways[] = $obj;
		}

		return $gateways;
	}

	public function init_test_gateway_class() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_generic.php';
	}
}
