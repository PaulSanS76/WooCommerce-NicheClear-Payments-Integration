<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

class NicheclearAPI_WooManager {
	public function add_test_gateway_class( $gateways ) {
		if ( ! empty( NicheclearAPI_Common::get_api_key() ) && ! empty( NicheclearAPI_Common::get_signing_key() ) ) {
//			$gateways[] = 'WC_Gateway_NicheClear_Base';
			$gateways[] = 'WC_Gateway_NicheClear_PIX';
			$gateways[] = 'WC_Gateway_NicheClear_Blik';
			$gateways[] = 'WC_Gateway_NicheClear_BasicCard';
		}

		return $gateways;
	}

//	static $path = __FILE__;

	public function init_test_gateway_class() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_pix.php';
		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_blik.php';
		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_basic_card.php';
	}
}
