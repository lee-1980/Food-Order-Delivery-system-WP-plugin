<?php
/**
 * This template is used to display the purchase summary with [pl8app_receipt]
 */

global $pl8app_receipt_args;

$payment = get_post( $pl8app_receipt_args['id'] );

if( empty( $payment ) ) : ?>

	<div class="pl8app_errors pl8app-alert pl8app-alert-error">
		<?php _e( 'The specified receipt ID appears to be invalid', 'pl8app' ); ?>
	</div> <?php

    return;
endif;

$meta           = pl8app_get_payment_meta( $payment->ID );
$service_time 	= pl8app_get_payment_meta( $payment->ID, '_pl8app_delivery_time' );
$service_date   = pl8app_get_payment_meta( $payment->ID, '_pl8app_delivery_date', true );
$cart           = pl8app_get_payment_meta_cart_details( $payment->ID, true );
$discount       = pl8app_get_discount_price_by_payment_id( $payment->ID );
$user           = pl8app_get_payment_meta_user_info( $payment->ID );
$email          = pl8app_get_payment_user_email( $payment->ID );
$payment_status = pl8app_get_payment_status( $payment, true );
$order_status 	= pl8app_get_order_status( $payment->ID );
$order_note	  	= pl8app_get_payment_meta( $payment->ID, '_pl8app_order_note', true );
$service_type 	= pl8app_get_payment_meta( $payment->ID, '_pl8app_delivery_type' );
$service_label 	= pl8app_service_label( $service_type );
$phone          = !empty( $meta['phone'] ) ? $meta['phone'] : ( !empty( $user['phone'] ) ? $user['phone'] : '' );
$firstname      = isset( $user['first_name'] ) ? $user['first_name'] : '';
$lastname       = isset( $user['last_name'] ) ? $user['last_name'] : '';
$address_info   = get_post_meta( $payment->ID, '_pl8app_delivery_address', true );
$address        = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
$address	     .= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
$address	     .= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
$address	     .= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';
$tax_content = '';
$taxes = array();

do_action( 'pl8app_before_payment_receipt', $payment, $pl8app_receipt_args );

?>

<div class="container-fluid pl8app-header">
	<div class="pl8app-row pl8app-customer-receipt">
		<div class="pl8app-col-sm-12">
			<p class="pl8app-center pl8app-tick"></p>
	    <h3 class="pl8app-center pl8app-order-head-text"><?php _e( "We've received your order", 'pl8app' ); ?></h3>
	    <h4 class="pl8app-center pl8app-order-no-text"><?php _e( 'Order: ', 'pl8app');  ?> <span>#<?php echo pl8app_get_payment_number( $payment->ID ); ?></span></h4>
	    <p class="pl8app-center pl8app-order-message-text">
	    	<?php _e( 'A copy of your receipt has been sent to', 'pl8app' ); ?>
	    	 <span><?php echo $email; ?></span></p>
		</div>
	</div>
</div>

