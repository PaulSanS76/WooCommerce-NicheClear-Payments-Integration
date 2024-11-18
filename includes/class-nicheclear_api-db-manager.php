<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

/**
 * Class NicheclearAPI_DB_Manager
 *
 * Handles the management of plugin settings and database operations for the NicheclearAPI plugin.
 */
class NicheclearAPI_DB_Manager {

	/**
	 * Retrieves the titles of active payment methods.
	 *
	 * @return array An associative array where keys are the methods and values are their corresponding titles.
	 */
	public static function get_active_methods_titles() {
		$ncapi_listed_methods = self::get_listed_payment_methods();

		$wc_opts = get_options( array_map( fn( $method ) => strtolower( "woocommerce_nc_{$method}_settings" ), $ncapi_listed_methods ) );
		$result  = [];
		foreach ( $ncapi_listed_methods as $method ) {
			$wc_opt = $wc_opts[ strtolower( "woocommerce_nc_{$method}_settings" ) ];
			if ( $wc_opt['enabled'] == 'yes' ) {
				$result[ $method ] = $wc_opt['title'] ?? $method;
			}
		}

		return $result;
	}

	/**
	 * Saves the new plugin settings by merging them with the existing settings.
	 *
	 * @param array $new_settings An associative array of the new settings to be saved.
	 *
	 * @return array The combined array of old and new settings.
	 */
	public static function save_plugin_settings( $new_settings ) {
		$old_settings = NicheclearAPI_Common::get_plugin_options();
		if ( ! is_array( $old_settings ) ) {
			$old_settings = array();
		}
		$options = array_merge( $old_settings, $new_settings );
		update_option( 'ncapi_settings', json_encode( $options ) );

		return $options;
	}

	/**
	 * Creates the necessary database tables for payment processing.
	 *
	 * Executes the SQL statement to create a table named 'ncapi_payments' if it does not exist.
	 * The table structure includes fields for UUID, order ID, status, notes, request/response data,
	 * webhook request, creation timestamp, and update timestamp.
	 *
	 * @return void
	 */
	public static function create_db_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

//		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ncapi_payments" );

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ncapi_payments (
    uuid            char(36)         NOT NULL default(uuid()),
    order_id        bigint unsigned,
    status          varchar(50)                          NULL,
    note         	text,
    request         text,
    response        text,
    webhook_request text,
    created_at      datetime default current_timestamp() NOT NULL,
    updated_at      datetime                             NULL on update current_timestamp(),
    PRIMARY KEY  (uuid)
) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Saves the plugin payment methods and updates their options.
	 *
	 * @param array $updated_payment_methods An array of updated payment method options. Each element should be an associative array
	 * containing 'payment_method', 'listed', 'enabled', 'sandbox', 'title', 'description', and 'countries' keys.
	 *
	 * @return void
	 */
	public static function save_plugin_payment_methods( $updated_payment_methods ) {
		foreach ( $updated_payment_methods as $upd_pm_opts ) {
			if ( $pm_code = $upd_pm_opts['payment_method'] ?? null ) {
				$pm_code_lower = strtolower( $pm_code );
				$option_name   = "woocommerce_nc_{$pm_code_lower}_settings";
				if ( $upd_pm_opts['listed'] == 0 ) {
					delete_option( $option_name );
				} else {
					$saved_opts            = get_option( $option_name, [] );
					$saved_opts['enabled'] = $upd_pm_opts['enabled'] == 1 ? 'yes' : 'no';
					if ( isset( $upd_pm_opts['sandbox'] ) ) {
						$saved_opts['sandbox'] = $upd_pm_opts['sandbox'] == 1 ? 'yes' : 'no';
					}
					if ( empty( $saved_opts['title'] ) ) {
						$saved_opts['title'] = $upd_pm_opts['payment_method'];
					}
					if ( empty( $saved_opts['description'] ) ) {
						$saved_opts['description'] = "Pay with {$upd_pm_opts['payment_method']}";
					}
					update_option( $option_name, $saved_opts );
				}

				if ( isset( $upd_pm_opts['countries'] ) ) {
					$countries_list = $upd_pm_opts['countries'];

					if ( $countries_list == 'all' ) {
						delete_option( "ncapi_allowed_countries_{$pm_code_lower}" );
					} else {
						if ( is_array( $countries_list ) ) {
							$countries_list = implode( ',', $countries_list );
						}
						update_option( "ncapi_allowed_countries_{$pm_code_lower}", $countries_list );
					}
				}

			}
		}

		$enabled_methods = array_filter( $updated_payment_methods, fn( $pm ) => $pm['listed'] );
		$concat_methods  = implode( ',', array_map( fn( $pm ) => $pm['payment_method'], $enabled_methods ) );
		update_option( 'ncapi_listed_methods', $concat_methods );
	}

	/**
	 * Retrieves the listed payment methods.
	 *
	 * @return array An array of listed payment methods as configured in the options.
	 */
	public static function get_listed_payment_methods() {
		$ncapi_listed_methods = get_option( 'ncapi_listed_methods', '' );
		$ncapi_listed_methods = explode( ',', $ncapi_listed_methods );
		$ncapi_listed_methods = array_filter( $ncapi_listed_methods );

		return $ncapi_listed_methods;
	}

