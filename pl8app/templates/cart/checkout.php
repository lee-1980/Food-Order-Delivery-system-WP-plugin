<?php

$cart_quantity = pl8app_get_cart_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
?>

<li class="cart_item pl8app-cart-meta pl8app_subtotal"><?php echo __( 'Subtotal:', 'pl8app' ). " <span class='cart-subtotal'>" . pl8app_currency_filter( pl8app_format_amount( pl8app_get_cart_subtotal() ) ); ?></span></li>

<?php do_action( 'pl8app_cart_line_item' ); ?>

<?php if ( pl8app_use_taxes() ) : ?>
    <li class="cart_item pl8app-cart-meta pl8app_cart_tax">
        <?php echo pl8app_get_cart_tax_summary(); ?>
    </li>
<?php endif; ?>

<li class="cart_item pl8app-cart-meta pl8app_total"><?php _e( 'Total (', 'pl8app' ); ?><span class="pl8app-cart-quantity" <?php echo $display; ?> ><?php echo $cart_quantity; ?></span><?php _e( ' Items)', 'pl8app' ); ?><span class="cart-total"><?php echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_cart_total() ) ); ?></span></li>


<!-- Service Type and Service Time -->
  <li class="delivery-items-options">
    <?php echo get_delivery_options( true ); ?>
  </li>

<?php if( apply_filters( 'pl8app_show_checkout_button', true ) ) : ?>
<li class="cart_item pl8app_checkout">
  <a data-url="<?php echo pl8app_get_checkout_uri(); ?>" href="#"> <?php
    $confirm_order_text = apply_filters( 'pla_confirm_order_text', _e( 'Checkout', 'pl8app' ) );
    echo $confirm_order_text; ?></a>
</li>
<?php endif; ?>
<?php do_action( 'pl8app_after_checkout_button' ); ?>
