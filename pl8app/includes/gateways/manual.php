<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manual Gateway does not need a CC form, so remove it.
 *
 * @since 1.0
 * @return void
 */
add_action( 'pl8app_manual_cc_form', '__return_false' );

/**
 * Processes the purchase data and uses the Manual Payment gateway to record
 * the transaction in the Order History
 *
 * @since 1.0
 * @param array $purchase_data Purchase Data
 * @return void
*/
function pl8app_manual_payment( $purchase_data ) {
	if( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'pl8app-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	/*
	* Purchase data comes in like this
	*
	$purchase_data = array(
		'menuitems' => array of menuitem IDs,
		'price' => total price of cart contents,
		'purchase_key' =>  // Random key
		'user_email' => $user_email,
		'date' => date('Y-m-d H:i:s'),
		'user_id' => $user_id,
		'post_data' => $_POST,
		'user_info' => array of user's information and used discount code
		'cart_details' => array of cart details,
	);
	*/

	$payment_data = array(
		'price' 		=> $purchase_data['price'],
		'date' 			=> $purchase_data['date'],
		'user_email' 	=> $purchase_data['user_email'],
		'purchase_key' 	=> $purchase_data['purchase_key'],
		'currency' 		=> pl8app_get_currency(),
		'menuitems' 	=> $purchase_data['menuitems'],
		'user_info' 	=> $purchase_data['user_info'],
		'cart_details' 	=> $purchase_data['cart_details'],
		'status' 		=> 'pending'
	);

	// Record the pending payment
	$payment = pl8app_insert_payment( $payment_data );

	if ( $payment ) {
		pl8app_update_payment_status( $payment, 'processing' );
		// Empty the shopping cart
		pl8app_empty_cart();
		pl8app_send_to_success_page();
	} else {
		pl8app_record_gateway_error( __( 'Payment Error', 'pl8app' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) order. Payment data: %s', 'pl8app' ), json_encode( $payment_data ) ), $payment );
		// If errors are present, send the user back to the purchase page so they can be corrected
		pl8app_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['pl8app-gateway'] );
	}
}
add_action( 'pl8app_gateway_manual', 'pl8app_manual_payment' );