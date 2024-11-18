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
	 * Activates the plugin by ensuring necessary directories are created, database tables are set up,
	 * Woocommerce checkout page is replaced, and a CRON job for database cleanup is scheduled.
	 *
	 * @return void
	 */
	public static function activate() {
		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';
		require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-db-manager.php';
		$log_dir = NicheclearAPI_Common::log_dir();

		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}
		if ( ! file_exists( "$log_dir/json" ) ) {
			wp_mkdir_p( "$log_dir/json" );
		}

		NicheclearAPI_DB_Manager::create_db_tables();
		NicheclearAPI_DB_Manager::replace_woo_checkout_page();

		if (!wp_next_scheduled(NicheclearAPI_Common::CRON_HOOK_DB_CLEANUP)) {
			$timestamp = ( new DateTime( 'tomorrow 3:00 am', new DateTimeZone( wp_timezone_string() ) ) )->getTimestamp();
			wp_schedule_event($timestamp, 'monthly', NicheclearAPI_Common::CRON_HOOK_DB_CLEANUP);
		}
	}

}
