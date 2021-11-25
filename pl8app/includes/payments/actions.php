<?php


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Complete a purchase
 *
 * Performs all necessary actions to complete a purchase.
 * Triggered by the pl8app_update_payment_status() function.
 *
 * @since 1.0
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
*/
function pl8app_complete_purchase( $payment_id, $new_status, $old_status ) {
    if ( $old_status == 'publish' || $old_status == 'complete' ) {
        return; // Make sure that payments are only completed once
    }

    // Make sure the payment completion is only processed when new status is complete
    if ( $new_status != 'publish' && $new_status != 'processing' ) {
        return;
    }

    $payment = new pl8app_Payment( $payment_id );

    $creation_date  = get_post_field( 'post_date', $payment_id, 'raw' );
    $completed_date = $payment->completed_date;
    $user_info      = $payment->user_info;
    $customer_id    = $payment->customer_id;
    $amount         = $payment->total;
    $cart_details   = $payment->cart_details;

    do_action( 'pl8app_pre_complete_purchase', $payment_id );

    if ( is_array( $cart_details ) ) {

        // Increase purchase count and earnings
        foreach ( $cart_details as $cart_index => $menuitem ) {

            // "bundle" or "default"
            $menuitem_type = pl8app_get_menuitem_type( $menuitem['id'] );
            $price_id      = isset( $menuitem['item_number']['options']['price_id'] ) ? (int) $menuitem['item_number']['options']['price_id'] : false;
            // Increase earnings and fire actions once per quantity number
            for( $i = 0; $i < $menuitem['quantity']; $i++ ) {

                // Ensure these actions only run once, ever
                if ( empty( $completed_date ) ) {

                    pl8app_record_sale_in_log( $menuitem['id'], $payment_id, $price_id, $creation_date );
                    do_action( 'pl8app_complete_menuitem_purchase', $menuitem['id'], $payment_id, $menuitem_type, $menuitem, $cart_index );

                }

            }

            $increase_earnings = $menuitem['price'];
            if ( ! empty( $menuitem['fees'] ) ) {
                foreach ( $menuitem['fees'] as $fee ) {
                    if ( $fee['amount'] > 0 ) {
                        continue;
                    }
                    $increase_earnings += $fee['amount'];
                }
            }

            // Increase the earnings for this menuitem ID
            pl8app_increase_earnings( $menuitem['id'], $increase_earnings );
            pl8app_increase_purchase_count( $menuitem['id'], $menuitem['quantity'] );

        }

        // Clear the total earnings cache
        delete_transient( 'pl8app_earnings_total' );
        // Clear the This Month earnings (this_monththis_month is NOT a typo)
        delete_transient( md5( 'pl8app_earnings_this_monththis_month' ) );
        delete_transient( md5( 'pl8app_earnings_todaytoday' ) );
    }

    if ( isset( $_COOKIE['service_time'] )
     && !empty( $_COOKIE['service_time'] ) ) {
        unset( $_COOKIE['service_time'] );
        setcookie( "service_time", "", time() - 300, "/" );
    }

    if ( isset( $_COOKIE['service_type'] )
        && !empty( $_COOKIE['service_type'] ) ) {
        unset( $_COOKIE['service_type'] );
        setcookie( "service_type", "", time() - 300, "/" );
    }

    if ( isset( $_COOKIE['delivery_date'] )
     && !empty( $_COOKIE['delivery_date']  ) ) {
        unset( $_COOKIE['delivery_date'] );
        setcookie( "delivery_date", "", time() - 300, "/" );
    }


    // Increase the customer's purchase stats
    $customer = new pl8app_Customer( $customer_id );
    $customer->increase_purchase_count();
    $customer->increase_value( $amount );

    pl8app_increase_total_earnings( $amount );

    // Check for discount codes and increment their use counts
    if ( ! empty( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {

        $discounts = array_map( 'trim', explode( ',', $user_info['discount'] ) );

        if( ! empty( $discounts ) ) {

            foreach( $discounts as $code ) {

                pl8app_increase_discount_usage( $code );

            }

        }
    }


    // Ensure this action only runs once ever
    if( empty( $completed_date ) ) {

        // Save the completed date
        $payment->completed_date = current_time( 'mysql' );
        $payment->save();

        /**
         * Runs **when** a purchase is marked as "complete".
         *
         * @since 1.0.0 - Added pl8app_Payment and pl8app_Customer object to action.
         *
         * @param int          $payment_id Payment ID.
         * @param pl8app_Payment  $payment    pl8app_Payment object containing all payment data.
         * @param pl8app_Customer $customer   pl8app_Customer object containing all customer data.
         */
        do_action( 'pl8app_complete_purchase', $payment_id, $payment, $customer );

        // If cron doesn't work on a site, allow the filter to use __return_false and run the events immediately.
        $use_cron = apply_filters( 'pl8app_use_after_payment_actions', true, $payment_id );
        if ( false === $use_cron ) {
            /**
             * Runs **after** a purchase is marked as "complete".
             *
             * @see pl8app_process_after_payment_actions()
             *
             * @since 1.0.0 - Added pl8app_Payment and pl8app_Customer object to action.
             *
             * @param int          $payment_id Payment ID.
             * @param pl8app_Payment  $payment    pl8app_Payment object containing all payment data.
             * @param pl8app_Customer $customer   pl8app_Customer object containing all customer data.
             */
            do_action( 'pl8app_after_payment_actions', $payment_id, $payment, $customer );
        }

    }

    // Empty the shopping cart
    pl8app_empty_cart();

}
add_action( 'pl8app_update_payment_status', 'pl8app_complete_purchase', 100, 3 );

/**
 * Schedules the one time event via WP_Cron to fire after purchase actions.
 *
 * Is run on the pl8app_complete_purchase action.
 *
 * @since 1.0.0
 * @param $payment_id
 */
function pl8app_schedule_after_payment_action( $payment_id ) {
	$use_cron = apply_filters( 'pl8app_use_after_payment_actions', true, $payment_id );
	if ( $use_cron ) {
		$after_payment_delay = apply_filters( 'pl8app_after_payment_actions_delay', 30, $payment_id );

		// Use time() instead of current_time( 'timestamp' ) to avoid scheduling the event in the past when server time
		// and WordPress timezone are different.
		wp_schedule_single_event( time() + $after_payment_delay, 'pl8app_after_payment_scheduled_actions', array( $payment_id, false ) );
	}
}
add_action( 'pl8app_complete_purchase', 'pl8app_schedule_after_payment_action', 10, 1 );

/**
 * Executes the one time event used for after purchase actions.
 *
 * @since 1.0.0
 * @param $payment_id
 * @param $force
 */
function pl8app_process_after_payment_actions( $payment_id = 0, $force = false ) {
	if ( empty( $payment_id ) ) {
		return;
	}

	$payment   = new pl8app_Payment( $payment_id );
	$has_fired = $payment->get_meta( '_pl8app_complete_actions_run' );
	if ( ! empty( $has_fired ) && false === $force ) {
		return;
	}

	$payment->add_note( __( 'After payment actions processed.', 'pl8app' ) );
	$payment->update_meta( '_pl8app_complete_actions_run', time() ); // This is in GMT
	do_action( 'pl8app_after_payment_actions', $payment_id );
}
add_action( 'pl8app_after_payment_scheduled_actions', 'pl8app_process_after_payment_actions', 10, 1 );

/**
 * Record payment status change
 *
 * @since 1.0.0
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
 */
function pl8app_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = pl8app_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %s to %s', 'pl8app' ), $old_status, $new_status );

	pl8app_insert_payment_note( $payment_id, $status_change );
}
add_action( 'pl8app_update_payment_status', 'pl8app_record_status_change', 100, 3 );

/**
 * Flushes the current user's order history transient when a payment status
 * is updated
 *
 * @since  1.0.0
 *
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function pl8app_clear_user_history_cache( $payment_id, $new_status, $old_status ) {
	$payment = new pl8app_Payment( $payment_id );

	if( ! empty( $payment->user_id ) ) {
		delete_transient( 'pl8app_user_' . $payment->user_id . '_purchases' );
	}
}
add_action( 'pl8app_update_payment_status', 'pl8app_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @since 1.0.0
 * @param array $data Arguments passed
 * @return void
*/
function pl8app_update_old_payments_with_totals( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'pl8app_upgrade_payments_nonce' ) ) {
		return;
	}

	if ( get_option( 'pl8app_payment_totals_upgraded' ) ) {
		return;
	}

	$payments = pl8app_get_payments( array(
		'offset' => 0,
		'number' => -1,
		'mode'   => 'all',
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$payment = new pl8app_Payment( $payment->ID );
			$meta    = $payment->get_meta();

			$payment->total = $meta['amount'];
			$payment->save();
		}
	}

	add_option( 'pl8app_payment_totals_upgraded', 1 );
}
add_action( 'pl8app_upgrade_payments', 'pl8app_update_old_payments_with_totals' );

