=== Plugin Name ===
Contributors: meadowlark
Donate link: https://meadowlark.com/
Tags: nicheclear, payment, gateway, woocommerce, api, multi-payment
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate multiple payment methods through Nicheclear's unified payment gateway API for WooCommerce stores.

== Description ==

**Nicheclear Payment Gateway** is a comprehensive WordPress plugin that integrates your WooCommerce store with Nicheclear's multi-payment gateway API. This plugin enables you to accept various payment methods through a single, unified integration, simplifying your payment processing while expanding customer payment options.

## Key Features

* **Multi-Payment Gateway Support**: Accept multiple payment methods through a single integration
* **Dynamic Gateway Loading**: Automatically load available payment methods from Nicheclear API
* **WooCommerce Native Integration**: Seamless integration with existing WooCommerce stores
* **Webhook Support**: Real-time payment notifications and order status updates
* **Flexible Configuration**: Easy setup and customization of payment gateway options
* **Database Management**: Efficient storage and retrieval of payment method configurations
* **Generic Gateway Framework**: Extensible architecture for adding new payment methods

## Perfect For

* E-commerce stores requiring multiple payment options
* Businesses looking to streamline payment processing
* Developers building custom payment integrations
* Online stores serving international customers

## How It Works

The plugin dynamically loads available payment methods from the Nicheclear API and creates corresponding WooCommerce payment gateways. Each payment method becomes a separate gateway option in your WooCommerce checkout, allowing customers to choose their preferred payment method while maintaining a unified backend integration.

== Installation ==

1. Upload the `nicheclear_api` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your Nicheclear API credentials in the plugin settings
4. Set up your preferred payment methods and gateway options
5. Test the integration with a test transaction

## Requirements

* WordPress 5.0 or higher
* WooCommerce 3.0 or higher
* PHP 7.4 or higher
* Valid Nicheclear API credentials
* SSL certificate (required for payment processing)

== Frequently Asked Questions ==

= What is Nicheclear? =

Nicheclear is a payment service provider that offers a unified API for multiple payment methods, allowing businesses to accept various payment types through a single integration.

= Do I need a Nicheclear account? =

Yes, you'll need to sign up for a Nicheclear account and obtain API credentials to use this plugin.

= Which payment methods are supported? =

The plugin dynamically loads available payment methods from your Nicheclear account, so the specific methods depend on your account configuration and location.

= Is this plugin secure? =

Yes, the plugin follows WordPress security best practices and integrates securely with Nicheclear's API using proper authentication and encryption.

= Can I customize the payment gateway appearance? =

Yes, the plugin provides options for customizing gateway labels, descriptions, and checkout appearance.

= Does this work with existing WooCommerce orders? =

Yes, the plugin integrates seamlessly with your existing WooCommerce setup and won't affect previous orders.

== Screenshots ==

1. Plugin configuration panel showing API settings and payment method options
2. WooCommerce payment gateway settings with Nicheclear integration
3. Checkout page displaying multiple payment method options

== Changelog ==

= 1.0.0 =
* Initial release with core Nicheclear API integration
* Multi-payment gateway support
* WooCommerce payment gateway integration
* Webhook handling for payment notifications

== Upgrade Notice ==

= 1.0.0 =
Initial release with comprehensive payment gateway integration. Perfect for stores requiring multiple payment options.

== Support ==

For support, feature requests, or bug reports, please visit [Meadowlark](https://meadowlark.com/) or contact our development team.

## Technical Details

The plugin is built with modern PHP practices and includes:
* Dynamic payment gateway loading from Nicheclear API
* WooCommerce payment gateway framework integration
* Webhook handling for real-time updates
* Efficient database operations for payment method storage
* Extensible architecture for future payment method additions
* Comprehensive error handling and logging

## Payment Methods

The plugin automatically detects and loads available payment methods from your Nicheclear account, which may include:
* Credit and debit cards
* Digital wallets
* Bank transfers
* Local payment methods
* Cryptocurrency payments (if supported)

Each payment method becomes a separate gateway option in your WooCommerce checkout, providing customers with multiple payment choices while maintaining a unified backend integration.