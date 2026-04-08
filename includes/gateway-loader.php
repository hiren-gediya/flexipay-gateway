<?php
/**
 * Gateway Loader for dynamic WooCommerce registration.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('woocommerce_payment_gateways', 'flexipay_register_dynamic_gateways', 999);

function flexipay_register_dynamic_gateways($gateways)
{
    $gateway_list = get_option('flexipay_gateways_list', array());
    $max_slots = get_option('flexipay_gateway_slots', 5);
    $index = 1;

    foreach ($gateway_list as $key => $name) {
        $class_name = 'FlexiPay_Gateway_' . $index;

        if (class_exists($class_name)) {
            FlexiPay_Gateway_Registry::register($class_name, $key, $name);
            $gateways[] = $class_name;
        }

        $index++;
        if ($index > $max_slots || $index > 10) {
            break;
        }
    }

    return $gateways;
}
