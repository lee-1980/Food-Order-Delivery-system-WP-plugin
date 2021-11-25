<?php
$cart_quantity = pl8app_get_cart_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
?>
<li class="cart_item empty"><?php echo pl8app_empty_cart_message(); ?></li>

<?php if ( pl8app_use_taxes() ) : ?>
<li class="cart_item pl8app-cart-meta pl8app_subtotal" style="display:none;"><?php echo __( 'Subtotal:', 'pl8app' ). " <span class='cart-subtotal'>" . pl8app_currency_filter( pl8app_format_amount( pl8app_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item pl8app-cart-meta pl8app_cart_tax" style="display:none;"><?php _e( 'Estimated Tax:', 'pl8app' ); ?> <span class="cart-tax"><?php echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item pl8app-cart-meta pl8app_total" style="display:none;"><?php _e( 'Total (', 'pl8app' ); ?><span class="pl8app-cart-quantity" <?php echo $display; ?>><?php echo $cart_quantity; ?></span> <?php _e( ' Items)', 'pl8app' ); ?><span class="cart-total"><?php echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_cart_total() ) ); ?></span></li>
<li class="delivery-items-options">
	<?php echo get_delivery_options( true ); ?>
</li>
<li class="cart_item pl8app_checkout" style="display:none;"><a href="<?php echo pl8app_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'pl8app' ); ?></a></li>
