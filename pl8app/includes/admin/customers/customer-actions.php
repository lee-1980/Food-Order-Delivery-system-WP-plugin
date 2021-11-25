<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes a custom edit
 *
 * @since  1.0.0
 * @param  array $args The $_POST array being passeed
 * @return array $output Response messages
 */
function pl8app_edit_customer( $args ) {
	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'pl8app' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_info = $args['customerinfo'];
	$customer_id   = (int)$args['customerinfo']['id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'pl8app' ) );
	}

	$customer = new pl8app_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	$defaults = array(
		'name'    => '',
		'email'   => '',
		'user_id' => 0
	);

	$customer_info = wp_parse_args( $customer_info, $defaults );

	if ( ! is_email( $customer_info['email'] ) ) {
		pl8app_set_error( 'pl8app-invalid-email', __( 'Please enter a valid email address.', 'pl8app' ) );
	}

	if ( (int) $customer_info['user_id'] != (int) $customer->user_id ) {

		// Make sure we don't already have this user attached to a customer
		if ( ! empty( $customer_info['user_id'] ) && false !== PL8PRESS()->customers->get_customer_by( 'user_id', $customer_info['user_id'] ) ) {
			pl8app_set_error( 'pl8app-invalid-customer-user_id', sprintf( __( 'The User ID %d is already associated with a different customer.', 'pl8app' ), $customer_info['user_id'] ) );
		}

		// Make sure it's actually a user
		$user = get_user_by( 'id', $customer_info['user_id'] );
		if ( ! empty( $customer_info['user_id'] ) && false === $user ) {
			pl8app_set_error( 'pl8app-invalid-user_id', sprintf( __( 'The User ID %d does not exist. Please assign an existing user.', 'pl8app' ), $customer_info['user_id'] ) );
		}

	}

	// Record this for later
	$previous_user_id  = $customer->user_id;

	if ( pl8app_get_errors() ) {
		return;
	}

	$user_id = intval( $customer_info['user_id'] );
	if ( empty( $user_id ) && ! empty( $customer_info['user_login'] ) ) {
		// See if they gave an email, otherwise we'll assume login
		$user_by_field = 'login';
		if ( is_email( $customer_info['user_login'] ) ) {
			$user_by_field = 'email';
		}

		$user = get_user_by( $user_by_field, $customer_info['user_login'] );
		if ( $user ) {
			$user_id = $user->ID;
		} else {
			pl8app_set_error( 'pl8app-invalid-user-string', sprintf( __( 'Failed to attach user. The login or email address %s was not found.', 'pl8app' ), $customer_info['user_login'] ) );
		}
	}

	// Setup the customer address, if present
	$address = array();
	if ( ! empty( $user_id ) ) {

		$current_address = get_user_meta( $customer_info['user_id'], '_pl8app_user_address', true );

		if ( empty( $current_address ) ) {
			$address['line1']   = isset( $customer_info['line1'] )   ? $customer_info['line1']   : '';
			$address['line2']   = isset( $customer_info['line2'] )   ? $customer_info['line2']   : '';
			$address['city']    = isset( $customer_info['city'] )    ? $customer_info['city']    : '';
			$address['country'] = isset( $customer_info['country'] ) ? $customer_info['country'] : '';
			$address['zip']     = isset( $customer_info['zip'] )     ? $customer_info['zip']     : '';
			$address['state']   = isset( $customer_info['state'] )   ? $customer_info['state']   : '';
		} else {
			$current_address    = wp_parse_args( $current_address, array( 'line1', 'line2', 'city', 'zip', 'state', 'country' ) );
			$address['line1']   = isset( $customer_info['line1'] )   ? $customer_info['line1']   : $current_address['line1']  ;
			$address['line2']   = isset( $customer_info['line2'] )   ? $customer_info['line2']   : $current_address['line2']  ;
			$address['city']    = isset( $customer_info['city'] )    ? $customer_info['city']    : $current_address['city']   ;
			$address['country'] = isset( $customer_info['country'] ) ? $customer_info['country'] : $current_address['country'];
			$address['zip']     = isset( $customer_info['zip'] )     ? $customer_info['zip']     : $current_address['zip']    ;
			$address['state']   = isset( $customer_info['state'] )   ? $customer_info['state']   : $current_address['state']  ;
		}

	}

	// Sanitize the inputs
	$customer_data            = array();
	$customer_data['name']    = strip_tags( stripslashes( $customer_info['name'] ) );
	$customer_data['email']   = $customer_info['email'];
	$customer_data['user_id'] = $user_id;

	$customer_data = apply_filters( 'pl8app_edit_customer_info', $customer_data, $customer_id );
	$address       = apply_filters( 'pl8app_edit_customer_address', $address, $customer_id );

	$customer_data = array_map( 'sanitize_text_field', $customer_data );
	$address       = array_map( 'sanitize_text_field', $address );

	do_action( 'pl8app_pre_edit_customer', $customer_id, $customer_data, $address );

	$output         = array();
	$previous_email = $customer->email;

	if ( $customer->update( $customer_data ) ) {

		if ( ! empty( $customer->user_id ) && $customer->user_id > 0 ) {
			update_user_meta( $customer->user_id, '_pl8app_user_address', $address );
		}

		// Update some payment meta if we need to
		$payments_array = explode( ',', $customer->payment_ids );

		if ( $customer->email != $previous_email ) {
			foreach ( $payments_array as $payment_id ) {
				pl8app_update_payment_meta( $payment_id, 'email', $customer->email );
			}
		}

		if ( $customer->user_id != $previous_user_id ) {
			foreach ( $payments_array as $payment_id ) {
				pl8app_update_payment_meta( $payment_id, '_pl8app_payment_user_id', $customer->user_id );
			}
		}

		$output['success']       = true;
		$customer_data           = array_merge( $customer_data, $address );
		$output['customer_info'] = $customer_data;

	} else {

		$output['success'] = false;

	}

	do_action( 'pl8app_post_edit_customer', $customer_id, $customer_data );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'pl8app_edit-customer', 'pl8app_edit_customer', 10, 1 );


