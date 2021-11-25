<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */

function pl8app_store_emails_page()
{
    $options = get_option('pl8app_settings');
    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Store Emails', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">

                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php _e('Admin Email Notification', 'pl8app'); ?></h3></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>
                                <label for="enable_disable"><?php _e('Disable/Enable', 'pl8app'); ?> </label>
                            </th>
                            <td>
                                <label for="enable_disable">
                                    <input id="pl8app_settings[admin_notification][enable_disable]" value="yes"
                                           type="checkbox"
                                           name="pl8app_settings[admin_notification][enable_notification]" <?PHP echo !empty($options['admin_notification']['enable_notification']) ? checked("yes", $options['admin_notification']['enable_notification'], false) : ''; ?>>
                                    <?php _e('Enable this email notification', 'pl8app'); ?> </label>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="admin_recipients"><?php _e('Recipient(s)', 'pl8app'); ?> </label>

                            </th>
                            <td>
                                <textarea class="large-text" rows="5" cols="50" id="admin_recipients"
                                          name="pl8app_settings[admin_notification][admin_recipients]"><?php echo !empty($options['admin_notification']['admin_recipients']) ? $options['admin_notification']['admin_recipients'] : ''; ?></textarea>
                                <span class="help-text"><?php _e('Enter the email address(es) that should receive a notification anytime a order is placed, one per line.', 'pl8app'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php _e('Customer Email Notification', 'pl8app'); ?></h3></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Statues', 'pl8app');?></th>
                            <td>
                                <input type="hidden" name="pl8app_settings[pending][enable_notification]" value="-1">
                                <input name="pl8app_settings[pending][enable_notification]"
                                       id="pl8app_settings[pending][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['pending']['enable_notification']) ? checked("yes", $options['pending']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[pending][enable_notification]"><?php _e('Pending Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[accepted][enable_notification]" value="-1">
                                <input name="pl8app_settings[accepted][enable_notification]"
                                       id="pl8app_settings[accepted][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['accepted']['enable_notification']) ? checked("yes", $options['accepted']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[accepted][enable_notification]"><?php _e('Accepted Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[processing][enable_notification]" value="-1">
                                <input name="pl8app_settings[processing][enable_notification]"
                                       id="pl8app_settings[processing][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['processing']['enable_notification']) ? checked("yes", $options['processing']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[processing][enable_notification]"><?php _e('Processing Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[ready][enable_notification]" value="-1">
                                <input name="pl8app_settings[ready][enable_notification]"
                                       id="pl8app_settings[ready][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['ready']['enable_notification']) ? checked("yes", $options['ready']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[ready][enable_notification]"><?php _e('Ready Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[transit][enable_notification]" value="-1">
                                <input name="pl8app_settings[transit][enable_notification]"
                                       id="pl8app_settings[transit][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['transit']['enable_notification']) ? checked("yes", $options['transit']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[transit][enable_notification]"><?php _e('In Transit Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[cancelled][enable_notification]" value="-1">
                                <input name="pl8app_settings[cancelled][enable_notification]"
                                       id="pl8app_settings[cancelled][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['cancelled']['enable_notification']) ? checked("yes", $options['cancelled']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[cancelled][enable_notification]"><?php _e('Cancelled Order', 'pl8app'); ?></label>
                                <br>
                                <input type="hidden" name="pl8app_settings[completed][enable_notification]" value="-1">
                                <input name="pl8app_settings[completed][enable_notification]"
                                       id="pl8app_settings[completed][enable_notification]" class=""
                                       type="checkbox"
                                       value="yes" <?PHP echo !empty($options['completed']['enable_notification']) ? checked("yes", $options['completed']['enable_notification'], false) : ''; ?>>&nbsp;
                                <label for="pl8app_settings[completed][enable_notification]"><?php _e('Completed Order', 'pl8app'); ?></label>
                                <br>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


function pl8app_settings_sanitize_store_emails($input)
{

    if (!current_user_can('manage_shop_settings')) {
        return $input;
    }

    if (!isset($_POST['pl8app_settings'])) {
        return $input;
    }

    $store_email = isset($_POST['pl8app_settings']['pl8app_st_email'])?$_POST['pl8app_settings']['pl8app_st_email']: pl8app_get_option('pl8app_st_email');
    $order_statuses = pl8app_get_order_statuses();

    if (is_array($order_statuses) && !empty($order_statuses)) {
        foreach ($order_statuses as $key => $status) {
            $input[$key]['heading'] = $store_email;
            $input[$key]['subject'] = $key . ' {order_id}';
            $input[$key]['content'] = '{order_id}<br>{date}<br>{fullname}<br>Billing Address : {billing_address}
                                      <br>Delivery Address : {delivery_address}<br>{phone}<br>{service_type}
                                      <br>{service_time}
                                      <br>{menuitem_list}
                                      <br>{order_note}
                                      <br>{price}
                                      <br>{payment_method}';
        }
        $input['admin_notification']['heading'] = $store_email;
        $input['admin_notification']['subject'] = 'Admin notification {order_id}';
        $input['admin_notification']['content'] = '
                                      {order_id}<br>{date}<br>{fullname}<br>Billing Address : {billing_address}
                                      <br>Delivery Address : {delivery_address}<br>{phone}<br>{service_type}
                                      <br>{service_time}
                                      <br>{menuitem_list}
                                      <br>{order_note}
                                      <br>{price}
                                      <br>{payment_method}';
    }

    return $input;
}


add_filter('pl8app_settings_sanitize_custom_addition', 'pl8app_settings_sanitize_store_emails', 10, 1);


/**
 * Google reCaptcha
 */
function pl8app_tools_form_recaptcha() {

    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return;
    }
    ?>
    <div class="card active" id="recaptcha">
    <h2 class="title"><?php echo __('Google reCAPTCHA', 'pl8app');?></h2>

    <br class="clear">

    <div class="inside">
        <form method="post" action="<?php echo admin_url('admin.php?page=pl8app-form-recaptcha'); ?>">
            <p><a href="https://www.google.com/recaptcha/intro/index.html"><?php _e( 'Google reCAPTCHA', 'pl8app' ); ?></a><?php _e( ' protects you against spam and other types of automated abuse.', 'pl8app' ); ?></p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="sitekey"><?php _e( 'Enable reCaptcha', 'pl8app' ); ?></label></th>
                    <td><input type="checkbox" aria-required="true" value="enable" id="recap_enable" name="recap_enable"
                               class="regular-text code" <?PHP echo checked(pl8app_get_recaptcha_enable(), 'enable');?> ></td>
                </tr>
                <tr>
                    <th scope="row"><label for="sitekey"><?php _e( 'Site Key', 'pl8app' ); ?></label></th>
                    <td><input type="text" aria-required="true" value="<?PHP echo pl8app_get_recaptcha_site_key();?>" id="sitekey" name="sitekey"
                               class="regular-text code"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="secret"><?php _e( 'Secret Key', 'pl8app' ); ?></label></th>
                    <td><input type="text" aria-required="true" value="<?PHP echo pl8app_get_recaptcha_secret_key();?>" id="secret" name="secret"
                               class="regular-text code"></td>
                </tr>
                </tbody>
            </table>
            <p>
                <input type="hidden" name="pl8app_action" value="save_recaptcha_key" />
                <?php wp_nonce_field( 'pl8app_recaptcha_nonce', 'pl8app_recaptcha_nonce' ); ?>
                <?php submit_button( __( 'Save', 'pl8app' ), 'secondary', 'submit', false ); ?>
            </p>
        </form>
    </div>
    <?PHP
}





