<div class="pl8app-price-holder">
	<span class="price">
	
    <?php
  	
    global $post;
    global $pl8app_options;

    pl8app_price($post->ID);
    ?>

	</span>
	
  <div class="pl8app_menuitem_buy_button">
		<?php echo pl8app_get_purchase_link( array( 'menuitem_id' => get_the_ID() ) ); ?>
	</div>

</div>