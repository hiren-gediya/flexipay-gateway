<?php
/**
 * Plugin Name: FlexiPay Gateway for WooCommerce
 * Description: FlexiPay Gateway for WooCommerce allows you to create and manage multiple custom offline payment methods with ease. Add payment instructions, QR codes, and handle manual payments efficiently from your WooCommerce store.
 * Version: 1.0.0
 * Text Domain: flexipay-gateway
 * Author: Hiren Gediya
 * Author URI: https://profiles.wordpress.org/hirengediya/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('FLEXIPAY_GATEWAY_PATH', plugin_dir_path(__FILE__));

/**
 * Check if WooCommerce is active
 */
function flexipay_gateway_init()
{
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            ?>
            <div class="error">
                <p><?php esc_html_e('FlexiPay Gateway for WooCommerce requires WooCommerce to be installed and active.', 'flexipay-gateway'); ?>
                </p>
            </div>
            <?php
        });
        return;
    }

    // Includes
    require_once FLEXIPAY_GATEWAY_PATH . 'includes/class-flexipay-gateway-registry.php';
    require_once FLEXIPAY_GATEWAY_PATH . 'includes/class-flexipay-gateway-base.php';
    require_once FLEXIPAY_GATEWAY_PATH . 'includes/admin-settings.php';
    require_once FLEXIPAY_GATEWAY_PATH . 'includes/gateway-loader.php';
    require_once FLEXIPAY_GATEWAY_PATH . 'includes/email-notifications.php';
}
add_action('plugins_loaded', 'flexipay_gateway_init');

/**
 * Add Settings link to plugin action links
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flexipay_gateway_settings_link');
function flexipay_gateway_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=flexipay-gateways">' . __('Settings', 'flexipay-gateway') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
