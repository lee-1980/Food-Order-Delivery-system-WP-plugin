<?php
/**
 * This template is used to display the pl8app cart widget.
 */
$cart_items    	= pl8app_get_cart_contents();
$cart_quantity 	= pl8app_get_cart_quantity();
$display       	= $cart_quantity > 0 ? '' : 'style="display:none;"';

?>

<?php do_action( 'pl8app_before_cart' ); ?>

<div class="pl8app-col-lg-4 pl8app-col-md-4 pl8app-col-sm-12 pl8app-col-xs-12 pull-right pl8app-sidebar-cart item-cart sticky-sidebar">
	<div class="pl8app-mobile-cart-icons">
	  <i class='fa fa-shopping-cart' aria-hidden='true'></i>
	  <span class='pl8app-cart-badge pl8app-cart-quantity'>
	    <?php echo pl8app_get_cart_quantity(); ?>
	  </span>
	</div>

	<div class="pl8app-sidebar-main-wrap">
		<i class="fa fa-times close-cart-ic" aria-hidden="true"></i>
	    <div class="pl8app-sidebar-cart-wrap">
	    	<div class="pl8app item-order">
	    		<span><?php echo apply_filters('pl8app_cart_title', __( 'Your Order', 'pl8app' ) ); ?></span>
				<a class="pl8app-clear-cart" href="#" <?php echo $display ?> >
					<span class="cart-clear-icon">&times;</span>
					<span class="cart-clear-text"><?php echo __('Clear Order', 'pl8app') ?></span>
				</a>
			</div>
			<ul class="pl8app-cart">
				<?php if( $cart_items ) : ?>
					<?php foreach( $cart_items as $key => $item ) : ?>
						<?php echo pl8app_get_cart_item_template( $key, $item, false, $data_key = '' ); ?>
					<?php endforeach; ?>
					<?php pl8app_get_template_part( 'cart/checkout' ); ?>
				<?php else : ?>
					<?php pl8app_get_template_part( 'cart/empty' ); ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php do_action( 'pl8app_after_cart' ); ?>
