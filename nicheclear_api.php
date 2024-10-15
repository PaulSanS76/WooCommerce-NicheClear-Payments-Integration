<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://meadowlark.com
 * @since             1.0.0
 * @package           Nicheclear_api
 *
 * @wordpress-plugin
 * Plugin Name:       Nicheclear Payment Gateway
 * Plugin URI:        https://apidoc.nicheclear.com
 * Description:       Nicheclear multi-payment gateway for WooCommerce
 * Version:           1.0.0
 * Author:            Meadowlark
 * Author URI:        https://meadowlark.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nicheclear_api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NICHECLEAR_API_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nicheclear_api-activator.php
 */
function activate_nicheclear_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nicheclear_api-activator.php';
	NicheclearAPI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nicheclear_api-deactivator.php
 */
function deactivate_nicheclear_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nicheclear_api-deactivator.php';
	NicheclearAPI_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nicheclear_api' );
register_deactivation_hook( __FILE__, 'deactivate_nicheclear_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nicheclear_api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nicheclear_api() {

	$plugin = new NicheclearAPI();
	$plugin->run();

}
run_nicheclear_api();
