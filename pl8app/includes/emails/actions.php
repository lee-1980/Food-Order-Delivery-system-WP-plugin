<?php
/**
 * Email Actions
 *
 * @package     pl8app
 * @subpackage  Emails
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Triggers Purchase Receipt to be sent after the payment status is updated
 *
 * @since 1.0
 * @since 1.0.0 - Add parameters for pl8app_Payment and pl8app_Customer object.
 *
 * @param int          $payment_id Payment ID.
 * @param pl8app_Payment  $payment    Payment object for payment ID.
 * @param pl8app_Customer $customer   Customer object for associated payment.
 * @return void
 */
function pl8app_trigger_purchase_receipt( $payment_id = 0, $payment = null, $customer = null ) {
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['pl8app-action'] ) && 'edit_payment' == $_POST['pl8app-action'] ) {
		return;
	}

	// Send email with secure menuitem link
	pl8app_email_purchase_receipt( $payment_id, true, '', $payment );
}
add_action( 'pl8app_complete_purchase', 'pl8app_trigger_purchase_receipt', 999, 3 );

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 * @param array $data Payment Data
 * @return void
 */
function pl8app_resend_purchase_receipt( $data ) {

	$purchase_id = absint( $data['purchase_id'] );

	if( empty( $purchase_id ) ) {
		return;
	}

	if( ! current_user_can( 'edit_shop_payments' ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$email = ! empty( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';

	if( empty( $email ) ) {
		$customer = new pl8app_Customer( pl8app_get_payment_customer_id( $purchase_id ) );
		$email    = $customer->email;
	}

	pl8app_email_purchase_receipt( $purchase_id, false, $email );

	// Grab all menuitems of the purchase and update their file menuitem limits, if needed
	// This allows admins to resend purchase receipts to grant additional file menuitems
	$menuitems = pl8app_get_payment_meta_cart_details( $purchase_id, true );

	wp_redirect( add_query_arg( array( 'pl8app-message' => 'email_sent', 'pl8app-action' => false, 'purchase_id' => false ) ) );
	exit;
}
add_action( 'pl8app_email_links', 'pl8app_resend_purchase_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.0
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function pl8app_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'pl8app-test-email' ) ) {
		return;
	}

	// Send a test email
	pl8app_email_test_purchase_receipt();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'pl8app_action' ) ); exit;
}
add_action( 'pl8app_send_test_email', 'pl8app_send_test_email' );

//Send notification to customer
function send_customer_purchase_notification( $payment_id, $new_status ) {

    $order_status = pl8app_get_option( $new_status );
    $check_notification_enabled = isset( $order_status['enable_notification'] ) ? true : false;

    if ( !empty( $payment_id ) && $check_notification_enabled && $new_status !== 'pending' ) {
        $customer = new pl8app_Customer( pl8app_get_payment_customer_id( $payment_id ) );
        $email    = $customer->email;
        pl8app_email_purchase_receipt( $payment_id, false, $email, null, $new_status );
    }
}
add_action( 'pl8app_update_order_status', 'send_customer_purchase_notification' , 10, 2 );

