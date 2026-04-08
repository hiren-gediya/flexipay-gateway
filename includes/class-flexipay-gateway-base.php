<?php
/**
 * Base WooCommerce Gateway Class.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'flexipay_init_gateways', 11);

function flexipay_init_gateways()
{
    if (!class_exists('WC_Payment_Gateway'))
        return;

    class FlexiPay_Gateway_Base extends WC_Payment_Gateway
    {
        public $gateway_key;
        public $gateway_name;

        public function __construct()
        {
            $data = FlexiPay_Gateway_Registry::get_data(get_class($this));
            if ($data) {
                $this->gateway_key = $data['key'];
                $this->gateway_name = $data['name'];
            }

            $this->id = 'flexipay_gateway_' . $this->gateway_key;
            $this->has_fields = true;
            $this->method_title = $this->gateway_name;

            /* translators: %s: Gateway name. */
            $this->method_description = sprintf(esc_html__('Pay via %s', 'flexipay-gateway'), $this->gateway_name);

            // Load settings
            $this->init_form_fields();
            $this->init_settings();

            // Always use the managed name as the gateway title
            $this->title = $this->method_title;
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled', 'no');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => esc_html__('Enable/Disable', 'flexipay-gateway'),
                    'type' => 'checkbox',
                    'label' => esc_html__('Enable this payment gateway', 'flexipay-gateway'),
                    'default' => 'no',
                ),
                'payment_details' => array(
                    'title' => esc_html__('Payment Details', 'flexipay-gateway'),
                    'type' => 'textarea',
                    'description' => esc_html__('Instructions that will be added to the thank you page and emails.', 'flexipay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => esc_html__('Description', 'flexipay-gateway'),
                    'type' => 'textarea',
                    'description' => esc_html__('Display this description on the checkout page.', 'flexipay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'qr_image' => array(
                    'title' => esc_html__('QR Image', 'flexipay-gateway'),
                    'type' => 'text',
                    'class' => 'flexipay-image-url',
                    'description' => $this->get_qr_image_preview_html(),
                    'default' => '',
                    'desc_tip' => false,
                ),
            );
        }

        protected function get_qr_image_preview_html()
        {
            $url = $this->get_option('qr_image');
            $has_image = !empty($url);

            ob_start();
            ?>
            <div class="flexipay-media-wrapper">
                <div class="flexipay-image-preview" id="flexipay_qr_preview">
                    <?php if ($has_image): ?>
                        <img src="<?php echo esc_url($url); ?>" style="max-width: 150px; display: block; margin-bottom: 10px;">
                    <?php else: ?>
                        <p class="description"><?php esc_html_e('No image selected.', 'flexipay-gateway'); ?></p>
                    <?php endif; ?>
                </div>
                <div class="flexipay-media-actions">
                    <button type="button"
                        class="button button-secondary flexipay-upload-button"><?php esc_html_e('Select Image', 'flexipay-gateway'); ?></button>
                    <button type="button" class="button button-link-delete flexipay-remove-button" <?php echo !$has_image ? 'style="display:none;"' : ''; ?>><?php esc_html_e('Remove', 'flexipay-gateway'); ?></button>
                </div>
                <p class="description"><?php esc_html_e('This image will be shown to customers at checkout.', 'flexipay-gateway'); ?></p>
            </div>
            <?php
            return ob_get_clean();
        }

        public function payment_fields()
        {
            $payment_details = $this->get_option('payment_details');
            $description = $this->get_option('description');
            $qr = $this->get_option('qr_image');

            if ($payment_details) {
                echo '<p><strong>' . esc_html__('Payment Details:', 'flexipay-gateway') . '</strong><br>';
                echo wp_kses_post(nl2br($payment_details)) . '</p>';
            }

            if ($qr) {
                echo '<div class="flexipay-checkout-qr" style="margin-top:15px; text-align:center;">';
                echo '<img src="' . esc_url($qr) . '" style="max-width:200px; height:auto; border:1px solid #ddd; padding:5px; background:#fff;">';
                echo '</div>';
            }

            if ($description) {
                echo '<p>' . wp_kses_post($description) . '</p>';
            }
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            /* translators: %s: Method title. */
            $order->update_status('on-hold', sprintf(esc_html__('Awaiting payment via %s', 'flexipay-gateway'), esc_html($this->title)));

            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        public function process_admin_options()
        {
            return parent::process_admin_options();
        }
    }

    /**
     * Pre-defined classes to satisfy Plugin Check's "no eval" rule.
     */
    class FlexiPay_Gateway_1 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_2 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_3 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_4 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_5 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_6 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_7 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_8 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_9 extends FlexiPay_Gateway_Base {}
    class FlexiPay_Gateway_10 extends FlexiPay_Gateway_Base {}
}
