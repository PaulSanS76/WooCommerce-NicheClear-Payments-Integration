<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';

/**
 * WC_Gateway_NicheClear_Base extends WC_Payment_Gateway to provide a custom payment gateway integration.
 */
class WC_Gateway_NicheClear_Base extends WC_Payment_Gateway {
	private $api_url = 'https://app.nicheclear.com';
	private $api_sandbox_url = 'https://app-demo.nicheclear.com';

	protected $api_key;

	protected bool $is_sandbox = true;

	protected function get_api_url(): string {
		return $this->is_sandbox ? $this->api_sandbox_url : $this->api_url;
	}

	protected function get_payment_processor_code() {
		return 'nc_base';
	}

	public function __construct() {
		$this->id = 'nc_base'; // Unique ID for the gateway.
	}

	// Define the settings fields for the payment gateway.
	public function init_form_fields() {
		$this->form_fields = array(
			'title'       => array(
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title that the user sees during checkout.',
				'default'     => $this->method_title,
			),
			'description' => array(
				'title'       => 'Description',
				'type'        => 'textarea',
				'description' => 'Payment method description that the customer will see during checkout.',
				'default'     => "Pay with a $this->method_title.",
			),
			'sandbox'     => array(
				'title'   => 'Sandbox Mode',
				'type'    => 'checkbox',
				'label'   => "Enable Sandbox Mode",
				'default' => 'no',
			),
		);
	}

	/**
	 * Processes a payment for a given order.
	 *
	 * @param int $order_id The ID of the order to process the payment for.
	 *
	 * @return array The result of the payment process, including status and URLs.
	 * @throws Exception If an error occurs during the payment process.
	 */
	public function process_payment( $order_id ) {

		try {
			$order = wc_get_order( $order_id );
			$req   = $this->ncapi_create_payment_request( $order );
			$uuid  = $req['uuid'];
			unset( $req['uuid'] );
			NicheclearAPI_DB_Manager::insert_payment_info( $uuid, $order_id, $req );

			$nc_resp = $this->ncapi_send_payment_request( $req );

			NicheclearAPI_DB_Manager::update_payment_info( $uuid, [ 'response' => json_encode( $nc_resp, JSON_PRETTY_PRINT ) ] );

			if ( ! empty( $nc_resp['errors'] ) ) {
				$err_msg = '';
				foreach ( $nc_resp['errors'] as $err ) {
					$err_msg .= implode( ': ', array_filter( [
							$err['objectName'] ?? null,
							$err['field'] ?? null,
							$err['defaultMessage'] ?? null
						] ) ) . "\n";
				}

				throw new Exception( $err_msg ?: 'An error occurred while processing the payment' );
			}

			if ( empty( $ncapi_redirect_url = $nc_resp['result']['redirectUrl'] ) ) {
				throw new Exception( 'An error occurred while processing the payment: no redirectUrl returned' );
			}

			return [
				'result'                  => 'success',
				'returnUrl'               => get_site_url() . "/wc-api/nc-payment-complete?uuid={$uuid}",
				'ncapi_checkout_dyn_data' => [
					'order_id'       => $order->get_id(),
					'payment_uuid'   => $uuid,
					'payment_method' => $this->get_payment_processor_code(),
					'nc_frame_url'   => $ncapi_redirect_url,
					'after_pay_url'  => get_site_url() . "/wc-api/nc-payment-complete?uuid={$uuid}",
				]
			];
		} catch ( Exception $e ) {
			NicheclearAPI_Common::error_log( "process_payment: {$e->getMessage()}" );

			return [
				'result'   => 'error',
				'messages' => $e->getMessage(),
			];

		}
	}


	/**
	 * @return string
	 */
	public function get_enabled(): string {
		return $this->enabled;
	}