/**
 * Add an email address to the customer from within the admin and log a customer note
 *
 * @since  1.0.0
 * @param  array $args  Array of arguments: nonce, customer id, and email address
 * @return mixed        If DOING_AJAX echos out JSON, otherwise returns array of success (bool) and message (string)
 */
function pl8app_add_customer_email( $args ) {

	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'pl8app' ) );
	}

	$output = array();

	if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {

		$output['success'] = false;

		if ( empty( $args['email'] ) ) {
			$output['message'] = __( 'Email address is required.', 'pl8app' );
		} else if ( empty( $args['customer_id'] ) ) {
			$output['message'] = __( 'Customer ID is required.', 'pl8app' );
		} else {
			$output['message'] = __( 'An error has occured. Please try again.', 'pl8app' );
		}

	} else if ( ! wp_verify_nonce( $args['_wpnonce'], 'pl8app-add-customer-email' ) ) {

		$output = array(
			'success' => false,
			'message' => __( 'Nonce verification failed.', 'pl8app' ),
		);

	} else if ( ! is_email( $args['email'] ) ) {

		$output = array(
			'success' => false,
			'message' => __( 'Invalid email address.', 'pl8app' ),
		);

	} else {

		$email       = sanitize_email( $args['email'] );
		$customer_id = (int) $args['customer_id'];
		$primary     = 'true' === $args['primary'] ? true : false;
		$customer    = new pl8app_Customer( $customer_id );

		if ( false === $customer->add_email( $email, $primary ) ) {

			if ( in_array( $email, $customer->emails ) ) {

				$output = array(
					'success'  => false,
					'message'  => __( 'Email already associated with this customer.', 'pl8app' ),
				);

			} else {

				$output = array(
					'success' => false,
					'message' => __( 'Email address is already associated with another customer.', 'pl8app' ),
				);

			}

		} else {

			$redirect = admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer_id . '&pl8app-message=email-added' );
			$output = array(
				'success'  => true,
				'message'  => __( 'Email successfully added to customer.', 'pl8app' ),
				'redirect' => $redirect,
			);

			$user          = wp_get_current_user();
			$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'pl8appBot';
			$customer_note = sprintf( __( 'Email address %s added by %s', 'pl8app' ), $email, $user_login );
			$customer->add_note( $customer_note );

			if ( $primary ) {
				$customer_note =  sprintf( __( 'Email address %s set as primary by %s', 'pl8app' ), $email, $user_login );
				$customer->add_note( $customer_note );
			}


		}

	}

	do_action( 'pl8app_post_add_customer_email', $customer_id, $args );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'pl8app_customer-add-email', 'pl8app_add_customer_email', 10, 1 );

