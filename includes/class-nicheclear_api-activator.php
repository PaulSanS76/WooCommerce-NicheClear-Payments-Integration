<?php

/**
 * Fired during plugin activation
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */
class NicheclearAPI_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
		$log_dir = NicheclearAPI_Common::log_dir();

		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}
		if ( ! file_exists( "$log_dir/json" ) ) {
			wp_mkdir_p( "$log_dir/json" );
		}

//		update_option( 'ncapi_key', 'yMkuqJlK5dVJ9Ciq9B8wBwKqGVQkyCE7' );
//		update_option( 'ncapi_signing_key', 'CclPd9hTvcXP' );
	}

}
