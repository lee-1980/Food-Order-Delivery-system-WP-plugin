<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the pl8app template for a privacy policy.
 *
 * Note, this is just a suggestion and should be customized to meet your businesses needs.
 *
 * @since 2.9.2
 */
function pl8app_register_privacy_policy_template() {

	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = wp_kses_post( apply_filters( 'pl8app_privacy_policy_content', __( 'We collect information about you during the checkout process on our store. This information may include, but is not limited to, your name, billing address, shipping address, email address, phone number, credit card/payment details and any other details that might be requested from you for the purpose of processing your orders. Handling this data also allows us to:
- Send you important account/order/service information.
- Respond to your queries, refund requests, or complaints.
- Process payments and to prevent fraudulent transactions. We do this on the basis of our legitimate business interests.
- Set up and administer your account, provide technical and/or customer support, and to verify your identity.
', 'pl8app' ) ) );

	$additional_collection = array(
		__( 'Location and traffic data (including IP address and browser type) if you place an order, or if we need to estimate taxes and shipping costs based on your location.', 'pl8app' ),
		__( 'Product pages visited and content viewed while your session is active.', 'pl8app' ),
		__( 'Your comments and product reviews if you choose to leave them on our website.', 'pl8app' ),
		__( 'Account email/password to allow you to access your account, if you have one.', 'pl8app' ),
		__( 'If you choose to create an account with us, your name, address, and email address, which will be used to populate the checkout for future orders.', 'pl8app' ),
	);

	$additional_collection = apply_filters( 'pl8app_privacy_policy_additional_collection', $additional_collection );

	$content .= __( 'Additionally we may also collect the following information:', 'pl8app' ) . "\n";
	if ( ! empty( $additional_collection ) ) {
		foreach ( $additional_collection as $item ) {
			$content .= '- ' . $item . "\n";
		}
	}

	wp_add_privacy_policy_content( 'pl8app', wpautop( $content ) );
}
add_action( 'admin_init', 'pl8app_register_privacy_policy_template' );

/** Helper Functions */

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will have the first character remain and the second be a *.
 *
 * @since 2.9.2
 * @param string $string
 *
 * @return string
 */
function pl8app_mask_string( $string = '' ) {

	if ( empty( $string ) ) {
		return '';
	}

	$first_char  = substr( $string, 0, 1 );
	$last_char   = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {

		$total_stars = strlen( $string ) - 2;
		$masked_string = $first_char . str_repeat( '*', $total_stars ) . $last_char;

	} elseif ( strlen( $string ) === 2 ) {

		$masked_string = $first_char . '*';

	}

	return $masked_string;

}

/**
 * Given a domain, mask it with the * character.
 *
 * TLD parts will remain intact (.com, .co.uk, etc). All subdomains will be masked t**t.e*****e.co.uk.
 *
 * @since 2.9.2
 * @param string $domain
 *
 * @return string
 */
function pl8app_mask_domain( $domain = '' ) {

	if ( empty( $domain ) ) {
		return '';
	}

	$domain_parts = explode( '.', $domain );

	if ( count( $domain_parts ) === 2 ) {

		// We have a single entry tld like .org or .com
		$domain_parts[0] = pl8app_mask_string( $domain_parts[0] );

	} else {

		$part_count     = count( $domain_parts );
		$possible_cctld = strlen( $domain_parts[ $part_count - 2 ] ) <= 3 ? true : false;

		$mask_parts = $possible_cctld ? array_slice( $domain_parts, 0, $part_count - 2 ) : array_slice( $domain_parts, 0, $part_count - 1 );

		$i = 0;
		while ( $i < count( $mask_parts ) ) {
			$domain_parts[ $i ] = pl8app_mask_string( $domain_parts[ $i ]);
			$i++;
		}

	}

	return implode( '.', $domain_parts );
}

/**
 * Given an email address, mask the name and domain according to domain and string masking functions.
 *
 * Will result in an email address like a***n@e*****e.org for admin@example.org.
 *
 * @since 2.9.2
 * @param $email_address
 *
 * @return string
 */
function pl8app_pseudo_mask_email( $email_address ) {
	if ( ! is_email( $email_address ) ) {
		return $email_address;
	}

	$email_parts = explode( '@', $email_address );
	$name        = pl8app_mask_string( $email_parts[0] );
	$domain      = pl8app_mask_domain( $email_parts[1] );

	$email_address = $name . '@' . $domain;


	return $email_address;
}

/**
 * Log the privacy and terms timestamp for the last completed purchase for a customer.
 *
 * Stores the timestamp of the last time the user clicked the 'complete purchase' button for the Agree to Terms and/or
 * Privacy Policy checkboxes during the checkout process.
 *
 * @since 2.9.2
 *
 * @param $payment_id
 * @param $payment_data
 *
 * @return void
 */
function pl8app_log_terms_and_privacy_times( $payment_id, $payment_data ) {
	$payment  = pl8app_get_payment( $payment_id );
	$customer = new pl8app_Customer( $payment->customer_id );

	if ( empty( $customer->id ) ) {
		return;
	}

	if ( ! empty( $payment_data['agree_to_terms_time'] ) ) {
		$customer->add_meta( 'agree_to_terms_time', $payment_data['agree_to_terms_time'] );
	}

	if ( ! empty( $payment_data['agree_to_privacy_time'] ) ) {
		$customer->add_meta( 'agree_to_privacy_time', $payment_data['agree_to_privacy_time'] );
	}
}
add_action( 'pl8app_insert_payment', 'pl8app_log_terms_and_privacy_times', 10, 2 );

/*
 * Return an anonymized email address.
 *
 * While WP Core supports anonymizing email addresses with the wp_privacy_anonymize_data function,
 * it turns every email address into deleted@site.invalid, which does not work when some purchase/customer records
 * are still needed for legal and regulatory reasons.
 *
 * This function will anonymize the email with an MD5 that is salted
 * and given a randomized uniqid prefixed with the store URL in order to prevent connecting a single customer across
 * multiple stores, as well as the timestamp at the time of anonymization (so it trying the same email again will not be
 * repeatable and therefore connected), and return the email address as <hash>@site.invalid.
 *
 * @since 2.9.2
 *
 * @param string $email_address
 *
 * @return string
 */
function pl8app_anonymize_email( $email_address ) {

	if ( empty( $email_address ) ) {
		return $email_address;
	}

	$email_address    = strtolower( $email_address );
	$email_parts      = explode( '@', $email_address );
	$anonymized_email = wp_hash( uniqid( get_option( 'site_url' ), true ) . $email_parts[0] . current_time( 'timestamp' ), 'nonce' );


	return $anonymized_email . '@site.invalid';
}

/**
 * Given a customer ID, anonymize the data related to that customer.
 *
 * Only the customer record is affected in this function. The data that is changed:
 * - The name is changed to 'Anonymized Customer'
 * - The email address is anonymized, but kept in a format that passes is_email checks
 * - The date created is set to the timestamp of 0 (January 1, 1970)
 * - Notes are fully cleared
 * - Any additional email addresses are removed
 *
 * Once completed, a note is left stating when the customer was anonymized.
 *
 * @param int $customer_id
 *
 * @return array
 */
function _pl8app_anonymize_customer( $customer_id = 0 ) {

	$customer = new pl8app_Customer( $customer_id );
	if ( empty( $customer->id ) ) {
		return array( 'success' => false, 'message' => sprintf( __( 'No customer with ID %d', 'pl8app' ), $customer_id ) );
	}

	/**
	 * Determines if this customer should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a customer. A sample use case
	 * would be if the customer has pending orders, and that payment requires shipping, anonymizing the customer may
	 * not be ideal.
	 *
	 * @since 2.9.2
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the customer should be anonymized.
	 *     @type string $message          A message to display if the customer could not be anonymized.
	 * }
	 */
	$should_anonymize_customer = apply_filters( 'pl8app_should_anonymize_customer', array( 'should_anonymize' => true, 'message' => '' ), $customer );

	if ( empty( $should_anonymize_customer['should_anonymize'] ) ) {
		return array( 'success' => false, 'message' => $should_anonymize_customer['message'] );
	}

	// Now we should look at payments this customer has associated, and if there are any payments that should not be modified,
	// do not modify the customer.
	$payments = pl8app_get_payments( array(
		'customer' => $customer->id,
		'output'   => 'payments',
		'number'   => -1,
	) );

	foreach ( $payments as $payment ) {
		$action = _pl8app_privacy_get_payment_action( $payment );
		if ( 'none' === $action ) {
			return array(
				'success' => false,
				'message' => __( 'Customer could not be anonymized due to payments that could not be anonymized or deleted.', 'pl8app' )
			);
		}
	}

	// Loop through all their email addresses, and remove any additional email addresses.
	foreach ( $customer->emails as $email ) {
		$customer->remove_email( $email );
	}

	if ( $customer->user_id > 0 ) {
		delete_user_meta( $customer->user_id, '_pl8app_user_address' );
	}

	$customer->update( array(
		'name'         => __( 'Anonymized Customer', 'pl8app' ),
		'email'        => pl8app_anonymize_email( $customer->email ),
		'date_created' => date( 'Y-m-d H:i:s', 0 ),
		'notes'        => '',
		'user_id'      => 0,
	) );

	/**
	 * Run further anonymization on a customer
	 *
	 * Developers and extensions can use the pl8app_Customer object passed into the pl8app_anonymize_customer action
	 * to complete further anonymization.
	 *
	 * @since 2.9.2
	 *
	 * @param pl8app_Customer $customer The pl8app_Customer object that was found.
	 */
	do_action( 'pl8app_anonymize_customer', $customer );

	$customer->add_note( __( 'Customer anonymized successfully', 'pl8app' ) );
	return array( 'success' => true, 'message' => sprintf( __( 'Customer ID %d successfully anonymized.', 'pl8app' ), $customer_id ) );

}

/**
 * Given a payment ID, anonymize the data related to that payment.
 *
 * Only the payment record is affected in this function. The data that is changed:
 * - First Name is made blank
 * - Last  Name is made blank
 * - All email addresses are converted to the anonymized email address on the customer
 * - The IP address is run to only be the /24 IP Address (ending in .0) so it cannot be traced back to a user
 * - Address line 1 is made blank
 * - Address line 2 is made blank
 *
 * @param int $payment_id
 *
 * @return array
 */
function _pl8app_anonymize_payment( $payment_id = 0 ) {

	$payment = pl8app_get_payment( $payment_id );
	if ( false === $payment ) {
		return array( 'success' => false, 'message' => sprintf( __( 'No payment with ID %d.', 'pl8app' ), $payment_id ) );
	}

	/**
	 * Determines if this payment should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a payment. A sample use case
	 * would be if the payment is pending orders, and the payment requires shipping, anonymizing the payment may
	 * not be ideal.
	 *
	 * @since 2.9.2
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the payment should be anonymized.
	 *     @type string $message          A message to display if the customer could not be anonymized.
	 * }
	 */
	$should_anonymize_payment = apply_filters( 'pl8app_should_anonymize_payment', array( 'should_anonymize' => true, 'message' => '' ), $payment );

	if ( empty( $should_anonymize_payment['should_anonymize'] ) ) {
		return array( 'success' => false, 'message' => $should_anonymize_payment['message'] );
	}

	$action = _pl8app_privacy_get_payment_action( $payment );

	switch( $action ) {

		case 'none':
		default:
			$return = array(
				'success' => false,
				'message' => sprintf( __( 'Payment not modified, due to status: %s.', 'pl8app' ), $payment->status )
			);
			break;

		case 'delete':
			pl8app_delete_purchase( $payment->ID, true, true );

			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Payment %d with status %s deleted.', 'pl8app' ), $payment->ID, $payment->status )
			);
			break;

		case 'anonymize':
			$customer = new pl8app_Customer( $payment->customer_id );

			$payment->ip    = wp_privacy_anonymize_ip( $payment->ip );
			$payment->email = $customer->email;

			// Anonymize the line 1 and line 2 of the address. City, state, zip, and country are possibly needed for taxes.
			$address = $payment->address;
			if ( isset( $address['line1'] ) ) {
				$address['line1'] = '';
			}

			if ( isset( $address['line2'] ) ) {
				$address['line2'] = '';
			}

			$payment->address = $address;

			$payment->first_name = '';
			$payment->last_name  = '';


			wp_update_post( array(
				'ID' => $payment->ID,
				'post_title' => __( 'Anonymized Customer', 'pl8app' ),
				'post_name'  => sanitize_title( __( 'Anonymized Customer', 'pl8app' ) ),
			) );

			// Because we changed the post_name, WordPress sets a meta on the item for the `old slug`, we need to kill that.
			delete_post_meta( $payment->ID, '_wp_old_slug' );

			/**
			 * Run further anonymization on a payment
			 *
			 * Developers and extensions can use the pl8app_Payment object passed into the pl8app_anonymize_payment action
			 * to complete further anonymization.
			 *
			 * @since 2.9.2
			 *
			 * @param pl8app_Payment $payment The pl8app_Payment object that was found.
			 */
			do_action( 'pl8app_anonymize_payment', $payment );

			$payment->save();
			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Payment ID %d successfully anonymized.', 'pl8app' ), $payment_id )
			);
			break;
	}

	return $return;
}

/**
 * Given an pl8app_Payment, determine what action should be taken during the eraser processes.
 *
 * @since 2.9.2
 *
 * @param pl8app_Payment $payment
 *
 * @return string
 */
function _pl8app_privacy_get_payment_action( pl8app_Payment $payment ) {

	$action = pl8app_get_option( 'payment_privacy_status_action_' . $payment->status, false );

	// If the store owner has not saved any special settings for the actions to be taken, use defaults.
	if ( empty( $action ) ) {

		switch ( $payment->status ) {

			case 'publish':
			case 'refunded':
			case 'revoked':
				$action = 'anonymize';
				break;

			case 'failed':
			case 'abandoned':
				$action = 'delete';
				break;

			case 'pending':
			case 'processing':
			default:
				$action = 'none';
				break;

		}

	}

	/**
	 * Allow filtering of what type of action should be taken for a payment.
	 *
	 * Developers and extensions can use this filter to modify how pl8app will treat an order
	 * that has been requested to be deleted or anonymized.
	 *
	 * @since 2.9.2
	 *
	 * @param string      $action  What action will be performed (none, delete, anonymize)
	 * @param pl8app_Payment $payment The pl8app_Payment object that has been requested to be anonymized or deleted.
	 */
	$action = apply_filters( 'pl8app_privacy_payment_status_action_' . $payment->status, $action, $payment );
	return $action;

}

/**
 * Since our eraser callbacks need to look up a stored customer ID by hashed email address, developers can use this
 * to retrieve the customer ID associated with an email address that's being requested to be deleted even after the
 * customer has been anonymized.
 *
 * @since 2.9.2
 *
 * @param $email_address
 *
 * @return pl8app_Customer
 */
function _pl8app_privacy_get_customer_id_for_email( $email_address ) {
	$customer_id = get_option( 'pl8app_priv_' . md5( $email_address ), true );
	$customer    = new pl8app_Customer( $customer_id );

	return $customer;
}

/** Exporter Functions */

/**
 * Register any of our Privacy Data Exporters
 *
 * @since 2.9.2
 *
 * @param $exporters
 *
 * @return array
 */
function pl8app_register_privacy_exporters( $exporters ) {

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Customer Record', 'pl8app' ),
		'callback'               => 'pl8app_privacy_customer_record_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Billing Information', 'pl8app' ),
		'callback'               => 'pl8app_privacy_billing_information_exporter',
	);


	$exporters[] = array(
		'exporter_friendly_name' => __( 'Menu Items', 'pl8app' ),
		'callback'               => 'pl8app_privacy_file_menuitem_log_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'API Access Logs', 'pl8app' ),
		'callback'               => 'pl8app_privacy_api_access_log_exporter',
	);

	return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'pl8app_register_privacy_exporters' );

