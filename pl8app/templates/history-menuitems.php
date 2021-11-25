<?php if( ! empty( $_GET['pl8app-verify-success'] ) ) : ?>
	<p class="pl8app-account-verified pl8app_success">
	<?php _e( 'Your account has been successfully verified!', 'pl8app' ); ?>
	</p>
<?php
endif;
/**
 * This template is used to display the menuitem history of the current user.
 */
$purchases = pl8app_get_users_orders( get_current_user_id(), 20, true, 'any' );
if ( $purchases ) :
	do_action( 'pl8app_before_menuitem_history' ); ?>
	<table id="pl8app_user_history" class="pl8app-table">
		<thead>
			<tr class="pl8app_menuitem_history_row">
				<?php do_action( 'pl8app_menuitem_history_header_start' ); ?>
				<th class="pl8app_menuitem_menuitem_name"><?php _e( 'Menu Item Name', 'pl8app' ); ?></th>
				<?php do_action( 'pl8app_menuitem_history_header_end' ); ?>
			</tr>
		</thead>
		<?php foreach ( $purchases as $payment ) :
			$menuitems      = pl8app_get_payment_meta_cart_details( $payment->ID, true );
			$purchase_data  = pl8app_get_payment_meta( $payment->ID );
			$email          = pl8app_get_payment_user_email( $payment->ID );

			if ( $menuitems ) :
				foreach ( $menuitems as $menuitem ) : ?>

					<tr class="pl8app_menuitem_history_row">
						<?php
						$price_id       = pl8app_get_cart_item_price_id( $menuitem );
						$name           = $menuitem['name'];

						// Retrieve and append the price option name
						if ( ! empty( $price_id ) && 0 !== $price_id ) {
							$name .= ' - ' . pl8app_get_price_option_name( $menuitem['id'], $price_id, $payment->ID );
						}

						do_action( 'pl8app_menuitem_history_row_start', $payment->ID, $menuitem['id'] );
						?>
						<td class="pl8app_menuitem_menuitem_name"><?php echo esc_html( $name ); ?></td>
						<?php

						do_action( 'pl8app_menuitem_history_row_end', $payment->ID, $menuitem['id'] );
						?>
					</tr>
					<?php
				endforeach; // End foreach $menuitems
			endif; // End if $menuitems
		endforeach;
		?>
	</table>
	<div id="pl8app_menuitem_history_pagination" class="pl8app_pagination navigation">
		<?php
		$big = 999999;
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => ceil( pl8app_count_purchases_of_customer() / 20 ) // 20 items per page
		) );
		?>
	</div>
	<?php do_action( 'pl8app_after_menuitem_history' ); ?>
<?php else : ?>
	<p class="pl8app-no-menuitems"><?php _e( 'You have not purchased any menuitems', 'pl8app' ); ?></p>
<?php endif; ?>