	/**
	 * Retrieves payment method options, including status and allowed countries.
	 *
	 * @return array An associative array where keys are payment method codes and values are an array of properties like 'payment_method', 'listed', 'enabled', 'sandbox', and 'countries'.
	 */
	public static function get_payment_method_options() {
		$wc_opt_names         = array_map( fn( $code ) => strtolower( "woocommerce_nc_{$code}_settings" ), NicheclearAPI_Common::$all_payment_methods );
		$countries_per_method = array_map( fn( $code ) => strtolower( "ncapi_allowed_countries_{$code}" ), NicheclearAPI_Common::$all_payment_methods );
		$options_per_method   = get_options( $wc_opt_names );
		$countries_per_method = get_options( $countries_per_method );
		$all_countries        = array_keys( WC()->countries->get_countries() );

		$result = [];
		foreach ( NicheclearAPI_Common::$all_payment_methods as $pm_code ) {
			$wc_pm_opt_name = strtolower( "woocommerce_nc_{$pm_code}_settings" );
			if ( is_array( $pm_opts = $options_per_method[ $wc_pm_opt_name ] ) ) {
				$result[ $pm_code ] = [
					'payment_method' => $pm_code,
					'listed'         => true,
					'enabled'        => $pm_opts['enabled'] == 'yes',
					'sandbox'        => ($pm_opts['sandbox'] ?? '') == 'yes',
				];
			} else {
				$result[ $pm_code ] = [
					'payment_method' => $pm_code,
					'listed'         => false,
				];
			}

			$wc_pm_countries_opt_name = strtolower( "ncapi_allowed_countries_{$pm_code}" );

			switch ( $countries_per_method[ $wc_pm_countries_opt_name ] ) {
				case 'all':
				case false:
					$result[ $pm_code ]['countries'] = $all_countries;
					break;
				case 'none':
					$result[ $pm_code ]['countries'] = [];
					break;
				default:
					$result[ $pm_code ]['countries'] = explode( ',', $countries_per_method[ $wc_pm_countries_opt_name ] );
			}
		}


		return $result;
	}

	/**
	 * Inserts payment information into the database.
	 *
	 * @param string $uuid The unique identifier for the payment.
	 * @param int $order_id The ID of the order associated with the payment.
	 * @param array $req The request data containing payment information.
	 *
	 * @return void
	 */
	public static function insert_payment_info( $uuid, $order_id, $req ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'ncapi_payments',
			[
				'uuid'     => $uuid,
				'order_id' => $order_id,
				'request'  => json_encode( $req, JSON_PRETTY_PRINT ),
			]
		);
	}

	/**
	 * Loads the payment information based on a given UUID.
	 *
	 * @param string $uuid The UUID of the payment whose information is to be loaded.
	 *
	 * @return array|null An associative array containing payment information if found, or null if not found or if UUID is not provided.
	 */
	public static function load_payment_info( $uuid ): array|null {
		if ( ! $uuid ) {
			return null;
		}

		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ncapi_payments WHERE uuid = %s", $uuid
			), ARRAY_A
		);

		return $result;
	}

	/**
	 * Retrieves the payment status for a given UUID.
	 *
	 * @param string $uuid The unique identifier of the payment.
	 *
	 * @return string|null The status of the payment or null if the UUID is not provided.
	 */
	public static function get_payment_status( $uuid ) {
		if ( ! $uuid ) {
			return null;
		}

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM {$wpdb->prefix}ncapi_payments WHERE uuid = %s", $uuid
			)
		);
	}

	/**
	 * Updates payment information for a given UUID.
	 *
	 * @param string $uuid The unique identifier for the payment to be updated.
	 * @param array $data An associative array containing the payment information to be updated.
	 *
	 * @return void
	 */
	public static function update_payment_info( $uuid, array $data ) {
		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'ncapi_payments', $data, [ 'uuid' => $uuid, ] );
	}

	/**
	 * Deletes payment information associated with a given UUID.
	 *
	 * @param string $uuid The unique identifier of the payment information to be deleted.
	 *
	 * @return void
	 */
	public static function delete_payment_info( $uuid ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ncapi_payments', [ 'uuid' => $uuid, ] );
	}

	/**
	 * Cleans up old payment data from the database.
	 *
	 * @return int|false The number of rows affected, or false on error.
	 */
	public static function clean_old_payment_data(  ) {
		global $wpdb;
		return $wpdb->query(
			"DELETE FROM {$wpdb->prefix}ncapi_payments WHERE created_at < DATE_SUB( NOW(), INTERVAL 1 MONTH )"
		);
	}

	/**
	 * Replaces the content of the WooCommerce checkout page with the default shortcode.
	 *
	 * @return void
	 */
	public static function replace_woo_checkout_page(  ) {
		$checkout_page_id = wc_get_page_id('checkout');

		$checkout_content = '[woocommerce_checkout]';

		wp_update_post( [
			'ID'           => $checkout_page_id,
			'post_content' => $checkout_content,
		] );
	}

}