<?php
/**
 * The Template for displaying Delivery FEE Settings
 *
 * @package pl8app_Delivery_Fee/Templates
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

$delivery_settings = pl8app_Delivery_Fee_Settings::pl8app_fee_settings();
$delivery_fee_method = isset( $delivery_settings['delivery_method'] ) ? $delivery_settings['delivery_method'] : 'zip_based';
$error_message = isset( $delivery_settings['unavailable_message'] ) ? stripslashes($delivery_settings['unavailable_message']) : 'Sorry we don\'t deliver here ';
$google_map_api = isset( $delivery_settings['google_map_api'] ) ? $delivery_settings['google_map_api'] : '';
$store_latlng = isset( $delivery_settings['store_latlng'] ) ? $delivery_settings['store_latlng'] : '51.5285582,-0.2416802 ';
$zip_code_placeholder = isset( $delivery_settings['zip_code_placeholder'] ) ? $delivery_settings['zip_code_placeholder'] : '';
$free_delivery_amount = isset( $delivery_settings['free_delivery_amount'] ) ? $delivery_settings['free_delivery_amount'] : '';
$distance_unit = isset( $delivery_settings['distance_unit'] ) ? $delivery_settings['distance_unit'] : 'km';
$store_location = isset( $delivery_settings['store_address'] ) ? $delivery_settings['store_address'] : 'London, UK';

?>

<div class="pl8app-delivery-fee-wrapper">

  <h4 class="pl8app-label-section">
    <?php esc_html_e( '--- Delivery Fee Settings ---', 'pl8app-delivery-fee' ); ?>
  </h4>

  <div class="settings-row">
    <h4><?php esc_html_e( 'ZIP/Postal code input placeholder' , 'pl8app-delivery-fee' ) ;  ?></h4>
    <span style="font-style:italic;">
      <?php esc_html_e( 'Enter the placeholder text that would appear for the input field of zip/postal code.', 'pl8app-delivery-fee' ); ?>
    </span>
    <input class="pl8app_xl_field" type="text" name="pl8app_delivery_fee[zip_code_placeholder]" value="<?php echo $zip_code_placeholder;  ?>">
  </div>

  <div class="settings-row">
    <h4>
      <?php esc_html_e( 'Error message for unavailable zip/postal code/distance', 'pl8app-delivery-fee' ); ?>
    </h4>
    <textarea rows="5" cols="100" name="pl8app_delivery_fee[unavailable_message]" value="<?php echo $error_message; ?>"><?php echo $error_message; ?></textarea>
  </div>

  <div class="settings-row pl8app-delivery-method">
    <h4>
      <?php esc_html_e( 'Select delivery fee method', 'pl8app-delivery-fee' ); ?>
    </h4>

    <div>
      <label for="zip-based">
        <input id="zip-based" <?php checked( $delivery_fee_method, 'zip_based' ); ?> type="radio" name="pl8app_delivery_fee[delivery_method]" value="zip_based">
        <?php echo __( 'Zip Based', 'pl8app-delivery-fee' ); ?>
      </label>
      <label for="location-based">
        <input id="location-based" <?php checked( $delivery_fee_method, 'location_based' ); ?> type="radio" name="pl8app_delivery_fee[delivery_method]" value="location_based">
        <?php echo __( 'Location Based', 'pl8app-delivery-fee' ); ?>
      </label>
    </div>
  </div>


  <div class="settings-row location-based-settings">
    <h4><?php esc_html_e( 'Google Map API Key' , 'pl8app-delivery-fee' ) ;  ?></h4>
    <span style="font-style:italic;">
      <?php _e( 'Enter google map api key. You can get your google map api from here <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">https://developers.google.com/maps/documentation/javascript/get-api-key</a>', 'pl8app-delivery-fee' ); ?>
    </span>
    <input class="pl8app_xl_field" type="text" name="pl8app_delivery_fee[google_map_api]" value="<?php echo $google_map_api;  ?>">
  </div>


  <div class="settings-row location-based-settings store-location-settings">
    <h4><?php esc_html_e( 'Store Location' , 'pl8app-delivery-fee' ) ;  ?></h4>

    <div id="pl8app-floating-panel">
      <input id="pl8app-address" type="textbox" name="pl8app_delivery_fee[store_address]" value="<?php echo $store_location; ?>">
      <input id="pl8app-location-submit" type="button" value="<?php _e( 'Set Location', 'pl8app-delivery-fee' ); ?>">
    </div>

    <div class="settings-row pl8app-floating-panel-help">
      <span style="font-style:italic;">
        <?php esc_html_e( 'You can drag the marker to set your exact location', 'pl8app-delivery-fee' ); ?>
      </span>
    </div>

    <div id="pl8app_map_canvas"></div>
    <div id="current"></div>
    <input class="pl8app_xl_field" type="text" name="pl8app_delivery_fee[store_latlng]" id="pl8app_map_latlng" value="<?php echo $store_latlng;  ?>">
  </div>

  <div class="settings-row location-based-settings">
    <h4><?php esc_html_e( 'Distance unit' , 'pl8app-delivery-fee' ) ;  ?></h4>
    <span style="font-style:italic;">
      <?php esc_html_e( 'Select distance unit what you want to use for distance units.', 'pl8app-delivery-fee' ); ?>
    </span>
    <select class="pl8app-delivery-units" name="pl8app_delivery_fee[distance_unit]">
      <option <?php echo selected( $distance_unit, 'km' ); ?> value="km"><?php echo __( 'KM','pl8app-delivery-fee' ); ?></option>
      <option <?php echo selected( $distance_unit, 'miles' ); ?> value="miles"><?php echo __( 'Miles','pl8app-delivery-fee' ); ?></option>
    </select>
  </div>

  <div class="settings-row delivery-fee-table location-based-settings">
    <table id="pl8app_delivery_location_fees" class="wp-list-table widefat fixed posts striped pl8app_delivery_location_fees">
      <thead>
        <tr>
          <td>
            <strong>
              <?php esc_html_e( 'Delivery Fee', 'pl8app-delivery-fee' ); ?>
            </strong>
            <span class="help-tag"><?php esc_html_e( 'Fee amount for the distance range.', 'pl8app-delivery-fee' ); ?></span>
            </td>
          <td>
            <strong>
              <?php esc_html_e( 'Distance', 'pl8app-delivery-fee' ); ?>
            </strong>
              <span class="help-tag"><?php esc_html_e( 'Enter the distance range within which the delivery fee would be applicable. &nbsp; &nbsp; eg: 5-20', 'pl8app-delivery-fee' ); ?></span>
            </td>
          <td>
            <strong>
              <?php esc_html_e( 'Free Delivery Fee', 'pl8app-delivery-fee' ); ?>
            </strong>
              <span class="help-tag"><?php esc_html_e( 'The amount below which the delivery fee would be added. If no amount has been set then the default amount would be used.', 'pl8app-delivery-fee' ); ?></span>
            </td>
          <td>
            <strong>
            <?php esc_html_e( 'Min Order Amount', 'pl8app-delivery-fee' ); ?>
            </strong>
            <span class="help-tag"><?php esc_html_e( 'Enter minimum order amount below which the customer can not place the order.', 'pl8app-delivery-fee' ); ?></span>
          </td>
          <td>
            <strong>
              <?php esc_html_e( 'Action', 'pl8app-delivery-fee' ); ?>
            </strong>
            </td>
        </tr>
      </thead>
      <tbody>
      <?php
        if ( isset( $delivery_settings['delivery_location_fee'] )
        && !empty( $delivery_settings['delivery_location_fee'] ) ) :

          $delivery_location_table = $delivery_settings['delivery_location_fee'];

          if ( is_array( $delivery_location_table ) ) :

            foreach( $delivery_location_table as $key => $delivery_fee_data ) :


              $fee_amount = isset( $delivery_fee_data['fee_amount'] ) ? $delivery_fee_data['fee_amount'] : '';

              $distance = isset( $delivery_fee_data['distance'] ) ? $delivery_fee_data['distance'] : '';

              $min_order_amount = isset( $delivery_fee_data['order_amount'] ) ? $delivery_fee_data['order_amount'] : '';

              $set_min_order_amount = isset( $delivery_fee_data['set_min_order_amount'] ) ? $delivery_fee_data['set_min_order_amount'] : '';

        ?>
        <tr data-row-id="">
          <td>
            <input type="text" value="<?php echo $fee_amount; ?>" name="pl8app_delivery_fee[delivery_location_fee][<?php echo $key; ?>][fee_amount]">
          </td>
          <td>
            <input type="text" value="<?php echo $distance;  ?>" name="pl8app_delivery_fee[delivery_location_fee][<?php echo $key; ?>][distance]">
          </td>
          <td>
            <input type="text" value="<?php echo $min_order_amount;  ?>" name="pl8app_delivery_fee[delivery_location_fee][<?php echo $key; ?>][order_amount]">
          </td>
          <td>
            <input type="text" value="<?php echo $set_min_order_amount;  ?>" name="pl8app_delivery_fee[delivery_location_fee][<?php echo $key; ?>][set_min_order_amount]">
          </td>
          <td>
            <a href="void(0)" data-row-id="<?php echo $key; ?>" class="pl8app-delivery-fee-remove"></a>
          </td>
        </tr>
      <?php
          endforeach;
        endif;
      endif;
      ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="delivery-table-footer">
            <button class="button button-primary add-delivery-location pl8app-pull-right"><?php esc_html_e( 'Add New Fee', 'pl8app-delivery-fee' ); ?></button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>


  <div class="settings-row delivery-fee-table zip-based-settings">
    <table id="pl8app_delivery_fees" class="wp-list-table widefat fixed posts striped pl8app_delivery_fees">
      <thead>
        <tr>
            <td>
                <strong>
                    <?php esc_html_e( 'Delivery Group', 'pl8app-delivery-fee' ); ?>
                </strong>
                <span class="help-tag"><?php esc_html_e( 'Name of Drivers group. It should be unique name.', 'pl8app-delivery-fee' ); ?></span>
            </td>

          <td>
            <strong>
              <?php esc_html_e( 'Delivery Fee', 'pl8app-delivery-fee' ); ?>
            </strong>
              <span class="help-tag"><?php esc_html_e( 'Fee amount for the zip/postal codes.', 'pl8app-delivery-fee' ); ?></span>
            </td>
          <td>
            <strong>
              <?php esc_html_e( 'ZIP/Postal Codes', 'pl8app-delivery-fee' ); ?>
            </strong>
              <span class="help-tag"><?php esc_html_e( 'Enter zip/postal codes separated by comma(,). It should be unique per Delivery group.', 'pl8app-delivery-fee' ); ?></span>
            </td>
            <td>
              <strong>
              <?php esc_html_e( 'Free Delivery Amount', 'pl8app-delivery-fee' ); ?>
              </strong>
              <span class="help-tag"><?php esc_html_e( 'Enter order amount below which the delivery fee would be applicable.', 'pl8app-delivery-fee' ); ?></span>
            </td>
            <td>
              <strong>
              <?php esc_html_e( 'Min Order Amount', 'pl8app-delivery-fee' ); ?>
              </strong>
              <span class="help-tag"><?php esc_html_e( 'Enter minimum order amount below which the customer could not able to place the order. If no amount has been set then the default amount would be taken into consideration.', 'pl8app-delivery-fee' ); ?></span>
            </td>
            <td>
              <strong>
                <?php esc_html_e( 'Action', 'pl8app-delivery-fee' ); ?>
              </strong>
            </td>
          </tr>
      </thead>
      <tbody>
        <?php
          if ( isset( $delivery_settings['delivery_fee'] )
            && !empty( $delivery_settings['delivery_fee'] ) ) :

            $delivery_fee_table = $delivery_settings['delivery_fee'];

            if ( is_array( $delivery_fee_table ) ) :

              foreach( $delivery_fee_table as $key => $delivery_fee_data ) :

                  $driver_group = isset( $delivery_fee_data['driver_group'] ) ? $delivery_fee_data['driver_group'] : '';

                $fee_amount = isset( $delivery_fee_data['fee_amount'] ) ? $delivery_fee_data['fee_amount'] : '';

                $zip_code = isset( $delivery_fee_data['zip_code'] ) ? $delivery_fee_data['zip_code'] : '';

                $min_order_amount = isset( $delivery_fee_data['order_amount'] ) ? $delivery_fee_data['order_amount'] : '';

                $set_min_order_amount = isset( $delivery_fee_data['set_min_order_amount'] ) ? $delivery_fee_data['set_min_order_amount'] : '';


                ?>
                <tr data-row-id="<?php echo $key; ?>">
                    <td>
                        <input type="text" value="<?php echo $driver_group; ?>" name="pl8app_delivery_fee[delivery_fee][<?php echo $key; ?>][driver_group]">
                    </td>
                  <td>
                    <input type="text" value="<?php echo $fee_amount; ?>" name="pl8app_delivery_fee[delivery_fee][<?php echo $key; ?>][fee_amount]">
                  </td>
                  <td>
                    <input type="text" value="<?php echo $zip_code; ?>" name="pl8app_delivery_fee[delivery_fee][<?php echo $key; ?>][zip_code]">
                  </td>
                  <td>
                    <input type="text" value="<?php echo $min_order_amount;  ?>" name="pl8app_delivery_fee[delivery_fee][<?php echo $key; ?>][order_amount]">
                  </td>
                  <td>
                    <input type="text" value="<?php echo $set_min_order_amount;  ?>" name="pl8app_delivery_fee[delivery_fee][<?php echo $key; ?>][set_min_order_amount]">
                  </td>
                  <td>
                    <a href="void(0)" data-row-id="<?php echo $key; ?>" class="pl8app-delivery-fee-remove"></a>
                  </td>
                </tr>
                <?php
              endforeach;
            endif;

          endif;
        ?>
      </tbody>

      <tfoot>
        <tr>
            <td colspan="6" class="error_msg"></td>
        </tr>
        <tr>
          <td colspan="6" class="delivery-table-footer">
            <button class="button button-primary pl8app-pull-right pl8app-add-delivery-fee-data"><?php esc_html_e( 'Add New Group', 'pl8app-delivery-fee' ); ?></button>
          </td>
        </tr>
      </tfoot>

    </table>
  </div>

  <div class="settings-row">
    <h4><?php esc_html_e( 'Free Delivery Amount' , 'pl8app-delivery-fee' ) ;  ?></h4>
    <span style="font-style:italic;">
      <?php esc_html_e( 'Enter the total amount above which the customer should get free delivery.', 'pl8app-delivery-fee' ); ?>
    </span>
    <input type="number" name="pl8app_delivery_fee[free_delivery_amount]" value="<?php echo $free_delivery_amount;  ?>">
  </div>

</div>