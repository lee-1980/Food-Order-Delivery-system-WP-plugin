<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Quick Entry of Order
 *
 * @since  1.0.0
 * @return void
 */

function pl8app_payment_quick_entry(){


    $time_format = get_option('time_format', true);
    $time_format = apply_filters('pla_store_time_format', $time_format);

    ?>
    <div class="wrap pl8app-wrap">
        <h2>
            <?php echo __('New Order', 'pl8app'); ?>
        </h2>

        <form id="pl8app-edit-order-form" method="post">

            <div id="poststuff">
                <div id="pl8app-dashboard-widgets-wrap">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="postbox-container-1" class="postbox-container">
                            <div id="pl8app-customer-details" class="postbox">
                                <h3 class="hndle">
                                    <span><?php _e('Order Details', 'pl8app'); ?></span>
                                </h3>
                                <div class="inside pl8app-clearfix">
                                    <div class="column-container customer-info">
                                        <div class="column">
                                            <a href="#change"
                                               class="pl8app-payment-change-customer"><?php _e('Select A Customer Already Registered', 'pl8app'); ?></a>
                                            &nbsp;|&nbsp;
                                            <a href="#new"
                                               class="pl8app-payment-new-customer"><?php _e('New Customer', 'pl8app'); ?></a>
                                        </div>
                                    </div>
                                    <div class="column-container change-customer" style="display: none">
                                        <div class="column">
                                            <strong><?php _e('Select a customer', 'pl8app'); ?>:</strong>
                                            <?php
                                            $args = array(
                                                'class' => 'pl8app-payment-change-customer-input',
                                                'selected' => '',
                                                'name' => 'customer-id',
                                                'placeholder' => __('Type to search all Customers', 'pl8app'),
                                            );

                                            echo PL8PRESS()->html->customer_dropdown($args);
                                            ?>
                                        </div>
                                        <div class="column"></div>
                                        <div class="column">
                                            <strong><?php _e('Actions', 'pl8app'); ?>:</strong>
                                            <br/>
                                            <input type="hidden" id="pl8app-change-customer" name="pl8app-change-customer"
                                                   value="0"/>
                                            <a href="#cancel"
                                               class="pl8app-payment-change-customer-cancel pl8app-delete"><?php _e('Cancel', 'pl8app'); ?></a>
                                        </div>
                                    </div>

                                    <div class="column-container new-customer" style="display: none">
                                        <div class="column">
                                            <strong><?php _e('Name', 'pl8app'); ?>:</strong>&nbsp;
                                            <input type="text" name="pl8app-new-customer-name" value=""
                                                   class="medium-text"/>
                                        </div>
                                        <div class="column">
                                            <strong><?php _e('Phone', 'pl8app'); ?>:</strong>&nbsp;
                                            <input type="tel" name="pl8app-new-customer-phone" value=""
                                                   class="medium-text"/>
                                        </div>
                                        <div class="column">
                                            <strong><?php _e('Email', 'pl8app'); ?>:</strong>&nbsp;
                                            <input type="email" name="pl8app-new-customer-email" value=""
                                                   class="medium-text"/>
                                        </div>
                                        <div class="column">
                                            <strong><?php _e('Actions', 'pl8app'); ?>:</strong>
                                            <br/>
                                            <input type="hidden" id="pl8app-new-customer" name="pl8app-new-customer"
                                                   value="0"/>
                                            <a href="#cancel"
                                               class="pl8app-payment-new-customer-cancel pl8app-delete"><?php _e('Cancel', 'pl8app'); ?></a>
                                        </div>

                                    </div>
                                    <div class="column-container order-info">
                                        <div class="column" style="vertical-align: top;">
                                            <div class="pl8app-delivery-details">
                                                <p class="pl8app-service-details">
                                                    <strong><?php _e('Service type: ', 'pl8app'); ?></strong><?php //echo pl8app_service_label( $service_type ); ?>
                                                    <select class="medium-text" name="pla_service_type">
                                                        <?php
                                                        $service_types = pl8app_get_service_types();
                                                        foreach ($service_types as $service_id => $service_label) { ?>
                                                            <option value="<?php echo $service_id; ?>"><?php echo $service_label; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </p>
                                            </div>

                                            <div class="pl8app-delivery-details">
                                                <p>
                                                    <strong><?php _e('Service date: ', 'pl8app'); ?></strong>
                                                    <input type="text" class="pl8app-order-date" name="pla_service_date"
                                                           value="">
                                                </p>
                                            </div>

                                            <div class="pl8app-delivery-details">
                                                <p class="pl8app-service-time">
                                                    <strong><?php _e('Service time: ', 'pl8app'); ?></strong>
                                                    <select name="pla_service_time" class="medium-text">
                                                        <?php
                                                        $full_times = pla_get_24hours_timings();
                                                        foreach ($full_times as $time) {
                                                            $store_time = date($time_format, $time);
                                                            ?>
                                                            <option
                                                                value='<?php echo $store_time; ?>'>
                                                                <?php echo $store_time; ?>
                                                            </option>

                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="column">
                                            <div class="pl8app-delivery-address">
                                                <h3><?php echo __('Delivery address:', 'pl8app'); ?></h3>
                                                <p>
                                                    <strong><?php _e('Street Address *', 'pl8app'); ?>:</strong>&nbsp;
                                                    <input type="text" name="pl8app-street-address" value=""
                                                           class="large-text"/>
                                                </p>
                                                <p>
                                                    <strong><?php _e('Apartment, suite, unit etc. (optional)', 'pl8app'); ?>:</strong>&nbsp;
                                                    <input type="text" name="pl8app-apt-suite" value=""
                                                           class="large-text"/>
                                                </p>
                                                <p>
                                                    <strong><?php _e('Town / City *', 'pl8app'); ?>:</strong>&nbsp;
                                                    <input type="text" name="pl8app-city" value=""
                                                           class="large-text"/>
                                                </p>
                                                <p>
                                                    <strong><?php _e('Postcode / ZIP *', 'pl8app'); ?>:</strong>&nbsp;
                                                    <input type="text" name="pl8app-postcode" value=""
                                                           class="large-text"/>
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <?php
                            $column_count = pl8app_use_taxes() ? 'columns-5' : 'columns-4';
                            $is_qty_enabled = pl8app_item_quantities_enabled() ? ' item_quantity' : ''; ?>
                            <div id="pl8app-purchased-items" class="postbox pl8app-edit-purchase-element <?php echo $column_count; ?>">
                                <div class="pl8app-purchased-items-header row header">
                                    <ul class="pl8app-purchased-items-list-header">
                                        <li class="menuitem"><?php printf(_x('%s Purchased', 'payment details purchased item title - full screen', 'pl8app'), pla_get_label_singular()); ?></li>
                                        <li class="item_price">
                                            <?php _ex('Price', 'payment details purchased item price - full screen', 'pl8app'); ?>
                                            <?php _ex(' & Quantity', 'payment details purchased item quantity - full screen', 'pl8app'); ?>
                                        </li>
                                        <?php if (pl8app_use_taxes()) : ?>
                                            <li class="item_tax"><?php _ex('Tax', 'payment details purchased item tax - full screen', 'pl8app'); ?></li>
                                        <?php endif; ?>
                                        <li class="price"><?php printf(_x('%s Total', 'payment details purchased item total - full screen', 'pl8app'), pla_get_label_singular()); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="postbox pl8app-edit-purchase-element pl8app-add-update-elements <?php echo $column_count; ?>">

                                <div class="pl8app-add-menuitem-to-purchase-header row header">
                                    <ul class="pl8app-purchased-items-list-wrapper">
                                        <li class="menuitem"><?php printf(__('Add New %s', 'pl8app'), pl8app_get_label_singular()); ?></li>
                                        <li class="item_price<?php echo $is_qty_enabled; ?>">
                                            <?php _e('Price', 'pl8app'); ?>
                                            <?php _e(' & Quantity', 'pl8app'); ?>
                                        </li>
                                        <?php if (pl8app_use_taxes()) : ?>
                                            <li class="item_tax">
                                                <?php echo _e('Tax', 'pl8app'); ?>
                                            </li>
                                        <?php endif; ?>
                                        <li class="price"><?php _e('Actions', 'pl8app'); ?></li>
                                    </ul>
                                </div>

                                <div class="pl8app-add-menuitem-to-purchase aa inside">
                                    <ul>
                                        <li class="menuitem">
                                            <span class="pl8app-payment-details-label-mobile">
                                                <?php printf(_x('Select %s To Add', 'payment details select item to add - mobile', 'pl8app'), pl8app_get_label_singular()); ?>
                                            </span>
                                            <?php echo PL8PRESS()->html->product_dropdown(array(
                                                'name' => 'pl8app-order-menuitem-select',
                                                'id' => 'pl8app-order-menuitem-select',
                                                'chosen' => true
                                            )); ?>
                                        </li>

                                        <li class="item_price<?php echo $is_qty_enabled; ?>">
                                            <span class="pl8app-payment-details-label-mobile">
                                                <?php
                                                _ex('Price', 'payment details add item price - mobile', 'pl8app');
                                                _ex(' & Quantity', 'payment details add item quantity - mobile', 'pl8app'); ?>
                                            </span>

                                            <span class="pl8app-menuitem-to-purchase-wrapper">
                                                <span class="pl8app-menuitem-variations"></span>
                                                <span class="pl8app-menuitem-price"></span>
                                            </span>
                                            <span>&nbsp;&times;&nbsp;</span>
                                            <input type="number" id="pl8app-order-menuitem-quantity"
                                                   name="pl8app-order-menuitem-quantity"
                                                   class="small-text pl8app-add-menuitem-field pl8app-order-input" min="1"
                                                   step="1" value="1"/>
                                        </li>

                                        <?php if (pl8app_use_taxes()) : ?>
                                            <li class="item_tax">
                                                <span class="pl8app-payment-details-label-mobile">
	                		<?php _ex('Tax', 'payment details add item tax - mobile', 'pl8app'); ?>
	                	</span>
                                                <?php
                                                echo pl8app_currency_symbol() . '&nbsp;';
                                                echo PL8PRESS()->html->text(
                                                    array(
                                                        'name' => 'pl8app-order-menuitem-tax',
                                                        'id' => 'pl8app-order-menuitem-tax',
                                                        'class' => 'small-text pl8app-order-menuitem-tax pl8app-add-menuitem-field pl8app-order-input',
                                                        'readonly' => true
                                                    )
                                                ); ?>
                                            </li>
                                        <?php endif; ?>

                                        <li class="pl8app-add-menuitem-to-purchase-actions actions">
                                            <span class="pl8app-payment-details-label-mobile">
							<?php _e('Actions', 'pl8app'); ?>
						</span>
                                            <a href="" id="pl8app-order-create-menuitem"
                                               class="button button-secondary"><?php printf(__('Add New %s', 'pl8app'), pl8app_get_label_singular()); ?></a>
                                        </li>
                                    </ul>

                                    <input type="hidden" name="pl8app-payment-menuitems-changed"
                                           id="pl8app-payment-menuitems-changed" value=""/>
                                    <input type="hidden" name="pl8app-payment-removed" id="pl8app-payment-removed"
                                           value="{}"/>

                                    <?php //if ( ! pl8app_item_quantities_enabled() ) : ?>
                                    <input type="hidden" id="pl8app-order-menuitem-quantity"
                                           name="pl8app-order-menuitem-quantity" value="1"/>
                                    <?php // endif; ?>

                                    <?php if (!pl8app_use_taxes()) : ?>
                                        <input type="hidden" id="pl8app-order-menuitem-tax" name="pl8app-order-menuitem-tax"
                                               value="0"/>
                                    <?php endif; ?>

                                </div><!-- /.inside -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php wp_nonce_field('pl8app_create_payment_details_nonce'); ?>
            <input type="hidden" name="pl8app_action" value="create_payment_details"/>
            <input type="submit" class="button button-primary right"
                   value="<?php esc_attr_e('Save Order', 'pl8app'); ?>"/>
        </form>
    </div>
    <?php
}