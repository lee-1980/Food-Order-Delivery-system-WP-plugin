<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process the payment details edit
 *
 * @access      private
 * @since  1.0.0
 * @return      void
*/
function pl8app_update_payment_details( $data ) {

	check_admin_referer( 'pl8app_update_payment_details_nonce' );

	if( ! current_user_can( 'edit_shop_payments', $data['pl8app_payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$payment_id = absint( $data['pl8app_payment_id'] );
	$payment    = new pl8app_Payment( $payment_id );
	
	$addon_data = array();

	//Update payment meta
	$service_type = isset( $_POST['pla_service_type'] ) ? $_POST['pla_service_type'] : '';
	$service_time = isset( $_POST['pla_service_time'] ) ? $_POST['pla_service_time'] : '';
    $service_date = isset( $_POST['pla_service_date'] ) ? $_POST['pla_service_date'] : '';
	$order_status = isset( $_POST['pl8app_order_status'] ) ? $_POST['pl8app_order_status'] : '';

	update_post_meta( $payment_id , '_pl8app_delivery_type', $service_type );
	update_post_meta( $payment_id , '_pl8app_delivery_time', $service_time );
    update_post_meta( $payment_id , '_pl8app_delivery_date', $service_date );
	update_post_meta( $payment_id , '_order_status', $order_status );

	// Retrieve the payment ID
	$payment_id = absint( $data['pl8app_payment_id'] );
	$payment    = new pl8app_Payment( $payment_id );

	// Retrieve existing payment meta
	$meta        = $payment->get_meta();
	$user_info   = $payment->user_info;

	$status      = $data['pl8app-payment-status'];
	$unlimited   = isset( $data['pl8app-unlimited-menuitems'] ) ? '1' : '';
	$date        = sanitize_text_field( $data['pl8app-payment-date'] );
	$hour        = sanitize_text_field( $data['pl8app-payment-time-hour'] );

	// Restrict to our high and low
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute      = sanitize_text_field( $data['pl8app-payment-time-min'] );

	// Restrict to our high and low
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	$address     = array_map( 'trim', $data['pl8app-payment-address'][0] );

	$curr_total  = pl8app_sanitize_amount( $payment->total );
	$new_total   = pl8app_sanitize_amount( $_POST['pl8app-payment-total'] );
	$tax         = isset( $_POST['pl8app-payment-tax'] ) ? pl8app_sanitize_amount( $_POST['pl8app-payment-tax'] ) : 0;
	$date        = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id  = sanitize_text_field( $data['pl8app-current-customer'] );
	$new_customer_id   = sanitize_text_field( $data['customer-id'] );

	// Setup purchased items and price options
	$updated_menuitems = isset( $_POST['pl8app-payment-details-menuitems'] ) ? $_POST['pl8app-payment-details-menuitems'] : false;

	if ( $updated_menuitems ) {

		foreach ( $updated_menuitems as $cart_position => $menuitem ) {

			if( isset($menuitem['addon_items']) && !empty($menuitem['addon_items']) ) {
				foreach(  $menuitem['addon_items'] as $key => $addons ) {
					$addons = explode('|', $addons);
					if( is_array($addons) && !empty($addons) ) {
						$addon_data[$menuitem['id']][$key]['addon_item_name'] = $addons[0];
						$addon_data[$menuitem['id']][$key]['addon_id'] = $addons[1];
						$addon_data[$menuitem['id']][$key]['price'] = $addons[2];
						$addon_data[$menuitem['id']][$key]['quantity'] = $addons[3];
						
					}
				}
			}

			// If this item doesn't have a log yet, add one for each quantity count
			$has_log = absint( $menuitem['has_log'] );
			$has_log = empty( $has_log ) ? false : true;

			if ( $has_log ) {

				$quantity   = isset( $menuitem['quantity'] ) ? absint( $menuitem['quantity']) : 1;
				$item_price = isset( $menuitem['item_price'] ) ? $menuitem['item_price'] : 0;
				$item_tax   = isset( $menuitem['item_tax'] ) ? $menuitem['item_tax'] : 0;

				// Format any items that are currency.
				$item_price = pl8app_format_amount( $item_price );
				$item_tax    = pl8app_format_amount( $item_tax );

				$args = array(
					'item_price' => $item_price,
					'quantity'   => $quantity,
					'tax'        => $item_tax,
				);

				$payment->modify_cart_item( $cart_position, $args, $addon_data );

			} else {

				// This
				if ( empty( $menuitem['item_price'] ) ) {
					$menuitem['item_price'] = 0.00;
				}

				if ( empty( $menuitem['item_tax'] ) ) {
					$menuitem['item_tax'] = 0.00;
				}

				$item_price  = $menuitem['item_price'];
				$menuitem_id = absint( $menuitem['id'] );
				$quantity    = absint( $menuitem['quantity'] ) > 0 ? absint( $menuitem['quantity'] ) : 1;
				$price_id    = false;
				$tax         = $menuitem['item_tax'];

				if ( pl8app_has_variable_prices( $menuitem_id ) && isset( $menuitem['price_id'] ) ) {
					$price_id = absint( $menuitem['price_id'] );
				}

				// Set some defaults
				$args = array(
					'quantity'    => $quantity,
					'item_price'  => $item_price,
					'price_id'    => $price_id,
					'tax'         => $tax,
				);

				$payment->add_menuitem( $menuitem_id, $args, $addon_data[$menuitem['id']] );

			}

		}

		$deleted_menuitems = json_decode( stripcslashes( $data['pl8app-payment-removed'] ), true );
		foreach ( $deleted_menuitems as $deleted_menuitem ) {
			$deleted_menuitem = $deleted_menuitem[0];

			if ( empty ( $deleted_menuitem['id'] ) ) {
				continue;
			}

			$price_id = false;

			if ( pl8app_has_variable_prices( $deleted_menuitem['id'] ) && isset( $deleted_menuitem['price_id'] ) ) {
				$price_id = absint( $deleted_menuitem['price_id'] );
			}

			$cart_index = isset( $deleted_menuitem['cart_index'] ) ? absint( $deleted_menuitem['cart_index'] ) : false;

			$args = array(
				'quantity'   => (int) $deleted_menuitem['quantity'],
				'price_id'   => $price_id,
				'item_price' => (float) $deleted_menuitem['amount'],
				'cart_index' => $cart_index
			);

			$payment->remove_menuitem( $deleted_menuitem['id'], $args );

			do_action( 'pl8app_remove_menuitem_from_payment', $payment_id, $deleted_menuitem['id'] );

		}

	}

	do_action( 'pl8app_update_edited_purchase', $payment_id );

	$payment->date = $date;

	$customer_changed = false;

	if ( isset( $data['pl8app-new-customer'] ) && $data['pl8app-new-customer'] == '1' ) {

		$email      = isset( $data['pl8app-new-customer-email'] ) ? sanitize_text_field( $data['pl8app-new-customer-email'] ) : '';
		$names      = isset( $data['pl8app-new-customer-name'] ) ? sanitize_text_field( $data['pl8app-new-customer-name'] ) : '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( __( 'New Customers require a name and email address', 'pl8app' ) );
		}

		$customer = new pl8app_Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array( 'name' => $names, 'email' => $email );
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new customer, assume the previous customer
				$customer_changed = false;
				$customer = new pl8app_Customer( $curr_customer_id );
				pl8app_set_error( 'pl8app-payment-new-customer-fail', __( 'Error creating new customer', 'pl8app' ) );
			}
		}

		$new_customer_id = $customer->id;

		$previous_customer = new pl8app_Customer( $curr_customer_id );

		$customer_changed = true;

	} elseif ( $curr_customer_id !== $new_customer_id ) {

		$customer = new pl8app_Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new pl8app_Customer( $curr_customer_id );

		$customer_changed = true;

	} else {

		$customer = new pl8app_Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

	}

	// Setup first and last name from input values
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';
	if( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {

		// Remove the stats and payment from the previous customer and attach it to the new customer
		$previous_customer->remove_payment( $payment_id, false );
		$customer->attach_payment( $payment_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if( 'revoked' == $status || 'publish' == $status ) {

			$previous_customer->decrease_purchase_count();
			$previous_customer->decrease_value( $new_total );

			$customer->increase_purchase_count();
			$customer->increase_value( $new_total );
		}

		$payment->customer_id = $customer->id;
	}

	// Set new meta values
	$payment->user_id        = $customer->user_id;
	$payment->email          = $customer->email;
	$payment->first_name     = $first_name;
	$payment->last_name      = $last_name;
	$payment->address        = $address;

	$payment->total          = $new_total;
	$payment->tax            = $tax;

	$payment->has_unlimited_menuitems = $unlimited;

	// Check for payment notes
	if ( ! empty( $data['pl8app-payment-note'] ) ) {

		$note  = wp_kses( $data['pl8app-payment-note'], array() );
		pl8app_insert_payment_note( $payment->ID, $note );

	}

	// Set new status
	$payment->status = $status;

	// Adjust total store earnings if the payment total has been changed
	if ( $new_total !== $curr_total && ( 'publish' == $status || 'revoked' == $status ) ) {

		if ( $new_total > $curr_total ) {
			// Increase if our new total is higher
			$difference = $new_total - $curr_total;
			pl8app_increase_total_earnings( $difference );

		} elseif ( $curr_total > $new_total ) {
			// Decrease if our new total is lower
			$difference = $curr_total - $new_total;
			pl8app_decrease_total_earnings( $difference );

		}

	}

	$updated = $payment->save();

  	$order_status = isset( $_POST['pl8app_order_status'] ) ? $_POST['pl8app_order_status'] : '';

	pl8app_update_order_status( $payment_id, $order_status );

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Payment', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
	}

	do_action( 'pl8app_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details&pl8app-message=payment-updated&id=' . $payment_id ) );
	exit;
}
add_action( 'pl8app_update_payment_details', 'pl8app_update_payment_details' );