/**
 * Retrieves the Customer record for the Privacy Data Exporter
 *
 * @since 2.9.2
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function pl8app_privacy_customer_record_exporter( $email_address = '', $page = 1 ) {

	$customer    = new pl8app_Customer( $email_address );

	if ( empty( $customer->id ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_data = array(
		'group_id'    => 'pl8app-customer-record',
		'group_label' => __( 'Customer Record', 'pl8app' ),
		'item_id'     => "pl8app-customer-record-{$customer->id}",
		'data'        => array(
			array(
				'name'  => __( 'Customer ID', 'pl8app' ),
				'value' => $customer->id
			),
			array(
				'name'  => __( 'Primary Email', 'pl8app' ),
				'value' => $customer->email
			),
			array(
				'name'  => __( 'Name', 'pl8app' ),
				'value' => $customer->name
			),
			array(
				'name'  => __( 'Date Created', 'pl8app' ),
				'value' => $customer->date_created
			),
			array(
				'name'  => __( 'All Email Addresses', 'pl8app' ),
				'value' => implode( ', ', $customer->emails )
			),
		)
	);

	$agree_to_terms_time = $customer->get_meta( 'agree_to_terms_time', false );
	if ( ! empty( $agree_to_terms_time ) ) {
		foreach ( $agree_to_terms_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Terms', 'pl8app' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	$agree_to_privacy_time = $customer->get_meta( 'agree_to_privacy_time', false );
	if ( ! empty( $agree_to_privacy_time ) ) {
		foreach ( $agree_to_privacy_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Privacy Policy', 'pl8app' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	$export_data = apply_filters( 'pl8app_privacy_customer_record', $export_data, $customer );

	return array( 'data' => array( $export_data ), 'done' => true );
}

/**
 * Retrieves the billing information for the Privacy Exporter
 *
 * @since 2.9.2
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function pl8app_privacy_billing_information_exporter( $email_address = '', $page = 1 ) {

	$customer = new pl8app_Customer( $email_address );
	$payments = pl8app_get_payments( array(
		'customer' => $customer->id,
		'output'   => 'payments',
		'page'     => $page,
	) );

	// If we haven't found any payments for this page, just return that we're done.
	if ( empty( $payments ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_items = array();
	foreach ( $payments as $payment ) {

		$order_items = array();
		foreach ( $payment->menuitems as $cart_item ) {
			$menuitem = new pl8app_Menuitem( $cart_item['id'] );
			$name     = $menuitem->get_name();

			if ( $menuitem->has_variable_prices() && isset( $cart_item['options']['price_id'] ) ) {
				$variation_name = pl8app_get_price_option_name( $menuitem->ID, $cart_item['options']['price_id'] );
				if ( ! empty( $variation_name ) ) {
					$name .= ' - ' . $variation_name;
				}
			}

			$order_items[] = $name . ' &times; ' . $cart_item['quantity'];
		}

		$items_purchased = implode( ', ', $order_items );

		$billing_name = array();
		if ( ! empty( $payment->user_info['first_name'] ) ) {
			$billing_name[] = $payment->user_info['first_name'];
		}

		if ( ! empty( $payment->user_info['last_name'] ) ) {
			$billing_name[] = $payment->user_info['last_name'];
		}
		$billing_name = implode( ' ', array_values( $billing_name ) );

		$billing_street = array();
		if ( ! empty( $payment->address['line1'] ) ) {
			$billing_street[] = $payment->address['line1'];
		}

		if ( ! empty( $payment->address['line2'] ) ) {
			$billing_street[] = $payment->address['line2'];
		}
		$billing_street = implode( "\n", array_values( $billing_street ) );


		$billing_city_state = array();
		if ( ! empty( $payment->address['city'] ) ) {
			$billing_city_state[] = $payment->address['city'];
		}

		if ( ! empty( $payment->address['state'] ) ) {
			$billing_city_state[] = $payment->address['state'];
		}
		$billing_city_state = implode( ', ', array_values( $billing_city_state ) );

		$billing_country_postal = array();
		if ( ! empty( $payment->address['zip'] ) ) {
			$billing_country_postal[] = $payment->address['zip'];
		}

		if ( ! empty( $payment->address['country'] ) ) {
			$billing_country_postal[] = $payment->address['country'];
		}
		$billing_country_postal = implode( "\n", array_values( $billing_country_postal ) );

		$full_billing_address = '';
		if ( ! empty( $billing_name ) ) {
			$full_billing_address .= $billing_name . "\n";
		}

		if ( ! empty( $billing_street ) ) {
			$full_billing_address .= $billing_street . "\n";
		}

		if ( ! empty( $billing_city_state ) ) {
			$full_billing_address .= $billing_city_state . "\n";
		}

		if ( ! empty( $billing_country_postal ) ) {
			$full_billing_address .= $billing_country_postal . "\n";
		}


		$data_points = array(
			array(
				'name'  => __( 'Order ID / Number', 'pl8app' ),
				'value' => $payment->number,
			),
			array(
				'name' => __( 'Order Date', 'pl8app' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $payment->date ) ),
			),
			array(
				'name' => __( 'Order Completed Date', 'pl8app' ),
				'value' =>  ! empty( $payment->completed_date )
					? date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $payment->completed_date ) )
					: '',
			),
			array(
				'name' => __( 'Order Total', 'pl8app' ),
				'value' => pl8app_currency_filter( pl8app_format_amount( $payment->total ), $payment->currency ),
			),
			array(
				'name' => __( 'Order Items', 'pl8app' ),
				'value' => $items_purchased,
			),
			array(
				'name'  => __( 'Email Address', 'pl8app' ),
				'value' => ! empty( $payment->email ) ? $payment->email : '',
			),
			array(
				'name'  => __( 'Billing Address', 'pl8app' ),
				'value' => $full_billing_address,
			),
			array(
				'name'  => __( 'IP Address', 'pl8app' ),
				'value' => ! empty( $payment->ip ) ? $payment->ip : '',
			),
			array(
				'name'  => __( 'Status', 'pl8app' ),
				'value' => pl8app_get_payment_status_label( $payment->status ),
			),
		);

		$data_points = apply_filters( 'pl8app_privacy_order_details_item', $data_points, $payment );

		$export_items[] = array(
			'group_id'    => 'pl8app-order-details',
			'group_label' => __( 'Customer Orders', 'pl8app' ),
			'item_id'     => "pl8app-order-details-{$payment->ID}",
			'data'        => $data_points,
		);

	}


	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);

}

/**
 * Adds the file menuitem logs for a customer to the WP Core Privacy Data exporter
 *
 * @since 2.9.2
 *
 * @param string $email_address The email address to look up file menuitem logs for.
 * @param int    $page          The page of logs to request.
 *
 * @return array
 */
