<?php

namespace tests;

require_once '../../../../wp-tests-config.php';
//require_once '../../../../wp-config.php';

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/payment_gateways/class-nicheclear_api-gateway_base.php';

use NicheclearAPI_Common;
use NicheclearAPI_DB_Manager;
use PHPUnit\Framework\TestCase;
use WC_Gateway_NicheClear_Base;

class NicheClearAPI_Test extends TestCase {

	protected function setUp(): void {

	}

	public function test_set_options(  ) {
		update_option( 'ncapi_key', 'yMkuqJlK5dVJ9Ciq9B8wBwKqGVQkyCE7' );
		update_option( 'ncapi_signing_key', 'CclPd9hTvcXP' );
		$this->assertTrue( true );
	}

	public function test_create_pix_payment() {
		$order = new \WC_Order();
		$order->set_id( 1 );
		$order->set_status( 'pending' );
		$order->set_total( 100.00 );
		$order->set_currency( 'USD' );
		$order->set_billing_first_name( 'John' );
		$order->set_billing_last_name( 'Doe' );
		$order->set_billing_email( 'john.doe@example.com' );
		$order->set_billing_phone( '1234567890' );
		$order->set_payment_method( 'pix' );

		$gateway = new WC_Gateway_NicheClear_Base();
		$req = $gateway->ncapi_create_payment_request($order);

		$nc_resp = $gateway->ncapi_send_payment_request($req);

		$this->assertTrue( true );
	}

	public function test_env_var() {
		$envType = getenv( 'envtype' );
		$this->assertTrue( true );
	}

	public function test_config_var() {
		$url = \NicheclearAPI_Common::get_webhook_url_base();
		$this->assertTrue( true );
	}

	public function test_plugin_basename(  ) {
		$basename = plugin_basename(ABSPATH . 'wp-content/plugins/nicheclear_api/nicheclear_api.php');
		$this->assertTrue( true );
	}

	public function test_get_multiple_options(  ) {
		$opts = get_options(['woocommerce_nc_blik_settings', 'woocommerce_nc_pix_settings', 'qqq']);
		$this->assertTrue( true );
	}

	public function test_create_tables(  ) {
		NicheclearAPI_DB_Manager::create_db_tables();
		$this->assertTrue( true );
	}

	public function test_uuid() {
		$uuid = wp_generate_uuid4();
		$l = strlen($uuid); //36
		$this->assertTrue( true );
	}

}
