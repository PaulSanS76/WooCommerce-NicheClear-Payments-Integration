<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/includes
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */
class NicheclearAPI_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( NicheclearAPI_Common::CRON_HOOK_DB_CLEANUP );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, NicheclearAPI_Common::CRON_HOOK_DB_CLEANUP );
		}
	}

}