<div id="pl8app-order-details">
  <div class="pl8app-row">
    <div class="pl8app-col-lg-6 pl8app-col-md-6 pl8app-col-sm-12">
      <div class="pl8app-order-section">
        <h3><?php
        /* translators: %s: Service type name */
        echo sprintf( __( '%s details', 'pl8app' ), ucfirst( $service_label ) );?></h3>
        <div class="pl8app-detils-content">
          <p><?php _e( 'Name', 'pl8app' ); ?> : <span><?php echo $firstname . ' ' . $lastname; ?></span></p>
            <p><?php _e( 'Phone Number', 'pl8app' ); ?> : <span><?php echo $phone; ?></span></p>
            <p><?php
            /* translators: %s : Service type name */
            echo sprintf( __( '%s Date', 'pl8app' ), ucfirst( $service_label ) );?> : <span><?php echo pl8app_local_date( $service_date ); ?></span></p>
            <p><?php
            /* translators: %s : Service time */
            echo sprintf( __( '%s Time', 'pl8app' ), ucfirst( $service_label ) );?> : <span><?php echo $service_time; ?></span>
          </p>
        </div>
      </div>

      <?php if( $service_type == 'delivery' ) : ?>
      <div class="pl8app-order-section pl8app-delivery-address">
        <h3><?php _e( 'Address', 'pl8app' ); ?></h3>
        <div class="pl8app-detils-content"><?php echo apply_filters( 'pl8app_delivery_address', $address ); ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div class="pl8app-col-lg-6 pl8app-col-md-6 pl8app-col-sm-12">
      <div class="pl8app-order-section">
        <?php if ( filter_var( $pl8app_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
          <h3><?php _e( 'Order details', 'pl8app' ); ?></h3>
          <div class="pl8app-detils-content">
            <p><?php _e( 'Order Status', 'pl8app' ); ?> : <span><?php echo pl8app_get_order_status_label( $order_status ); ?></span></p>
            <p><?php _e( 'Order Date', 'pl8app' ); ?> : <span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></span></p>
          </div>
        <?php endif; ?>
      </div>

      <div class="pl8app-order-section">
        <h3><?php _e( 'Payment Details', 'pl8app' ); ?></h3>
        <div class="pl8app-detils-content">
          <p><?php _e( 'Payment Method', 'pl8app' ); ?> : <span><?php echo pl8app_get_gateway_checkout_label( pl8app_get_payment_gateway( $payment->ID ) ); ?></span></p>
          <p><?php _e( 'Payment Status', 'pl8app' ); ?> : <span><?php echo $payment_status; ?></span></p>
        </div>
      </div>
    </div>
    <div class="clear"></div>
  </div>

  <?php do_action( 'pl8app_after_order_details', $payment, $pl8app_receipt_args ); ?>
</div>

<div class="pl8app-row">
  <div class="pl8app-col-sm-12">
    <div class="pl8app-order-summary-main">
      <h3><?php _e( 'Order summary', 'pl8app' ); ?></h3>
      <table id="pl8app-order-summary" width="100%">
        <thead>
          <tr>
            <th class="pl8app-tb-left"><?php _e( 'Item', 'pl8app' ); ?></th>
            <th class="pl8app-center"><?php _e( 'Quantity', 'pl8app' ); ?></th>
            <th class="pl8app-tb-right"><?php _e( 'Amount', 'pl8app' ); ?></th>
          </tr>
        </thead>

        <tbody>
        <?php
        if ( $cart ) :
          foreach ( $cart as $key => $item ) :

            if( ! apply_filters( 'pl8app_user_can_view_receipt_item', true, $item ) ) :
              continue;
            endif;

              if (isset($item['tax_name'])) {
                  if(isset($taxes[$item['tax_name']])){
                      $taxes[$item['tax_name']]['tax'] += $item['tax'];
                      $taxes[$item['tax_name']]['subtotal'] += $item['subtotal'];
                  }
                  else{
                      $taxes[$item['tax_name']]['tax_name'] = $item['tax_name'];
                      $taxes[$item['tax_name']]['tax_rate'] = $item['tax_rate'];
                      $taxes[$item['tax_name']]['tax_desc'] = $item['tax_desc'];
                      $taxes[$item['tax_name']]['tax'] = $item['tax'];
                      $taxes[$item['tax_name']]['subtotal'] = $item['subtotal'];
                  }
              }


            if ( empty( $item['in_bundle'] ) ) : ?>

            <tr>
              <td>
              <?php
              $price_id = pl8app_get_cart_item_price_id( $item );
              $special_instruction = isset( $item['instruction'] ) ? $item['instruction'] : '';
              ?>

              <div class="pl8app_purchase_receipt_product_name">

              <?php echo pl8app_get_cart_item_name( $item ); ?>

              <?php
              if ( is_array( $item['item_number']['options'] ) && !empty($item['item_number']['options'] ) ) {

                foreach( $item['item_number']['options'] as $k => $v ) {
                  if( !empty($v['addon_item_name']) ) { ?>
                    <br/>&nbsp;&nbsp;
                  <small class="pl8app-receipt-addon-item"><?php echo $v['addon_item_name']; ?> (<?php echo pl8app_currency_filter(pl8app_format_amount($v['price'])); ?>)</small>
                <?php
                    }
                }
              }
              ?>
              <br/><br/>

              <?php if ( !empty( $special_instruction ) ) : ?>
                <span> <?php _e( 'Special Instructions', 'pl8app'); ?> : </span>
                <small><?php echo $special_instruction; ?></small>
              <?php endif; ?>
            </div>
          </td>

          <td class="pl8app-center"><?php echo $item['quantity']; ?></td>

          <td class="pl8app-tb-right">
            <?php if( empty( $item['in_bundle'] ) ) :  ?>
              <?php echo pl8app_currency_filter( pl8app_format_amount( $item[ 'subtotal' ] ) ); ?>
            <?php endif; ?>
          </td>
                <?php if (isset($item['tax_name']) &&  pl8app_use_taxes()) : ?>
                    <td class="pl8app-tb-left"><?php echo $item['tax_name']; ?></td>
                <?php endif ?>
        </tr>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="pl8app_cart_footer_row pl8app_cart_subtotal_row">
          <td colspan="2" class="pl8app-tb-right"><?php _e( 'Subtotal', 'pl8app' ); ?>:</td>
          <td class="pl8app-tb-right pl8app-amount-right">
            <?php echo pl8app_payment_subtotal( $payment->ID ); ?>
          </td>
        </tr>

        <?php
        if ( ( $fees = pl8app_get_payment_fees( $payment->ID, 'fee' ) ) ) :
          foreach( $fees as $fee ) : ?>
            <tr class="pl8app_cart_footer_row pl8app_cart_delivery_row">
              <td colspan="2" class="pl8app-tb-right"><?php echo esc_html( $fee['label'] ); ?>:</td>
              <td class="pl8app-tb-right pl8app-amount-right"><?php echo pl8app_currency_filter( pl8app_format_amount( $fee['amount'] ) ); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if( pl8app_use_taxes() ) : ?>
        <tr class="pl8app_cart_footer_row kk pl8app_cart_tax_row">
          <td colspan="2" class="pl8app-tb-right">
            <?php echo _e('Tax summary'); ?>:
          </td>
          <td class="pl8app-tb-right pl8app-amount-right">
              <div>
                  <ul class="item-tax-wrapp">
                      <?php
                      if(count($taxes) > 0) :
                          ob_start();
                          ?>
                          <li class="pl8app-cart-item-tax">
                            <span class="pl8app-cart-item-title">
                                <span>
                                <span class="pl8app_tax_name">
                                    <b></b>
                                </span>
                                <span class="pl8app_tax_desc">
                                   <b></b>
                                </span>&nbsp;
                                <span class="pl8app_tax_rate">
                                   <b>RATE</b>
                                </span>&nbsp;
                                <span class="pl8app_item_subtotal">
                                    <b>NET</b>
                                </span>
                                </span>
                            </span>
                              <span class="addon-item-price cart-item-quantity-wrap">
			                <span class="pl8app-cart-item-price qty-class">
                                <b>TAX</b>
                            </span>
			              </span>
                          </li>
                          <?php
                          foreach ($taxes as $key => $item) :
                      ?>
                      <li class="pl8app-cart-item-tax">
                            <span class="pl8app-cart-item-title">
                                <span>
                                <span class="pl8app_tax_name">
                                    <?PHP echo $item['tax_name']; ?>
                                </span>
                                <span class="pl8app_tax_desc">
                                    <?PHP echo $item['tax_desc']; ?>
                                </span>&nbsp;
                                <span class="pl8app_tax_rate">
                                    <?PHP echo $item['tax_rate']; ?>%
                                </span>&nbsp;
                                <span class="pl8app_item_subtotal">
                                    (<?php echo pl8app_currency_filter(pl8app_format_amount($item['subtotal'])); ?>)
                                </span>
                                </span>
                            </span>
                          <span class="addon-item-price cart-item-quantity-wrap">
			                <span class="pl8app-cart-item-price qty-class">
                                <?php echo pl8app_currency_filter(pl8app_format_amount($item['tax'])); ?>
                            </span>
			              </span>
                      </li>
                      <?php
                      endforeach;
                          $tax_content .= ob_get_clean();
                      endif;
                      echo $tax_content; ?>
                  </ul>
              </div>
          </td>
        </tr>
        <?php endif; ?>

        <?php if ( filter_var( $pl8app_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) && isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
          <tr class="pl8app_cart_footer_row pl8app_cart_discount_row">
            <td colspan="2"class="pl8app-tb-right"><?php _e( 'Coupon', 'pl8app' ); ?>:</td>
            <td class="pl8app-tb-right pl8app-amount-right"><?php echo $discount; ?></td>
          </tr>
        <?php endif; ?>

        <?php if ( filter_var( $pl8app_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
          <tr class="pl8app_cart_footer_row pl8app_cart_total_row">
            <td colspan="2" class="pl8app-tb-right pl8app-bold"><?php _e( 'Total', 'pl8app' ); ?>:</td>
            <td class="pl8app-tb-right pl8app-amount-right pl8app-bold"><?php echo pl8app_payment_amount( $payment->ID ); ?></td>
          </tr>
        <?php endif; ?>
        </tfoot>
      </table>

      <?php do_action( 'pl8app_payment_receipt_after_table', $payment, $pl8app_receipt_args ); ?>
      </div>
    </div>
  </div>

<?php do_action( 'pl8app_after_payment_receipt', $payment, $pl8app_receipt_args ); ?>