function pl8app_create_payment_details ($data){

    check_admin_referer( 'pl8app_create_payment_details_nonce' );

    if( ! current_user_can( 'edit_shop_payments') ) {
        wp_die( __( 'You do not have permission to create new order', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
    }


    // Setup purchased items and price options
    $updated_menuitems = isset( $_POST['pl8app-payment-details-menuitems'] ) ? $_POST['pl8app-payment-details-menuitems'] : false;

    if(!$updated_menuitems || !is_array($updated_menuitems)){
        wp_die( __( 'MenuItems should be added!', 'pl8app' ) );
    }

    $addon_data = array();

    $new_customer_id   = sanitize_text_field( !empty($data['customer-id'])?$data['customer-id']:'');

    if ( isset( $data['pl8app-new-customer'] ) && $data['pl8app-new-customer'] == '1' ) {

        $email      = isset( $data['pl8app-new-customer-email'] ) ? sanitize_text_field( $data['pl8app-new-customer-email'] ) : '';
        $names      = isset( $data['pl8app-new-customer-name'] ) ? sanitize_text_field( $data['pl8app-new-customer-name'] ) : '';
        $phone      = isset( $data['pl8app-new-customer-phone'] ) ? sanitize_text_field( $data['pl8app-new-customer-phone'] ) : '';

        if ( empty( $email ) || empty( $names ) ) {
            wp_die( __( 'New Customers require a name and email address', 'pl8app' ) );
        }

        $customer = new pl8app_Customer( $email );
        if ( empty( $customer->id ) ) {
            $customer_data = array( 'name' => $names, 'email' => $email);
            $user_id       = email_exists( $email );
            if ( false !== $user_id ) {
                $customer_data['user_id'] = $user_id;
            }

            if ( ! $customer->create( $customer_data ) ) {
                // Failed to crete the new customer, assume the previous customer
                $customer_changed = false;
                pl8app_set_error( 'pl8app-payment-new-customer-fail', __( 'Error creating new customer', 'pl8app' ) );
            }
        }


    } elseif ( !empty($new_customer_id) ) {

        $customer = new pl8app_Customer( $new_customer_id );
        $email    = $customer->email;
        $names    = $customer->name;
    }
    else{
        wp_die( __( 'Invalid Customer Details', 'pl8app' ) );
    }

    // Setup first and last name from input values
    $names      = explode( ' ', $names );
    $first_name = ! empty( $names[0] ) ? $names[0] : '';
    $last_name  = '';
    if( ! empty( $names[1] ) ) {
        unset( $names[0] );
        $last_name = implode( ' ', $names );
    }


    $payment = new pl8app_Payment();
    $created = $payment->save();

    if ( 0 === $created ) {
        wp_die( __( 'Error Creating Order', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
    }

    $payment_id = $payment->ID;

    $payment->customer_id = $customer->id;
    $payment->user_id        = $customer->user_id;
    $payment->email          = $customer->email;
    $payment->first_name     = $first_name;
    $payment->last_name      = $last_name;


    //Update payment meta
    $service_type = isset( $_POST['pla_service_type'] ) ? $_POST['pla_service_type'] : '';
    $service_time = isset( $_POST['pla_service_time'] ) ? $_POST['pla_service_time'] : '';
    $service_date = isset( $_POST['pla_service_date'] ) ? $_POST['pla_service_date'] : '';

    update_post_meta( $payment_id , '_pl8app_delivery_type', $service_type );
    update_post_meta( $payment_id , '_pl8app_delivery_time', $service_time );
    update_post_meta( $payment_id , '_pl8app_delivery_date', $service_date );


    $flat = !empty($_POST['pl8app-apt-suite']) ? $_POST['pl8app-apt-suite'] : '';
    $city = !empty($_POST['pl8app-city']) ? $_POST['pl8app-city'] : '';
    $postcode = !empty($_POST['pl8app-postcode']) ? $_POST['pl8app-postcode'] : '';
    $street = !empty($_POST['pl8app-street-address']) ? $_POST['pl8app-street-address'] : '';

    update_post_meta( $payment_id , '_pl8app_delivery_address', array(
        'address' => $street,
        'postcode' => $postcode,
        'city' => $city,
        'flat' => $flat
    ) );

    if(isset($phone)){
        $meta_info = $payment->get_meta('_pl8app_payment_meta', true);
        $meta_info['phone'] = $phone;
        $payment->update_meta('_pl8app_payment_meta', $meta_info);
    }



    if ( $updated_menuitems ) {


        foreach ( $updated_menuitems as $cart_position => $menuitem ) {

            if( isset($menuitem['addon_items']) && !empty($menuitem['addon_items']) ) {
                foreach(  $menuitem['addon_items'] as $key => $addons ) {
                    $addons = explode('|', $addons);
                    if( is_array($addons) && !empty($addons) ) {
                        $addon_data[$menuitem['id']][$key]['addon_item_name'] = $addons[0];
                        $addon_data[$menuitem['id']][$key]['addon_id'] = $addons[1];
                        $addon_data[$menuitem['id']][$key]['price'] = $addons[2];
                        $addon_data[$menuitem['id']][$key]['quantity'] = $addons[3];

                    }
                }
            }


            // If this item doesn't have a log yet, add one for each quantity count
            $has_log = absint( $menuitem['has_log'] );
            $has_log = empty( $has_log ) ? false : true;

            if ( $has_log ) {

                $quantity   = isset( $menuitem['quantity'] ) ? absint( $menuitem['quantity']) : 1;
                $item_price = isset( $menuitem['item_price'] ) ? $menuitem['item_price'] : 0;
                $item_tax   = isset( $menuitem['item_tax'] ) ? $menuitem['item_tax'] : 0;

                // Format any items that are currency.
                $item_price = pl8app_format_amount( $item_price );
                $item_tax    = pl8app_format_amount( $item_tax );

                $args = array(
                    'item_price' => $item_price,
                    'quantity'   => $quantity,
                    'tax'        => $item_tax,
                );

                $payment->modify_cart_item( $cart_position, $args, $addon_data );

            } else {

                // This
                if ( empty( $menuitem['item_price'] ) ) {
                    $menuitem['item_price'] = 0.00;
                }

                if ( empty( $menuitem['item_tax'] ) ) {
                    $menuitem['item_tax'] = 0.00;
                }

                $item_price  = $menuitem['item_price'];
                $menuitem_id = absint( $menuitem['id'] );
                $quantity    = absint( $menuitem['quantity'] ) > 0 ? absint( $menuitem['quantity'] ) : 1;
                $price_id    = false;
                $tax         = $menuitem['item_tax'];

                if ( pl8app_has_variable_prices( $menuitem_id ) && isset( $menuitem['price_id'] ) ) {
                    $price_id = absint( $menuitem['price_id'] );
                }

                // Set some defaults
                $args = array(
                    'quantity'    => $quantity,
                    'item_price'  => $item_price,
                    'price_id'    => $price_id,
                    'tax'         => $tax,
                );

                $payment->add_menuitem( $menuitem_id, $args, $addon_data[$menuitem['id']] );
            }
        }
    }

    $updated = $payment->save();

    if ( 0 === $updated ) {
        wp_die( __( 'Error Updating Payment', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 400 ) );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details&id=' . $payment_id ) );
    exit;

}
add_action( 'pl8app_create_payment_details', 'pl8app_create_payment_details');

/**
 * Trigger a Purchase Deletion
 *
 * @since  1.0.0
 * @param $data Arguments passed
 * @return void
 */
function pl8app_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'pl8app_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
		}

		pl8app_delete_purchase( $payment_id );
		wp_redirect( admin_url( 'admin.php?page=pl8app-payment-history&pl8app-message=payment_deleted' ) );
		pl8app_die();
	}
}
add_action( 'pl8app_delete_payment', 'pl8app_trigger_purchase_delete' );

function pl8app_ajax_store_payment_note() {

	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );

	if( ! current_user_can( 'edit_shop_payments', $payment_id ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	if( empty( $payment_id ) )
		die( '-1' );

	if( empty( $note ) )
		die( '-1' );

	$note_id = pl8app_insert_payment_note( $payment_id, $note );
	die( pl8app_get_payment_note_html( $note_id ) );
}
add_action( 'wp_ajax_pl8app_insert_payment_note', 'pl8app_ajax_store_payment_note' );

/**
 * Triggers a payment note deletion without ajax
 *
 * @since  1.0.0
 * @param array $data Arguments passed
 * @return void
*/
function pl8app_trigger_payment_note_deletion( $data ) {

	if( ! wp_verify_nonce( $data['_wpnonce'], 'pl8app_delete_payment_note_' . $data['note_id'] ) )
		return;

	if( ! current_user_can( 'edit_shop_payments', $data['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$edit_order_url = admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details&pl8app-message=payment-note-deleted&id=' . absint( $data['payment_id'] ) );

	pl8app_delete_payment_note( $data['note_id'], $data['payment_id'] );

	wp_redirect( $edit_order_url );
}
add_action( 'pl8app_delete_payment_note', 'pl8app_trigger_payment_note_deletion' );

/**
 * Delete a payment note deletion with ajax
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_ajax_delete_payment_note() {

	if( ! current_user_can( 'edit_shop_payments', $_POST['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	if( pl8app_delete_payment_note( $_POST['note_id'], $_POST['payment_id'] ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}
add_action( 'wp_ajax_pl8app_delete_payment_note', 'pl8app_ajax_delete_payment_note' );
