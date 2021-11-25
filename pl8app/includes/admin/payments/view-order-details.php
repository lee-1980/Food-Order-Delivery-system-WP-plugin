<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * View Order Details Page
 *
 * @since  1.0.0
 * @return void
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    wp_die(__('Payment ID not supplied. Please try again', 'pl8app'), __('Error', 'pl8app'));
}

// Setup the variables
$payment_id = absint($_GET['id']);
$payment = new pl8app_Payment($payment_id);

// Sanity check... fail if purchase ID is invalid
$payment_exists = $payment->ID;
if (empty($payment_exists)) {
    wp_die(__('The specified ID does not belong to a payment. Please try again', 'pl8app'), __('Error', 'pl8app'));
}

$number = $payment->number;
$payment_meta = $payment->get_meta();
$transaction_id = esc_attr($payment->transaction_id);
$cart_items = $payment->cart_details;
$user_id = $payment->user_id;
$payment_date = strtotime($payment->date);
$unlimited = $payment->has_unlimited_menuitems;
$user_info = pl8app_get_payment_meta_user_info($payment_id);
$address = $payment->address;
$gateway = $payment->gateway;
$currency_code = $payment->currency;
$customer = new pl8app_Customer($payment->customer_id);
$order_status = pl8app_get_order_status($payment_id);
$address_info = get_post_meta($payment_id, '_pl8app_delivery_address', true);
$phone = !empty($payment_meta['phone']) ? $payment_meta['phone'] : (!empty($address_info['phone']) ? $address_info['phone'] : '');
$flat = !empty($address_info['flat']) ? $address_info['flat'] : '';
$city = !empty($address_info['city']) ? $address_info['city'] : '';
$postcode = !empty($address_info['postcode']) ? $address_info['postcode'] : '';
$street = !empty($address_info['address']) ? $address_info['address'] : '';
$service_type = $payment->get_meta('_pl8app_delivery_type');
$service_time = $payment->get_meta('_pl8app_delivery_time');
$service_date = $payment->get_meta('_pl8app_delivery_date');
$order_note = $payment->get_meta('_pl8app_order_note');
$discount = pl8app_get_discount_price_by_payment_id($payment_id);

$customer_name = is_array($payment_meta['user_info']) ? $payment_meta['user_info']['first_name'] . ' ' . $payment_meta['user_info']['last_name'] : $customer->name;
$customer_email = is_array($payment_meta['user_info']) ? $payment_meta['user_info']['email'] : $customer->email;


$time_format = get_option('time_format', true);
$time_format = apply_filters('pla_store_time_format', $time_format);


?>

