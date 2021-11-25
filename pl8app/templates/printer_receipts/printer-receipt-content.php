<?php

$payment        = new pl8app_Payment( $data );
$cart_items     = $payment->cart_details;
$payment_amount = pl8app_payment_amount( $data );
$payment_fees   = $payment->fees;

$subtotal = pl8app_payment_subtotal( $payment->ID );
$discounts = 0;
$tax_content = '';
$taxes = array();
$taxes_total_value = 0;


//VAT Registeration Number
$printer_settings = get_option( 'pl8app_settings', array() );

if($printer_settings['enable_taxes']){
    $footer_vat_registeration_number = '
           <tr>
           <td width="256">'. __('<span style="font-size: 16px;padding: 0px;"><strong>VAT Registration Number:</strong></span>') .'<span style="font-size: 16px;float: right;">'. $printer_settings['pl8app_tax_vat_number'].'</span></td>
           </tr>';
}
else{
    $footer_vat_registeration_number = '';
}

?>

<table class="page_items">
  <?php if ( is_array( $cart_items ) && !empty( $cart_items ) ) :

      $reorder_cart_items = array();

      foreach($cart_items as $key => $item ) :
          $item_id = isset( $item['id'] ) ? $item['id'] : '';
          $menuitem = new pl8app_Menuitem($item_id);
          $categories = $menuitem->get_menu_categories();
          $category = is_array($categories)&&count($categories) > 0? $categories[0]: '';
          if(!empty($category)){
              $tax_position = get_term_meta($category, 'tax_position', true);
              $item_data = $item;
              $item_data['tax_position'] = $tax_position;
              $reorder_cart_items[$key] = $item_data;
          }
      endforeach;

      usort($reorder_cart_items, function($a, $b){
          return $a['tax_position'] - $b['tax_position'];
      });

      ?>

    <tr>
      <td width="202" style="padding: 6px 0;"><strong><?php echo apply_filters( 'printer_product_column', esc_html_e( 'Particulars', 'pl8app-printer' ) ); ?></strong></td>
      <td width="32" style="padding: 6px 0; text-align: right;"><strong><?php echo apply_filters( 'printer_price_column', esc_html_e( 'Price', 'pl8app-printer' ) ); ?></strong></td>
    </tr>

    <?php foreach( $reorder_cart_items as $key => $item ) : ?>

      <?php if ( isset( $item['name'] ) ) :
      $item_name = isset( $item['name'] ) ? pl8app_get_cart_item_name($item) : '';
      $item_qty = isset( $item['item_number']['quantity'] ) ? $item['item_number']['quantity'] : '';
      $item_id = isset( $item['id'] ) ? $item['id'] : '';
      $item_price = $item['subtotal'];

      $discounts = $discounts + $item['discount'];

          if (isset($item['tax_name'])) {
              if(isset($taxes[$item['tax_name']])){
                  $taxes[$item['tax_name']]['tax'] += isset($item['tax'])?$item['tax']:0;
                  $taxes[$item['tax_name']]['subtotal'] += isset($item['subtotal'])?$item['subtotal']:0;
              }
              else{
                  $taxes[$item['tax_name']]['tax_name'] = isset($item['tax_name'])?$item['tax_name']:'??';
                  $taxes[$item['tax_name']]['tax_rate'] = isset($item['tax_rate'])?$item['tax_rate']:'??';
                  $taxes[$item['tax_name']]['tax_desc'] = isset($item['tax_desc'])?$item['tax_desc']:'??';
                  $taxes[$item['tax_name']]['tax'] = isset($item['tax'])?$item['tax']:0;
                  $taxes[$item['tax_name']]['subtotal'] = isset($item['subtotal'])?$item['subtotal']:0;
              }
              $taxes_total_value += (float) $item['tax'];
          }
      ?>

      <tr>
        <td width="202" style="padding: 4px 0; vertical-align: top;"><?php echo $item_qty; ?> &times; <strong><?php echo $item_name; ?></strong></td>
        <td width="32" style="padding: 4px 0; text-align: right; font-weight: bold; vertical-align: top;"><?php echo pl8app_currency_filter( pl8app_format_amount( $item_price ) ); ?></td>
      </tr>
      <?php if ( isset($item['item_number']['options']) && is_array( $item['item_number']['options'] )  ) : ?>
        <?php foreach( $item['item_number']['options'] as $key => $addon_item ): ?>
          <tr>
            <?php if ( isset( $addon_item['addon_item_name'] ) ) :
              $addon_item_name = $addon_item['addon_item_name'];
              $addon_qty = (int) $item['quantity'];
              $addon_subtotal = (float) $addon_item['price'] * $addon_qty;
              $subtotal = (float)$subtotal + $addon_subtotal;
              $addon_price = pl8app_currency_filter( pl8app_format_amount( $addon_subtotal ) );
              ?>
              <td style="padding: 4px 0 0 10px; vertical-align: top; font-size: 12pt;"> - <?php echo $addon_item_name; ?></td>
              <td style="padding: 4px 0; text-align: right; font-weight: bold; vertical-align: top;"><?php echo $addon_price; ?></td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php if( $item['instruction'] != '' ): ?>
          <tr>
            <td colspan="3" style="padding: 6px 0;"><b><?php echo apply_filters( 'printer_item_note_label', esc_html_e( 'Customer Note:&nbsp;', 'pl8app-printer' ) ); ?></b><?php echo $item['instruction']; ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</table>

