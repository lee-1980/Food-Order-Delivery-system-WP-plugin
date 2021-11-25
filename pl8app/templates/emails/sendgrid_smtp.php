<?php

$options = get_option('pl8app_settings');
$smtp_option = isset($options['smtp_config'])?$options['smtp_config']: array();
pl8app_send_test_email_to_smtp($smtp_option);
ob_start();
?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Store Email SMTP Server Configuration', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php _e('SendGrid SMTP Server Configuration', 'pl8app'); ?></h3></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th>
                                <label for="enable_disable"><?php echo __('SendGrid SMTP Server Enable/Disable', 'pl8app'); ?></label>
                            </th>
                            <td>
                                <input name="pl8app_settings[smtp_config][enable_disable]" value="0" type="hidden">
                                <input id="pl8app_settings[smtp_config][enable_disable]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[smtp_config][enable_disable]"
                                    <?PHP echo !empty($options['smtp_config']['enable_disable']) ? checked(1, $options['smtp_config']['enable_disable'], false) : ''; ?>>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php echo __('API key', 'pl8app');?></th>
                            <td>
                                <input type ="text" class = "large-text" name = "pl8app_settings[smtp_config][sendgrid_api_key]" id = "pl8app_settings[smtp_config][sendgrid_api_key]"
                                       placeholder = "API key"  value = "<?PHP echo !empty($smtp_option['sendgrid_api_key']) ? $smtp_option['sendgrid_api_key'] : ''; ?>" >
                                <i class = 'sendgrid-apikey-generation-link'> <a target = '_blank' href = 'https://app.sendgrid.com/settings/api_keys'> Click here to get API key </a> </i>
                            </td>
                        </tr>

                    </table>
                </div>
                <?php submit_button(); ?>
            </form>

            <form method="post" action="<?php echo admin_url('admin.php?page=pl8app-store-email-smtp'); ?>">
                <?php wp_nonce_field('pl8app-mail-send-test'); ?>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php _e('Test sending email', 'pl8app'); ?></h3></th>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('To Email', 'pl8app');?></th>
                            <td>
                                <input type="email" class="regular-text" name="to_email" placeholder="bill999@gmail.com" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php echo __('Body', 'pl8app');?></th>
                            <td>
                                <textarea rows="6" class="regular-text" name="email_body" placeholder='Test Mail content here'></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <p class="submit">
                    <input  class='button button-primary' type="submit" value="Send test mail">
                </p>
            </form>
        </div>
    </div>
<?php
echo ob_get_clean();