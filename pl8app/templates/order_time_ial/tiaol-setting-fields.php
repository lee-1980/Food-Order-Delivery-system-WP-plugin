<?php
/**
 * The Template for displaying TIAOL Settings
 *
 * @package pl8app_StoreTiming/Templates
 * @version 1.1
 */

defined( 'ABSPATH' ) || exit;


$otil_settings          = new Order_Time_Interval_Limit_Settings();
$otil_settings          = $otil_settings->options;


$disable_pickup_time    = !empty( $otil_settings['disable_pickup_time'] ) ? $otil_settings['disable_pickup_time'] : '';
$disable_delivery_time  = !empty( $otil_settings['disable_delivery_time'] ) ? $otil_settings['disable_delivery_time'] : '';
$pickup_message         = !empty( $otil_settings['pickup_message'] ) ? trim( $otil_settings['pickup_message'] ) : '';
$delivery_message       = !empty( $otil_settings['delivery_message'] ) ? $otil_settings['delivery_message'] : '';


//Time interval duration
$time_interval_duration         = !empty( $otil_settings['time_interval_duration'] ) ? $otil_settings['time_interval_duration'] : '';


$orders_per_interval          = !empty( $otil_settings['orders_per_interval'] ) ? $otil_settings['orders_per_interval'] : '';
$orders_per_delivery_interval = !empty( $otil_settings['orders_per_delivery_interval'] ) ? $otil_settings['orders_per_delivery_interval'] :  $orders_per_interval;
$orders_per_pickup_interval   = !empty( $otil_settings['orders_per_pickup_interval'] ) ? $otil_settings['orders_per_pickup_interval'] :  $orders_per_interval;


$menuitems_per_order          = !empty( $otil_settings['menuitems_per_order'] ) ? $otil_settings['menuitems_per_order'] : '';
$menuitems_per_delivery_order = !empty( $otil_settings['menuitems_per_delivery_order'] ) ? $otil_settings['menuitems_per_delivery_order'] : $menuitems_per_order;
$menuitems_per_pickup_order   = !empty( $otil_settings['menuitems_per_pickup_order'] ) ? $otil_settings['menuitems_per_pickup_order'] : $menuitems_per_order;



$menuitems_order_error      = !empty( $otil_settings['menuitems_order_error'] ) ? $otil_settings['menuitems_order_error'] : '';
$order_interval_error_msg   = !empty( $otil_settings['order_interval_error_msg'] ) ? trim( $otil_settings['order_interval_error_msg'] ) : esc_html( 'The time slot what you have selected is not available', 'pl8app-otil' );

