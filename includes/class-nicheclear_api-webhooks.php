<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

/**
 * Class NicheclearAPI_Webhooks
 *
 * This class handles the webhooks for NicheclearAPI, including payment creation and payment completion events.
 */
class NicheclearAPI_Webhooks {

	// /wc-api/ncapi_create_payment
	/**
	 * Creates a payment webhook by processing incoming POST requests.
	 * This method validates the request signature, parses the JSON payload,
	 * and updates the order information in the database based on the payload
	 * details. It logs various stages of the process if JSON logging is enabled.
	 *
	 * @return void
	 */
	public function create_payment_webhook() {

		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			exit( 'Invalid request method' );
		}

		$post_body = file_get_contents( 'php://input' );

		$sandbox   = isset( $_REQUEST['sandbox'] );
		$hmac_hash = hash_hmac( 'sha256', $post_body, NicheclearAPI_Common::get_signing_key( $sandbox ) );

		$signature = $_SERVER['HTTP_SIGNATURE'] ?? null;
		if ( $signature !== $hmac_hash ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Invalid signature: " . $signature );

			return;
		}

		$webhook_payload = json_decode( $post_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Error parsing response: " . json_last_error_msg() );

			return;
		}

		if ( NicheclearAPI_Common::json_logging ) {
			file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_create_payment_webhook.json',
				json_encode( $webhook_payload, JSON_PRETTY_PRINT ) );
		}

		$uuid = $_REQUEST['uuid'] ?? null;
		if ( ! $uuid ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: No UUID provided" );

			return;
		}

		$order_id = $webhook_payload['referenceId'];
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Order not found: " . $order_id );

			return;
		}

		NicheclearAPI_DB_Manager::update_payment_info( $uuid,
			[
				'status'          => $webhook_payload['state'],
				'webhook_request' => json_encode( $webhook_payload, JSON_PRETTY_PRINT ),
			]
		);

		$note = implode( ': ', array_filter( [
			$webhook_payload['terminalName'] ?? null,
			$webhook_payload['state'] ?? null,
			$webhook_payload['errorMessage'] ?? null,
			$webhook_payload['externalResultCode'] ?? null,
		] ) );
		$order->add_order_note( $note );

		if ( $webhook_payload['state'] === 'COMPLETED' ) {
			$order->payment_complete();
			$order->update_status( 'completed' ); //TODO: check this
		}

	}

	// /wc-api/nc-payment-complete?uuid={$uuid}
	/**
	 * Completes the payment process.
	 *
	 * Retrieves payment information using the provided UUID. If the UUID is not provided or invalid,
	 * it logs an error and redirects the user to the home page. If the order associated with the
	 * payment information cannot be found, it logs an error and redirects the user to the home page.
	 *
	 * Waits for a valid payment status and handles different statuses:
	 * - COMPLETED: Redirects the user to the thank you page.
	 * - DECLINED: Logs an error note and redirects the user to the checkout payment page.
	 * - CANCELLED: Logs a cancellation note and redirects the user to the checkout payment page.
	 * - Default: Redirects the user to the checkout payment page.
	 *
	 * @return void
	 */
	public function payment_complete() {

		$uuid = $_REQUEST['uuid'] ?? null;
//		$order_id = $_REQUEST['order_id'] ?? null;

		if ( ! $uuid || ! ( $payment_info = NicheclearAPI_DB_Manager::load_payment_info( $uuid ) ) ) {
			NicheclearAPI_Common::error_log( "payment_complete: No UUID provided" );
			NicheclearAPI_Common::redirect_in_top_frame( home_url() );
			exit();
		}

		$order_id = $payment_info['order_id'];
		if ( ! ( $order = wc_get_order( $order_id ) ) ) {
			NicheclearAPI_Common::error_log( "payment_complete: Order not found: " . $order_id );
			NicheclearAPI_Common::redirect_in_top_frame( home_url() );
			exit();
		}

		$checkout_payment_url = $order->get_checkout_payment_url();
		$thank_you_url        = $order->get_checkout_order_received_url();

		$waited_sec = 0;
		while ( ! ( $status = NicheclearAPI_DB_Manager::get_payment_status( $uuid, $order_id ) ) ) {
			if ( $waited_sec >= 3 ) {
				NicheclearAPI_Common::error_log( "payment_complete: Timed out waiting for webhook for order #$order_id" );
				NicheclearAPI_DB_Manager::update_payment_info( $uuid, [ 'note' => "Payment request timed out. Please retry later." ] );
				NicheclearAPI_Common::redirect_in_top_frame( $checkout_payment_url );
				exit();
			}
			usleep( 0.5e6 );
			$waited_sec += 0.5;
		}

		switch ( $status ) {
			case 'COMPLETED':
//				NicheclearAPI_DB_Manager::update_payment_info( $order_id, [ 'note' => "Your payment has been successfully processed." ] );
				NicheclearAPI_Common::redirect_in_top_frame( $thank_you_url );
				exit;
			case 'DECLINED':
				NicheclearAPI_DB_Manager::update_payment_info( $uuid, [ 'note' => "An error happened processing your payment. Please retry later." ] );
				NicheclearAPI_Common::redirect_in_top_frame( $checkout_payment_url );
				exit;
			case 'CANCELLED':
				NicheclearAPI_DB_Manager::update_payment_info( $uuid, [ 'note' => "Your payment has been canceled." ] );
				NicheclearAPI_Common::redirect_in_top_frame( $checkout_payment_url );
				exit;
			default:
				NicheclearAPI_Common::redirect_in_top_frame( $checkout_payment_url );
				exit;
		}

	}
}