/**
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 *  This function is only intended to be used by WordPress cron.
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_mark_abandoned_orders() {

	// Bail if not in WordPress cron
	if ( ! pl8app_doing_cron() ) {
		return;
	}

	$args = array(
		'status' => 'pending',
		'number' => -1,
		'output' => 'pl8app_payments',
	);

	add_filter( 'posts_where', 'pl8app_filter_where_older_than_week' );

	$payments = pl8app_get_payments( $args );

	remove_filter( 'posts_where', 'pl8app_filter_where_older_than_week' );

	if( $payments ) {
		foreach( $payments as $payment ) {
			if( 'pending' === $payment->post_status ) {
				$payment->status = 'abandoned';
				$payment->save();
			}
		}
	}
}
add_action( 'pl8app_weekly_scheduled_events', 'pl8app_mark_abandoned_orders' );

/**
 * Listens to the updated_postmeta hook for our backwards compatible payment_meta updates, and runs through them
 *
 * @since  1.0.0
 * @param  int $meta_id    The Meta ID that was updated
 * @param  int $object_id  The Object ID that was updated (post ID)
 * @param  string $meta_key   The Meta key that was updated
 * @param  string|int|float $meta_value The Value being updated
 * @return bool|int             If successful the number of rows updated, if it fails, false
 */