/**
 * Remove an email address to the customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_remove_customer_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'pl8app-remove-customer-email' ) ) {
		wp_die( __( 'Nonce verification failed', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$customer = new pl8app_Customer( $_GET['id'] );
	if ( $customer->remove_email( $_GET['email'] ) ) {

		$url = add_query_arg( 'pl8app-message', 'email-removed', admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ) );

		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'pl8appBot';
		$customer_note = sprintf( __( 'Email address %s removed by %s', 'pl8app' ), sanitize_email( $_GET['email'] ), $user_login );
		$customer->add_note( $customer_note );

	} else {
		$url = add_query_arg( 'pl8app-message', 'email-remove-failed', admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}
add_action( 'pl8app_customer-remove-email', 'pl8app_remove_customer_email', 10 );

/**
 * Set an email address as the primary for a customer from within the admin and log a customer note
 * and redirect back to the customer interface for feedback
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_set_customer_primary_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'pl8app-set-customer-primary-email' ) ) {
		wp_die( __( 'Nonce verification failed', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$customer = new pl8app_Customer( $_GET['id'] );
	if ( $customer->set_primary_email( $_GET['email'] ) ) {

		$url = add_query_arg( 'pl8app-message', 'primary-email-updated', admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ) );

		$user          = wp_get_current_user();
		$user_login    = ! empty( $user->user_login ) ? $user->user_login : 'pl8appBot';
		$customer_note = sprintf( __( 'Email address %s set as primary by %s', 'pl8app' ), sanitize_email( $_GET['email'] ), $user_login );
		$customer->add_note( $customer_note );

	} else {
		$url = add_query_arg( 'pl8app-message', 'primary-email-failed', admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ) );
	}

	wp_safe_redirect( $url );
	exit;
}
add_action( 'pl8app_customer-primary-email', 'pl8app_set_customer_primary_email', 10 );

/**
 * Save a customer note being added
 *
 * @since  1.0.0
 * @param  array $args The $_POST array being passeed
 * @return int         The Note ID that was saved, or 0 if nothing was saved
 */
function pl8app_customer_save_note( $args ) {

	$customer_view_role = apply_filters( 'pl8app_view_customers_role', 'view_shop_reports' );

	if ( ! is_admin() || ! current_user_can( $customer_view_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'pl8app' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_note = trim( sanitize_text_field( $args['customer_note'] ) );
	$customer_id   = (int)$args['customer_id'];
	$nonce         = $args['add_customer_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-customer-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'pl8app' ) );
	}

	if ( empty( $customer_note ) ) {
		pl8app_set_error( 'empty-customer-note', __( 'A note is required', 'pl8app' ) );
	}

	if ( pl8app_get_errors() ) {
		return;
	}

	$customer = new pl8app_Customer( $customer_id );
	$new_note = $customer->add_note( $customer_note );

	do_action( 'pl8app_pre_insert_customer_note', $customer_id, $new_note );

	if ( ! empty( $new_note ) && ! empty( $customer->id ) ) {

		ob_start();
		?>
		<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
			<span class="note-content-wrap">
				<?php echo stripslashes( $new_note ); ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $new_note;

	}

	return false;

}
add_action( 'pl8app_add-customer-note', 'pl8app_customer_save_note', 10, 1 );

/**
 * Delete a customer
 *
 * @since  1.0.0
 * @param  array $args The $_POST array being passeed
 * @return int         Wether it was a successful deletion
 */
function pl8app_customer_delete( $args ) {

	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete this customer.', 'pl8app' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id   = (int)$args['customer_id'];
	$confirm       = ! empty( $args['pl8app-customer-delete-confirm'] ) ? true : false;
	$remove_data   = ! empty( $args['pl8app-customer-delete-records'] ) ? true : false;
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'delete-customer' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'pl8app' ) );
	}

	if ( ! $confirm ) {
		pl8app_set_error( 'customer-delete-no-confirm', __( 'Please confirm you want to delete this customer', 'pl8app' ) );
	}

	if ( pl8app_get_errors() ) {
		wp_redirect( admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer_id ) );
		exit;
	}

	$customer = new pl8app_Customer( $customer_id );

	do_action( 'pl8app_pre_delete_customer', $customer_id, $confirm, $remove_data );

	$success = false;

	if ( $customer->id > 0 ) {

		$payments_array = explode( ',', $customer->payment_ids );
		$success        = PL8PRESS()->customers->delete( $customer->id );

		if ( $success ) {

			if ( $remove_data ) {

				// Remove all payments, logs, etc
				foreach ( $payments_array as $payment_id ) {
					pl8app_delete_purchase( $payment_id, false, true );
				}

			} else {

				// Just set the payments to customer_id of 0
				foreach ( $payments_array as $payment_id ) {
					pl8app_update_payment_meta( $payment_id, '_pl8app_payment_customer_id', 0 );
				}

			}

			$redirect = admin_url( 'admin.php?page=pl8app-customers&pl8app-message=customer-deleted' );

		} else {

			pl8app_set_error( 'pl8app-customer-delete-failed', __( 'Error deleting customer', 'pl8app' ) );
			$redirect = admin_url( 'admin.php?page=pl8app-customers&view=delete&id=' . $customer_id );

		}

	} else {

		pl8app_set_error( 'pl8app-customer-delete-invalid-id', __( 'Invalid Customer ID', 'pl8app' ) );
		$redirect = admin_url( 'admin.php?page=pl8app-customers' );

	}

	wp_redirect( $redirect );
	exit;

}
add_action( 'pl8app_delete-customer', 'pl8app_customer_delete', 10, 1 );