function pl8app_privacy_file_menuitem_log_exporter( $email_address = '', $page = 1 ) {
	global $pl8app_logs;

	$customer = new pl8app_Customer( $email_address );
	$log_query = array(
		'log_type'               => 'file_menuitem',
		'posts_per_page'         => 100,
		'paged'                  => $page,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'   => '_pl8app_log_customer_id',
				'value' => $customer->id,
			)
		)
	);

	$logs = $pl8app_logs->get_connected_logs( $log_query );

	// If we haven't found any payments for this page, just return that we're done.
	if ( empty( $logs ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$found_menuitems = array();

	$export_items = array();
	foreach ( $logs as $log ) {

		$log_meta = get_post_meta( $log->ID );

		if ( ! isset( $found_menuitems[ $log->post_parent ] ) ) {
			$found_menuitems[ $log->post_parent ] = new pl8app_Menuitem( $log->post_parent );
		}

		$menuitem = $found_menuitems[ $log->post_parent ];

		$data_points = array(
			array(
				'name' => __( 'Date of Order', 'pl8app' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $log->post_date ) ),
			),
			array(
				'name' => __( 'Menu Item Name', 'pl8app' ),
				'value' =>  $menuitem->get_name(),
			),
			array(
				'name' => __( 'Order ID', 'pl8app' ),
				'value' => $log_meta['_pl8app_log_payment_id'][0],
			),
			array(
				'name' => __( 'Customer ID', 'pl8app' ),
				'value' => $log_meta['_pl8app_log_customer_id'][0],
			),
			array(
				'name'  => __( 'User ID', 'pl8app' ),
				'value' => $log_meta['_pl8app_log_user_id'][0],
			),
			array(
				'name'  => __( 'IP Address', 'pl8app' ),
				'value' => $log_meta['_pl8app_log_ip'][0],
			),
		);

		$data_points = apply_filters( 'pl8app_privacy_file_menuitem_log_item', $data_points, $log, $log_meta );

	}


	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/**
 * Adds the api access logs for a user to the WP Core Privacy Data exporter
 *
 * @since 2.9.2
 *
 * @param string $email_address The email address to look up api access logs for.
 * @param int    $page          The page of logs to request.
 *
 * @return array
 */
function pl8app_privacy_api_access_log_exporter( $email_address = '', $page = 1 ) {
	global $pl8app_logs;

	$user = get_user_by( 'email', $email_address );

	if ( false === $user ) {
		return array( 'data' => array(), 'done' => true );
	}

	$log_query = array(
		'log_type'               => 'api_access',
		'posts_per_page'         => 100,
		'paged'                  => $page,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'   => '_pl8app_log_user',
				'value' => $user->ID,
			)
		)
	);

	$logs = $pl8app_logs->get_connected_logs( $log_query );

	// If we haven't found any api access logs for this page, just return that we're done.
	if ( empty( $logs ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_items = array();
	foreach ( $logs as $log ) {

		$ip_address = get_post_meta( $log->ID, '_pl8app_log_request_ip', true );

		$data_points = array(
			array(
				'name' => __( 'Date', 'pl8app' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $log->post_date ) ),
			),
			array(
				'name'  => __( 'Request', 'pl8app' ),
				'value' => $log->post_excerpt,
			),
			array(
				'name'  => __( 'IP Address', 'pl8app' ),
				'value' => $ip_address,
			),
		);

		$data_points = apply_filters( 'pl8app_privacy_api_access_log_item', $data_points, $log );

		$export_items[] = array(
			'group_id'    => 'pl8app-api-access-logs',
			'group_label' => __( 'API Access Logs', 'pl8app' ),
			'item_id'     => "pl8app-api-access-logs-{$log->ID}",
			'data'        => $data_points,
		);

	}

	// Add the data to the list, and tell the exporter to come back for the next page of payments.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/** Anonymization Functions */

/**
 * This registers a single eraser _very_ early to avoid any other hook into the pl8app data from running first.
 *
 * We are going to set an option of what customer we're currently deleting for what email address, so that after the customer
 * is anonymized we can still find them. Then we'll delete it.
 *
 * @param array $erasers
 */
function pl8app_register_privacy_eraser_customer_id_lookup( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => 'pre-eraser-customer-id-lookup',
		'callback'             => 'pl8app_privacy_prefetch_customer_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'pl8app_register_privacy_eraser_customer_id_lookup', 5, 1 );

/**
 * Lookup the customer ID for this email address so that we can use it later in the anonymization process.
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function pl8app_privacy_prefetch_customer_id( $email_address, $page = 1 ) {
	$customer = new pl8app_Customer( $email_address );
	update_option( 'pl8app_priv_' . md5( $email_address ), $customer->id, false );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * This registers a single eraser _very_ late to remove a customer ID that was found for the erasers.
 *
 * We are now assumed done with our exporters, so we can go ahead and delete the customer ID we found for this eraser.
 *
 * @param array $erasers
 */
function pl8app_register_privacy_eraser_customer_id_removal( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => __( 'Possibly Delete Customer', 'pl8app' ),
		'callback'             => 'pl8app_privacy_maybe_delete_customer_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => 'post-eraser-customer-id-lookup',
		'callback'             => 'pl8app_privacy_remove_customer_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'pl8app_register_privacy_eraser_customer_id_removal', 9999, 1 );

/**
 * Delete the customer ID for this email address that was found in pl8app_privacy_prefetch_customer_id()
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function pl8app_privacy_remove_customer_id( $email_address, $page = 1 ) {
	delete_option( 'pl8app_priv_' . md5( $email_address ) );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * If after the payment anonymization/erasure methods have been run, and there are no longer payments
 * for the requested customer, go ahead and delete the customer
 *
 * @since 2.9.2
 *
 * @param string $email_address The email address requesting anonymization/erasure
 * @param int    $page          The page (not needed for this query)
 *
 * @return array
 */
function pl8app_privacy_maybe_delete_customer_eraser( $email_address, $page = 1 ) {
	$customer = _pl8app_privacy_get_customer_id_for_email( $email_address );

	if ( empty( $customer->id ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$payments = pl8app_get_payments( array(
		'customer' => $customer->id,
		'output'   => 'payments',
		'page'     => $page,
	) );

	if ( ! empty( $payments ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf( __( 'Customer for %s not deleted, due to remaining payments.', 'pl8app' ), $email_address ),
			),
			'done'           => true,
		);
	}

	if ( empty( $payments ) ) {
		global $wpdb;

		$deleted_customer = PL8PRESS()->customers->delete( $customer->id );
		if ( $deleted_customer ) {
			$customer_meta_table = PL8PRESS()->customer_meta->table_name;
			$deleted_meta = $wpdb->query( "DELETE FROM {$customer_meta_table} WHERE customer_id = {$customer->id}" );

			return array(
				'items_removed'  => true,
				'items_retained' => false,
				'messages'       => array(
					sprintf( __( 'Customer for %s successfully deleted.', 'pl8app' ), $email_address ),
				),
				'done'           => true,
			);
		}
	}

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf( __( 'Customer for %s failed to be deleted.', 'pl8app' ), $email_address ),
			),
			'done'           => true,
		);
}

/**
 * Register eraser for pl8app Data
 *
 * @param array $erasers
 *
 * @return array
 */
function pl8app_register_privacy_erasers( $erasers = array() ) {

	// The order of these matter, customer needs to be anonymized prior to the customer, so that the payment can adopt
	// properties of the customer like email.

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Customer Record', 'pl8app' ),
		'callback'             => 'pl8app_privacy_customer_anonymizer',
	);

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Payment Record', 'pl8app' ),
		'callback'             => 'pl8app_privacy_payment_eraser',
	);


	$erasers[] = array(
		'eraser_friendly_name' => __( 'API Access Logs', 'pl8app' ),
		'callback'             => 'pl8app_privacy_api_access_logs_eraser',
	);

	return $erasers;

}
add_filter( 'wp_privacy_personal_data_erasers', 'pl8app_register_privacy_erasers', 11, 1 );