function pl8app_update_payment_backwards_compat( $meta_id, $object_id, $meta_key, $meta_value ) {

	$meta_keys = array( '_pl8app_payment_meta', '_pl8app_payment_tax' );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return;
	}

	global $wpdb;
	switch( $meta_key ) {

		case '_pl8app_payment_meta':
			$meta_value   = maybe_unserialize( $meta_value );

			if( ! isset( $meta_value['tax'] ) ){
				return;
			}

			$tax_value    = $meta_value['tax'];

			$data         = array( 'meta_value' => $tax_value );
			$where        = array( 'post_id'  => $object_id, 'meta_key' => '_pl8app_payment_tax' );
			$data_format  = array( '%f' );
			$where_format = array( '%d', '%s' );
			break;

		case '_pl8app_payment_tax':
			$tax_value    = ! empty( $meta_value ) ? $meta_value : 0;
			$current_meta = pl8app_get_payment_meta( $object_id, '_pl8app_payment_meta', true );

			$current_meta['tax'] = $tax_value;
			$new_meta            = maybe_serialize( $current_meta );

			$data         = array( 'meta_value' => $new_meta );
			$where        = array( 'post_id' => $object_id, 'meta_key' => '_pl8app_payment_meta' );
			$data_format  = array( '%s' );
			$where_format = array( '%d', '%s' );

			break;

	}

	$updated = $wpdb->update( $wpdb->postmeta, $data, $where, $data_format, $where_format );

	if ( ! empty( $updated ) ) {
		// Since we did a direct DB query, clear the postmeta cache.
		wp_cache_delete( $object_id, 'post_meta' );
	}

	return $updated;


}
add_action( 'updated_postmeta', 'pl8app_update_payment_backwards_compat', 10, 4 );

/**
 * Deletes pl8app_stats_ transients that have expired to prevent database clogs
 *
 * @since 1.0
 * @return void
*/
function pl8app_cleanup_stats_transients() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( defined( 'WP_INSTALLING' ) ) {
		return;
	}

	$now        = current_time( 'timestamp' );
	$transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%\_transient_timeout\_pl8app\_stats\_%' AND option_value+0 < $now LIMIT 0, 200;" );
	$to_delete  = array();

	if( ! empty( $transients ) ) {

		foreach( $transients as $transient ) {

			$to_delete[] = $transient->option_name;
			$to_delete[] = str_replace( '_timeout', '', $transient->option_name );

		}

	}

	if ( ! empty( $to_delete ) ) {

		$option_names = implode( "','", $to_delete );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')"  );

	}

}
add_action( 'pl8app_daily_scheduled_events', 'pl8app_cleanup_stats_transients' );

/**
 * Process an attempt to complete a recoverable payment.
 *
 * @since 1.0
 * @return void
 */
