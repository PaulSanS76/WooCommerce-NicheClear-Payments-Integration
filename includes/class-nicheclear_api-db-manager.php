<?php

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

class NicheclearAPI_DB_Manager {

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

	public static function save_plugin_settings( $new_settings ) {
		$old_settings = NicheclearAPI_Common::get_plugin_options();
		if ( ! is_array( $old_settings ) ) {
			$old_settings = array();
		}
		$options = array_merge( $old_settings, $new_settings );
		update_option( 'ncapi_settings', json_encode( $options ) );

		return $options;
	}

	public static function create_db_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ncapi_payments" );

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ncapi_payments (
    ID              bigint unsigned auto_increment,
    uuid            CHAR(36)                             NOT NULL unique,
    order_id        bigint unsigned,
    status          varchar(50)                          NULL,
    request         text,
    response        text,
    webhook_request text,
    created_at      datetime default current_timestamp() NOT NULL,
    updated_at      datetime                             NULL on update current_timestamp(),
    PRIMARY KEY  (ID)
) {$charset_collate};";

		dbDelta( $sql );
	}

	public static function save_plugin_payment_methods( $new_payment_methods ) {
		foreach ( $new_payment_methods as $new_pm_opts ) {
			if ( isset( $new_pm_opts['payment_method'] ) ) {
				$option_name = 'woocommerce_nc_' . strtolower( $new_pm_opts['payment_method'] ) . '_settings';
				if ( $new_pm_opts['listed'] == 0 ) {
					delete_option( $option_name );
				} else {
					$saved_opts            = get_option( $option_name, [] );
					$saved_opts['enabled'] = $new_pm_opts['enabled'] == 1 ? 'yes' : 'no';
					if ( isset( $new_pm_opts['sandbox'] ) ) {
						$saved_opts['sandbox'] = $new_pm_opts['sandbox'] == 1 ? 'yes' : 'no';
					}
					if ( empty( $saved_opts['title'] ) ) {
						$saved_opts['title'] = $new_pm_opts['payment_method'];
					}
					if ( empty( $saved_opts['description'] ) ) {
						$saved_opts['description'] = "Pay with {$new_pm_opts['payment_method']}";
					}
					update_option( $option_name, $saved_opts );
				}
			}
		}

		$enabled_methods = array_filter( $new_payment_methods, fn( $pm ) => $pm['listed'] );
		$concat_methods  = implode( ',', array_map( fn( $pm ) => $pm['payment_method'], $enabled_methods ) );
		update_option( 'ncapi_listed_methods', $concat_methods );

	}

	public static function get_listed_payment_methods() {
		$ncapi_listed_methods = get_option( 'ncapi_listed_methods', '' );
		$ncapi_listed_methods = explode( ',', $ncapi_listed_methods );
		$ncapi_listed_methods = array_filter( $ncapi_listed_methods );

		return $ncapi_listed_methods;
	}

	public static function get_payment_method_options() {
		$wc_opt_names = array_map( fn( $x ) => strtolower( "woocommerce_nc_{$x}_settings" ), NicheclearAPI_Common::$all_payment_methods );
		$wc_opts      = get_options( $wc_opt_names );

		$result = [];
		foreach ( NicheclearAPI_Common::$all_payment_methods as $payment_method ) {
			$wc_pm_name = strtolower( "woocommerce_nc_{$payment_method}_settings" );
			if ( $pm_opts = $wc_opts[ $wc_pm_name ] ) {
				$result[ $payment_method ] = [
					'payment_method' => $payment_method,
					'listed'         => true,
					'enabled'        => $pm_opts['enabled'] == 'yes',
					'sandbox'        => $pm_opts['sandbox'] == 'yes',
				];
			} else {
				$result[ $payment_method ] = [
					'payment_method' => $payment_method,
					'listed'         => false,
				];
			}
		}


		return $result;
	}

	public static function insert_payment_info( $uuid, $order_id ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'ncapi_payments',
			[
				'uuid'      => $uuid,
				'order_id'  => $order_id,
			]
		);

		return $wpdb->insert_id;
	}
}