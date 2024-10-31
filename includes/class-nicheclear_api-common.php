<?php

class NicheclearAPI_Common {

	const plugin_name = 'Nicheclear Payment Gateway';

	//whether to log requests and responses as JSON files
	const json_logging = true;

	public static function get_plugin_dir(): string {
		return dirname( __FILE__, 2 );
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

	public static function get_api_key() {
		return get_option( 'ncapi_key' );
	}

	public static function get_signing_key() {
		return get_option( 'ncapi_signing_key' );
	}

	public static function get_webhook_url_base() {
		return defined( 'WEBHOOK_URL_BASE' ) ? WEBHOOK_URL_BASE : get_site_url();
	}


}