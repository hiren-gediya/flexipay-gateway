<?php
/**
 * Admin scripts and settings page logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', 'flexipay_admin_scripts');

function flexipay_admin_scripts($hook)
{
    $is_wc_settings = (isset($hook) && 'woocommerce_page_wc-settings' === $hook);
    $is_flexipay_settings = (isset($hook) && 'settings_page_flexipay-gateways' === $hook);

    if (in_array($hook, array('post.php', 'post-new.php'), true) || $is_wc_settings || $is_flexipay_settings) {
        wp_enqueue_media();
        wp_enqueue_script('flexipay-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/admin.js', ['jquery'], '1.0', true);
        wp_enqueue_style('flexipay-admin-style', plugin_dir_url(dirname(__FILE__)) . 'assets/admin.css', [], '1.0');
    }
}

add_action('admin_menu', 'flexipay_admin_menu');

function flexipay_admin_menu()
{
    add_options_page(
        'FlexiPay Gateway',
        'FlexiPay Gateway',
        'manage_options',
        'flexipay-gateways',
        'flexipay_settings_page_callback'
    );
}

function flexipay_settings_page_callback()
{
    // Handle processing
    $action = isset($_POST['flexipay_action']) ? sanitize_text_field(wp_unslash($_POST['flexipay_action'])) : '';
    $delete_gateway = isset($_GET['delete_gateway']) ? sanitize_text_field(wp_unslash($_GET['delete_gateway'])) : '';
    $message_type = '';
    $message_text = '';

    // Handle adding new gateway
    if ($action === 'add' && isset($_POST['add_flexipay_gateway']) && check_admin_referer('flexipay_add_gateway')) {
        $name = isset($_POST['gateway_name']) ? sanitize_text_field(wp_unslash($_POST['gateway_name'])) : '';
        if (!empty($name)) {
            $gateways = get_option('flexipay_gateways_list', array());
            $key = sanitize_title($name);

            if (isset($gateways[$key])) {
                $key = $key . '_' . time();
            }

            $gateways[$key] = $name;
            update_option('flexipay_gateways_list', $gateways);
            set_transient('flexipay_msg_' . get_current_user_id(), 'added', 30);
            wp_safe_redirect(menu_page_url('flexipay-gateways', false));
            exit;
        }
    }

    // Handle deleting gateway
    if (!empty($delete_gateway) && check_admin_referer('flexipay_delete_gateway_' . $delete_gateway)) {
        $gateways = get_option('flexipay_gateways_list', array());
        if (isset($gateways[$delete_gateway])) {
            unset($gateways[$delete_gateway]);
            update_option('flexipay_gateways_list', $gateways);
            set_transient('flexipay_msg_' . get_current_user_id(), 'deleted', 30);
            wp_safe_redirect(menu_page_url('flexipay-gateways', false));
            exit;
        }
    }

    // Handle general settings saving
    if ($action === 'save_general' && isset($_POST['save_flexipay_settings']) && check_admin_referer('flexipay_save_settings')) {
        $slots = isset($_POST['flexipay_gateway_slots']) ? absint($_POST['flexipay_gateway_slots']) : 5;
        $slots = min(max($slots, 1), 10);
        update_option('flexipay_gateway_slots', $slots);

        $email_opt = isset($_POST['flexipay_email_notification_option']) ? sanitize_text_field(wp_unslash($_POST['flexipay_email_notification_option'])) : 'none';
        update_option('flexipay_email_notification_option', $email_opt);

        $instruction = isset($_POST['flexipay_payment_instruction']) ? wp_kses_post(wp_unslash($_POST['flexipay_payment_instruction'])) : '';
        update_option('flexipay_payment_instruction', $instruction);

        set_transient('flexipay_msg_' . get_current_user_id(), 'saved', 30);
        wp_safe_redirect(menu_page_url('flexipay-gateways', false));
        exit;
    }

    // Handle messages
    $msg = get_transient('flexipay_msg_' . get_current_user_id());
    if ($msg) {
        delete_transient('flexipay_msg_' . get_current_user_id());
        if ($msg === 'added') {
            add_settings_error('flexipay_messages', 'flexipay_added', __('Gateway added successfully!', 'flexipay-gateway'), 'updated');
        } elseif ($msg === 'deleted') {
            add_settings_error('flexipay_messages', 'flexipay_deleted', __('Gateway removed successfully!', 'flexipay-gateway'), 'updated');
        } elseif ($msg === 'saved') {
            add_settings_error('flexipay_messages', 'flexipay_saved', __('Settings saved successfully!', 'flexipay-gateway'), 'updated');
        }
    }

    settings_errors('flexipay_messages');

    $gateways = get_option('flexipay_gateways_list', array());
    $max_slots = get_option('flexipay_gateway_slots', 5);
    $email_opt = get_option('flexipay_email_notification_option', 'none');
    $instruction = get_option('flexipay_payment_instruction', '');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('FlexiPay Gateway for WooCommerce Settings', 'flexipay-gateway'); ?></h1>
        <p><?php esc_html_e('Add the names of the payment gateways you want to register. You can configure their details in', 'flexipay-gateway'); ?>
            <strong><?php esc_html_e('WooCommerce > Settings > Payments', 'flexipay-gateway'); ?></strong>
            <?php esc_html_e('after adding them here.', 'flexipay-gateway'); ?>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=flexipay-gateways')); ?>"
            style="margin-bottom: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
            <?php wp_nonce_field('flexipay_add_gateway'); ?>
            <input type="hidden" name="flexipay_action" value="add">
            <table class="form-table">
                <tr>
                    <th scope="row"><label
                            for="gateway_name"><?php esc_html_e('Payment Gateway Name', 'flexipay-gateway'); ?></label></th>
                    <td>
                        <input name="gateway_name" type="text" id="gateway_name" value="" class="regular-text"
                            placeholder="e.g. Zelle Pay" required <?php echo count($gateways) >= $max_slots ? 'disabled' : ''; ?>>
                        <?php if (count($gateways) >= $max_slots): ?>
                            <p class="description" style="color: #dc3232;">
                                <?php
                                /* translators: %d: Maximum number of gateways allowed. */
                                printf(esc_html__('Maximum limit of %d gateways reached. Increase the limit below if needed.', 'flexipay-gateway'), absint($max_slots));
                                ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="add_flexipay_gateway" id="submit" class="button button-primary"
                    value="Add Gateway" <?php echo count($gateways) >= $max_slots ? 'disabled' : ''; ?>>
            </p>
        </form>

        <hr>

        <h2><?php esc_html_e('General Settings', 'flexipay-gateway'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=flexipay-gateways')); ?>"
            style="margin-bottom: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
            <?php wp_nonce_field('flexipay_save_settings'); ?>
            <input type="hidden" name="flexipay_action" value="save_general">
            <table class="form-table">
                <tr>
                    <th scope="row"><label
                            for="flexipay_gateway_slots"><?php esc_html_e('Max Gateway Slots', 'flexipay-gateway'); ?></label>
                    </th>
                    <td>
                        <input name="flexipay_gateway_slots" type="number" id="flexipay_gateway_slots"
                            value="<?php echo esc_attr($max_slots); ?>" class="small-text" min="1" max="10">
                        <p class="description">
                            <?php esc_html_e('Maximum number of unique payment gateways you can create (Max 10).', 'flexipay-gateway'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Email Notifications', 'flexipay-gateway'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input name="flexipay_email_notification_option" type="radio" value="none" <?php checked($email_opt, 'none'); ?>>
                                <?php esc_html_e('None (Disable)', 'flexipay-gateway'); ?>
                            </label>
                            <br>
                            <label>
                                <input name="flexipay_email_notification_option" type="radio" value="selected" <?php checked($email_opt, 'selected'); ?>>
                                <?php esc_html_e('Send selected payment gateway details in On-Hold email.', 'flexipay-gateway'); ?>
                            </label>
                            <br>
                            <label>
                                <input name="flexipay_email_notification_option" type="radio" value="all" <?php checked($email_opt, 'all'); ?>>
                                <?php esc_html_e('Send ALL custom payment gateway details in On-Hold email.', 'flexipay-gateway'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                            for="flexipay_payment_instruction"><?php esc_html_e('Payment Instruction', 'flexipay-gateway'); ?></label>
                    </th>
                    <td>
                        <textarea name="flexipay_payment_instruction" id="flexipay_payment_instruction" rows="5" cols="50"
                            class="large-text"><?php echo esc_textarea($instruction); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('General instructions that will be added to the bottom of the payment details table in the On-Hold email.', 'flexipay-gateway'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="save_flexipay_settings" class="button button-secondary" value="Save">
            </p>
        </form>

        <hr>

        <h2><?php esc_html_e('Registered Payment Gateways', 'flexipay-gateway'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'flexipay-gateway'); ?></th>
                    <th><?php esc_html_e('ID / Key', 'flexipay-gateway'); ?></th>
                    <th><?php esc_html_e('Actions', 'flexipay-gateway'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gateways)): ?>
                    <tr>
                        <td colspan="3"><?php esc_html_e('No gateways registered yet.', 'flexipay-gateway'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gateways as $key => $name): ?>
                        <tr>
                            <td><strong><?php echo esc_html($name); ?></strong></td>
                            <td><code><?php esc_html_e('flexipay_gateway_', 'flexipay-gateway'); ?><?php echo esc_html($key); ?></code>
                            </td>
                            <td>
                                <a
                                    href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=flexipay_gateway_' . $key)); ?>"><?php esc_html_e('Configure in WooCommerce', 'flexipay-gateway'); ?></a>
                                |
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=flexipay-gateways&delete_gateway=' . $key), 'flexipay_delete_gateway_' . $key)); ?>"
                                    style="color: #dc3232;"
                                    onclick="return confirm('Are you sure you want to remove this gateway? Settings will be lost.');"><?php esc_html_e('Remove', 'flexipay-gateway'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
