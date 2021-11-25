<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email the menuitem link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @since 1.0.0 - Add parameters for pl8app_Payment and pl8app_Customer object.
 *
 * @param int          $payment_id   Payment ID
 * @param bool         $admin_notice Whether to send the admin email notification or not (default: true)
 * @param pl8app_Payment  $payment      Payment object for payment ID.
 * @return void
 */
function pl8app_email_purchase_receipt( $payment_id, $admin_notice = true, $to_email = '', $payment = null, $order_status = '' ) {

    if ( is_null( $payment ) ) {
		$payment = pl8app_get_payment( $payment_id );
	}

    if( empty( $order_status ) ) {
        $order_status = pl8app_get_order_status( $payment_id );
    }
	$payment_data = $payment->get_meta( '_pl8app_payment_meta', true );

	$from_name    = pl8app_get_option( 'pl8app_store_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name    = apply_filters( 'pl8app_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email   = pl8app_get_option( 'pl8app_st_email', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'pl8app_purchase_from_address', $from_email, $payment_id, $payment_data );

	if ( empty( $to_email ) ) {
		$to_email = $payment->email;
	}

	$email_settings = pl8app_get_option( $order_status );
    $check_notification_enabled = isset($email_settings['enable_notification']) && $email_settings['enable_notification'] == 'yes' ? true : false;


    if($check_notification_enabled){

        $subject = isset( $email_settings['subject'] ) ? $email_settings['subject'] : '';

        if ( $order_status == 'pending' && empty( $subject ) ) {
            $subject      = pl8app_get_option( 'purchase_subject', __( 'Purchase Receipt', 'pl8app' ) );
        }

        $subject      = apply_filters( 'pl8app_purchase_subject', wp_strip_all_tags( $subject ), $payment_id );
        $subject      = wp_specialchars_decode( pl8app_do_email_tags( $subject, $payment_id ) );

        $heading            = isset( $email_settings['heading'] ) ? $email_settings['heading'] : '';
        if ( $order_status == 'pending' && empty( $heading ) ) {
            $heading      = pl8app_get_option( 'purchase_heading', __( 'Purchase Receipt', 'pl8app' ) );
        }

        $heading      = apply_filters( 'pl8app_purchase_heading', $heading, $payment_id, $payment_data );
        $heading      = pl8app_do_email_tags( $heading, $payment_id );

        $attachments  = apply_filters( 'pl8app_receipt_attachments', array(), $payment_id, $payment_data );

        $message      = pl8app_do_email_tags( pl8app_get_email_body_content( $payment_id, $payment_data, $order_status ), $payment_id );

        $emails = PL8PRESS()->emails;

        $emails->__set( 'from_name', $from_name );
        $emails->__set( 'from_email', $from_email );
        $emails->__set( 'heading', $heading );

        $headers = apply_filters( 'pl8app_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
        $emails->__set( 'headers', $headers );

        $emails->send( $to_email, $subject, $message, $attachments );
    }


	if ( $admin_notice ) {
		do_action( 'pl8app_admin_order_notice', $payment_id, $payment_data );
	}
}

/**
 * Email the menuitem link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.0
 * @return void
 */
function pl8app_email_test_purchase_receipt() {

	$from_name   = pl8app_get_option( 'pl8app_store_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'pl8app_purchase_from_name', $from_name, 0, array() );

	$from_email  = pl8app_get_option( 'pl8app_st_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'pl8app_test_purchase_from_address', $from_email, 0, array() );

	$subject     = pl8app_get_option( 'purchase_subject', __( 'Purchase Receipt', 'pl8app' ) );
	$subject     = apply_filters( 'pl8app_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject     = pl8app_do_email_tags( $subject, 0 );

	$heading     = pl8app_get_option( 'purchase_heading', __( 'Purchase Receipt', 'pl8app' ) );
	$heading     = apply_filters( 'pl8app_purchase_heading', $heading, 0, array() );

	$attachments = apply_filters( 'pl8app_receipt_attachments', array(), 0, array() );

	$message     = pl8app_do_email_tags( pl8app_get_email_body_content( 0, array() ), 0 );

	$emails = PL8PRESS()->emails;
	$emails->__set( 'from_name' , $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading'   , $heading );

	$headers = apply_filters( 'pl8app_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	//$emails->send( pl8app_get_admin_notice_emails(), $subject, $message, $attachments );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.0.0
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function pl8app_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

    $notification_settings = pl8app_get_option( 'admin_notification' );

    if ( empty( $notification_settings ) ) {
        $enable_admin_notification = false;
    } else {
        $enable_admin_notification = isset( $notification_settings['enable_notification'] ) ? true : false;
    }

    if ( ! $enable_admin_notification ) {
        return;
    }

    $payment_id = absint( $payment_id );

    if( empty( $payment_id ) ) {
        return;
    }

    if( ! pl8app_get_payment_by( 'id', $payment_id ) ) {
        return;
    }

    $from_name   = pl8app_get_option( 'pl8app_store_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
    $from_name   = apply_filters( 'pl8app_purchase_from_name', $from_name, $payment_id, $payment_data );

    $from_email  = pl8app_get_option( 'pl8app_st_email', get_bloginfo( 'admin_email' ) );
    $from_email  = apply_filters( 'pl8app_admin_order_from_address', $from_email, $payment_id, $payment_data );

    $subject     = isset( $notification_settings['subject'] ) ? $notification_settings['subject'] : pl8app_get_option( 'order_notification_subject', sprintf( __( 'New order received - Order #%1$s', 'pl8app' ), $payment_id ) );
    $subject     = apply_filters( 'pl8app_admin_order_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
    $subject     = wp_specialchars_decode( pl8app_do_email_tags( $subject, $payment_id ) );

    $heading     = isset( $notification_settings['heading'] ) ? $notification_settings['heading'] : pl8app_get_option( 'order_notification_heading', __( 'New Order Received!', 'pl8app' ) );
    $heading     = apply_filters( 'pl8app_admin_order_notification_heading', $heading, $payment_id, $payment_data );
    $heading     = pl8app_do_email_tags( $heading, $payment_id );

    $attachments = apply_filters( 'pl8app_admin_order_notification_attachments', array(), $payment_id, $payment_data );

    $message     = pl8app_get_order_notification_body_content( $payment_id, $payment_data );

    $emails = PL8PRESS()->emails;

    $emails->__set( 'from_name', $from_name );
    $emails->__set( 'from_email', $from_email );
    $emails->__set( 'heading', $heading );

    $headers = apply_filters( 'pl8app_admin_order_notification_headers', $emails->get_headers(), $payment_id, $payment_data );
    $emails->__set( 'headers', $headers );

    $emails->send( pl8app_get_admin_notice_emails(), $subject, $message, $attachments );

}
add_action( 'pl8app_admin_order_notice', 'pl8app_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the pl8app Settings)
 *
 * @since 1.0
 * @return mixed
 */
function pl8app_get_admin_notice_emails() {
    $admin_notification = pl8app_get_option( 'admin_notification' );
    $store_email = pl8app_get_option('pl8app_st_email');
    $emails = isset( $admin_notification['admin_recipients'] ) ? $admin_notification['admin_recipients'] : pl8app_get_option( 'admin_notice_emails', false );
    if(! (strlen( trim( $emails ) ) > 0)) {
        if(!empty($store_email)){
            $emails = $store_email;
        }
        else{
            $emails = get_bloginfo( 'admin_email' );
        }
    }
    $emails = array_map( 'trim', explode( "\n", $emails ) );

    return apply_filters( 'pl8app_admin_notice_emails', $emails );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since  1.0.0
 * @author pl8app
 * @return string $message
 */
function pl8app_get_default_sale_notification_email() {
	$default_email_body = __( 'Hello', 'pl8app' ) . "\n\n" . __( 'A new order has been received', 'pl8app' ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s ordered:', 'pl8app' ), pl8app_get_label_plural() ) . "\n\n";
	$default_email_body .= '{menuitem_list}' . "\n\n";
	$default_email_body .= __( 'Ordered by: ', 'pl8app' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'pl8app' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'pl8app' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'pl8app' );

	$message = pl8app_get_option( 'order_notification', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since  1.0.0
 * @param $user_info
 * @param $payment   pl8app_Payment for getting the names
 *
 * @return array $email_names
 */
function pl8app_get_email_names( $user_info, $payment = false ) {
	$email_names = array();
	$email_names['fullname'] = '';

	if ( $payment instanceof pl8app_Payment ) {

		if ( $payment->user_id > 0 ) {

			$user_data = get_userdata( $payment->user_id );
			$email_names['name']      = $payment->first_name;
			$email_names['fullname']  = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username']  = $user_data->user_login;

		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		} else {

			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;

		}

	} else {

		if ( is_serialized( $user_info ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}

		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data = get_userdata( $user_info['id'] );
			$email_names['name']      = $user_info['first_name'];
			$email_names['fullname']  = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username']  = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}

	}

	return $email_names;
}

/**
 *
 * Send Test Emails to SMTP server which is configured in Store Email SMTP configuration Page in admin menu
 *
 * @param $options
 */
function pl8app_send_test_email_to_smtp($options){
    if( ! isset($_POST['to_email']) ||  ! isset($_POST['email_body']) || $options['enable_disable'] != 1 ) return;

    check_admin_referer( 'pl8app-mail-send-test' );

    $nonce_vrfy = $_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce_vrfy, 'pl8app-mail-send-test') ) return;

    $to      = sanitize_email( $_POST['to_email'] );
    $subject = 'pl8app Test Mail';
    $body    = sanitize_text_field( $_POST['email_body'] );
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $sent = wp_mail( $to, $subject, $body, $headers );

    if( $sent ){
        $msg = __('Test mail was sent successfully!', 'pl8app' );
        echo "<div class='notice notice-success'><p> $msg </p></div>";
    } else {
        echo "<div class='notice notice-error'><p>";
        $errorInfo = __('Failed in sending email!', 'pl8app');
        echo $errorInfo;
        echo "</p></div>";
    }
}