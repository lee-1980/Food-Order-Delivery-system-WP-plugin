<?php
/**
 * Edit Payment Template
 *
 * @package     pl8app
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$payment_id   = absint( $_GET['purchase_id'] );
$payment      = get_post( $payment_id );
$payment_data = pl8app_get_payment_meta( $payment_id  );
?>
<div class="wrap">
	<h2><?php _e( 'Edit Payment', 'pl8app' ); ?>: <?php echo get_the_title( $payment_id ) . ' - #' . $payment_id; ?> - <a href="<?php echo admin_url( 'admin.php?page=pl8app-payment-history' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'pl8app' ); ?></a></h2>
	<form id="pl8app-edit-payment" action="" method="post">
		<table class="form-table">
			<tbody>
				<?php do_action( 'pl8app_edit_payment_top', $payment->ID ); ?>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Buyer\'s Email', 'pl8app' ); ?></span>
					</th>
					<td>
						<input class="regular-text" type="text" name="pl8app-buyer-email" id="pl8app-buyer-email" value="<?php echo pl8app_get_payment_user_email( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the buyer\'s email here.', 'pl8app' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Buyer\'s User ID', 'pl8app' ); ?></span>
					</th>
					<td>
						<input class="small-text" type="number" min="-1" step="1" name="pl8app-buyer-user-id" id="pl8app-buyer-user-id" value="<?php echo pl8app_get_payment_user_id( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the buyer\'s WordPress user ID here.', 'pl8app' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php printf( __( 'Payment Amount in %s', 'pl8app' ), pl8app_get_currency() ); ?></span>
					</th>
					<td>
						<input class="small-text" type="number" min="0" step="0.01" name="pl8app-payment-amount" id="pl8app-payment-amount" value="<?php echo pl8app_get_payment_amount( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the purchase total here.', 'pl8app' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'pl8app Purchased', 'pl8app' ); ?></span>
					</th>
					<td id="purchased-menuitems">
						<?php
							$menuitems = maybe_unserialize( $payment_data['menuitems'] );
							$cart_items = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : false;
							if ( $menuitems ) {
								foreach ( $menuitems as $menuitem ) {
									$id = isset( $payment_data['cart_details'] ) ? $menuitem['id'] : $menuitem;

									if ( isset( $menuitem['options']['price_id'] ) ) {
										$variable_prices = '<input type="hidden" name="pl8app-purchased-menuitems[' . $id . '][options][price_id]" value="'. $menuitem['options']['price_id'] .'" />';
										$variable_prices .= '(' . pl8app_get_price_option_name( $id, $menuitem['options']['price_id'], $payment_id ) . ')';
									} else {
										$variable_prices = '';
									}

									echo '<div class="purchased_menuitem_' . $id . '">
											<input type="hidden" name="pl8app-purchased-menuitems[' . $id . ']" value="' . $id . '"/>
											<strong>' . get_the_title( $id ) . ' ' . $variable_prices . '</strong> - <a href="#" class="pl8app-remove-purchased-menuitem" data-action="remove_purchased_menuitem" data-id="' . $id . '">'. __( 'Remove', 'pl8app' ) .'</a>
										  </div>';
								}
							}
						?>
						<p id="edit-menuitems"><a href="#TB_inline?width=640&amp;inlineId=available-menuitems" class="thickbox" title="<?php printf( __( 'Add %s to purchase', 'pl8app' ), strtolower( pl8app_get_label_plural() ) ); ?>"><?php printf( __( 'Add %s to purchase', 'pl8app' ), strtolower( pl8app_get_label_plural() ) ); ?></a></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Order Notes', 'pl8app' ); ?></span>
					</th>
					<td>
						<?php
							$notes = pl8app_get_payment_notes( $payment->ID );
							if ( ! empty( $notes ) ) {
								echo '<ul id="payment-notes">';
								foreach ( $notes as $note ) {
									if ( ! empty( $note->user_id ) ) {
										$user = get_userdata( $note->user_id );
										$user = $user->display_name;
									} else {
										$user = __( 'pl8app Bot', 'pl8app' );
									}
									$delete_note_url = wp_nonce_url( add_query_arg( array(
										'pl8app-action' => 'delete_payment_note',
										'note_id'    => $note->comment_ID
									) ), 'pl8app_delete_payment_note' );
									echo '<li>';
										echo '<strong>' . $user . '</strong>&nbsp;<em>' . $note->comment_date . '</em>&nbsp;&mdash;&nbsp;' . $note->comment_content;
										echo '&nbsp;&ndash;&nbsp;<a href="' . $delete_note_url . '" class="pl8app-delete-payment-note" title="' . __( 'Delete this payment note', 'pl8app' ) . '">' . __( 'Delete', 'pl8app' ) . '</a>';
										echo '</li>';
								}
								echo '</ul>';
							} else {
								echo '<p>' . __( 'No payment notes', 'pl8app' ) . '</p>';
							}
						?>
						<label for="pl8app-payment-note"><?php _e( 'Add New Note', 'pl8app' ); ?></label><br/>
						<textarea name="pl8app-payment-note" id="pl8app-payment-note" cols="30" rows="5"></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Status', 'pl8app' ); ?></span>
					</th>
					<td>
						<select name="pl8app-payment-status" id="pl8app_payment_status">
							<?php
							$status = $payment->post_status; // Current status
							$statuses = pl8app_get_payment_statuses();
							foreach( $statuses as $status_id => $label ) {
								echo '<option value="' . $status_id	. '" ' . selected( $status, $status_id, false ) . '>' . $label . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Unlimited pl8app', 'pl8app' ); ?></span>
					</th>
					<td>
						<input type="checkbox" name="pl8app-unlimited-menuitems" id="pl8app_unlimited_menuitems" value="1"<?php checked( true, get_post_meta( $payment_id, '_unlimited_file_menuitems', true ) ); ?>/>
						<label class="description" for="pl8app_unlimited_menuitems"><?php _e( 'Check this box to enable unlimited file menuitems for this purchase.', 'pl8app' ); ?></label>
					</td>
				</tr>
				<tr id="pl8app_payment_notification" style="display:none;">
					<th scope="row" valign="top">
						<span><?php _e( 'Send Purchase Receipt', 'pl8app' ); ?></span>
					</th>
					<td>
						<input type="checkbox" name="pl8app-payment-send-email" id="pl8app_send_email" value="yes"/>
						<label class="description" for="pl8app_send_email"><?php _e( 'Check this box to send the purchase receipt, including all menuitem links.', 'pl8app' ); ?></label>
					</td>
				</tr>
				<?php do_action( 'pl8app_edit_payment_bottom', $payment->ID ); ?>
			</tbody>
		</table>

		<input type="hidden" name="pl8app_action" value="edit_payment"/>
		<input type="hidden" name="pl8app-old-status" value="<?php echo $status; ?>"/>
		<input type="hidden" name="payment-id" value="<?php echo $payment_id; ?>"/>
		<?php wp_nonce_field( 'pl8app_payment_nonce', 'pl8app-payment-nonce' ); ?>
		<?php echo submit_button( __( 'Update Payment', 'pl8app' ) ); ?>
	</form>
	<div id="available-menuitems" style="display:none;">
		<form id="pl8app-add-menuitems-to-purchase">
			<p>
				<?php echo PL8PRESS()->html->product_dropdown( 'menuitems[0][id]' ); ?>
				&nbsp;<img src="<?php echo admin_url('/images/wpspin_light.gif'); ?>" class="hidden pl8app_add_menuitem_to_purchase_waiting waiting" />
			</p>
			<p>
				<a href="#" class="button-secondary pl8app-add-another-menuitem"><?php echo sprintf( __( 'Add Another %s', 'pl8app' ), esc_html( pl8app_get_label_singular() ) ); ?></a>
			</p>
			<p>
				<a id="pl8app-add-menuitem" class="button-primary" title="<?php _e( 'Add Selected pl8app', 'pl8app' ); ?>"><?php _e( 'Add Selected pl8app', 'pl8app' ); ?></a>
				<a id="pl8app-close-add-menuitem" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Close', 'pl8app' ); ?>"><?php _e( 'Close', 'pl8app' ); ?></a>
			</p>
			<?php wp_nonce_field( 'pl8app_add_menuitems_to_purchase_nonce', 'pl8app_add_menuitems_to_purchase_nonce' ); ?>
		</form>
	</div>
</div>
