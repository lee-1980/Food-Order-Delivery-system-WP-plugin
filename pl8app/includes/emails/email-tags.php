<?php

/**
 * Add an email tag
 *
 * @since  1.0.0
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function pl8app_add_email_tag( $tag, $description, $func ) {
    PL8PRESS()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since  1.0.0
 *
 * @param string $tag Email tag to remove hook from
 */
function pl8app_remove_email_tag( $tag ) {
    PL8PRESS()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since  1.0.0
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function pl8app_email_tag_exists( $tag ) {
    return PL8PRESS()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since  1.0.0
 *
 * @return array
 */
function pl8app_get_email_tags() {
    return PL8PRESS()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since  1.0.0
 *
 * @return string
 */
function pl8app_get_emails_tags_list() {
  // The list
  $list = '';

  // Get all tags
  $email_tags = pl8app_get_email_tags();

  // Check
  if ( is_array( $email_tags )
    && count( $email_tags ) > 0 ) {

    // Loop
    foreach ( $email_tags as $email_tag ) {

      //Add email tag to list
      $list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

    }

  }

  // Return the list
  return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since  1.0.0
 *
 * @return string Content with email tags filtered out.
 */
function pl8app_do_email_tags( $content, $payment_id ) {

  // Replace all tags
  $content = PL8PRESS()->email_tags->do_tags( $content, $payment_id );

  // Maintaining backwards compatibility
  $content = apply_filters( 'pl8app_email_template_tags', $content, pl8app_get_payment_meta( $payment_id ), $payment_id );

  // Return content
  return $content;
}

/**
 * Load email tags
 *
 * @since  1.0.0
 */
function pl8app_load_email_tags() {
  do_action( 'pl8app_add_email_tags' );
}
add_action( 'init', 'pl8app_load_email_tags', -999 );

/**
 * Add default pl8app email template tags
 *
 * @since  1.0.0
 */
function pl8app_setup_email_tags() {

  // Setup default tags array
  $email_tags = array(

    array(
      'tag'         => 'menuitem_list',
      'description' => __( 'A list of menuitem purchased', 'pl8app' ),
      'function'    => 'pl8app_email_tag_menuitem_list'
    ),

    array(
      'tag'         => 'name',
      'description' => __( "The buyer's first name", 'pl8app' ),
      'function'    => 'pl8app_email_tag_first_name'
    ),

    array(
      'tag'         => 'fullname',
      'description' => __( "The buyer's full name, first and last", 'pl8app' ),
      'function'    => 'pl8app_email_tag_fullname'
    ),

    array(
      'tag'         => 'username',
      'description' => __( "The buyer's user name on the site, if they registered an account", 'pl8app' ),
      'function'    => 'pl8app_email_tag_username'
    ),

    array(
      'tag'         => 'user_email',
      'description' => __( "The buyer's email address", 'pl8app' ),
      'function'    => 'pl8app_email_tag_user_email'
    ),

    array(
      'tag'         => 'billing_address',
      'description' => __( 'The buyer\'s billing address', 'pl8app' ),
      'function'    => 'pl8app_email_tag_billing_address'
    ),

    array(
      'tag'         => 'date',
      'description' => __( 'The date of the purchase', 'pl8app' ),
      'function'    => 'pl8app_email_tag_date'
    ),

    array(
      'tag'         => 'subtotal',
      'description' => __( 'The price of the purchase before taxes', 'pl8app' ),
      'function'    => 'pl8app_email_tag_subtotal'
    ),

    array(
      'tag'         => 'tax',
      'description' => __( 'The taxed amount of the purchase', 'pl8app' ),
      'function'    => 'pl8app_email_tag_tax'
    ),

    array(
      'tag'         => 'price',
      'description' => __( 'The total price of the purchase', 'pl8app' ),
      'function'    => 'pl8app_email_tag_price'
    ),

    array(
      'tag'         => 'phone',
      'description' => __( 'Customer\'s phone number', 'pl8app' ),
      'function'    => 'pl8app_email_tag_phone'
    ),

    array(
      'tag'         => 'service_type',
      'description' => __( 'Service Type', 'pl8app' ),
      'function'    => 'pl8app_email_tag_service_type'
    ),

    array(
      'tag'         => 'service_time',
      'description' => __( 'Service Time', 'pl8app' ),
      'function'    => 'pl8app_email_tag_service_time'
    ),

    array(
      'tag'         => 'order_note',
      'description' => __( 'Order note', 'pl8app' ),
      'function'    => 'pl8app_email_tag_order_note'
    ),

    array(
      'tag'         => 'payment_id',
      'description' => __( 'The unique Order ID number for this purchase', 'pl8app' ),
      'function'    => 'pl8app_email_tag_order_id'
    ),

    array(
      'tag'         => 'receipt_id',
      'description' => __( 'The unique ID number for this purchase receipt', 'pl8app' ),
      'function'    => 'pl8app_email_tag_receipt_id'
    ),

    array(
      'tag'         => 'delivery_address',
      'description' => __( 'Delivery address', 'pl8app' ),
      'function'    => 'pl8app_email_tag_delivery_address'
    ),

    array(
      'tag'         => 'payment_method',
      'description' => __( 'The method of payment used for this purchase', 'pl8app' ),
      'function'    => 'pl8app_email_tag_payment_method'
    ),

    array(
      'tag'         => 'sitename',
      'description' => __( 'Your site name', 'pl8app' ),
      'function'    => 'pl8app_email_tag_sitename'
    ),

    array(
      'tag'         => 'receipt_link',
      'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'pl8app' ),
      'function'    => 'pl8app_email_tag_receipt_link'
    ),

    array(
      'tag'         => 'order_id',
      'description' => __( 'The order# for this order', 'pl8app' ),
      'function'    => 'pl8app_email_tag_order_id'
    ),

  );

  // Apply pl8app_email_tags filter
  $email_tags = apply_filters( 'pl8app_email_tags', $email_tags );

  // Add email tags
  foreach ( $email_tags as $email_tag ) {
    pl8app_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
  }

}
add_action( 'pl8app_add_email_tags', 'pl8app_setup_email_tags' );

/**
 * Email template tag: menuitem_list
 * A list of menuitem purchased
 *
 * @param int $payment_id
 *
 * @return string menuitem_list
 */
function pl8app_email_tag_menuitem_list( $payment_id ) {

  $payment        = new pl8app_Payment( $payment_id );
  $payment_data   = pl8app_get_payment_meta( $payment_id );
  $cart_items     = pl8app_get_payment_meta_cart_details( $payment_id );
  $email          = pl8app_get_payment_user_email( $payment_id );
  $user           = pl8app_get_payment_meta_user_info( $payment_id );

  ob_start();

  if ( $cart_items ) :
    $total_price = 0;
    $show_names = apply_filters( 'pl8app_email_show_names', true );
    $cart_sub_total = 0;

  ?>
  <table id="email-table" class="display responsive no-wrap order-column" width="100%" style="border: 1px solid black; border-collapse: collapse;">
    <thead>
      <tr>
        <th style="border: 1px solid black; border-collapse: collapse;">
          <?php echo __( 'Menu Item Purchased', 'pl8app' ); ?>
        </th>
        <th class="center" style="text-align: center; border: 1px solid black; border-collapse: collapse;">
          <?php echo __( 'Quantity', 'pl8app' ); ?>
        </th>
        <th class="center" style="text-align: center; border: 1px solid black; border-collapse: collapse;">
          <?php echo __( 'Price', 'pl8app' ); ?>
        </th>
      </tr>
    </thead>

    <tbody>
      <?php
      foreach ( $cart_items as $item ) :

        $cart_sub_total += isset( $item['subtotal'] ) ? floatval( $item['subtotal'] ) : '0.00';

        if ( pl8app_use_skus() ) {
          $sku = pl8app_get_menuitem_sku( $item['id'] );
        }

        $price_id = pl8app_get_cart_item_price_id( $item );

         if ( $show_names ) {

          $menuitem_id = $item['id'];

          $title = get_the_title( $item['id'] );

          if ( pl8app_has_variable_prices( $menuitem_id ) ) {
            $price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : NULL;

            if ( ! is_null( $price_id ) ) {
              $variation_name = pl8app_get_price_option_name( $menuitem_id, $price_id );

              $title .= ' - ' . $variation_name;
            }

          }

        }

        $quantity = isset( $item['quantity'] ) ? $item['quantity'] : 1 ;
        $item_price = isset( $item['item_price'] ) ? $item['item_price']  : '0.00' ;

        $special_instruction = isset( $item['instruction'] ) ? $item['instruction'] : '';

        $addon_items_price = 0;

        ?>
        <tr>
          <td style="border: 1px solid black; border-collapse: collapse;">
            <?php echo $title; ?>
            <?php
            if ( isset( $item['addon_items'] ) ) {
                $item['addon_items'] = array_slice( $item['addon_items'], 2 );
                for ( $i = 0; $i < count( $item['addon_items'] ); $i++ ) {
                  ?>
                  <div style="margin-left:10px; margin-top:5px; font-size: smaller; color:#444;">
                    <?php echo $item['addon_items'][$i]['addon_item_name']; ?>
                  </div>
                  <?php
                }
            }

            if ( !empty( $special_instruction ) ) {
              echo '<p>' . sprintf( __( '<strong>Special Instruction</strong> : %s', 'pl8app' ), $special_instruction ) . '</p>';
            }

            ?>

          </td>

          <td class="center" style="border: 1px solid black; border-collapse: collapse; text-align: center;">
            <span><?php echo $quantity; ?></span>
          </td>

          <td class="center" style="border: 1px solid black; border-collapse: collapse; text-align: center;">
            <span><?php echo pl8app_currency_filter( pl8app_format_amount( $item_price ) ); ?></span>

            <?php
            $addon_items_price = 0;
            $addon_items =  isset( $item['addon_items'] ) ? $item['addon_items'] : array() ;
              foreach( $addon_items as $k => $addon_item ) {
                $addon_price = isset( $addon_item['price'] ) ? $addon_item['price'] : '0.00';
                $addon_items_price += floatval($addon_price);

            ?>
            <div style="margin: 0;font-size: 14px;"><?php echo pl8app_currency_filter( pl8app_format_amount( $addon_price ) ); ?></div>
               <?php
              }
            ?>
          </td>

        </tr>
        <?php

       endforeach;
       ?>
    </tbody>

    <tfoot>
      <tr>
        <td colspan="2" class="right" style="border: 1px solid black; border-collapse: collapse; text-align:right; padding-right: 10px;">
          <strong><?php echo __( 'Sub Total', 'pl8app' ); ?>:</strong>
          <br><br>

          <?php
          if ( ( $fees = pl8app_get_payment_fees( $payment_id, 'fee' ) ) ) :

            foreach( $fees as $fee ) : ?>
              <strong><?php echo esc_html( $fee['label'] ); ?>:</strong>
              <br><br>
            <?php endforeach; ?>

          <?php endif; ?>

          <?php if( pl8app_use_taxes() ) : ?>
            <strong><?php echo pl8app_get_tax_name(); ?>:</strong>
            <br><br>
          <?php endif; ?>

          <?php if( isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
            <strong><?php echo __( 'Discount(s)', 'pl8app' ); ?>:</strong>
            <br><br>
          <?php endif; ?>


          <strong><?php echo __( 'Total', 'pl8app' ); ?>:</strong>

        </td>

        <td class="right" style="border: 1px solid black; border-collapse: collapse; text-align:center;">
          <span><?php echo pl8app_currency_filter( pl8app_format_amount( $cart_sub_total ) ); ?></span>
          <br><br>

          <?php
          if ( ( $fees = pl8app_get_payment_fees( $payment_id, 'fee' ) ) ) :

            foreach( $fees as $fee ) : ?>
              <span><?php echo pl8app_currency_filter( pl8app_format_amount( $fee['amount'] ) ); ?></span>
              <br><br>
            <?php endforeach; ?>

          <?php endif; ?>

          <?php if( pl8app_use_taxes() ) : ?>
            <span><?php echo pl8app_payment_tax( $payment_id ); ?>  </span>
            <br><br>
          <?php endif; ?>

          <?php if( isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
            <strong><?php echo pl8app_get_discount_price_by_payment_id( $payment_id ); ?></strong>
            <br><br>
          <?php endif; ?>

          <span><?php echo pl8app_payment_amount( $payment_id ); ?></span>

        </td>
      </tr>

    </tfoot>

  </table>
  <?php
  endif;

  $output = ob_get_clean();

  return $output;

}

/**
 * Email template tag: name
 * The buyer's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function pl8app_email_tag_first_name( $payment_id ) {
    $payment_data = pl8app_get_payment_meta( $payment_id );
    $email_name   = pl8app_get_email_names( $payment_data['user_info'] );
    return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function pl8app_email_tag_fullname( $payment_id ) {
    $payment_data = pl8app_get_payment_meta( $payment_id );
    $email_name   = pl8app_get_email_names( $payment_data['user_info'] );
    return $email_name['fullname'];
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function pl8app_email_tag_username( $payment_id ) {
    $payment_data = pl8app_get_payment_meta( $payment_id );
    $email_name   = pl8app_get_email_names( $payment_data['user_info'] );
    return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function pl8app_email_tag_user_email( $payment_id ) {
    return pl8app_get_payment_user_email( $payment_id );
}

/**
 * Email template tag: delivery_address
 * The customer's delivery address
 *
 * @param int $payment_id
 *
 * @return string delivery_address
 */
function pl8app_email_tag_delivery_address( $payment_id ) {
  $delivery_address  = get_post_meta( $payment_id, '_pl8app_delivery_address', true );
    $address = $delivery_address['address'] . "\n";
    if( !empty( $delivery_address['flat'] ) )
        $address .=  $delivery_address['flat'] . "\n";
    $address .= $delivery_address['city'] . ' ' . $delivery_address['postcode'];
    return $address;
}


/**
 * Email template tag: billing_address
 * The buyer's billing address
 *
 * @param int $payment_id
 *
 * @return string billing_address
 */
function pl8app_email_tag_billing_address( $payment_id ) {

  $payment      = new pl8app_Payment( $payment_id );
  $address        = $payment->address;

  $return = '';
  if ( !empty( $address['line1'] ) ) {
    $return .= $address['line1'] . ' ';
  }

  if ( !empty( $address['line2'] ) ) {
    $return .= $address['line2'] . ' ';
  }

  if ( !empty( $address['city'] ) ) {
    $return .= $address['city'] . ' ';
  }

  if ( !empty( $address['zip'] ) ) {
    $return .= $address['zip'] . ' ';
  }

    if ( !empty( $address['state'] ) ) {
    $return .= $address['state'] . ' ';
  }

  if ( !empty( $address['country'] ) ) {
    $return .= "\n" . $address['country'];
  }

  return $return;
}

/**
 * Email template tag: date
 * Date of purchase
 *
 * @param int $payment_id
 *
 * @return string date
 */
function pl8app_email_tag_date( $payment_id ) {
    $payment_data = pl8app_get_payment_meta( $payment_id );
    return date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int $payment_id
 *
 * @return string subtotal
 */
function pl8app_email_tag_subtotal( $payment_id ) {
    $subtotal = pl8app_currency_filter( pl8app_format_amount( pl8app_get_payment_subtotal( $payment_id ) ) );
    return html_entity_decode( $subtotal, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: tax
 * The taxed amount of the purchase
 *
 * @param int $payment_id
 *
 * @return string tax
 */
function pl8app_email_tag_tax( $payment_id ) {
    $tax = pl8app_currency_filter( pl8app_format_amount( pl8app_get_payment_tax( $payment_id ) ) );
    return html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: price
 * The total price of the purchase
 *
 * @param int $payment_id
 *
 * @return string price
 */
function pl8app_email_tag_price( $payment_id ) {
    $price = pl8app_currency_filter( pl8app_format_amount( pl8app_get_payment_amount( $payment_id ) ) );
    return html_entity_decode( $price, ENT_COMPAT, 'UTF-8' );
}

/**
 * The {phone} email tag
 */
function pl8app_email_tag_phone( $payment_id ) {
    $payment_data  = pl8app_get_payment_meta( $payment_id );
  $phone = !empty( $payment_data['phone'] ) ? $payment_data['phone'] : '';
  return $phone;
}

/**
 * The {service_type} email tag
 */
function pl8app_email_tag_service_type( $payment_id ) {
  $service_type = pl8app_get_service_type( $payment_id );
  return pl8app_service_label( $service_type );
}

/**
 * The {service_time} email tag
 */
function pl8app_email_tag_service_time( $payment_id ) {
  $service_time = get_post_meta( $payment_id, '_pl8app_delivery_time', true );
  return $service_time;
}

/**
* The {order_note} email tag
*/
function pl8app_email_tag_order_note( $payment_id ) {
  $order_note = get_post_meta( $payment_id, '_pl8app_order_note', true );
  return $order_note;
}

/**
 * Email template tag: order_id
 * The unique  Order ID number for this order
 *
 * @param int $order_id
 *
 * @return int order_id
 */
function pl8app_email_tag_order_id( $order_id ) {
  return pl8app_get_payment_number( $order_id );
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function pl8app_email_tag_receipt_id( $payment_id ) {
    return pl8app_get_payment_key( $payment_id );
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function pl8app_email_tag_payment_method( $payment_id ) {
    return pl8app_get_gateway_checkout_label( pl8app_get_payment_gateway( $payment_id ) );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function pl8app_email_tag_sitename( $payment_id ) {
    return get_bloginfo( 'name' );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $int payment_id
 *
 * @return string receipt_link
 */
function pl8app_email_tag_receipt_link( $payment_id ) {
    return sprintf( __( '%1$sView it in your browser.%2$s', 'pl8app' ), '<a href="' . add_query_arg( array( 'payment_key' => pl8app_get_payment_key( $payment_id ), 'pl8app_action' => 'view_receipt' ), home_url() ) . '">', '</a>' );
}