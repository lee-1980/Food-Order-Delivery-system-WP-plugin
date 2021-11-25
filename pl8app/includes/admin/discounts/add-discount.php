<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<h2><?php _e( 'Add New Discount', 'pl8app' ); ?></h2>

<?php if ( isset( $_GET['pl8app_discount_added'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php _e( 'Discount code created.', 'pl8app' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-discounts' ) ); ?>"><?php _e( '&larr; Back to Discounts', 'pl8app' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="pl8app-add-discount" action="" method="POST">
	<?php do_action( 'pl8app_add_discount_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'pl8app_add_discount_form_before_name' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-name"><?php _e( 'Name', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="pl8app-name" type="text" value="" />
					<p class="description"><?php _e( 'Enter the name of this discount.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_discount_form_before_code' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-code"><?php _e( 'Code', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="pl8app-code" name="code" value="" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as <span class="pl8app-discount-demo"style="background:#FFF; padding: 2px 8px;" > 10PERCENT</span>. Only alphanumeric characters are allowed.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_type' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-type"><?php _e( 'Type', 'pl8app' ); ?></label>
				</th>
				<td>
					<select name="type" id="pl8app-type">
						<option value="percent"><?php _e( 'Percentage', 'pl8app' ); ?></option>
						<option value="flat"><?php _e( 'Flat amount', 'pl8app' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-amount"><?php _e( 'Amount', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" class="pl8app-price-field" id="pl8app-amount" name="amount" value="" />
					<p class="description pl8app-amount-description flat-discount" style="display:none;"><?php printf( __( 'Enter the discount amount in %s', 'pl8app' ), pl8app_get_currency() ); ?></p>
					<p class="description pl8app-amount-description percent-discount"><?php _e( 'Enter the discount percentage. 10 = 10%', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-products"><?php printf( __( '%s Requirements', 'pl8app' ), pl8app_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo PL8PRESS()->html->product_dropdown( array(
							'name'        => 'products[]',
							'id'          => 'products',
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'pl8app' ), pl8app_get_label_plural() ),
						) ); ?><br/>
					</p>
					<div id="pl8app-discount-product-conditions" style="display:none;">
						<p>
							<select id="pl8app-product-condition" name="product_condition">
								<option value="all"><?php printf( __( 'Cart must contain all selected %s', 'pl8app' ), pl8app_get_label_plural() ); ?></option>
								<option value="any"><?php printf( __( 'Cart needs one or more of the selected %s', 'pl8app' ), pl8app_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="not_global" value="0" checked="checked"/>
								<?php _e( 'Apply discount to entire purchase.', 'pl8app' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="not_global" value="1"/>
								<?php printf( __( 'Apply discount only to selected %s.', 'pl8app' ), pl8app_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'pl8app' ), pl8app_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_excluded_products' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-excluded-products"><?php printf( __( 'Excluded %s', 'pl8app' ), pl8app_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo PL8PRESS()->html->product_dropdown( array(
						'name'        => 'excluded-products[]',
						'id'          => 'excluded-products',
						'selected'    => array(),
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'pl8app' ), pl8app_get_label_plural() ),
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'pl8app' ), pl8app_get_label_plural() ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_start' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-start"><?php _e( 'Start date', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="start" id="pl8app-start" type="text" value="" class="pl8app_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_expiration' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-expiration"><?php _e( 'Expiration date', 'pl8app' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="pl8app-expiration" type="text" class="pl8app_datepicker"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_min_cart_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-min-cart-amount"><?php _e( 'Minimum Amount', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" id="pl8app-min-cart-amount" name="min_price" value="" />
					<p class="description"><?php _e( 'The minimum dollar amount that must be in the cart before this discount can be used. Leave blank for no minimum.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_max_uses' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-max-uses"><?php _e( 'Max Uses', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="text" id="pl8app-max-uses" name="max" value="" />
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'pl8app' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'pl8app_add_discount_form_before_use_once' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="pl8app-use-once"><?php _e( 'Use Once Per Customer', 'pl8app' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="pl8app-use-once" name="use_once" value="1"/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'pl8app' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'pl8app_add_discount_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="pl8app-action" value="add_discount"/>
		<input type="hidden" name="pl8app-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-discounts' ) ); ?>"/>
		<input type="hidden" name="pl8app-discount-nonce" value="<?php echo wp_create_nonce( 'pl8app_discount_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Add Discount Code', 'pl8app' ); ?>" class="button-primary"/>
	</p>
</form>
