=== FlexiPay Gateway for WooCommerce ===
Contributors: hirengediya
Tags: woocommerce, payment gateway, manual payment, offline payment, custom payment, qr payment
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage up to 10 custom manual payment gateways for WooCommerce with flexible instructions and QR code support.

== Description ==
FlexiPay Gateway for WooCommerce allows you to create and manage multiple custom offline payment methods with ease. Add payment instructions, QR codes, and handle manual payments efficiently from your WooCommerce store.

This plugin requires WooCommerce to be installed and activated.

### Key Features:
* Dynamic Gateway Creation: Add multiple gateway names in the settings, and register up to 10 unique gateways with WooCommerce.
* Custom Instructions & QR Codes: Assign unique payment instructions and QR codes for each gateway.
* Email Notifications: Send specific gateway details or a list of all custom gateways in the customer's "On-Hold" email.
* Flexible Slot Limits: Control how many gateways are active to optimize performance.
* Clean Interface: Integrated directly into the WooCommerce menu for a seamless management experience.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/flexipay-gateway` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings > FlexiPay Gateway.
4. Add a new payment gateway using the "Add Gateway" option.
5. After creating a gateway, go to WooCommerce > Settings > Payments.
6. Enable your created FlexiPay Gateway and configure it.

### Security
All inputs are sanitized and escaped following WordPress coding standards. Nonces and capability checks are used for secure data handling.

### Use Cases
* Accept bank transfers with instructions
* Collect payments via QR codes
* Handle manual payments for local businesses
* Provide alternative payment options to customers

== Support ==

For support or feature requests, please contact the plugin author.

== Frequently Asked Questions ==

= How many gateways can I create? =
You can add as many names as you like, but you can currently register and enable up to 10 unique gateways with WooCommerce simultaneously.

= Can I show a QR code at checkout? =
Yes! Every custom gateway has its own "QR Image" field in the WooCommerce payment settings.

= Does this process payments automatically? =
No, this plugin is for manual (offline) payments. It sets the order status to "On Hold" so you can verify the payment manually before processing the order.

== Screenshots ==

1. The main management page under Settings > FlexiPay Gateway.
2. Gateway configuration in WooCommerce Settings.
3. Example of payment details in the customer email.

== Changelog ==

= 1.0.0 =
* Initial release.
* Added dynamic gateway registration.
* Added email notification options.
* Added global payment instructions.