/**
 * Anonymize a customer record through the WP Core Privacy Data Eraser methods.
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function pl8app_privacy_customer_anonymizer( $email_address, $page = 1 ) {
	$customer = _pl8app_privacy_get_customer_id_for_email( $email_address );

	$anonymized = _pl8app_anonymize_customer( $customer->id );
	if ( empty( $anonymized['success'] ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $anonymized['message'] ),
			'done'           => true,
		);
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array( sprintf( __( 'Customer for %s has been anonymized.', 'pl8app' ), $email_address ) ),
		'done'           => true,
	);
}

/**
 * Anonymize a payment record through the WP Core Privacy Data Eraser methods.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function pl8app_privacy_payment_eraser( $email_address, $page = 1 ) {
	$customer = _pl8app_privacy_get_customer_id_for_email( $email_address );

	$payments = pl8app_get_payments( array(
		'customer' => $customer->id,
		'output'   => 'payments',
		'page'     => $page,
	) );

	if ( empty( $payments ) ) {

		$message = 1 === $page ?
			sprintf( __( 'No payments found for %s.', 'pl8app' ), $email_address ) :
			sprintf( __( 'All eligible payments anonymized or deleted for %s.', 'pl8app' ), $email_address );

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $message ),
			'done'           => true,
		);
	}

	$items_removed  = null;
	$items_retained = null;
	$messages       = array();
	foreach ( $payments as $payment ) {
		$result = _pl8app_anonymize_payment( $payment->ID );

		if ( ! is_null( $items_removed ) && $result['success'] ) {
			$items_removed = true;
		}

		if ( ! is_null( $items_removed ) && ! $result['success'] ) {
			$items_retained = true;
		}

		$messages[] = $result['message'];
	}



	return array(
		'items_removed'  => ! is_null( $items_removed ) ? $items_removed : false,
		'items_retained' => ! is_null( $items_retained ) ? $items_retained : false,
		'messages'       => $messages,
		'done'           => false,
	);
}

/**
 * Anonymize the file menuitem logs.
 *
 * @since 2.9.2
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function pl8app_privacy_file_menuitem_logs_eraser( $email_address, $page = 1 ) {
	global $pl8app_logs;

	$customer = _pl8app_privacy_get_customer_id_for_email( $email_address );

	$log_query = array(
		'log_type'               => 'file_menuitem',
		'posts_per_page'         => 25,
		'paged'                  => $page,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'   => '_pl8app_log_customer_id',
				'value' => $customer->id,
			)
		)
	);

	$logs = $pl8app_logs->get_connected_logs( $log_query );

	if ( empty( $logs ) ) {

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'All eligible file menuitem logs anonymized or deleted for %s.', 'pl8app' ), $email_address ) ),
			'done'           => true,
		);
	}

	foreach ( $logs as $log ) {
		$current_ip = get_post_meta( $log->ID, '_pl8app_log_ip', true );
		update_post_meta( $log->ID, '_pl8app_log_ip', wp_privacy_anonymize_ip( $current_ip ) );

		/**
		 * Run further anonymization on a file menuitem log
		 *
		 * Developers and extensions can use the $log WP_Post object passed into the pl8app_anonymize_file_menuitem_log action
		 * to complete further anonymization.
		 *
		 * @since 2.9.2
		 *
		 * @param WP_Post $log The WP_Post object for the log
		 */
		do_action( 'pl8app_anonymize_file_menuitem_log', $log );
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => false,
	);
}