	/**
	 * Create a payment request for the given WooCommerce order.
	 *
	 * @param WC_Order $order WooCommerce order object to create the payment request for.
	 *
	 * @return array Associative array containing the payment request details.
	 */
	public function ncapi_create_payment_request( WC_Order $order ): array {

		$uuid = wp_generate_uuid4();

		return [
			'uuid'           => $uuid,
			'referenceId'    => $order->get_id(),
			'description'    => "Order #{$order->get_id()} from " . get_bloginfo( 'name' ),
			'paymentType'    => 'DEPOSIT',
			'paymentMethod'  => $this->get_payment_processor_code(),
			'amount'         => $order->get_total(),
			'currency'       => $order->get_currency(),
			'customer'       => [
				'referenceId' => $order->get_user_id(),
				'firstName'   => $order->get_billing_first_name(),
				'lastName'    => $order->get_billing_last_name(),
				'email'       => $order->get_billing_email(),
			],
			'billingAddress' => [
				'countryCode'  => $order->get_billing_country(),
				'addressLine1' => $order->get_billing_address_1(),
				'city'         => $order->get_billing_city(),
				'state'        => $order->get_billing_state(),
				'postalCode'   => $order->get_billing_postcode()
			],
			'webhookUrl'     => NicheclearAPI_Common::get_webhook_url_base() . "/wc-api/ncapi_create_payment?uuid={$uuid}" . ( $this->is_sandbox ? '&sandbox' : '' ),
			'returnUrl'      => get_site_url() . "/wc-api/nc-payment-complete?uuid={$uuid}",
		];

	}

	/**
	 * Sends a payment request to the Nicheclear API.
	 *
	 * @param array $request The payment request data to be sent to the API.
	 *
	 * @return mixed The response from the API, decoded from JSON.
	 * @throws Exception If there is an error creating the payment request or parsing the response.
	 */
	public function ncapi_send_payment_request( array $request ): mixed {
		if ( NicheclearAPI_Common::json_logging ) {
			file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_payment_request.json',
				json_encode( $request, JSON_PRETTY_PRINT ) );
		}
		$response = wp_remote_post(
			"{$this->get_api_url()}/api/v1/payments",
			[
				'timeout' => 100000,
				'headers' => [ 'Authorization' => "Bearer $this->api_key", 'Content-Type' => 'application/json' ],
				'body'    => json_encode( $request ),
			]
		);

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			$nc_resp = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				NicheclearAPI_Common::error_log( "ncapi_send_payment_request: Error parsing response: " . json_last_error_msg() );
				throw new Exception( 'Error parsing ncapi_send_payment_request response: ' . json_last_error_msg() );
			}

			if ( NicheclearAPI_Common::json_logging ) {
				file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_payment_response.json',
					json_encode( $nc_resp, JSON_PRETTY_PRINT ) );
			}

			return $nc_resp;
		} else {
			NicheclearAPI_Common::error_log( $response->get_error_message() );
			throw new Exception( 'Error creating payment request' );
		}

	}

	/**
	 * Determines if the payment method is available for use.
	 *
	 * Checks if the customer's billing country is in the allowed countries list
	 * for the payment processor.
	 *
	 * @return bool True if the payment method is available, false otherwise.
	 */
	public function is_available() {
		// Default WooCommerce checks
		if ( ! parent::is_available() ) {
			return false;
		}

		if ( ! is_checkout() ) {
			return true;
		}

		$payment_processor_code = strtolower( $this->get_payment_processor_code() );

		$allowed_countries = get_option( "ncapi_allowed_countries_{$payment_processor_code}", 'all' );

		if ( $allowed_countries == 'all' ) {
			return true;
		}

		if ( $allowed_countries == 'none' ) {
			return false;
		}

		$allowed_countries = explode( ',', $allowed_countries );

		// Get the customer's billing country
		$billing_country = WC()->customer->get_billing_country();

		if ( ! in_array( $billing_country, $allowed_countries ) ) {
			return false;
		}

		return true;
	}

}