<div class="wrap pl8app-wrap">
    <h2>
        <?php printf(__('Order #%s', 'pl8app'), $number); ?>
        <?php do_action('pl8app_after_order_title', $payment_id); ?>
    </h2>
    <?php do_action('pl8app_view_order_details_before', $payment_id); ?>
    <form id="pl8app-edit-order-form" method="post">
        <?php do_action('pl8app_view_order_details_form_top', $payment_id); ?>
        <div id="poststuff">
            <div id="pl8app-dashboard-widgets-wrap">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables" class="meta-box-sortables ui-sortable">
                            <?php do_action('pl8app_view_order_details_sidebar_before', $payment_id); ?>
                            <div id="pl8app-order-update" class="postbox pl8app-order-data">
                                <h3 class="hndle">
                                    <span><?php _e('Update Order', 'pl8app'); ?></span>
                                </h3>
                                <div class="inside">
                                    <div class="pl8app-admin-box">

                                        <?php do_action('pl8app_view_order_details_totals_before', $payment_id); ?>

                                        <div class="pl8app-admin-box-inside">

                                            <p>
                                                <span class="label"><?php _e('Order Status:', 'pl8app'); ?></span>
                                                <select name="pl8app_order_status" class="medium-text">
                                                    <?php foreach (pl8app_get_order_statuses() as $key => $status) : ?>
                                                        <option value="<?php echo $key; ?>" <?php selected($order_status, $key, true); ?> >
                                                            <?php echo $status; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php
                                                $order_status_help = '<ul>';
                                                $order_status_help .= '<li>' . __('<strong>Pending</strong>: When the order is initially received by the restaurant.', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>Accepted</strong>: When the restaurant accepts the order.', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>Processing</strong>: When the restaurant starts preparing the menu.', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>Ready</strong>: When the order has been prepared by the restaurant.', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>In Transit</strong>: When the order is out for delivery', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>Cancelled</strong>: Order has been cancelled', 'pl8app') . '</li>';
                                                $order_status_help .= '<li>' . __('<strong>Completed</strong>: Payment has been done and the order has been completed.', 'pl8app') . '</li>';
                                                $order_status_help .= '</ul>';
                                                ?>
                                                <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help"
                                                      title="<?php echo $order_status_help; ?>"></span>
                                            </p>
                                        </div>

                                        <div class="pl8app-admin-box-inside">

                                            <p>
                                                <span class="label"><?php _e('Payment:', 'pl8app'); ?></span>
                                                <select name="pl8app-payment-status"
                                                        class="medium-text pl8app-payment-status">
                                                    <?php foreach (pl8app_get_payment_statuses() as $key => $status) : ?>
                                                        <option value="<?php echo esc_attr($key); ?>"<?php selected($payment->status, $key, true); ?>><?php echo esc_html($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php
                                                $status_help = '<ul>';
                                                $status_help .= '<li>' . __('<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'pl8app') . '</li>';
                                                $status_help .= '<li>' . __('<strong>Complete</strong>: all processing is completed for this purchase.', 'pl8app') . '</li>';
                                                $status_help .= '<li>' . __('<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'pl8app') . '</li>';
                                                $status_help .= '<li>' . __('<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'pl8app') . '</li>';
                                                $status_help .= '<li>' . __('<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'pl8app') . '</li>';
                                                $status_help .= '<li>' . __('<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'pl8app') . '</li>';
                                                $status_help .= '</ul>';
                                                ?>
                                                <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help"
                                                      title="<?php echo $status_help; ?>"></span>
                                            </p>
                                        </div>


                                        <?php if ($payment->is_recoverable()) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p>
                                                    <span class="label"><?php _e('Recovery URL', 'pl8app'); ?>:</span>
                                                    <?php $recover_help = __('Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'pl8app'); ?>
                                                    <span alt="f223"
                                                          class="pl8app-help-tip dashicons dashicons-editor-help"
                                                          title="<?php echo $recover_help; ?>"></span>
                                                    <input type="text" class="large-text" readonly="readonly"
                                                           value="<?php echo $payment->get_recovery_url(); ?>"/>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="pl8app-admin-box-inside">
                                            <p>
                                                <span class="label"><?php _e('Date:', 'pl8app'); ?></span>
                                                <input type="text" name="pl8app-payment-date"
                                                       value="<?php echo esc_attr(date('m/d/Y', $payment_date)); ?>"
                                                       class="medium-text pl8app_datepicker"/>
                                            </p>
                                        </div>

                                        <div class="pl8app-admin-box-inside">
                                            <p>
                                                <span class="label"><?php _e('Time:', 'pl8app'); ?></span>
                                                <input type="text" maxlength="2" name="pl8app-payment-time-hour"
                                                       value="<?php echo esc_attr(date_i18n('H', $payment_date)); ?>"
                                                       class="small-text pl8app-payment-time-hour"/>
                                                <input type="text" maxlength="2" name="pl8app-payment-time-min"
                                                       value="<?php echo esc_attr(date('i', $payment_date)); ?>"
                                                       class="small-text pl8app-payment-time-min"/>
                                            </p>
                                        </div>

                                        <?php
                                        $fees = $payment->fees;
                                        if (!empty($fees)) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p class="pl8app-order-fees strong">
                                                    <span class="label"><?php _e('Fees:', 'pl8app'); ?></span>
                                                <ul class="pl8app-payment-fees">
                                                    <?php foreach ($fees as $fee) : ?>
                                                        <li data-fee-id="<?php echo $fee['id']; ?>"><span
                                                                    class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr($fee['amount']) . '">' . pl8app_currency_filter($fee['amount'], $currency_code); ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (pl8app_use_taxes()) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p class="pl8app-order-taxes">
                                                    <span class="label"><?php echo pl8app_get_tax_name(); ?>:</span>
                                                    <input name="pl8app-payment-tax" class="med-text" type="text"
                                                           value="<?php echo esc_attr(pl8app_format_amount($payment->tax)); ?>"/>
                                                    <?php if (!empty($payment->tax_rate)) : ?>
                                                        <span class="pl8app-tax-rate">
										<?php echo $payment->tax_rate * 100; ?>%
									</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($discount)) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p class="pl8app-order-discount">
                                                    <span class="label"><?php _e('Coupon', 'pl8app'); ?>:</span>&nbsp;
                                                    <?php echo $discount; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="pl8app-admin-box-inside">
                                            <p class="pl8app-order-payment">
                                                <span class="label"><?php _e('Total Price', 'pl8app'); ?>:</span>&nbsp;
                                                <?php echo pl8app_currency_symbol($payment->currency); ?>&nbsp;<input
                                                        name="pl8app-payment-total" type="text" class="med-text"
                                                        value="<?php echo esc_attr(pl8app_format_amount($payment->total)); ?>"/>
                                            </p>
                                        </div>

                                        <div class="pl8app-order-payment-recalc-totals pl8app-admin-box-inside"
                                             style="display:none">
                                            <p>
                                                <span class="label"><?php _e('Recalculate Totals', 'pl8app'); ?>:</span>&nbsp;
                                                <a href="" id="pl8app-order-recalc-total"
                                                   class="button button-secondary right"><?php _e('Recalculate', 'pl8app'); ?></a>
                                            </p>
                                        </div>

                                        <?php do_action('pl8app_view_order_details_totals_after', $payment_id); ?>

                                    </div><!-- /.pl8app-admin-box -->
                                </div><!-- /.inside -->

                                <div class="pl8app-order-update-box pl8app-admin-box">
                                    <?php do_action('pl8app_view_order_details_update_before', $payment_id); ?>
                                    <div id="major-publishing-actions">
                                        <div id="delete-action">
                                            <a href="<?php echo wp_nonce_url(add_query_arg(array('pl8app-action' => 'delete_payment', 'purchase_id' => $payment_id), admin_url('admin.php?page=pl8app-payment-history')), 'pl8app_payment_nonce') ?>"
                                               class="pl8app-delete-payment pl8app-delete"><?php _e('Delete Order', 'pl8app'); ?></a>
                                        </div>
                                        <input type="submit" class="button button-primary right"
                                               value="<?php esc_attr_e('Save Order', 'pl8app'); ?>"/>
                                        <div class="clear"></div>
                                    </div>
                                    <?php do_action('pl8app_view_order_details_update_after', $payment_id); ?>
                                </div><!-- /.pl8app-order-update-box -->
                            </div><!-- /#pl8app-order-data -->

                            <?php if (pl8app_is_payment_complete($payment_id)) : ?>
                                <div id="pl8app-order-resend-receipt" class="postbox pl8app-order-data">
                                    <div class="inside">
                                        <div class="pl8app-order-resend-receipt-box pl8app-admin-box">

                                            <?php do_action('pl8app_view_order_details_resend_receipt_before', $payment_id); ?>
                                            <a href="<?php echo esc_url(add_query_arg(array('pl8app-action' => 'email_links', 'purchase_id' => $payment_id))); ?>"
                                               id="<?php if (count($customer->emails) > 1) {
                                                   echo 'pl8app-select-receipt-email';
                                               } else {
                                                   echo 'pl8app-resend-receipt';
                                               } ?>"
                                               class="button-secondary alignleft"><?php _e('Resend Receipt', 'pl8app'); ?></a>
                                            <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help"
                                                  title="<?php _e('<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If menuitem URLs are included in the receipt, new file menuitem URLs will also be included with the receipt.', 'pl8app'); ?>"></span>
                                            <?php if (count($customer->emails) > 1) : ?>
                                                <div class="clear"></div>
                                                <div class="pl8app-order-resend-receipt-addresses"
                                                     style="display:none;">
                                                    <select class="pl8app-order-resend-receipt-email">
                                                        <option value=""><?php _e(' -- select email --', 'pl8app'); ?></option>
                                                        <?php foreach ($customer->emails as $email) : ?>
                                                            <option value="<?php echo urlencode(sanitize_email($email)); ?>"><?php echo $email; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                            <div class="clear"></div>
                                            <?php do_action('pl8app_view_order_details_resend_receipt_after', $payment_id); ?>
                                        </div><!-- /.pl8app-order-resend-receipt-box -->
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div id="pl8app-order-details" class="postbox pl8app-order-data pl8app-payment-info-wrap">
                                <h3 class="hndle">
                                    <span><?php _e('Payment Info', 'pl8app'); ?></span>
                                </h3>
                                <div class="inside">
                                    <div class="pl8app-admin-box order-payment-info">
                                        <?php do_action('pl8app_view_order_details_payment_meta_before', $payment_id); ?>
                                        <?php if ($gateway) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p class="pl8app-order-gateway">
                                                    <span class="label"><?php _e('Gateway:', 'pl8app'); ?></span>
                                                    <?php echo pl8app_get_gateway_admin_label($gateway); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="pl8app-admin-box-inside">
                                            <p class="pl8app-order-payment-key">
                                                <span class="label"><?php _e('Key:', 'pl8app'); ?></span><?php echo $payment->key; ?>
                                            </p>
                                        </div>

                                        <div class="pl8app-admin-box-inside">
                                            <p class="pl8app-order-ip">
                                                <span class="label"><?php _e('IP:', 'pl8app'); ?></span>
                                                <span><?php echo pl8app_payment_get_ip_address_url($payment_id); ?></span>
                                            </p>
                                        </div>

                                        <?php if ($transaction_id) : ?>
                                            <div class="pl8app-admin-box-inside">
                                                <p class="pl8app-order-tx-id">
                                                    <span class="label"><?php _e('Transaction ID:', 'pl8app'); ?></span>
                                                    <span><?php echo apply_filters('pl8app_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id); ?></span>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php do_action('pl8app_view_order_details_payment_meta_after', $payment_id); ?>

                                    </div><!-- /.column-container -->
                                </div><!-- /.inside -->
                            </div><!-- /#pl8app-order-data -->

                            <div id="pl8app-payment-notes" class="postbox">
                                <h3 class="hndle">
                                    <span><?php _e('Payment Notes', 'pl8app'); ?></span>
                                </h3>
                                <div class="inside">
                                    <div id="pl8app-payment-notes-inner">

                                        <?php
                                        $notes = pl8app_get_payment_notes($payment_id);
                                        if (!empty($notes)) :
                                            $no_notes_display = ' style="display:none;"';
                                            foreach ($notes as $note) :
                                                echo pl8app_get_payment_note_html($note, $payment_id);
                                            endforeach;
                                        else :
                                            $no_notes_display = '';
                                        endif;
                                        echo '<p class="pl8app-no-payment-notes"' . $no_notes_display . '>' . __('No payment notes', 'pl8app') . '</p>'; ?>
                                    </div>

                                    <textarea name="pl8app-payment-note" id="pl8app-payment-note"
                                              class="large-text"></textarea>

                                    <p>
                                        <button id="pl8app-add-payment-note" class="button button-secondary right"
                                                data-payment-id="<?php echo absint($payment_id); ?>"><?php _e('Add Note', 'pl8app'); ?></button>
                                    </p>
                                    <div class="clear"></div>
                                </div><!-- /.inside -->
                            </div><!-- /#pl8app-payment-notes -->

                            <div id="pl8app-order-logs" class="postbox pl8app-order-logs">

                                <h3 class="hndle">
                                    <span><?php _e('Logs', 'pl8app'); ?></span>
                                </h3>

                                <div class="inside">
                                    <div class="pl8app-admin-box">
                                        <div class="pl8app-admin-box-inside">
                                            <p>
                                                <?php $purchase_url = admin_url('admin.php?page=pl8app-payment-history&user=' . esc_attr(pl8app_get_payment_user_email($payment_id))); ?>
                                                <a class="customer-order-logs"
                                                   href="<?php echo $purchase_url; ?>"><?php _e('View all orders for this customer', 'pl8app'); ?></a>
                                            </p>
                                        </div>

                                        <?php do_action('pl8app_view_order_details_logs_inner', $payment_id); ?>

                                    </div><!-- /.column-container -->
                                </div><!-- /.inside -->
                            </div><!-- /#pl8app-order-logs -->

                            <?php do_action('pl8app_view_order_details_sidebar_after', $payment_id); ?>

                        </div><!-- /#side-sortables -->
                    </div><!-- /#postbox-container-1 -->

                    <div id="postbox-container-2" class="postbox-container">


                        <div id="pl8app-customer-details" class="postbox">
                            <h3 class="hndle">
                                <span><?php _e('Order Details', 'pl8app'); ?></span>
                            </h3>
                            <div class="inside pl8app-clearfix">

                                <div class="column-container customer-info">
                                    <div class="column">
                                        <?php if (!empty($customer->id)) : ?>
                                            <?php $customer_url = admin_url('admin.php?page=pl8app-customers&view=overview&id=' . $customer->id); ?>
                                            <a href="<?php echo $customer_url; ?>"><?php echo $customer_name; ?>
                                                - <?php echo $customer_email; ?></a>
                                        <?php endif; ?>
                                        <input type="hidden" name="pl8app-current-customer"
                                               value="<?php echo $customer->id; ?>"/>
                                        <div style="margin-top:10px; margin-bottom:10px;">
                                            <strong><?php echo __('Phone:', 'pl8app'); ?> </strong>
                                            <?php echo $phone; ?>
                                        </div>
                                    </div>
                                    <div class="column">
                                        <a href="#change"
                                           class="pl8app-payment-change-customer"><?php _e('Assign to another customer', 'pl8app'); ?></a>
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
                                            'selected' => $customer->id,
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
                                    <div class="column">
                                        <small>
                                            <em>*<?php _e('Click "Save Payment" to change the customer', 'pl8app'); ?></em>
                                        </small>
                                    </div>
                                </div>

                                <div class="column-container new-customer" style="display: none">
                                    <div class="column">
                                        <strong><?php _e('Name', 'pl8app'); ?>:</strong>&nbsp;
                                        <input type="text" name="pl8app-new-customer-name" value=""
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
                                    <div class="column">
                                        <small>
                                            <em>*<?php _e('Click "Save Payment" to create new customer', 'pl8app'); ?></em>
                                        </small>
                                    </div>
                                </div>

                                <div class="column-container order-info">

                                    <div class="column">

                                        <?php apply_filters('pl8app_view_service_details_before', $payment_id); ?>

                                        <div class="pl8app-delivery-details">
                                            <p>
                                                <strong><?php _e('Service date: ', 'pl8app'); ?></strong>
                                                <?php if (!empty($service_date)) :
                                                    $service_date = pl8app_local_date($service_date);
                                                    $service_date = apply_filters('pl8app_service_date_view', $service_date);
                                                endif; ?>
                                                <input type="text" class="pl8app-order-date" name="pla_service_date"
                                                       value="<?php echo $service_date; ?>">
                                            </p>
                                        </div>

                                        <div class="pl8app-delivery-details">
                                            <p class="pl8app-service-details">
                                                <strong><?php _e('Service type: ', 'pl8app'); ?></strong><?php //echo pl8app_service_label( $service_type ); ?>
                                                <select class="medium-text" name="pla_service_type">
                                                    <?php
                                                    $service_types = pl8app_get_service_types();
                                                    foreach ($service_types as $service_id => $service_label) { ?>
                                                        <option value="<?php echo $service_id; ?>" <?php echo selected($service_type, $service_id, true) ?>><?php echo $service_label; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </p>
                                        </div>

                                        <?php if (!empty($service_time)) : ?>
                                            <div class="pl8app-delivery-details">
                                                <p class="pl8app-service-time">
                                                    <strong><?php _e('Service time: ', 'pl8app'); ?></strong>
                                                    <select name="pla_service_time" class="medium-text">
                                                        <?php
                                                        $full_times = pla_get_24hours_timings();
                                                        $selected_time = date($time_format, strtotime($service_time));
                                                        $selected_time = str_replace(' ', '', $selected_time);
                                                        foreach ($full_times as $time) {
                                                            $store_time = date($time_format, $time);
                                                            $timing_slug = str_replace(' ', '', $store_time);
                                                            ?>

                                                            <option <?php selected($selected_time, $timing_slug); ?>
                                                                    value='<?php echo $store_time; ?>'>
                                                                <?php echo $store_time; ?>
                                                            </option>

                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php apply_filters('pl8app_view_service_details_after', $payment_id); ?>

                                    </div>

                                    <?php if ($service_type == 'delivery') : ?>
                                        <div class="column">
                                            <div class="pl8app-delivery-address">
                                                <h3><?php echo sprintf(__('%s address:'), pl8app_service_label($service_type)); ?></h3>
                                                <?php echo $street; ?><br/>
                                                <?php if ($flat) : ?>
                                                    <?php echo $flat; ?><br/>
                                                <?php endif; ?>
                                                <?php echo $city . ' ' . $postcode; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>

                                <?php if (!empty($order_note)) : ?>
                                    <div class="column-container customer-instructions">
                                        <h3><?php echo sprintf(__('%s instructions:'), pl8app_service_label($service_type)); ?></h3>
                                        <?php echo $order_note ?>
                                    </div>
                                <?php endif;

                                // The pl8app_payment_personal_details_list hook is left here for backwards compatibility
                                do_action('pl8app_payment_personal_details_list', $payment_id, $payment_meta, $user_info);
                                do_action('pl8app_payment_view_details', $payment_id);
                                ?>

                            </div><!-- /.inside -->
                        </div><!-- /#pl8app-customer-details -->

                        <?php do_action('pl8app_view_order_details_main_before', $payment_id); ?>
                        <?php $column_count = pl8app_use_taxes() ? 'columns-5' : 'columns-4'; ?>

                        <?php
                        if (is_array($cart_items)) :
                            $is_qty_enabled = pl8app_item_quantities_enabled() ? ' item_quantity' : ''; ?>
                            <div id="pl8app-purchased-items"
                                 class="postbox pl8app-edit-purchase-element <?php echo $column_count; ?>">
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

                                <?php
                                $i = 0;
                                foreach ($cart_items as $key => $cart_item) :
                                    $item_id = isset($cart_item['id']) ? $cart_item['id'] : $cart_item;
                                    $menuitem = new pl8app_Menuitem($item_id);
                                    $menuitem_name = !empty($menuitem->ID) ? $menuitem->get_name() : '';
                                    $price = isset($cart_item['price']) ? $cart_item['price'] : false;
                                    $item_price = isset($cart_item['item_price']) ? $cart_item['item_price'] : $price;
                                    $subtotal = isset($cart_item['subtotal']) ? $cart_item['subtotal'] : $price;
                                    $item_tax = isset($cart_item['tax']) ? $cart_item['tax'] : 0;
                                    $item_tax_name = isset($cart_item['tax_name']) ? $cart_item['tax_name'] : 0;
                                    $price_id = isset($cart_item['item_number']['options']['price_id']) ? $cart_item['item_number']['options']['price_id'] : null;
                                    $quantity = isset($cart_item['quantity']) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;

                                    if (false === $price) {
                                        // This function is only used on payments with near 1.0 cart data structure
                                        $price = pl8app_get_menuitem_final_price($item_id, $user_info, null);
                                    } ?>

                                    <div class="row pl8app-purchased-row">

                                        <div class="pl8app-order-items-wrapper">
                                            <ul class="pl8app-purchased-items-list-wrapper <?php echo $key; ?>">
                                                <li class="menuitem">
                      				<span class="pl8app-purchased-menuitem-actions actions">
		                                <input type="hidden" class="pl8app-payment-details-menuitem-has-log"
                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][has_log]"
                                               value="1"/>
		                                <a href="" class="pl8app-order-remove-menuitem pl8app-delete"
                                           data-key="<?php echo esc_attr($key); ?>"><?php _e('&times;', 'pl8app'); ?></a>
		                            </span>
                                                    <span class="pl8app-purchased-menuitem-title">
                      					<?php if (!empty($menuitem->ID)) : ?>
                                            <a href="<?php echo admin_url('post.php?post=' . $item_id . '&action=edit'); ?>">
                      							<?php echo $menuitem->get_name();
                                                if (isset($cart_items[$key]['item_number']) && isset($cart_items[$key]['item_number']['options'])) {
                                                    $price_options = $cart_items[$key]['item_number']['options'];
                                                    if (pl8app_has_variable_prices($item_id) && isset($price_id)) {
                                                        echo ' - ' . pl8app_get_price_option_name($item_id, $price_id, $payment_id);
                                                    }
                                                } ?>
                      						</a>
                                        <?php else: ?>
                                            <span class="deleted">
                  								<?php if (!empty($cart_item['name'])) : ?>
                                                    <?php echo $cart_item['name']; ?>&nbsp;-&nbsp;
                                                    <em>(<?php _e('Deleted', 'pl8app'); ?>)</em>
                                                <?php else: ?>
                                                    <em><?php printf(__('%s deleted', 'pl8app'), pl8app_get_label_singular()); ?></em>
                                                <?php endif; ?>
                  							</span>
                                        <?php endif; ?>
                  					</span>

                                                    <input type="hidden"
                                                           name="pl8app-payment-details-menuitems[<?php echo $key; ?>][id]"
                                                           class="pl8app-payment-details-menuitem-id"
                                                           value="<?php echo esc_attr($item_id); ?>"/>

                                                    <input type="hidden"
                                                           name="pl8app-payment-details-menuitems[<?php echo $key; ?>][price_id]"
                                                           class="pl8app-payment-details-menuitem-price-id"
                                                           value="<?php echo esc_attr($price_id); ?>"/>

                                                    <input type="hidden"
                                                           name="pl8app-payment-details-menuitems[<?php echo $key; ?>][quantity]"
                                                           class="pl8app-payment-details-menuitem-quantity"
                                                           value="<?php echo esc_attr($quantity); ?>"/>

                                                    <?php if (!pl8app_use_taxes()): ?>
                                                        <input type="hidden"
                                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][item_tax]"
                                                               class="pl8app-payment-details-menuitem-item-tax"
                                                               value="<?php echo $item_tax; ?>"/>
                                                    <?php endif; ?>

                                                    <?php if (!empty($cart_items[$key]['fees'])) :
                                                        $fees = array_keys($cart_items[$key]['fees']); ?>
                                                        <input type="hidden"
                                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][fees]"
                                                               class="pl8app-payment-details-menuitem-fees"
                                                               value="<?php echo esc_attr(json_encode($fees)); ?>"/>
                                                    <?php endif; ?>
                                                </li>

                                                <li class="item_price">
                  					<span class="pl8app-order-price-wrap">
                  						<span class="pl8app-payment-details-label-mobile">
                  							<?php _ex('Price', 'payment details purchased item price - mobile', 'pl8app'); ?>
                  						</span>
                                        <?php echo pl8app_currency_symbol($currency_code); ?>
                                        <input type="text"
                                               class="pl8app-order-input medium-text pl8app-price-field pl8app-payment-details-menuitem-item-price pl8app-payment-item-input"
                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][item_price]"
                                               value="<?php echo pl8app_format_amount($item_price); ?>"/>
                  					</span>

                                                    <span class="pl8app-order-quantity-wrap">
                  						<span class="pl8app-payment-details-label-mobile">
                  							<?php _ex('Quantity', 'payment details purchased item quantity - mobile', 'pl8app'); ?>
                  						</span>
                  						<input type="number"
                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][quantity]"
                                               class="small-text pl8app-payment-details-menuitem-quantity pl8app-payment-item-input pl8app-order-input"
                                               min="1" step="1" value="<?php echo $quantity; ?>"/>
                  					</span>
                                                </li>

                                                <?php if (pl8app_use_taxes()) : ?>
                                                    <li class="item_tax">
                                                        <span class="pl8app-payment-details-label-mobile"><?php echo $item_tax_name; ?></span>
                                                        <?php echo pl8app_currency_symbol($currency_code); ?>
                                                        <input type="text"
                                                               class="small-text pl8app-price-field pl8app-payment-details-menuitem-item-tax pl8app-payment-item-input pl8app-order-input"
                                                               name="pl8app-payment-details-menuitems[<?php echo $key; ?>][item_tax]"
                                                               value="<?php echo pl8app_format_amount($item_tax); ?>"
                                                               readonly/>
                                                    </li>
                                                <?php endif; ?>

                                                <li class="price">
                  					<span class="pl8app-payment-details-label-mobile">
                  						<?php printf(_x('%s Total Price', 'payment details purchased item total - mobile', 'pl8app'), pl8app_get_label_singular()); ?>
                  					</span>
                                                    <span class="pl8app-price-currency"><?php echo pl8app_currency_symbol($currency_code); ?></span>
                                                    <span class="price-text pl8app-payment-details-menuitem-amount"><?php echo pl8app_format_amount($price); ?></span>
                                                    <input type="hidden"
                                                           name="pl8app-payment-details-menuitems[<?php echo $key; ?>][amount]"
                                                           class="pl8app-payment-details-menuitem-amount"
                                                           value="<?php echo esc_attr($price); ?>"/>
                                                </li>
                                            </ul>

                                            <!-- Options and Upgrades Items Starts Here -->
                                            <div class="pl8app-addon-items">
                                                <?php if (!empty($menuitem->ID)) : ?>
                                                    <span class="order-addon-items">
                  						<?php esc_html_e('Options and Upgrades Items', 'pl8app'); ?>
                  					</span>

                                                    <div class="menu-item-list">
                                                        <select multiple class="addon-items-list"
                                                                name="pl8app-payment-details-menuitems[<?php echo $key; ?>][addon_items][]">
                                                            <?php
                                                            $addons = get_post_meta($menuitem->ID, '_addon_items', array());
                                                            if (is_array($addons) && !empty($addons)) :
                                                                foreach ($addons as $addon_items) :
                                                                    if (is_array($addon_items)) :
                                                                        foreach ($addon_items as $addon_key => $addon_item) :
                                                                            $addon_id = isset($addon_item['category']) ? $addon_item['category'] : '';
                                                                            $get_addons = pl8app_get_addons($addon_id);
                                                                            if (is_array($get_addons) && !empty($get_addons)) :
                                                                                foreach ($get_addons as $get_addon) :
                                                                                    $addon_item_id = $get_addon->term_id;
                                                                                    $addon_item_name = $get_addon->name;
                                                                                    $addon_slug = $get_addon->slug;
                                                                                    $addon_raw_price = pl8app_get_addon_data($addon_item_id, 'price');
                                                                                    $addon_price = !empty($addon_raw_price) ? pl8app_currency_filter(pl8app_format_amount($addon_raw_price)) : '';
                                                                                    $selected_addon_items = isset($cart_item['addon_items']) ? $cart_item['addon_items'] : array();
                                                                                    if (!empty($selected_addon_items)) {
                                                                                        foreach ($selected_addon_items as $selected_addon_item) {
                                                                                            $selected_addon_id = !empty($selected_addon_item['addon_id']) ? $selected_addon_item['addon_id'] : '';
                                                                                            if ($selected_addon_id == $addon_item_id) { ?>
                                                                                                <option selected
                                                                                                        data-price="<?php echo $addon_price; ?>"
                                                                                                        data-id="<?php echo $addon_item_id; ?>"
                                                                                                        value="<?php echo $addon_item_name . '|' . $addon_item_id . '|' . $addon_raw_price . '|' . '1'; ?>">
                                                                                                    <?php
                                                                                                    echo $addon_item_name;
                                                                                                    if (!empty($addon_price)) echo ' (' . $addon_price . ') ';
                                                                                                    ?>
                                                                                                </option> <?php
                                                                                            }
                                                                                        }
                                                                                    } ?>

                                                                                    <option data-price="<?php echo $addon_price; ?>"
                                                                                            data-id="<?php echo $addon_item_id; ?>"
                                                                                            value="<?php echo $addon_item_name . '|' . $addon_item_id . '|' . $addon_raw_price . '|' . '1'; ?>">
                                                                                        <?php echo $addon_item_name . ' (' . $addon_price . ') '; ?>
                                                                                    </option>
                                                                                <?php endforeach;
                                                                            endif;
                                                                        endforeach;
                                                                    endif;
                                                                endforeach;
                                                            endif; ?>
                                                        </select>
                                                    </div>

                                                <?php endif; ?>
                                            </div> <!-- end of Option and Upgrade items-->

                                            <!-- Options and Upgrades Items Ends Here -->

                                            <div class="clear"></div>

                                            <?php
                                            if (isset($cart_items[$key]['instruction']) && !empty($cart_items[$key]['instruction'])) : ?>
                                                <div class="pl8app-special-instruction">
									<span class="special-instruction-label">
										<?php _e('Special Instruction:', 'pl8app'); ?>
									</span>
                                                    <?php echo $cart_items[$key]['instruction']; ?>
                                                </div> <!-- //end of special instruction-->
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php $i++;
                                endforeach; ?>
                            </div>

                        <?php else : $key = 0; ?>

                            <div class="row">
                                <p><?php printf(__('No %s included with this purchase', 'pl8app'), pla_get_label_plural()); ?></p>
                            </div>

                        <?php endif; ?>

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
                                            echo pl8app_currency_symbol($currency_code) . '&nbsp;';
                                            echo PL8PRESS()->html->text(
                                                array(
                                                    'name' => 'pl8app-order-menuitem-tax',
                                                    'id' => 'pl8app-order-menuitem-tax',
                                                    'class' => 'small-text pl8app-order-menuitem-tax pl8app-add-menuitem-field pl8app-order-input'
                                                )
                                            ); ?>
                                        </li>
                                    <?php endif; ?>

                                    <li class="pl8app-add-menuitem-to-purchase-actions actions">
						<span class="pl8app-payment-details-label-mobile">
							<?php _e('Actions', 'pl8app'); ?>
						</span>
                                        <a href="" id="pl8app-order-add-menuitem"
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

                        <?php do_action('pl8app_view_order_details_files_after', $payment_id); ?>
                        <?php do_action('pl8app_view_order_details_billing_before', $payment_id); ?>

                        <?php if (pl8app_show_billing_fields()) : ?>
                            <div id="pl8app-billing-details" class="postbox">
                                <h3 class="hndle">
                                    <span><?php _e('Billing Address', 'pl8app'); ?></span>
                                </h3>
                                <div class="inside pl8app-clearfix">

                                    <div id="pl8app-order-address">

                                        <div class="order-data-address">
                                            <div class="data column-container">
                                                <div class="column">
                                                    <p>
                                                        <?php
                                                        $line1_address = !empty($address['line1']) ? $address['line1'] : '';
                                                        ?>
                                                        <strong class="order-data-address-line"><?php _e('Street Address Line 1:', 'pl8app'); ?></strong><br/>
                                                        <input type="text" name="pl8app-payment-address[0][line1]"
                                                               value="<?php echo esc_attr($line1_address); ?>"
                                                               class="large-text"/>
                                                    </p>
                                                    <p>

                                                        <strong class="order-data-address-line"><?php _e('Street Address Line 2:', 'pl8app'); ?></strong><br/>
                                                        <input type="text" name="pl8app-payment-address[0][line2]"
                                                               value="<?php echo esc_attr($address['line2']); ?>"
                                                               class="large-text"/>
                                                    </p>

                                                </div>
                                                <div class="column">
                                                    <p>
                                                        <?php
                                                        $city = !empty($address['city']) ? $address['city'] : '';
                                                        ?>
                                                        <strong class="order-data-address-line"><?php echo _x('City:', 'Address City', 'pl8app'); ?></strong><br/>
                                                        <input type="text" name="pl8app-payment-address[0][city]"
                                                               value="<?php echo esc_attr($city); ?>"
                                                               class="large-text"/>

                                                    </p>
                                                    <p>
                                                        <?php $zip = !empty($address['zip']) ? $address['zip'] : ''; ?>
                                                        <strong class="order-data-address-line"><?php echo _x('Zip / Postal Code:', 'Zip / Postal code of address', 'pl8app'); ?></strong><br/>
                                                        <input type="text" name="pl8app-payment-address[0][zip]"
                                                               value="<?php echo esc_attr($zip); ?>"
                                                               class="large-text"/>

                                                    </p>
                                                </div>
                                                <div class="column">
                                                    <?php

                                                    $country = !empty($address['country']) ? $address['country'] : '';

                                                    ?>
                                                    <p id="pl8app-order-address-country-wrap">
                                                        <strong class="order-data-address-line"><?php echo _x('Country:', 'Address country', 'pl8app'); ?></strong><br/>
                                                        <?php
                                                        echo PL8PRESS()->html->select(array(
                                                            'options' => pl8app_get_country_list(),
                                                            'name' => 'pl8app-payment-address[0][country]',
                                                            'id' => 'pl8app-payment-address-country',
                                                            'selected' => $country,
                                                            'show_option_all' => false,
                                                            'show_option_none' => false,
                                                            'chosen' => true,
                                                            'placeholder' => __('Select a country', 'pl8app'),
                                                            'data' => array(
                                                                'search-type' => 'no_ajax',
                                                                'search-placeholder' => __('Type to search all Countries', 'pl8app'),
                                                            ),
                                                        ));
                                                        ?>
                                                    </p>
                                                    <p id="pl8app-order-address-state-wrap">
                                                        <strong class="order-data-address-line"><?php echo _x('State / Province:', 'State / province of address', 'pl8app'); ?></strong><br/>
                                                        <?php
                                                        $state = !empty($address['state']) ? $address['state'] : '';
                                                        ?>
                                                        <?php
                                                        $states = pl8app_get_states($address['country']);
                                                        if (!empty($states)) {
                                                            echo PL8PRESS()->html->select(array(
                                                                'options' => $states,
                                                                'name' => 'pl8app-payment-address[0][state]',
                                                                'id' => 'pl8app-payment-address-state',
                                                                'selected' => $state,
                                                                'show_option_all' => false,
                                                                'show_option_none' => false,
                                                                'chosen' => true,
                                                                'placeholder' => __('Select a state', 'pl8app'),
                                                                'data' => array(
                                                                    'search-type' => 'no_ajax',
                                                                    'search-placeholder' => __('Type to search all States/Provinces', 'pl8app'),
                                                                ),
                                                            ));
                                                        } else { ?>
                                                            <input type="text" name="pl8app-payment-address[0][state]"
                                                                   value="<?php echo esc_attr($address['state']); ?>"
                                                                   class="large-text"/>
                                                            <?php
                                                        } ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- /#pl8app-order-address -->

                                    <?php do_action('pl8app_payment_billing_details', $payment_id); ?>

                                </div><!-- /.inside -->
                            </div><!-- /#pl8app-billing-details -->
                        <?php endif; ?>

                        <?php do_action('pl8app_view_order_details_billing_after', $payment_id); ?>
                        <?php do_action('pl8app_view_order_details_main_after', $payment_id); ?>


                    </div><!-- #postbox-container-2 -->
                </div><!-- /#post-body -->
            </div><!-- #pl8app-dashboard-widgets-wrap -->
        </div><!-- /#post-stuff -->
        <?php do_action('pl8app_view_order_details_form_bottom', $payment_id); ?>
        <?php wp_nonce_field('pl8app_update_payment_details_nonce'); ?>
        <input type="hidden" name="pl8app_payment_id" value="<?php echo esc_attr($payment_id); ?>"/>
        <input type="hidden" name="pl8app_action" value="update_payment_details"/>
    </form>
    <?php do_action('pl8app_view_order_details_after', $payment_id); ?>
</div><!-- /.wrap -->

<div id="pl8app-menuitem-link"></div>
