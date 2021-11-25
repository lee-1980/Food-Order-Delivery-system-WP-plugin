<?php
/**
 * Orders Actions
 *
 * @package     pl8app
 * @copyright   Copyright (c) 2019, MagniGenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


/**
 * Update order on edit
 *
 * @access      private
 * @since       2.2
 * @return      void
 */
function pl8app_update_order_status($payment_id = 0, $new_status = 'completed')
{

    if (empty($payment_id)) {
        return;
    }

    if (0 >= did_action('pl8app_update_order_status')) {
        do_action('pl8app_update_order_status', $payment_id, $new_status);
    }

    if ($new_status == 'completed') {
        pl8app_update_payment_status($payment_id, 'publish');
    }

    //check the limit of No Show for customer of payment
    do_action('pl8app_check_noshow_option', $payment_id, $new_status);

    update_post_meta($payment_id, '_order_status', $new_status);
}


/**
 * Get order ststus by payment id
 *
 * @access      private
 * @since       2.1
 * @param       int $payment_id Payment id
 * @return      void
 */
function pl8app_get_order_status($payment_id)
{

    if (empty($payment_id)) {
        return;
    }

    $order_status = !empty(get_post_meta($payment_id, '_order_status', true)) ? get_post_meta($payment_id, '_order_status', true) : 'pending';

    return apply_filters('pla_get_order_status', $order_status);

}


/**
 * Get HTML for some action buttons. Used in list tables.
 *
 * @since 1.0
 * @param array $actions Actions to output.
 * @return string
 */
function pla_render_action_buttons($actions)
{

    $actions_html = '';

    if (!empty($actions)) {
        foreach ($actions as $action) {
            if (isset($action['group'])) {
                $actions_html .= '<div class="pl8app-action-button-group"><label>' . $action['group'] . '</label> <span class="pl8app-action-button-group__items">' . pla_render_action_buttons($action['actions']) . '</span></div>';
            } elseif (isset($action['action'], $action['name'])) {
                $actions_html .= sprintf('<a class="button pl8app-action-button pl8app-action-button-%1$s %1$s" data-update-status="%1$s"  aria-label="%2$s" data-payment="%3$s" data-action="pl8app_update_order_status" title="%2$s" href="%4$s">%2$s</a>', esc_attr($action['action']), esc_html($action['name']), $action['payment_id'], $action['url']);
            }
        }
    }

    return $actions_html;
}