?>
<table class="form-table otil-settings" role="presentation">
  <tbody>
    <tr>
      <th class="header-settings" scope="row">
        <h3><?php esc_html_e( 'Order Time, Interval & Limits', 'pl8app-otil' ); ?>
        </h3>
      </th>
      <td></td>
    </tr>

    <!-- Disable pickup time starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Disable Pickup Time?', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input id="disable_pickup_time" type="checkbox" name="pl8app_otil[disable_pickup_time]" value="enable" <?php echo checked( $disable_pickup_time, 'enable', true ); ?>>
        <label for="disable_pickup_time"> <?php esc_html_e( 'Enable this option to disable pickup time.','pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Disable pickup time ends here -->

    <!-- Disable delivery time starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Disable Delivery Time?', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input id="disable_delivery_time" type="checkbox" name="pl8app_otil[disable_delivery_time]" value="enable" <?php echo checked( $disable_delivery_time, 'enable', true ); ?>>
        <label for="disable_delivery_time"> <?php esc_html_e( 'Enable this option to disable delivery time.', 'pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Disable pickup time ends here -->

    <!-- Pickup message starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Pickup Message', 'pl8app-otil' ); ?>
      </th>
      <td>
        <textarea class=" large-text" cols="50" rows="5" id="pickup_message" name="pl8app_otil[pickup_message]"><?php echo $pickup_message; ?></textarea>
        <label for="pl8app_otil[pickup_message]">
          <?php esc_html_e( 'This message will be displayed in place of pickup time', 'pl8app-otil'); ?>
        </label>
      </td>
    </tr>
    <!-- Pickup time ends here -->

    <!-- Delivery message starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Delivery Message', 'pl8app-otil' ); ?>
      </th>
      <td>
        <textarea class="large-text" cols="50" rows="5" id="delivery_message" name="pl8app_otil[delivery_message]"><?php echo $delivery_message; ?></textarea>
        <label for="pl8app_otil[delivery_message]">
          <?php esc_html_e( 'This message will be displayed in place of delivery time.','pl8app-otil'); ?>
        </label>
      </td>
    </tr>
    <!-- Delivery message ends here -->

    <!-- Time interval for delivery starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Time Interval for services', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input type="number" class="small-text" name="pl8app_otil[time_interval_duration]" value="<?php echo $time_interval_duration; ?>" id="time_interval_duration">
        <label for="time_interval_duration"> <?php esc_html_e( 'Enter time in minutes for time slot for service. Keep it empty if you want to keep it default.', 'pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Time interval for delivery ends here -->

    <!-- Orders interval for delivery starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Max orders per delivery slot', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input type="number" class="small-text" name="pl8app_otil[orders_per_delivery_interval]" value="<?php echo $orders_per_delivery_interval; ?>" id="orders_per_delivery_interval">
        <label for="orders_per_delivery_interval">
          <?php esc_html_e( 'Enter the number of orders for delivery service you want to accept for each time slot.' , 'pl8app-otil'); ?>
        </label>
      </td>
    </tr>
    <!-- Orders interval time ends here -->

    <!-- Orders interval for pickup starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Max orders per pickup slot', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input type="number" class="small-text" name="pl8app_otil[orders_per_pickup_interval]" value="<?php echo $orders_per_pickup_interval; ?>" id="orders_per_pickup_interval">
        <label for="orders_per_pickup_interval">
          <?php esc_html_e( 'Enter the number of orders you want to accept for pickup service in each time slot.' , 'pl8app-otil'); ?>
        </label>
      </td>
    </tr>
    <!-- Orders interval time ends here -->

    <!-- Orders interval starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Order limit reached message', 'pl8app-otil' ); ?>
      </th>
      <td>
         <textarea class="large-text" cols="50" rows="5" id="menuitems_order_error" name="pl8app_otil[order_interval_error_msg]"><?php echo $order_interval_error_msg; ?></textarea>
        <label for="menuitems_order_error"> <?php esc_html_e( 'This message will be displayed when there order limit is reach for the selected time slot.', 'pl8app-otil'); ?></label>
        </label>
      </td>
    </tr>
    <!-- Orders interval time ends here -->

    <!-- Foditems per orders starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Maximum items per delivery order', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input type="number" class="small-text" name="pl8app_otil[menuitems_per_delivery_order]" value="<?php echo $menuitems_per_delivery_order; ?>" id="menuitems_per_delivery_order">
        <label for="menuitems_per_delivery_order"> <?php esc_html_e( 'Enter max number of menuitems allowed for delivery order. Leave empty if you don\'t want to restrict', 'pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Menuitems per order ends here -->

    <!-- Foditems per orders starts here -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Maximum items per pickup order', 'pl8app-otil' ); ?>
      </th>
      <td>
        <input type="number" class="small-text" name="pl8app_otil[menuitems_per_pickup_order]" value="<?php echo $menuitems_per_pickup_order; ?>" id="menuitems_per_pickup_order">
        <label for="menuitems_per_pickup_order"> <?php esc_html_e( 'Enter max number of menuitems allowed for pickup order. Leave empty if you don\'t want to restrict', 'pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Menuitems per order ends here -->

    
    <!-- Menuitems order error -->
    <tr>
      <th scope="row">
        <?php esc_html_e( 'Error message for max items', 'pl8app-otil' ); ?>
      </th>
      <td>
        <textarea class="large-text" cols="50" rows="5" id="menuitems_order_error" name="pl8app_otil[menuitems_order_error]"><?php echo $menuitems_order_error; ?></textarea>
        <label for="menuitems_order_error"> <?php esc_html_e( 'This message will be displayed when the order has more than allowed menu items in one order.', 'pl8app-otil'); ?></label>
      </td>
    </tr>
    <!-- Menuitems order error -->

  </tbody>
</table>