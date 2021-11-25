<?php if (!empty($_GET['pl8app-verify-success'])) : ?>
    <p class="pl8app-account-verified pl8app_success">
        <?php _e('Your account has been successfully verified!', 'pl8app'); ?>
    </p>
<?php
endif;
/**
 * This template is used to display the order history of the current user.
 */
if (is_user_logged_in()):
    $payments = pl8app_get_users_orders(get_current_user_id(), 20, true, 'any');
    if ($payments) :
        ?>
        <div class="pl8app-section pl8app-col-lg-12 pl8app-col-md-12 pl8app-col-sm-12 pl8app-col-xs-12" style="transform: none">
        <div class="pl8app-col-lg-8 pl8app-col-md-8 pl8app-col-sm-12 pl8app-col-xs-12">
        <?php do_action('pl8app_before_order_history', $payments); ?>
        <table id="pl8app_user_history" class="pl8app-table">
            <thead>
            <tr class="pl8app_purchase_row">
                <?php do_action('pl8app_order_history_header_before'); ?>
                <th class="pl8app_purchase_id"><?php _e('ID', 'pl8app'); ?></th>
                <th class="pl8app_purchase_date"><?php _e('Date', 'pl8app'); ?></th>
                <th class="pl8app_purchase_amount"><?php _e('Amount', 'pl8app'); ?></th>
                <th class="pl8app_purchase_details"><?php _e('Details', 'pl8app'); ?></th>
                <?php do_action('pl8app_order_history_header_after'); ?>
            </tr>
            </thead>
            <?php foreach ($payments as $payment) : ?>
                <?php $payment = new pl8app_Payment($payment->ID);
                ?>
                <tr class="pl8app_purchase_row">
                    <?php do_action('pl8app_order_history_row_start', $payment->ID, $payment->payment_meta); ?>
                    <td class="pl8app_purchase_id">#<?php echo $payment->number ?></td>
                    <td class="pl8app_purchase_date"><?php echo date_i18n(get_option('date_format'), strtotime($payment->date)); ?></td>
                    <td class="pl8app_purchase_amount">
                        <span class="pl8app_purchase_amount"><?php echo pl8app_currency_filter(pl8app_format_amount($payment->total)); ?></span>
                    </td>
                    <td class="pl8app_purchase_details">
                        <?php if ($payment->status != 'publish') : ?>
                            <span class="pl8app_purchase_status <?php echo $payment->status; ?>"><?php echo $payment->status_nicename; ?></span>
                            <?php if ($payment->is_recoverable()) : ?>
                                &mdash; <a
                                        href="<?php echo $payment->get_recovery_url(); ?>"><?php _e('Complete Purchase', 'pl8app'); ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('payment_key', $payment->key, pl8app_get_success_page_uri())); ?>"><?php _e('View Details', 'pl8app'); ?></a>
                        <?php endif; ?>
                    </td>
                    <?php do_action('pl8app_order_history_row_end', $payment->ID, $payment->payment_meta); ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <div id="pl8app_order_history_pagination" class="pl8app_pagination navigation">
            <?php
            $big = 999999;
            echo paginate_links(array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total' => ceil(pl8app_count_purchases_of_customer() / 20) // 20 items per page
            ));
            ?>
        </div>
        <?php do_action('pl8app_after_order_history', $payments); ?>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p class="pl8app-no-purchases"><?php _e('You have not made any orders', 'pl8app'); ?></p>
    <?php endif; ?>
    </div>
    <?php
    pl8app_shopping_cart(true);
    ?>
    </div>
<?PHP
endif;