<table class="page_totals">
  <tr>
    <td width="130" style="text-align: left;"><?php echo apply_filters( 'printer_subtotal_amount', esc_html_e( 'Subtotal', 'pl8app-printer' ) ); ?>:</td>
    <td width="25" style="text-align: right; font-size: 16px;"><b><?php echo pl8app_currency_filter( pl8app_format_amount( $subtotal ) ); ?></b></td>
  </tr>
  <?php if( $discounts > 0 ) : ?>
    <tr>
      <td width="130" style="text-align: left;"><?php echo apply_filters( 'printer_discount_price', esc_html_e( 'Discounts', 'pl8app-printer' ) ); ?>:</td>
      <td width="25" style="text-align: right; font-size: 16px;"><b><?php echo pl8app_currency_filter( pl8app_format_amount( $discounts ) ); ?></b></td>
    </tr>
  <?php endif; ?>
  <?php if( count($taxes) > 0 && $taxes_total_value > 0) : ?>
      <tr>
          <td width="255" colspan="2"><hr></td>
      </tr>
    <?php echo $footer_vat_registeration_number; ?>
    <tr>
        <td width="10" style="text-align: left;"><?php echo apply_filters( 'printer_tax_price', esc_html_e( 'Taxes', 'pl8app-printer' ) ); ?>:</td>
    </tr>
      <?php
          ob_start();
          ?>
          <tr>
              <td width="130" style="text-align: left; font-size: 16px;">
                  <span><b>RATE</b></span> |
                  <span><b>NET</b></span>
              </td>
              <td width="25" style="text-align: right; font-size: 16px;">
                  <b>VAT</b>
              </td>
          </tr>
          <?php
          foreach ($taxes as $key => $item) :
              ?>
              <tr>
                  <td width="130" style="text-align: left; font-size: 14px;">
                      <span>
                      <?PHP echo $item['tax_name']; ?> &nbsp;
                      <?PHP echo $item['tax_desc']; ?> &nbsp;
                      <?PHP echo $item['tax_rate']; ?>%
                      </span>|
                      <span>
                          (<?php echo pl8app_currency_filter(pl8app_format_amount($item['subtotal'])); ?>)
                      </span>
                  </td>
                  <td width="25" style="text-align: right; font-size: 16px;">
                      <b><?php echo pl8app_currency_filter(pl8app_format_amount($item['tax'])); ?></b>
                  </td>
              </tr>
          <?php
          endforeach;
          $tax_content .= ob_get_clean();

      echo $tax_content; ?>

  <?php
  endif; ?>
  <?php if ( ! empty( $payment_fees ) ) : ?>
    <?php foreach( $payment_fees as $fee ) : ?>
      <tr>
        <td width="130" style="text-align: left;"><?php echo $fee['label']; ?>:</td>
        <td width="25" style="text-align: right; font-size: 16px;"><b><?php echo pl8app_currency_filter( pl8app_format_amount( $fee['amount'] ) ); ?></b></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  <tr>
    <td width="255" colspan="2"><hr></td>
  </tr>
  <tr>
    <td width="130" style="text-align: left;"><?php echo apply_filters( 'printer_total_price', esc_html_e( 'Total', 'pl8app-printer' ) ); ?>:</td>
    <td width="25" style="text-align: right; font-size: 16px;"><b><?php echo $payment_amount; ?></b></td>
  </tr>
  <tr>
    <td width="275" colspan="2"><hr></td>
  </tr>
</table>