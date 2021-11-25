<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Delivery, Returns and Refunds Page
 *
 * Renders the Delivery, Returns and Refunds page contents.
 *
 * @since 1.0
 * @return void
 */

function pl8app_delivery_refund_page(){

    $options = get_option('pl8app_settings');
    $delivery = !empty($options['delivery_refund']['delivery'])? $options['delivery_refund']['delivery'] : '';
    $refund = !empty($options['delivery_refund']['refund']) ? $options['delivery_refund']['refund'] : '';
    ob_start();
    ?>
    <div class="wrap wrap-st-location">
    <h2><?php _e('Delivery, Returns and Refunds', 'pl8app'); ?></h2>
        <form method="post" action="options.php">
            <table class="form-table pl8app-settings">
                <?PHP settings_fields( 'pl8app_settings' );?>
            </table>
            <div class="pl8app-wrapper">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php echo __('Delivery','pl8app') ?></th>
                    <td>
                        <?PHP
                        if (!empty($delivery)) {
                            $value = $delivery;
                        } else {
                            $value = '';
                        }
                        ob_start();
                        wp_editor(stripslashes($value), 'pl8app_settings_' . esc_attr('delivery_refund_delivery'), array('textarea_name' => 'pl8app_settings[' . esc_attr('delivery_refund') . ']['. esc_attr('delivery') .']', 'textarea_rows' => absint(20)));
                        $html = ob_get_clean();

                        echo $html;
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo __('Returns and Refunds','pl8app') ?></th>
                    <td>
                        <?PHP
                        if (!empty($refund)) {
                            $value = $refund;
                        } else {
                            $value = '';
                        }
                        ob_start();
                        wp_editor(stripslashes($value), 'pl8app_settings_' . esc_attr('delivery_refund_refund'), array('textarea_name' => 'pl8app_settings[' . esc_attr('delivery_refund') . ']['. esc_attr('refund') .']', 'textarea_rows' => absint(20)));
                        $html = ob_get_clean();

                        echo $html;
                        ?>
                    </td>
                </tr>
            </table>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    $delivery_refund_display = ob_get_clean();
    echo $delivery_refund_display;
}

/**
 * Privacy and Policy content Edit page
 */


function pl8app_privacy_page()
{
    $options = get_option('pl8app_settings');
    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Privacy Policy', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php _e('Terms of Agreement', 'pl8app'); ?></h3></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Agree to Terms', 'pl8app'); ?></th>
                            <td>
                                <input type="hidden" name="pl8app_settings[show_agree_to_terms]" value="-1">
                                <input type="checkbox" id="pl8app_settings[show_agree_to_terms]"
                                       name="pl8app_settings[show_agree_to_terms]" value="1"
                                       class="" <?PHP echo !empty($options['show_agree_to_terms']) ? checked(1, $options['show_agree_to_terms'], false) : ''; ?>>
                                <label for="pl8app_settings[show_agree_to_terms]"> <?php _e('Check this to show an <b><i>Agree to
                                            Terms</i></b> on checkout that users must check before creating
                                    orders.', 'pl8app'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Agree to Terms Label', 'pl8app'); ?></th>
                            <td><input type="text" class=" regular-text" id="pl8app_settings[agree_label]"
                                       name="pl8app_settings[agree_label]"
                                       value="<?PHP echo !empty($options['agree_label']) ? $options['agree_label'] : ''; ?>"
                                       placeholder="">
                                <label
                                        for="pl8app_settings[agree_label]"> <?php _e('Label shown next to <b><i>Agree to Terms</i></b>
                                    checkbox.', 'pl8app'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Agreement Text', 'pl8app'); ?></th>
                            <td><?PHP
                                if (!empty($options['agree_text'])) {
                                    $value = $options['agree_text'];
                                } else {
                                    $value = '';
                                }
                                ob_start();
                                wp_editor(stripslashes($value), 'pl8app_settings_' . esc_attr('agree_text'), array('textarea_name' => 'pl8app_settings[' . esc_attr('agree_text') . ']', 'textarea_rows' => absint(20)));
                                $html = ob_get_clean();
                                $html .= '<br/><label for="pl8app_settings[' . pl8app_sanitize_key('agree_text') . ']"> ' . wp_kses_post('If <b><i>Agree to Terms</i></b> is checked, enter the agreement terms here. <br> Use alliases <b><i>{%pl8app_store_contact_information%}</i></b> to show store information.') . '</label>';

                                echo $html;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Privacy Policy', 'pl8app')?></th>
                            <td><?PHP
                                if (!empty($options['privacy_policy_content'])) {
                                    $value = $options['privacy_policy_content'];
                                } else {
                                    $value = '';
                                }
                                ob_start();
                                wp_editor(stripslashes($value), 'pl8app_settings_' . esc_attr('privacy_policy_content'), array('textarea_name' => 'pl8app_settings[' . esc_attr('privacy_policy_content') . ']', 'textarea_rows' => absint(20)));
                                $html = ob_get_clean();
                                $html .= '<br/><label for="pl8app_settings[' . pl8app_sanitize_key('privacy_policy_content') . ']"> ' . wp_kses_post('Use alliases <b><i>{%pl8app_store_contact_information%}</i></b> to show store information.') . '</label>';

                                echo $html;
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
}