/**
 * Delete API Access Logs
 *
 * @since 2.9.2
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function pl8app_privacy_api_access_logs_eraser( $email_address, $page = 1 ) {
	global $pl8app_logs;

	$user = get_user_by( 'email', $email_address );

	if ( false === $user ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'No User found for %s, no access logs to remove.', 'pl8app' ), $email_address ) ),
			'done'           => true,
		);
	}

	$log_query = array(
		'log_type'               => 'api_access',
		'posts_per_page'         => 25,
		'paged'                  => $page,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'   => '_pl8app_log_user',
				'value' => $user->ID,
			)
		)
	);

	$logs = $pl8app_logs->get_connected_logs( $log_query );

	if ( empty( $logs ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( sprintf( __( 'All API access logs deleted for %s.', 'pl8app' ), $email_address ) ),
			'done'           => true,
		);
	}

	foreach ( $logs as $log ) {
		wp_delete_post( $log->ID );

		/**
		 * Run further actions on an api access log
		 *
		 * Developers and extensions can use the $log WP_Post object passed into the pl8app_delete_api_access_log action
		 * to complete further actions.
		 *
		 * @since 2.9.2
		 *
		 * @param WP_Post $log The WP_Post object for the log
		 */
		do_action( 'pl8app_delete_api_access_log', $log );
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => false,
	);
}