function pl8app_recover_payment() {

	if ( empty( $_GET['payment_id'] ) ) {
		return;
	}



	$payment = new pl8app_Payment( $_GET['payment_id'] );
	if ( $payment->ID !== (int) $_GET['payment_id'] ) {
		return;
	}



	if ( ! $payment->is_recoverable() ) {
		return;
	}


	if (
		// Logged in, but wrong user ID
		( is_user_logged_in() && $payment->user_id != get_current_user_id() )

		// ...OR...
		||

		// Logged out, but payment is for a user
		( ! is_user_logged_in() && ! empty( $payment->user_id ) )
	) {
		$redirect = get_permalink( pl8app_get_option( 'order_history_page' ) );
		pl8app_set_error( 'pl8app-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'pl8app' ) );
		wp_redirect( $redirect );
	}

	$payment->add_note( __( 'Payment recovery triggered URL', 'pl8app' ) );

	// Empty out the cart.
	PL8PRESS()->cart->empty_cart();


	// Recover any menuitems.
	foreach ( $payment->cart_details as $key => $menuitem ) {

		$menuitem['item_number']['options']['id'] = $menuitem['id'];

		$menuitem['item_number']['options']['addon_items'] = $menuitem['item_number']['options'];



		pl8app_add_to_cart( $menuitem['id'], $menuitem['item_number']['options'] );

		//Recover any item specific fees.
		if ( ! empty( $menuitem['fees'] ) ) {
			foreach ( $menuitem['fees'] as $fee ) {
				PL8PRESS()->fees->add_fee( $fee );
			}
		}
	}

	// Recover any global fees.
	foreach ( $payment->fees as $fee ) {
		PL8PRESS()->fees->add_fee( $fee );
	}

	// Recover any discounts.
	if ( 'none' !== $payment->discounts && ! empty( $payment->discounts ) ){
		$discounts = ! is_array( $payment->discounts ) ? explode( ',', $payment->discounts ) : $payment->discounts;

		foreach ( $discounts as $discount ) {
			pl8app_set_cart_discount( $discount );
		}
	}

	PL8PRESS()->session->set( 'pl8app_resume_payment', $payment->ID );

	$redirect_args = array( 'payment-mode' => $payment->gateway );
	$redirect      = add_query_arg( $redirect_args, pl8app_get_checkout_uri() );
	wp_redirect( $redirect );
	exit;
}
add_action( 'pl8app_recover_payment', 'pl8app_recover_payment' );

/**
 * If the payment trying to be recovered has a User ID associated with it, be sure it's the same user.
 *
 * @since 1.0
 * @return void
 */
function pl8app_recovery_user_mismatch() {
	if ( ! pl8app_is_checkout() ) {
		return;
	}

	$resuming_payment = PL8PRESS()->session->get( 'pl8app_resume_payment' );
	if ( $resuming_payment ) {
		$payment = new pl8app_Payment( $resuming_payment );
		if ( is_user_logged_in() && $payment->user_id != get_current_user_id() ) {
			pl8app_empty_cart();
			pl8app_set_error( 'pl8app-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'pl8app' ) );
			wp_redirect( get_permalink( pl8app_get_option( 'purchase_page' ) ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'pl8app_recovery_user_mismatch' );

/**
 * If the payment trying to be recovered has a User ID associated with it, we need them to log in.
 *
 * @since 1.0
 * @return void
 */
function pl8app_recovery_force_login_fields() {
	$resuming_payment = PL8PRESS()->session->get( 'pl8app_resume_payment' );
	if ( $resuming_payment ) {
		$payment        = new pl8app_Payment( $resuming_payment );
		$requires_login = pl8app_no_guest_checkout();
		if ( ( $requires_login && ! is_user_logged_in() ) && ( $payment->user_id > 0 && ( ! is_user_logged_in() ) ) ) {
			?>
			<div class="pl8app-alert pl8app-alert-info">
				<p><?php _e( 'To complete this payment, please login to your account.', 'pl8app' ); ?></p>
				<p>
					<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'pl8app' ); ?>">
						<?php _e( 'Lost Password?', 'pl8app' ); ?>
					</a>
				</p>
			</div>
			<?php
			do_action( 'pl8app_purchase_form_login_fields' );
		}
	}
}
add_action( 'pl8app_purchase_form_before_register_login', 'pl8app_recovery_force_login_fields' );

/**
 * When processing the payment, check if the resuming payment has a user id and that it matches the logged in user.
 *
 * @since 1.0
 * @param $verified_data
 * @param $post_data
 */
function pl8app_recovery_verify_logged_in( $verified_data, $post_data ) {
	$resuming_payment = PL8PRESS()->session->get( 'pl8app_resume_payment' );
	if ( $resuming_payment ) {
		$payment    = new pl8app_Payment( $resuming_payment );
		$same_user  = ! empty( $payment->user_id ) && ( is_user_logged_in() && $payment->user_id == get_current_user_id() );
		$same_email = strtolower( $payment->email ) === strtolower( $post_data['pl8app_email'] );

		if ( ( is_user_logged_in() && ! $same_user ) || ( ! is_user_logged_in() && (int) $payment->user_id > 0 && ! $same_email ) ) {
			pl8app_set_error( 'recovery_requires_login', __( 'To complete this payment, please login to your account.', 'pl8app' ) );
		}
	}
}
add_action( 'pl8app_checkout_error_checks', 'pl8app_recovery_verify_logged_in', 10, 2 );