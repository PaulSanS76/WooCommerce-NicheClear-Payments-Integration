<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';

/**
 * Class NicheclearAPI_WooManager
 *
 * Manages WooCommerce payment gateway integrations for the Nicheclear API.
 */
class NicheclearAPI_WooManager {

	/**
	 * Adds gateway classes to the list of payment gateways.
	 *
	 * @param array $gateways An array of existing payment gateway objects.
	 *
	 * @return array The updated array of payment gateway objects with added gateways.
	 */
	public function add_nc_gateway_classes( $gateways ) {

		$ncapi_listed_methods = NicheclearAPI_DB_Manager::get_listed_payment_methods();
		foreach ( $ncapi_listed_methods as $gateway ) {
			$obj        = new WC_Gateway_NicheClear_Generic( $gateway );
			$gateways[] = $obj;
		}

		return $gateways;
	}

	/**
	 * Initializes the NicheClear API Gateway class for WooCommerce payment gateways.
	 *
	 * @return void
	 */
	public function init_nc_gateway_class() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_generic.php';
	}
}
