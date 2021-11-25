<?php
/**
 *  This template is used to display the Checkout page when items are in the cart
 */

global $post;

?>

<table id="pl8app_checkout_cart " class="pl8app-cart ajaxed">

  <thead>
    <th colspan="3">
      <div class="pl8app item-order">
        <h6><?php echo apply_filters( 'pl8app_cart_title', __('Your Order', 'pl8app' ) ); ?></h6>
      </div>
    </th>
  </thead>

  <tbody>

    <?php $cart_items = pl8app_get_cart_contents(); ?>

    <?php do_action( 'pl8app_cart_items_before' ); ?>

    <?php if ( $cart_items ) : ?>

      <?php foreach ( $cart_items as $key => $item ) :

        $cart_list_item   = pl8app_get_cart_items_by_key($key);
        $cart_item_price  = pl8app_get_cart_item_by_price($key);
        $get_item_qty     = pl8app_get_item_qty_by_key($key);

        ?>

        <tr class="pl8app_cart_item" id="pl8app_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-menuitem-id="<?php echo esc_attr( $item['id'] ); ?>">

          <?php do_action( 'pl8app_checkout_table_body_first', $item ); ?>

          <td class="pl8app_cart_item_name" colspan="3">

            <?php

            $item_title = pl8app_get_cart_item_name( $item );
            $item_options = isset( $item['options'] ) ? $item['options'] : array();
            $item_price = pl8app_cart_item_price( $item['id'], $item_options );

            if ( pl8app_has_variable_prices( $item['id'] ) ) {
              $price_id = !empty( $item['price_id'] ) ? $item['price_id'] : 0;
              $item_price = pl8app_get_price_option_amount( $item['id'], $price_id );
              $item_price = esc_html( pl8app_currency_filter( pl8app_format_amount( $item_price ) ) );
            } ?>

            <div class="pl8app-checkout-item-row">

              <!-- Item Quantity Wrap starts Here -->
              <span class="pl8app_checkout_cart_item_qty"><?php echo $get_item_qty; ?>&nbsp;x&nbsp;</span>

              <!-- Item Name Here -->
              <span class="pl8app-cart-item-title pl8app-cart-item pl8app_checkout_cart_item_title"><?php echo esc_html( $item_title ); ?></span>

              <!-- Item Price Wrap starts Here -->
              <span class="cart-item-quantity-wrap"><?php echo $item_price; ?></span>

              <!-- Item TAX Class Name Here-->
              <?php if( pl8app_use_taxes() ) : ?>
              <?php $tax_name = get_cart_item_tax_name($item); ?>
              <span class="pl8app-cart-item-tax-class"><p style="width: 100px;"><?php echo $tax_name; ?></p></span>
              <?php endif; ?>

              <?php if( is_array( $cart_list_item ) ) {

                foreach( $cart_list_item as $k => $val ) {

                  if( isset($val['addon_item_name']) && isset($val['price']) ) { ?>

                    <!-- Item Row Starts Here -->
                    <div class="pl8app-checkout-addon-row">

                      <!-- Item Title -->
                      <span class="pl8app-cart-item-title"><?php echo $val['addon_item_name']; ?></span>

                      <!-- Item Quanity Starts Here -->
                      <span class="cart-item-quantity-wrap">
                        <span class="pl8app_checkout_cart_item_qty"><?php echo esc_html( pl8app_currency_filter( pl8app_format_amount( $val['price'] ) ) )?></span>
                      </span>
                      <!-- Item Quanity Ends Here -->

                      <!--Item Action Here -->
                      <span class="cart-action-wrap addon-items">
                        <a class="pl8app_cart_remove_item_btn" href="<?php echo esc_url( pl8app_remove_item_url( $key ) ); ?>"></a>
                      </span>
                    </div>
                    <!-- Item Row Ends Here -->

                  <?php }
                }
              }

              if( isset($item['instruction']) && !empty($item['instruction']) ) { ?>
                <div class="special-instruction-wrapper">
                  <span class="restro-instruction"><?php echo $item['instruction']; ?></span>
                </div>
              <?php }

              /**
               * Runs after the item in cart's title is echoed
               * @since 1.0.0
               *
               * @param array $item Cart Item
               * @param int $key Cart key
               */
              do_action( 'pl8app_checkout_cart_item_title_after', $item, $key ); ?>

              <!-- Item Action Here -->
              <?php do_action( 'pl8app_cart_actions', $item, $key ); ?>

              <a class="pl8app_cart_remove_item_btn" href="<?php echo esc_url( pl8app_remove_item_url( $key ) ); ?>"><?php echo __( 'Remove', 'pl8app' ) ?></a>

            </div>

          </td>

        <?php do_action( 'pl8app_checkout_table_body_last', $item ); ?>

        </tr>

      <?php endforeach; ?>

    <?php endif; ?>

    <?php do_action( 'pl8app_cart_items_middle' ); ?>

    <tr>
      <th colspan="3" class="pl8app_get_subtotal">
        <?php _e( 'Subtotal', 'pl8app' ); ?>:&nbsp;<span class="pl8app_cart_subtotal_amount pull-right"><?php echo pl8app_cart_subtotal(); ?></span>
      </th>
    </tr>

    <?php do_action( 'pl8app_cart_items_after' ); ?>

  </tbody>

  <tfoot>

    <?php if( pl8app_use_taxes() && ! pl8app_prices_include_tax() ) : ?>
      <tr class="pl8app_cart_footer_row pl8app_cart_subtotal_row"<?php if ( ! pl8app_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
        <?php do_action( 'pl8app_checkout_table_subtotal_first' ); ?>
        <th colspan="<?php echo pl8app_checkout_cart_columns(); ?>" class="pl8app_cart_subtotal">
        </th>
        <?php do_action( 'pl8app_checkout_table_subtotal_last' ); ?>
      </tr>
    <?php endif; ?>

    <tr class="pl8app_cart_footer_row pl8app_cart_discount_row" <?php if( ! pl8app_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
      <?php do_action( 'pl8app_checkout_table_discount_first' ); ?>
      <th colspan="<?php echo pl8app_checkout_cart_columns(); ?>" class="pl8app_cart_discount">
        <?php pl8app_cart_discounts_html(); ?>
      </th>
      <?php do_action( 'pl8app_checkout_table_discount_last' ); ?>
    </tr>

    <?php if( pl8app_use_taxes() ) : ?>
      <tr class="pl8app_cart_footer_row test pl8app_cart_tax_row"<?php if( ! pl8app_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
        <?php do_action( 'pl8app_checkout_table_tax_first' ); ?>
        <th colspan="<?php echo pl8app_checkout_cart_columns(); ?>" class="pl8app_cart_tax">
          <?PHP echo pl8app_get_cart_tax_summary();?>
        </th>
        <?php do_action( 'pl8app_checkout_table_tax_last' ); ?>
      </tr>
    <?php endif; ?>

    <!-- Show any cart fees, both positive and negative fees -->
    <?php if( pl8app_cart_has_fees() ) : ?>
      <?php foreach( pl8app_get_cart_fees() as $fee_id => $fee ) : ?>
        <tr class="pl8app_cart_fee" id="pl8app_cart_fee_<?php echo $fee_id; ?>">

          <?php do_action( 'pl8app_cart_fee_rows_before', $fee_id, $fee ); ?>

          <th colspan="3" class="pl8app_cart_fee_label">
            <?php echo esc_html( $fee['label'] ); ?>
            <span style="float:right">
              <?php echo esc_html( pl8app_currency_filter( pl8app_format_amount( $fee['amount'] ) ) ); ?>

              <?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
              <a href="<?php echo esc_url( pl8app_remove_cart_fee_url( $fee_id ) ); ?>"><?php _e( 'Remove', 'pl8app' ); ?></a>
            <?php endif; ?>

            </span>
          </th>

          <?php do_action( 'pl8app_cart_fee_rows_after', $fee_id, $fee ); ?>

        </tr>
      <?php endforeach; ?>
    <?php endif; ?>

    <tr class="pl8app_cart_footer_row">
      <?php do_action( 'pl8app_checkout_table_footer_first' ); ?>
      <th colspan="<?php echo pl8app_checkout_cart_columns(); ?>" class="pl8app_cart_total"><?php _e( 'Total', 'pl8app' ); ?>: <span class="pl8app_cart_amount pull-right" data-subtotal="<?php echo pl8app_get_cart_subtotal(); ?>" data-total="<?php echo pl8app_get_cart_total(); ?>"><?php pl8app_cart_total(); ?></span>
        <?php echo get_delivery_options( true ); ?>
      </th>
      <?php do_action( 'pl8app_checkout_table_footer_last' ); ?>
    </tr>

    <?php if( has_action( 'pl8app_cart_footer_buttons' ) ) : ?>
      <tr class="pl8app_cart_footer_row<?php if ( pl8app_is_cart_saving_disabled() ) { echo ' pl8app-no-js'; } ?>">
        <th colspan="<?php echo pl8app_checkout_cart_columns(); ?>">
          <?php do_action( 'pl8app_cart_footer_buttons' ); ?>
        </th>
      </tr>
    <?php endif; ?>

  </tfoot>
</table>