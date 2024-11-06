<?php

class NicheclearAPI_Common {

	const plugin_name = 'Nicheclear Payment Gateway';

	//whether to log requests and responses as JSON files
	const json_logging = true;
	public static $all_payment_methods = [
		"ALFAKIT",
		"ALYCEPAY",
		"ARI10",
		"ASTROPAY",
		"B2BINPAY",
		"BANKTRANSFER",
		"BASIC_CARD",
		"BILLLINE",
		"BKASH",
		"BOLETO",
		"CLICK",
		"CODI",
		"CRYPTO",
		"CUP",
		"DPOPAY",
		"EFECTY",
		"EMANAT",
		"EXTERNAL_HPP",
		"FINRAX",
		"FLEXEPIN",
		"GATE8TRANSACT",
		"GATEIQ",
		"GIROPAY",
		"GCASH",
		"IDEAL",
		"IMPS",
		"INAI",
		"INRPAY",
		"INTERAC",
		"INTERAC_ACH",
		"INTERAC_ETO",
		"INTERAC_RTO",
		"JETONCASH",
		"JETONWALLET",
		"KESSPAY",
		"KLARNA",
		"LOCALPAYMENT",
		"M10",
		"MACROPAY",
		"MIFINITY",
		"MOBILE_MONEY",
		"MOLLIE",
		"MONERCHY",
		"MONETIX",
		"MONNET",
		"MUCHBETTER",
		"NAGAD",
		"NETBANKING",
		"NETELLER",
		"NGENIUS",
		"NODA",
		"NODA_REVOLUT",
		"OMNIMATRIX",
		"OPEN_BANKING",
		"OXXO",
		"P2P_CARD",
		"P2P_IBAN",
		"P2P_SBP",
		"PAGOEFECTIVO",
		"PAGOFACIL",
		"PAY2PLAY",
		"PAYMAYA",
		"PAYCASH",
		"PAYFUN",
		"PAYID",
		"PAYMAXIS",
		"PAYOUT_NONSEPA_REQUEST",
		"PAYOUT_SEPA_BATCH",
		"PAYPAL",
		"PAYPORT",
		"PAYRETAILERS",
		"PAYSAFECARD",
		"PAYTM",
		"PEC",
		"PERFECTMONEY",
		"PICPAY",
		"PIX",
		"PSE",
		"PUSH",
		"QR",
		"RAPIDTRANSFER",
		"RAPIPAGO",
		"ROCKET",
		"RTGS",
		"RUBPAY",
		"SBP",
		"SKRILL",
		"SOFORT",
		"SPEI",
		"SPOYNT",
		"TED",
		"TINK",
		"TRUSTPAYMENTS",
		"UPI",
		"VIETTEL",
		"VOLT",
		"WEBPAY",
		"XANPAY",
		"XINPAY",
		"YAPILY",
		"ZALO",
		"ZIPAY",
		"ZOTAPAY",
	];

	public static function get_plugin_dir(): string {
		return dirname( __FILE__, 2 );
	}

	public static function get_plugin_options() {
		$opts = get_option( 'ncapi_settings' );

		return json_decode( $opts, true );
	}

	public static function log_dir(): string {
		return wp_upload_dir()['basedir'] . '/nicheclear_api';
	}

	public static function cur_date() {
		return date( '[d-M-Y H:i:s e]' );
	}

	/**
	 * Static error log function.
	 *
	 * @param string $msg
	 *
	 * @return void
	 */
	public static function error_log( string $msg ): void {
		$log_dir = wp_upload_dir()['basedir'] . '/nicheclear_api';
		error_log( self::cur_date() . ' ' . $msg . "\n", 3, self::log_dir() . "/debug.log" );
	}

	/*	public static function get_api_key() {
			return get_option( 'ncapi_key' );
		}*/

	public static function get_signing_key( $sandbox = false ) {
		$settings = self::get_plugin_options();

		return $sandbox ? $settings['signing_key_sandbox'] : $settings['signing_key_prod'];
	}

	public static function get_webhook_url_base() {
		return defined( 'WEBHOOK_URL_BASE' ) ? WEBHOOK_URL_BASE : get_site_url();
	}


}