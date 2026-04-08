<?php
/**
 * Gateway Registry to handle dynamic data without eval().
 */

if (!defined('ABSPATH')) {
    exit;
}

class FlexiPay_Gateway_Registry
{
    private static $gateways = array();

    public static function register($class_name, $key, $name)
    {
        self::$gateways[$class_name] = array(
            'key' => $key,
            'name' => $name,
        );
    }

    public static function get_data($class_name)
    {
        return isset(self::$gateways[$class_name]) ? self::$gateways[$class_name] : null;
    }
}
