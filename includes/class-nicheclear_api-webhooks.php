<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

class NicheclearAPI_Webhooks {

	// /wc-api/ncapi_create_payment
	public function create_payment_webhook() {
		$post_body = file_get_contents( 'php://input' );

		$sandbox = isset( $_REQUEST['sandbox'] );

		$hmac_hash = hash_hmac( 'sha256', $post_body, NicheclearAPI_Common::get_signing_key($sandbox) );

		/*		if ( NicheclearAPI_Common::json_logging ) {
					file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_create_payment_webhook_hmac.json',
						json_encode( [ 'hmac_hash' => $hmac_hash ], JSON_PRETTY_PRINT ) );
				}*/

		$signature = $_SERVER['HTTP_SIGNATURE'];
		if ( $signature !== $hmac_hash ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Invalid signature: " . $signature );

			return;
		} else {
//			NicheclearAPI_Common::error_log( "create_payment_webhook: Signature checked successfully" );
		}
		/*		if ( NicheclearAPI_Common::json_logging ) {
					file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_create_payment_webhook_signature.json', $signature );
				}*/

		$webhook_payload = json_decode( $post_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Error parsing response: " . json_last_error_msg() );

			return;
		}

		if ( NicheclearAPI_Common::json_logging ) {
			file_put_contents( NicheclearAPI_Common::log_dir() . '/json/' . date( 'Y-m-d_H-i-s' ) . '_create_payment_webhook.json',
				json_encode( $webhook_payload, JSON_PRETTY_PRINT ) );
		}

		$order_id = $webhook_payload['referenceId'];
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			NicheclearAPI_Common::error_log( "create_payment_webhook: Order not found: " . $order_id );

			return;
		}

//		$note = "PSP response: {$webhook_payload['state']}: {$webhook_payload['externalResultCode']}";
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

	// /wc-api/nc-payment-complete?order_id=$order_id
	public function payment_complete() {
		$order_id = $_REQUEST['order_id'] ?? null;

		if ( ! $order_id || ! is_numeric( $order_id ) || ! ( $order = wc_get_order( $order_id ) ) ) {
			NicheclearAPI_Common::error_log( "payment_complete: Order not found: " . $order_id );

			header( "Location: /" );
			exit;
		}

		if ( $order->is_paid() ) {
			$thank_you_url = $order->get_checkout_order_received_url();
			header( "Location: " . $thank_you_url );
			exit;
		} else {
			$checkout_payment_url = $order->get_checkout_payment_url();
			header( "Location: " . $checkout_payment_url );
			exit;
		}


	}
}