/**
 * Disconnect a user ID from a customer
 *
 * @since  1.0.0
 * @param  array $args Array of arguments
 * @return bool        If the disconnect was sucessful
 */
function pl8app_disconnect_customer_user_id( $args ) {

	$customer_edit_role = apply_filters( 'pl8app_edit_customers_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $customer_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this customer.', 'pl8app' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$customer_id   = (int)$args['customer_id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-customer' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'pl8app' ) );
	}

	$customer = new pl8app_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return false;
	}

	do_action( 'pl8app_pre_customer_disconnect_user_id', $customer_id, $customer->user_id );

	$customer_args = array( 'user_id' => 0 );

	if ( $customer->update( $customer_args ) ) {
		global $wpdb;

		if ( ! empty( $customer->payment_ids ) ) {
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_pl8app_payment_user_id' AND post_id IN ( $customer->payment_ids )" );
		}

		$output['success'] = true;

	} else {

		$output['success'] = false;
		pl8app_set_error( 'pl8app-disconnect-user-fail', __( 'Failed to disconnect user from customer', 'pl8app' ) );
	}

	do_action( 'pl8app_post_customer_disconnect_user_id', $customer_id );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'pl8app_disconnect-userid', 'pl8app_disconnect_customer_user_id', 10, 1 );

/**
 * Process manual verification of customer account by admin
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_process_admin_user_verification() {

	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'pl8app-verify-user' ) ) {
		wp_die( __( 'Nonce verification failed', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$customer = new pl8app_Customer( $_GET['id'] );
	pl8app_set_user_to_verified( $customer->user_id );

	$url = add_query_arg( 'pl8app-message', 'user-verified', admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $customer->id ) );

	wp_safe_redirect( $url );
	exit;

}
add_action( 'pl8app_verify_user_admin', 'pl8app_process_admin_user_verification' );

/**
 * Register the reset single customer stats batch processor
 * @since  1.0.0
 */
function pl8app_register_batch_single_customer_recount_tool() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_single_customer_recount_tool_batch_processer', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_single_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for recounding stats for a single customer
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function pl8app_include_single_customer_recount_tool_batch_processer( $class ) {

	if ( 'pl8app_Tools_Recount_Single_Customer_Stats' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/tools/class-pl8app-tools-recount-single-customer-stats.php';
	}

}

/**
 * Sets up additional action calls for the set_last_changed method in the pl8app_DB_Customers class.
 *
 * @since  1.0.0
 * @param  void
 * @return void
 */
function pl8app_customer_action_calls() {
	add_action( 'added_customer_meta', array( PL8PRESS()->customers, 'set_last_changed' ) );
	add_action( 'updated_customer_meta', array( PL8PRESS()->customers, 'set_last_changed' ) );
	add_action( 'deleted_customer_meta', array( PL8PRESS()->customers, 'set_last_changed' ) );
}
add_action( 'init', 'pl8app_customer_action_calls' );

/**
 * Check the Customer's No show orders and limit of no show option ,if reaches to limit , move customer's email to banned Email list.
 * @param $payment_id
 * @param $new_status
 */
function pl8app_check_noshow_option_process($payment_id, $new_status) {

    $payment = new pl8app_Payment($payment_id);
    $customer_id = $payment->user_id;
    $customer = new pl8app_Customer($customer_id);

    $noshow_option_count = 0;
    if($new_status == 'no_show'){
        $noshow_option_count += 1;
    }

    $payment_ids = $customer->get_payment_ids();

    foreach ($payment_ids as $id){

        $status = get_post_meta($id, '_order_status', true);
        if($status == 'no_show') {
            $noshow_option_count += 1;
        }
    }

    $noshow_option_limit = pl8app_get_customer_noshow_limit();

    if($noshow_option_count >= $noshow_option_limit && $noshow_option_limit > 0){
        $email = $payment->email;
        $banned_emails = pl8app_get_banned_emails();
        if (!in_array(trim($email), $banned_emails)){
            array_push($banned_emails, $email);
            pl8app_update_option( 'banned_emails', $banned_emails );
        }
    }
}

add_action('pl8app_check_noshow_option', 'pl8app_check_noshow_option_process', 10 , 2);
