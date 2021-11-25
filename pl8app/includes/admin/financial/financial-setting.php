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

function pl8app_financial_settings_page(){

    $currencies = pl8app_get_currencies();
    $options = get_option('pl8app_settings');
    ob_start();
    ?>

    <div class="wrap wrap-st-location">
        <h2><?php _e('Financial Setting', 'pl8app'); ?></h2>

        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">

                    <?PHP settings_fields( 'pl8app_settings' );?>
                </table>
                <div class="pl8app-wrapper">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?PHP echo __('Currency','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[currency]"
                                    name="pl8app_settings[currency]" data-placeholder="Select a country"
                                    readonly="readonly" value="<?PHP echo !empty($options['currency'])?$options['currency']:'';?>">
                                <?php
                                foreach ($currencies as $key => $currency){
                                    echo '<option value="'. $key.'" '. (isset($options['currency'])&& $options['currency'] == $key? "selected": "").'>'. $currency .'</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[currency]"><?php echo __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'pl8app' ); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Currency Position','pl8app');?></th>
                        <td>
                            <select id="pl8app_settings[currency_position]" name="pl8app_settings[currency_position]"
                                    class="" data-placeholder="">
                                <?php
                                if(!empty($options['currency'])&&$options['currency_position'] === 'before') {
                                    ?>
                                    <option value="before" <?php echo 'selected'; ?>><?PHP echo __('Before - $10','pl8app');?></option>
                                    <option value="after"><?PHP echo __('After - 10$','pl8app');?></option>
                                    <?php
                                }
                                else {
                                        ?>
                                    <option value="before">Before - $10</option>
                                    <option value="after" <?Php echo 'selected'; ?>>After - 10$</option>
                                    <?php
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[currency_position]"><?PHP echo __(' Choose the location of the currency sign.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Thousands Separator','pl8app');?></th>
                        <td>
                            <input type="text" class=" small-text" id="pl8app_settings[thousands_separator]" name="pl8app_settings[thousands_separator]" value="<?PHP echo isset($options['thousands_separator'])?$options['thousands_separator']:'';?>" placeholder="">
                            <label for="pl8app_settings[thousands_separator]"><?PHP echo __(' The symbol (usually , or .) to separate
                                thousands.','pl8app');?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?PHP echo __('Decimal Separator','pl8app');?></th>
                        <td>
                            <input type="text" class=" small-text" id="pl8app_settings[decimal_separator]"
                                   name="pl8app_settings[decimal_separator]" value="<?PHP echo isset($options['decimal_separator'])?$options['decimal_separator']:'';?>" placeholder="">
                            <label for="pl8app_settings[decimal_separator]"><?PHP echo __(' The symbol (usually , or .) to separate
                                decimal points.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Enable Taxes','pl8app');?></th>
                        <td>
                            <input type="hidden" name="pl8app_settings[enable_taxes]" value="-1">
                            <input type="checkbox" id="pl8app_settings[enable_taxes]" name="pl8app_settings[enable_taxes]" value="1" class="" <?PHP echo ! empty( $options['enable_taxes'] ) ? checked( 1, $options['enable_taxes'], false ) : '' ;?>>
                            <label for="pl8app_settings[enable_taxes]"><?PHP echo __(' Check this to enable taxes on purchases.','pl8app');?></label>
                            <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Enabling Taxes</strong><br />With taxes enabled, pl8app will use the rules below to charge tax to customers. With taxes enabled, customers are required to input their address on checkout so that taxes can be properly calculated."></span>
                        </td>
                    </tr>
                    <tr class="tax_row <?PHP echo !empty( $options['enable_taxes'] ) && $options['enable_taxes'] == 1?"":"hidden"; ?>">
                        <th scope="row"><?PHP echo __('VAT Registration  Number','pl8app');?></th>
                        <td>
                            <input type="text" value="<?PHP echo !empty($options['pl8app_tax_vat_number'])? $options['pl8app_tax_vat_number']: ''; ?>" placeholder="0" name="pl8app_settings[pl8app_tax_vat_number]" data-attribute="tax_rate">
                            <p class="description"><?PHP echo __('To write VAT Registration  Number on Receipt, please confirm Enable Taxes above first.','pl8app');?></p>
                        </td>
                    </tr>
                    <tr class="tax_row <?PHP echo !empty( $options['enable_taxes'] ) && $options['enable_taxes'] == 1?"":"hidden"; ?>">
                        <th scope="row"><?PHP echo __('Taxes','pl8app');?></th>
                        <td>
                            <table id="pl8app_tax_rates">
                                <thead>
                                <tr>
                                    <th>Rate %
                                        <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Tax rate</strong><br />Enter a tax rate(percentage) to 4 decimal places."></span>
                                    </th>

                                    <th>Tax name
                                        <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Tax rate</strong><br />Enter a tax name for this tax rate. Tax name should be single alphabetical letter. i.e: A, B, C"></span>
                                    </th>
                                    <th>Tax Description
                                        <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Tax rate</strong><br />Enter a tax short description for this tax rate."></span>
                                    </th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th colspan="2">
                                        <a href="#" class="button" id="pl8app_add_tax_rate">Insert row</a>
                                        <a href="#" class="button pl8app_remove_tax_rate disabled">Remove selected row(s)</a>
                                    </th>
                                </tr>
                                </tfoot>
                                <tbody id="rates">
                                <input type="hidden" value="-1" name="pl8app_settings[tax]">
                                <?PHP if(isset($options['tax']) && is_array($options['tax'])) {
                                    foreach($options['tax'] as $key => $tax) {?>
                                <tr class="rate">
                                    <td class="rate">
                                        <input type="text" value="<?PHP echo !empty($tax['rate'])? $tax['rate']: ''; ?>" placeholder="0" name="pl8app_settings[tax][<?PHP echo $key; ?>][rate]" data-attribute="tax_rate" required>
                                    </td>
                                    <td class="name">
                                        <input type="text" value="<?PHP echo !empty($tax['name'])? $tax['name']: ''; ?>" name="pl8app_settings[tax][<?PHP echo $key; ?>][name]" data-attribute="tax_rate_name" pattern="[A-Z]{1,1}" required>
                                    </td>
                                    <td class="desc">
                                        <input type="text" value="<?PHP echo !empty($tax['desc'])? $tax['desc']: ''; ?>" name="pl8app_settings[tax][<?PHP echo $key; ?>][desc]" data-attribute="tax_rate_desc" required>
                                    </td>
                                </tr>
                                <?PHP } } ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="tax_row <?PHP echo !empty( $options['enable_taxes'] ) && $options['enable_taxes'] == 1?"":"hidden"; ?>">
                        <th scope="row">Prices entered with tax</th>
                        <td>
                            <?PHP if(isset($options['prices_include_tax']) && $options['prices_include_tax'] === 'yes'){ ;?>
                                <input name="pl8app_settings[prices_include_tax]"
                                       id="pl8app_settings[prices_include_tax][yes]" class="" type="radio"
                                       value="yes" checked="checked">&nbsp;
                                <label for="pl8app_settings[prices_include_tax][yes]">Yes, I will enter prices inclusive
                                    of tax</label><br>
                                <input name="pl8app_settings[prices_include_tax]"
                                       id="pl8app_settings[prices_include_tax][no]" class="" type="radio" value="no">&nbsp;
                                <label for="pl8app_settings[prices_include_tax][no]">No, I will enter prices exclusive
                                    of tax</label><br>
                            <?php } else {?>
                                <input name="pl8app_settings[prices_include_tax]"
                                       id="pl8app_settings[prices_include_tax][yes]" class="" type="radio"
                                       value="yes">&nbsp;
                                <label for="pl8app_settings[prices_include_tax][yes]">Yes, I will enter prices inclusive
                                    of tax</label><br>
                                <input name="pl8app_settings[prices_include_tax]"
                                       id="pl8app_settings[prices_include_tax][no]" class="" type="radio" value="no" checked="checked"
                                       >&nbsp;
                                <label for="pl8app_settings[prices_include_tax][no]">No, I will enter prices exclusive
                                    of tax</label><br>
                            <?php } ?>
                            <p class="description">This option affects how you enter prices.
                                <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Prices Inclusive of Tax</strong><br />When using prices inclusive of tax, you will be entering your prices as the total amount you want a customer to pay for the menuitem, including tax. pl8app will calculate the proper amount to tax the customer for the defined total price.">
                                </span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Enable Billing Fields','pl8app'); ?></th>
                        <td>
                            <input type="hidden" name="pl8app_settings[enable_billing_fields]" value="-1">
                            <input type="checkbox" id="pl8app_settings[enable_billing_fields]" name="pl8app_settings[enable_billing_fields]" value="1" <?PHP echo ! empty( $options['enable_billing_fields'] ) ? checked( 1, $options['enable_billing_fields'], false ) : '' ;?> class="">
                            <label for="pl8app_settings[enable_billing_fields]"> <?php echo __('Check this to enable billing fields in the checkout page.','pl8app'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Enable Tips on Checkout','pl8app'); ?></th>
                        <td><input type="hidden" name="pl8app_settings[enable_tips_on_checkout]" value="-1">
                            <input type="checkbox" id="pl8app_settings[enable_tips_on_checkout]"
                                    name="pl8app_settings[enable_tips_on_checkout]" value="1"
                                <?PHP echo ! empty( $options['enable_tips_on_checkout'] ) ? checked( 1, $options['enable_tips_on_checkout'], false ) : '' ;?>>
                            <label for="pl8app_settings[enable_tips_on_checkout]">
                                <?php echo __('Check this option to enable Tips on checkout page.','pl8app'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Include tax on Tips','pl8app'); ?></th>
                        <td><input type="hidden" name="pl8app_settings[inculde_tax_on_tips]" value="-1">
                            <input type="checkbox" id="pl8app_settings[inculde_tax_on_tips]"
                                   name="pl8app_settings[inculde_tax_on_tips]" value="1"
                                <?PHP echo ! empty( $options['inculde_tax_on_tips'] ) ? checked( 1, $options['inculde_tax_on_tips'], false ) : '' ;?>>
                            <label for="pl8app_settings[inculde_tax_on_tips]">
                                <?php echo __('Check this option to apply tax on Tips.','pl8app'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Tips label on cart','pl8app');?></th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[tips_cart_label]"
                                   name="pl8app_settings[tips_cart_label]" value="<?PHP echo !empty($options['tips_cart_label'])? $options['tips_cart_label']: ''; ?>" placeholder="">
                            <label for="pl8app_settings[tips_cart_label]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Text for Tips option','pl8app');?></th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[tips_text]"
                                   name="pl8app_settings[tips_text]" value="<?PHP echo !empty($options['tips_text'])? $options['tips_text']: ''; ?>" placeholder="">
                            <label for="pl8app_settings[tips_text]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?PHP echo __('Description for Tips','pl8app');?></th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[tips_subtext]"
                                   name="pl8app_settings[tips_subtext]" value="<?PHP echo !empty($options['tips_subtext'])? $options['tips_subtext']: ''; ?>" placeholder="">
                            <label for="pl8app_settings[tips_subtext]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Type', 'pl8app'); ?></th>
                        <td>

                            <select name="pl8app_settings[tips_type]" id="pl8app_settings[tips_type]"
                                    class="">
                                <?PHP
                                $tips_type = isset($options['tips_type'])?$options['tips_type']:'';
                                switch ($tips_type) {
                                    case "fixed_values" :
                                        ?>
                                        <option value="fixed_values" selected><?php echo __('Flat Amount', 'pl8app'); ?></option>
                                        <option value="percentage_value"><?php echo __('Cart Percentage', 'pl8app'); ?></option>
                                        <?PHP break;
                                    case "percentage_value" :
                                        ?>
                                        <option value="fixed_values"><?php echo __('Flat Amount', 'pl8app'); ?></option>
                                        <option value="percentage_value" selected><?php echo __('Cart Percentage', 'pl8app'); ?></option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="fixed_values"><?php echo __('Flat Amount', 'pl8app'); ?></option>
                                        <option value="percentage_value"><?php echo __('Cart Percentage', 'pl8app'); ?></option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[tips_type]"> <?php echo __(' What type of Tips option you want to present to customers?', 'pl8app'); ?></label></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo __('Tips Options', 'pl8app'); ?></th>
                        <td>

                            <select name="pl8app_settings[tips_type_display]" id="pl8app_settings[tips_type_display]"
                                    class="">
                                <?PHP
                                $tips_type_display = isset($options['tips_type_display'])?$options['tips_type_display']:'';
                                switch ($tips_type_display) {
                                    case "both" :
                                        ?>
                                        <option value="both" selected><?php echo __('Both', 'pl8app'); ?></option>
                                        <option value="manual_tips"><?php echo __('Custom Tips Entry', 'pl8app'); ?></option>
                                        <option value="tips_options"><?php echo __('Predefined Tips Values', 'pl8app'); ?></option>
                                        <?PHP break;
                                    case "manual_tips" :
                                        ?>
                                        <option value="both"><?php echo __('Both', 'pl8app'); ?></option>
                                        <option value="manual_tips" selected><?php echo __('Custom Tips Entry', 'pl8app'); ?></option>
                                        <option value="tips_options"><?php echo __('Predefined Tips Values', 'pl8app'); ?></option>
                                        <?PHP break;
                                    case "tips_options" :
                                        ?>
                                        <option value="both"><?php echo __('Both', 'pl8app'); ?></option>
                                        <option value="manual_tips"><?php echo __('Custom Tips Entry', 'pl8app'); ?></option>
                                        <option value="tips_options" selected><?php echo __('Predefined Tips Values', 'pl8app'); ?></option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="both"><?php echo __('Both', 'pl8app'); ?></option>
                                        <option value="tips_options"><?php echo __('Predefined Tips Values', 'pl8app'); ?></option>
                                        <option value="manual_tips"><?php echo __('Custom Tips Entry', 'pl8app'); ?></option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[tips_type_display]"> <?php echo __('Select how you would like your customers to enter tips option.', 'pl8app'); ?></label></td>
                    </tr>

                    <tr>
                        <th scope="row"><?PHP echo __('Predefined values','pl8app');?></th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[tips_values]"
                                   name="pl8app_settings[tips_values]" value="<?PHP echo !empty($options['tips_values'])? $options['tips_values']: ''; ?>" placeholder="">
                            <label for="pl8app_settings[tips_values]"><?php echo __(' Enter the predefined Tips options you want to show on checkout.
                            <br>Enter values separated by comma(,). eg. (10,15,20)','pl8app'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?PHP echo __('Remove Tips Label','pl8app');?></th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[remove_tips_label]"
                                   name="pl8app_settings[remove_tips_label]" value="<?PHP echo !empty($options['remove_tips_label'])? $options['remove_tips_label']: ''; ?>" placeholder="">
                            <label for="pl8app_settings[remove_tips_label]"><?php echo __(' Text for remove tips button label.','pl8app'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo __('Admin Tips Column','pl8app'); ?></th>
                        <td><input type="hidden" name="pl8app_settings[show_tips_column]" value="-1">
                            <input type="checkbox" id="pl8app_settings[show_tips_column]"
                                   name="pl8app_settings[show_tips_column]" value="1"
                                <?PHP echo ! empty( $options['show_tips_column'] ) ? checked( 1, $options['show_tips_column'], false ) : '' ;?>>
                            <label for="pl8app_settings[show_tips_column]">
                                <?php echo __(' Enable to show Tips column in the order history.','pl8app'); ?></label></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo __('Test Mode','pl8app'); ?></th>
                        <td>
                            <input type="hidden" name="pl8app_settings[test_mode]" value="-1">
                            <input type="checkbox" id="pl8app_settings[test_mode]" name="pl8app_settings[test_mode]" value="1" <?PHP echo ! empty( $options['test_mode'] ) ? checked( 1, $options['test_mode'], false ) : '' ;?> class="">
                            <label
                                for="pl8app_settings[test_mode]"> <?php echo __('While in test mode no live transactions are processed.
                                To fully use test mode, you must have a sandbox (test) account for the payment gateway
                                you are testing.','pl8app'); ?></label></td>
                    </tr>

                    <tr>
                        <th scope="row">Payment Gateways</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[gateways]" value="-1">
                            <input name="pl8app_settings[gateways][paypal]" id="pl8app_settings[gateways][paypal]" class=""
                                type="checkbox" value="1" <?PHP echo ! empty( $options['gateways']['paypal'] ) ? checked( 1, $options['gateways']['paypal'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways][paypal]">PayPal Standard</label>
                            <br>
                            <input name="pl8app_settings[gateways][manual]" id="pl8app_settings[gateways][manual]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways']['manual'] ) ? checked( 1, $options['gateways']['manual'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways][manual]">Test Payment</label>
                            <br>
                            <input name="pl8app_settings[gateways][cash_on_delivery]" id="pl8app_settings[gateways][cash_on_delivery]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways']['cash_on_delivery'] ) ? checked( 1, $options['gateways']['cash_on_delivery'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways][cash_on_delivery]">Pay by cash</label>
                            <br>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Disable Payment In Delivery</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[gateways_disable_del]" value="-1">
                            <input name="pl8app_settings[gateways_disable_del][paypal]" id="pl8app_settings[gateways_disable_del][paypal]" class=""
                                   type="checkbox" value="1" <?PHP echo ! empty( $options['gateways_disable_del']['paypal'] ) ? checked( 1, $options['gateways_disable_del']['paypal'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_del][paypal]">PayPal Standard</label>
                            <br>
                            <input name="pl8app_settings[gateways_disable_del][manual]" id="pl8app_settings[gateways_disable_del][manual]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways_disable_del']['manual'] ) ? checked( 1, $options['gateways_disable_del']['manual'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_del][manual]">Test Payment</label>
                            <br>
                            <input name="pl8app_settings[gateways_disable_del][cash_on_delivery]" id="pl8app_settings[gateways_disable_del][cash_on_delivery]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways_disable_del']['cash_on_delivery'] ) ? checked( 1, $options['gateways_disable_del']['cash_on_delivery'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_del][cash_on_delivery]">Pay by cash</label>
                            <br>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Disable Payment In PickUp</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[gateways_disable_pic]" value="-1">
                            <input name="pl8app_settings[gateways_disable_pic][paypal]" id="pl8app_settings[gateways_disable_pic][paypal]" class=""
                                   type="checkbox" value="1" <?PHP echo ! empty( $options['gateways_disable_pic']['paypal'] ) ? checked( 1, $options['gateways_disable_pic']['paypal'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_pic][paypal]">PayPal Standard</label>
                            <br>
                            <input name="pl8app_settings[gateways_disable_pic][manual]" id="pl8app_settings[gateways_disable_pic][manual]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways_disable_pic']['manual'] ) ? checked( 1, $options['gateways_disable_pic']['manual'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_pic][manual]">Test Payment</label>
                            <br>
                            <input name="pl8app_settings[gateways_disable_pic][cash_on_delivery]" id="pl8app_settings[gateways_disable_pic][cash_on_delivery]" class="" type="checkbox" value="1"
                                <?PHP echo ! empty( $options['gateways_disable_pic']['cash_on_delivery'] ) ? checked( 1, $options['gateways_disable_pic']['cash_on_delivery'], false ) : '' ;?>>&nbsp;
                            <label for="pl8app_settings[gateways_disable_pic][cash_on_delivery]">Pay by cash</label>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Default Gateway', 'pl8app'); ?></th>
                        <td>

                            <select name="pl8app_settings[default_gateway]" id="pl8app_settings[default_gateway]"
                                    class="">
                                <?PHP
                                $default_gateway = isset($options['default_gateway'])?$options['default_gateway']:'';
                                    switch ($default_gateway) {
                                        case "paypal" :
                                            ?>
                                            <option value="paypal" selected><?php echo __('PayPal Standard', 'pl8app'); ?></option>
                                            <option value="manual"><?php echo __('Test Payment', 'pl8app'); ?></option>
                                            <option value="cash_on_delivery"><?php echo __('Pay by cash', 'pl8app'); ?></option>
                                        <?PHP break;
                                        case "manual" :
                                            ?>
                                            <option value="paypal"><?php echo __('PayPal Standard', 'pl8app'); ?></option>
                                            <option value="manual" selected><?php echo __('Test Payment', 'pl8app'); ?></option>
                                            <option value="cash_on_delivery"><?php echo __('Pay by cash', 'pl8app'); ?></option>
                                        <?PHP break;
                                        case "cash_on_delivery" :
                                            ?>
                                            <option value="paypal"><?php echo __('PayPal Standard', 'pl8app'); ?></option>
                                            <option value="manual"><?php echo __('Test Payment', 'pl8app'); ?></option>
                                            <option value="cash_on_delivery" selected><?php echo __('Pay by cash', 'pl8app'); ?></option>
                                        <?PHP break;
                                        Default:
                                                ?>
                                    <option value="paypal"><?php echo __('PayPal Standard', 'pl8app'); ?></option>
                                    <option value="manual"><?php echo __('Test Payment', 'pl8app'); ?></option>
                                    <option value="cash_on_delivery"><?php echo __('Pay by cash', 'pl8app'); ?></option>
                                <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[default_gateway]"> <?php echo __('This gateway will be loaded
                                automatically with the checkout page.', 'pl8app'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row">PayPal Email</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_email]"
                                   name="pl8app_settings[paypal_email]" value="<?PHP echo isset($options['paypal_email'])?$options['paypal_email']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_email]"> Enter your PayPal account's email</label></td>
                    </tr>
                    <tr>
                        <th scope="row">PayPal Identity Token</th>
                        <td><input type="text" class=" regular-text" id="pl8app_settings[paypal_identity_token]"
                                   name="pl8app_settings[paypal_identity_token]" value="<?PHP echo isset($options['paypal_identity_token'])?$options['paypal_identity_token']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_identity_token]"> Enter your PayPal Identity Token in order
                                to enable Payment Data Transfer (PDT).
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Disable PayPal IPN Verification</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[disable_paypal_verification]" value="-1">
                            <input type="checkbox" id="pl8app_settings[disable_paypal_verification]"
                                name="pl8app_settings[disable_paypal_verification]" value="1" class="" <?PHP echo ! empty( $options['disable_paypal_verification'] ) ? checked( 1, $options['disable_paypal_verification'], false ) : '' ;?>>
                            <label for="pl8app_settings[disable_paypal_verification]"> If you are unable to use Payment
                                Data Transfer and payments are not getting marked as complete, then check this box. This
                                forces the site to use a slightly less secure method of verifying purchases.</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section">API Credentials</th>
                        <td>API credentials are necessary to process PayPal refunds from inside WordPress. These can be
                            obtained from <a
                                href="https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature"
                                target="_blank">your PayPal account</a>.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Live API Username</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_live_api_username]"
                                   name="pl8app_settings[paypal_live_api_username]" value="<?PHP echo isset($options['paypal_live_api_username'])?$options['paypal_live_api_username']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_live_api_username]"> Your PayPal live API username. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Live API Password</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_live_api_password]"
                                   name="pl8app_settings[paypal_live_api_password]" value="<?PHP echo isset($options['paypal_live_api_password'])?$options['paypal_live_api_password']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_live_api_password]"> Your PayPal live API password. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Live API Signature</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_live_api_signature]"
                                   name="pl8app_settings[paypal_live_api_signature]" value="<?PHP echo isset($options['paypal_live_api_signature'])?$options['paypal_live_api_signature']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_live_api_signature]"> Your PayPal live API signature. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test API Username</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_test_api_username]"
                                   name="pl8app_settings[paypal_test_api_username]" value="<?PHP echo isset($options['paypal_test_api_username'])?$options['paypal_test_api_username']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_test_api_username]"> Your PayPal test API username. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test API Password</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_test_api_password]"
                                   name="pl8app_settings[paypal_test_api_password]" value="<?PHP echo isset($options['paypal_test_api_password'])?$options['paypal_test_api_password']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_test_api_password]"> Your PayPal test API password. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test API Signature</th>
                        <td>
                            <input type="text" class=" regular-text" id="pl8app_settings[paypal_test_api_signature]"
                                   name="pl8app_settings[paypal_test_api_signature]" value="<?PHP echo isset($options['paypal_test_api_signature'])?$options['paypal_test_api_signature']:'';?>" placeholder="">
                            <label for="pl8app_settings[paypal_test_api_signature]"> Your PayPal test API signature. </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section">Payment Privacy/Status Actions</th>
                        <td>When a user requests to be anonymized or removed from a site, these are the actions that
                            will be taken on payments associated with their customer, by status.
                            <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>What settings should I use?</strong><br />By default, pl8app sets suggested actions based on the Payment Status. These are purely recommendations, and you may need to change them to suit your store's needs. If you are unsure, you can safely leave these settings as is."></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Pending Payments</th>
                        <td>
                            <select id="pl8app_settings[payment_privacy_status_action_pending]"
                                    name="pl8app_settings[payment_privacy_status_action_pending]" class=""
                                    data-placeholder="">
                                <?PHP
                                $payment_privacy_status_action_pending = isset($options['payment_privacy_status_action_pending'])?$options['payment_privacy_status_action_pending']:'';
                                switch ($payment_privacy_status_action_pending) {
                                    case "anonymize" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize" selected="selected">Anonymize</option>
                                        <option value="delete">Delete</option>
                                        <?PHP break;
                                    case "delete" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete" selected="selected">Delete</option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="none" selected="selected">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete">Delete</option>
                                    <?PHP } ?>
                            </select><label for="pl8app_settings[payment_privacy_status_action_pending]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row">Paid Payments</th>
                        <td>
                            <select id="pl8app_settings[payment_privacy_status_action_publish]" name="pl8app_settings[payment_privacy_status_action_publish]" class=""
                                    data-placeholder="">
                                <?PHP
                                $payment_privacy_status_action_publish = isset($options['payment_privacy_status_action_publish'])?$options['payment_privacy_status_action_publish']:'';
                                switch ($payment_privacy_status_action_publish) {
                                    case "anonymize" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize" selected="selected">Anonymize</option>
                                        <option value="delete">Delete</option>
                                        <?PHP break;
                                    case "delete" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete" selected="selected">Delete</option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="none" selected="selected">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete">Delete</option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[payment_privacy_status_action_publish]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row">Refunded Payments</th>
                        <td>
                            <select id="pl8app_settings[payment_privacy_status_action_refunded]" name="pl8app_settings[payment_privacy_status_action_refunded]" class=""
                                    data-placeholder="">
                                <?PHP
                                $payment_privacy_status_action_refunded = isset($options['payment_privacy_status_action_refunded'])?$options['payment_privacy_status_action_refunded']:'';
                                switch ($payment_privacy_status_action_refunded) {
                                    case "anonymize" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize" selected="selected">Anonymize</option>
                                        <option value="delete">Delete</option>
                                        <?PHP break;
                                    case "delete" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete" selected="selected">Delete</option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="none" selected="selected">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete">Delete</option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[payment_privacy_status_action_refunded]"> </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Failed Payments</th>
                        <td>
                            <select id="pl8app_settings[payment_privacy_status_action_failed]" name="pl8app_settings[payment_privacy_status_action_failed]" class=""
                                    data-placeholder="">
                                <?PHP
                                $payment_privacy_status_action_failed = isset($options['payment_privacy_status_action_failed'])?$options['payment_privacy_status_action_failed']:'';
                                switch ($payment_privacy_status_action_failed) {
                                    case "anonymize" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize" selected="selected">Anonymize</option>
                                        <option value="delete">Delete</option>
                                        <?PHP break;
                                    case "delete" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete" selected="selected">Delete</option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="none" selected="selected">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete">Delete</option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[payment_privacy_status_action_failed]"> </label></td>
                    </tr>
                    <tr>
                        <th scope="row">Processing Payments</th>
                        <td>
                            <select id="pl8app_settings[payment_privacy_status_action_processing]" name="pl8app_settings[payment_privacy_status_action_processing]" class=""
                                    data-placeholder="">
                                <?PHP
                                $payment_privacy_status_action_processing = isset($options['payment_privacy_status_action_processing'])?$options['payment_privacy_status_action_processing']:'';
                                switch ($payment_privacy_status_action_processing) {
                                    case "anonymize" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize" selected="selected">Anonymize</option>
                                        <option value="delete">Delete</option>
                                        <?PHP break;
                                    case "delete" :
                                        ?>
                                        <option value="none">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete" selected="selected">Delete</option>
                                        <?PHP break;
                                    Default:
                                        ?>
                                        <option value="none" selected="selected">No Action</option>
                                        <option value="anonymize">Anonymize</option>
                                        <option value="delete">Delete</option>
                                    <?PHP } ?>
                            </select>
                            <label for="pl8app_settings[payment_privacy_status_action_processing]"> </label></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
                </div>
            </form>
        </div>
    </div>

    <?php
    echo ob_get_clean();
}



