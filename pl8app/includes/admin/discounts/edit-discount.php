<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
}

$discount_id       = absint( $_GET['discount'] );
$discount          = pl8app_get_discount( $discount_id );
$product_reqs      = pl8app_get_discount_product_reqs( $discount_id );
$excluded_products = pl8app_get_discount_excluded_products( $discount_id );
$condition         = pl8app_get_discount_product_condition( $discount_id );
$single_use        = pl8app_discount_is_single_use( $discount_id );
$flat_display      = pl8app_get_discount_type( $discount_id ) == 'flat' ? '' : ' style="display:none;"';
$percent_display   = pl8app_get_discount_type( $discount_id ) == 'percent' ? '' : ' style="display:none;"';
$condition_display = empty( $product_reqs ) ? ' style="display:none;"' : '';
?>
<h2><?php _e( 'Edit Discount', 'pl8app' ); ?></h2>

<?php if ( isset( $_GET['pl8app_discount_updated'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php _e( 'Discount code updated.', 'pl8app' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-discounts' ) ); ?>"><?php _e( '&larr; Back to Discounts', 'pl8app' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="pl8app-edit-discount" action="" method="post">
	<?php do_action( 'pl8app_edit_discount_form_top', $discount_id, $discount ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'pl8app_edit_discount_form_before_name', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-name"><?php _e( 'Name', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="pl8app-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->post_title ) ); ?>" />
					<p class="description"><?php _e( 'The name of this discount', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_code', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-code"><?php _e( 'Code', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="pl8app-code" name="code" value="<?php echo esc_attr( pl8app_get_discount_code( $discount_id ) ); ?>" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_type', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-type"><?php _e( 'Type', 'pl8app' ); ?></label>
				</th>
				<td>
					<select name="type" id="pl8app-type">
						<option value="percent" <?php selected( pl8app_get_discount_type( $discount_id ), 'percent' ); ?>><?php _e( 'Percentage', 'pl8app' ); ?></option>
						<option value="flat"<?php selected( pl8app_get_discount_type( $discount_id ), 'flat' ); ?>><?php _e( 'Flat amount', 'pl8app' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-amount"><?php _e( 'Amount', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" class="pl8app-price-field" required="required" id="pl8app-amount" name="amount" value="<?php echo esc_attr( pl8app_get_discount_amount( $discount_id ) ); ?>" />
					<p class="description pl8app-amount-description flat"<?php echo $flat_display; ?>><?php printf( __( 'Enter the discount amount in %s', 'pl8app' ), pl8app_get_currency() ); ?></p>
					<p class="description pl8app-amount-description percent"<?php echo $percent_display; ?>><?php _e( 'Enter the discount percentage. 10 = 10%', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-products"><?php printf( __( '%s Requirements', 'pl8app' ), pl8app_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo PL8PRESS()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'selected'    => $product_reqs,
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'pl8app' ), pl8app_get_label_plural() )
						) ); ?><br/>
					</p>
					<div id="pl8app-discount-product-conditions"<?php echo $condition_display; ?>>
						<p>
							<select id="pl8app-product-condition" name="product_condition">
								<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'Cart must contain all selected %s', 'pl8app' ), pl8app_get_label_plural() ); ?></option>
								<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Cart needs one or more of the selected %s', 'pl8app' ), pl8app_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0"<?php checked( false, pl8app_is_discount_not_global( $discount_id ) ); ?>/>
								<?php _e( 'Apply discount to entire purchase.', 'pl8app' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"<?php checked( true, pl8app_is_discount_not_global( $discount_id ) ); ?>/>
								<?php printf( __( 'Apply discount only to selected %s.', 'pl8app' ), pl8app_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'pl8app' ), pl8app_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_excluded_products', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-excluded-products"><?php printf( __( 'Excluded %s', 'pl8app' ), pl8app_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo PL8PRESS()->html->product_dropdown( array(
						'name'        => 'excluded-products[]',
						'id'          => 'excluded-products',
						'selected'    => $excluded_products,
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'pl8app' ), pl8app_get_label_plural() )
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'pl8app' ), pl8app_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_start', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-start"><?php _e( 'Start date', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="start" id="pl8app-start" type="text" value="<?php echo esc_attr( pl8app_get_discount_start_date( $discount_id ) ); ?>"  class="pl8app_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_expiration', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-expiration"><?php _e( 'Expiration date', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="pl8app-expiration" type="text" value="<?php echo esc_attr( pl8app_get_discount_expiration( $discount_id ) ); ?>"  class="pl8app_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_max_uses', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-max-uses"><?php _e( 'Max Uses', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" id="pl8app-max-uses" name="max" value="<?php echo esc_attr( pl8app_get_discount_max_uses( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_min_cart_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-min-cart-amount"><?php _e( 'Minimum Amount', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" id="pl8app-min-cart-amount" name="min_price" value="<?php echo esc_attr( pl8app_get_discount_min_price( $discount_id ) ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_status', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-status"><?php _e( 'Status', 'pl8app' ); ?></label>
				</th>
				<td>
					<select name="status" id="pl8app-status">
						<option value="active" <?php selected( $discount->post_status, 'active' ); ?>><?php _e( 'Active', 'pl8app' ); ?></option>
						<option value="inactive"<?php selected( $discount->post_status, 'inactive' ); ?>><?php _e( 'Inactive', 'pl8app' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this discount code.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_edit_discount_form_before_use_once', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-use-once"><?php _e( 'Use Once Per Customer', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="pl8app-use-once" name="use_once" value="1"<?php checked( true, $single_use ); ?>/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'pl8app' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'pl8app_edit_discount_form_bottom', $discount_id, $discount ); ?>
	<p class="submit">
		<input type="hidden" name="pl8app-action" value="edit_discount"/>
		<input type="hidden" name="discount-id" value="<?php echo absint( $_GET['discount'] ); ?>"/>
		<input type="hidden" name="pl8app-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-discounts&pl8app-action=edit_discount&discount=' . $discount_id ) ); ?>"/>
		<input type="hidden" name="pl8app-discount-nonce" value="<?php echo wp_create_nonce( 'pl8app_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Update Discount Code', 'pl8app' ); ?>" class="button-primary"/>
	</p>
</form>
