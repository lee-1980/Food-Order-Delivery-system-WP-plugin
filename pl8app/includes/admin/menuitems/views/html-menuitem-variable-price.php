<?php
/**
 * Menu Item variable price html
 *
 * @package pl8app/Admin
 */

defined( 'ABSPATH' ) || exit;
$count 	= !empty( $current ) ? $current : 0;
$name  	= !empty( $price ) && is_array( $price )  ? $price['name'] : '';
$amount = !empty( $price ) ? $price['amount'] : '';
?>
<div class="pl8app-metabox variable-price">
	<h3>
		<a href="#" class="remove_row delete">
			<?php esc_html_e( 'Remove', 'pl8app' ); ?>
		</a>
		<div class="tips sort" data-tip="<?php esc_html_e( 'Drag Drop to reorder the addon categories.', 'pl8app' );?>"></div>
		<strong class="price_name">
			<?php echo $name == '' ? __( 'Option Name', 'pl8app' ) : $name; ?>
		</strong>
	</h3>
	<div class="pl8app-metabox-content">
		<div class="pl8app-col-6 price-name">
			<input type="text" value="<?php echo $name; ?>" name="pl8app_variable_prices[<?php echo $count; ?>][name]" class="pl8app-input pl8app-input-variable-name" placeholder="<?php esc_html_e( 'Option Name', 'pl8app' ); ?>">
		</div>
		<div class="pl8app-col-6 price-value">
			<?php esc_html_e( 'Price:', 'pl8app' ); ?>
			<?php echo pl8app_currency_symbol(); ?>
			<input type="text" value="<?php echo $amount; ?>" name="pl8app_variable_prices[<?php echo $count; ?>][amount]" class="pl8app-input" placeholder="9.99">
		</div>
	</div>
</div>