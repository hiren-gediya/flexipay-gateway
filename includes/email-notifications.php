<?php
/**
 * Email Notifications for FlexiPay Gateway for WooCommerce.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_email_after_order_table', 'flexipay_custom_payment_details_in_onhold_email', 20, 4);

function flexipay_custom_payment_details_in_onhold_email($order, $sent_to_admin, $plain_text, $email)
{
    if ($email->id !== 'customer_on_hold_order') {
        return;
    }

    $email_opt = get_option('flexipay_email_notification_option', 'none');

    if ($email_opt === 'none') {
        return;
    }

    $payment_method = $order->get_payment_method();
    $is_flexipay_gateway = (strpos($payment_method, 'flexipay_gateway_') === 0);

    if (!$is_flexipay_gateway && $email_opt !== 'all') {
        return;
    }

    $gateway_list = get_option('flexipay_gateways_list', array());
    $max_slots = get_option('flexipay_gateway_slots', 5);

    // We only care about gateways that are actually registered/enabled based on current slots
    $active_keys = array_slice(array_keys($gateway_list), 0, $max_slots);

    echo '<h2 style="margin-top:20px;">' . esc_html__('Payment Details', 'flexipay-gateway') . '</h2>';
    echo '<table cellpadding="10" cellspacing="0" border="1" width="100%" 
          style="border-collapse:collapse;font-family:helvetica neue, helvetica, roboto, arial, sans-serif; font-size:14px;margin-bottom:20px;">';

    foreach ($active_keys as $key) {
        $gateway_id = 'flexipay_gateway_' . $key;
        $settings = get_option('woocommerce_' . $gateway_id . '_settings', array());

        $is_enabled = isset($settings['enabled']) && $settings['enabled'] === 'yes';
        if (!$is_enabled) {
            continue;
        }

        // Show if "Send All" is on OR if it's the selected method and "Send Selected" is on
        $should_show = ($email_opt === 'all') || ($email_opt === 'selected' && $payment_method === $gateway_id);

        if ($should_show) {
            $name = $gateway_list[$key];
            $payment_details = isset($settings['payment_details']) ? $settings['payment_details'] : '';
            $qr_image = isset($settings['qr_image']) ? $settings['qr_image'] : '';

            if (empty($payment_details) && empty($qr_image)) {
                continue;
            }

            echo '<tr style="background:#e5e5e5;">
                    <th colspan="2" align="left" style="color:#636363;background-color:#eeeeee;">' . esc_html($name) . '</th>
                  </tr>';

            if (!empty($payment_details)) {
                echo '<tr>
                        <td width="30%" style="color:#636363;">' . esc_html__('Details', 'flexipay-gateway') . '</td>
                        <td style="color:#636363;">' . nl2br(esc_html($payment_details)) . '</td>
                      </tr>';
            }

            if (!empty($qr_image)) {
                echo '<tr>
                        <td style="color:#636363;background-color:#eeeeee;">' . esc_html__('QR Code', 'flexipay-gateway') . '</td>
                        <td><img src="' . esc_url($qr_image) . '" width="180" style="width: 180px; display: block;" alt="' . esc_attr__('QR Code', 'flexipay-gateway') . '"></td>
                      </tr>';
            }
        }
    }

    $instruction = get_option('flexipay_payment_instruction', '');
    if (!empty($instruction)) {
        echo '<tr style="background:#e5e5e5;">
                <th colspan="2" align="left" style="color:#636363;background-color:#eeeeee;">' . esc_html__('Instructions', 'flexipay-gateway') . '</th>
              </tr>';
        echo '<tr>
                <td colspan="2" style="color:#636363;">' . nl2br(esc_html($instruction)) . '</td>
              </tr>';
    }

    echo '</table>';
}
