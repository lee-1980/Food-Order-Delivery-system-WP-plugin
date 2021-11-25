<?php
/**
 * Gateway Functions
 *
 * @package     pl8app
 * @subpackage  Gateways
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns a list of all available gateways.
 *
 * @since 1.0
 * @return array $gateways All the available gateways
 */
function pl8app_get_payment_gateways() {
	// Default, built-in gateways
	$gateways = array(
		'paypal' => array(
			'admin_label'    => __( 'PayPal Standard', 'pl8app' ),
			'checkout_label' => __( 'PayPal', 'pl8app' ),
			'supports'       => array( 'buy_now' )
		),
		'manual' => array(
			'admin_label'    => __( 'Test Payment', 'pl8app' ),
			'checkout_label' => __( 'Test Payment', 'pl8app' )
		),
		'cash_on_delivery' => array(
			'admin_label'    => __( 'Pay by cash', 'pl8app' ),
			'checkout_label' => __( 'Pay by cash', 'pl8app' )
		),
	);

	return apply_filters( 'pl8app_payment_gateways', $gateways );
}

/**
 * Returns a list of all enabled gateways.
 *
 * @since 1.0
 * @param  bool $sort If true, the default gateway will be first
 * @return array $gateway_list All the available gateways
*/
function pl8app_get_enabled_payment_gateways( $sort = false ) {
	$gateways = pl8app_get_payment_gateways();
	$enabled  = (array) pl8app_get_option( 'gateways', false );

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}


	if ( true === $sort ) {
		// Reorder our gateways so the default is first
		$default_gateway_id = pl8app_get_default_gateway();

		if( pl8app_is_gateway_active( $default_gateway_id ) ) {

			$default_gateway    = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
			unset( $gateway_list[ $default_gateway_id ] );

			$gateway_list = array_merge( $default_gateway, $gateway_list );

		}

	}

	return apply_filters( 'pl8app_enabled_payment_gateways', $gateway_list );
}

/**
 * Checks whether a specified gateway is activated.
 *
 * @since 1.0
 * @param string $gateway Name of the gateway to check for
 * @return boolean true if enabled, false otherwise
*/
function pl8app_is_gateway_active( $gateway ) {
	$gateways = pl8app_get_enabled_payment_gateways();
	$ret = array_key_exists( $gateway, $gateways );
	return apply_filters( 'pl8app_is_gateway_active', $ret, $gateway, $gateways );
}

/**
 * Gets the default payment gateway selected from the pl8app Settings
 *
 * @since 1.0
 * @return string Gateway ID
 */
function pl8app_get_default_gateway() {
	$default = pl8app_get_option( 'default_gateway', 'paypal' );

	if( ! pl8app_is_gateway_active( $default ) ) {
		$gateways = pl8app_get_enabled_payment_gateways();
		$gateways = array_keys( $gateways );
		$default  = reset( $gateways );
	}

	return apply_filters( 'pl8app_default_gateway', $default );
}

/**
 * Returns the admin label for the specified gateway
 *
 * @since 1.0
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Gateway admin label
 */
function pl8app_get_gateway_admin_label( $gateway ) {
	$gateways = pl8app_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
	$payment  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;

	if( $gateway == 'manual' && $payment ) {
		if( pl8app_get_payment_amount( $payment ) == 0 ) {
			$label = __( 'Test Payment', 'pl8app' );
		}
	}

	return apply_filters( 'pl8app_gateway_admin_label', $label, $gateway );
}

/**
 * Returns the checkout label for the specified gateway
 *
 * @since 1.0
 * @param string $gateway Name of the gateway to retrieve a label for
 * @return string Checkout label for the gateway
 */
function pl8app_get_gateway_checkout_label( $gateway ) {
	$gateways = pl8app_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

	if( $gateway == 'manual' ) {
		$label = __( 'Test Payment', 'pl8app' );
	}

	return apply_filters( 'pl8app_gateway_checkout_label', $label, $gateway );
}

/**
 * Returns the options a gateway supports
 *
 * @since 1.0
 * @param string $gateway ID of the gateway to retrieve a label for
 * @return array Options the gateway supports
 */
function pl8app_get_gateway_supports( $gateway ) {
	$gateways = pl8app_get_enabled_payment_gateways();
	$supports = isset( $gateways[ $gateway ]['supports'] ) ? $gateways[ $gateway ]['supports'] : array();
	return apply_filters( 'pl8app_gateway_supports', $supports, $gateway );
}

/**
 * Checks if a gateway supports buy now
 *
 * @since 1.0
 * @param string $gateway ID of the gateway to retrieve a label for
 * @return bool
 */
function pl8app_gateway_supports_buy_now( $gateway ) {
	$supports = pl8app_get_gateway_supports( $gateway );
	$ret = in_array( 'buy_now', $supports );
	return apply_filters( 'pl8app_gateway_supports_buy_now', $ret, $gateway );
}

/**
 * Checks if an enabled gateway supports buy now
 *
 * @since 1.0
 * @return bool
 */
function pl8app_shop_supports_buy_now() {
	$gateways = pl8app_get_enabled_payment_gateways();
	$ret      = false;

	if( ! pl8app_use_taxes()  && $gateways && 1 === count( $gateways ) ) {
		foreach( $gateways as $gateway_id => $gateway ) {
			if( pl8app_gateway_supports_buy_now( $gateway_id ) ) {
				$ret = true;
				break;
			}
		}
	}

	return apply_filters( 'pl8app_shop_supports_buy_now', $ret );
}

/**
 * Build the purchase data for a straight-to-gateway purchase button
 *
 * @since  1.0.0
 *
 * @param int   $menuitem_id
 * @param array $options
 * @param int   $quantity
 * @return mixed|void
 */
function pl8app_build_straight_to_gateway_data( $menuitem_id = 0, $options = array(), $quantity = 1 ) {

	$price_options = array();

	if( empty( $options ) || ! pl8app_has_variable_prices( $menuitem_id ) ) {
		$price = pl8app_get_menuitem_price( $menuitem_id );
	} else {

		if( is_array( $options['price_id'] ) ) {
			$price_id = $options['price_id'][0];
		} else {
			$price_id = $options['price_id'];
		}

		$prices = pl8app_get_variable_prices( $menuitem_id );

		// Make sure a valid price ID was supplied
		if( ! isset( $prices[ $price_id ] ) ) {
			wp_die( __( 'The requested price ID does not exist.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 404 ) );
		}

		$price_options = array(
			'price_id' => $price_id,
			'amount'   => $prices[ $price_id ]['amount']
		);
		$price  = $prices[ $price_id ]['amount'];
	}

	// Set up pl8app array
	$menuitems = array(
		array(
			'id'      => $menuitem_id,
			'options' => $price_options
		)
	);

	// Set up Cart Details array
	$cart_details = array(
		array(
			'name'        => get_the_title( $menuitem_id ),
			'id'          => $menuitem_id,
			'item_number' => array(
				'id'      => $menuitem_id,
				'options' => $price_options
			),
			'tax'         => 0,
			'discount'    => 0,
			'item_price'  => $price,
			'subtotal'    => ( $price * $quantity ),
			'price'       => ( $price * $quantity ),
			'quantity'    => $quantity,
		)
	);

	if( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
	}


	// Setup user information
	$user_info = array(
		'id'         => is_user_logged_in() ? get_current_user_id()         : -1,
		'email'      => is_user_logged_in() ? $current_user->user_email     : '',
		'first_name' => is_user_logged_in() ? $current_user->user_firstname : '',
		'last_name'  => is_user_logged_in() ? $current_user->user_lastname  : '',
		'discount'   => 'none',
		'address'    => array()
	);

	// Setup purchase information
	$purchase_data = array(
		'menuitems'    => $menuitems,
		'fees'         => pl8app_get_cart_fees(),
		'subtotal'     => $price * $quantity,
		'discount'     => 0,
		'tax'          => 0,
		'price'        => $price * $quantity,
		'purchase_key' => strtolower( md5( uniqid() ) ),
		'user_email'   => $user_info['email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => $user_info,
		'post_data'    => array(),
		'cart_details' => $cart_details,
		'gateway'      => 'paypal',
		'buy_now'      => true,
		'card_info'    => array()
	);

	return apply_filters( 'pl8app_straight_to_gateway_purchase_data', $purchase_data );

}

/**
 * Sends all the payment data to the specified gateway
 *
 * @since 1.0
 * @param string $gateway Name of the gateway
 * @param array $payment_data All the payment data to be sent to the gateway
 * @return void
*/
function pl8app_send_to_gateway( $gateway, $payment_data ) {

	$payment_data['gateway_nonce'] = wp_create_nonce( 'pl8app-gateway' );

	// $gateway must match the ID used when registering the gateway
	do_action( 'pl8app_gateway_' . $gateway, $payment_data );
}

/**
 * Determines if the gateway menu should be shown
 *
 * If the cart amount is zero, no option is shown and the cart uses the manual gateway
 * to emulate a no-gateway-setup for a free menuitem
 *
 * @since 1.0
 * @return bool $show_gateways Whether or not to show the gateways
 */
function pl8app_show_gateways() {
	$gateways = pl8app_get_enabled_payment_gateways();
	$show_gateways = false;

	if ( count( $gateways ) > 1 ) {
		$show_gateways = true;
		if ( pl8app_get_cart_total() <= 0 ) {
			$show_gateways = false;
		}
	}

	return apply_filters( 'pl8app_show_gateways', $show_gateways );
}

/**
 * Determines what the currently selected gateway is
 *
 * If the cart amount is zero, no option is shown and the cart uses the manual
 * gateway to emulate a no-gateway-setup for a free menuitem
 *
 * @since 1.0
 * @return string $chosen_gateway The slug of the gateway
 */
function pl8app_get_chosen_gateway() {
	$gateways = pl8app_get_enabled_payment_gateways();
	$chosen   = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : false;

	if ( false !== $chosen ) {
		$chosen = preg_replace('/[^a-zA-Z0-9-_]+/', '', $chosen );
	}

	if ( ! empty ( $chosen ) ) {

		$chosen_gateway = urldecode( $chosen );

		if( ! pl8app_is_gateway_active( $chosen_gateway ) ) {
			$chosen_gateway = pl8app_get_default_gateway();
		}

	} else {
		$chosen_gateway = pl8app_get_default_gateway();
	}

	if ( pl8app_get_cart_subtotal() <= 0 ) {
		$chosen_gateway = 'manual';
	}

	return apply_filters( 'pl8app_chosen_gateway', $chosen_gateway );
}

/**
 * Record a gateway error
 *
 * A simple wrapper function for pl8app_record_log()
 *
 * @since 1.0.0
 * @param string $title Title of the log entry (default: empty)
 * @param string $message  Message to store in the log entry (default: empty)
 * @param int $parent Parent log entry (default: 0)
 * @return int ID of the new log entry
 */
function pl8app_record_gateway_error( $title = '', $message = '', $parent = 0 ) {
	return pl8app_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Counts the number of purchases made with a gateway
 *
 * @since  1.0.0
 *
 * @param string $gateway_id
 * @param string $status
 * @return int
 */
function pl8app_count_sales_by_gateway( $gateway_id = 'paypal', $status = 'publish' ) {

	$ret  = 0;
	$args = array(
		'meta_key'    => '_pl8app_payment_gateway',
		'meta_value'  => $gateway_id,
		'nopaging'    => true,
		'post_type'   => 'pl8app_payment',
		'post_status' => $status,
		'fields'      => 'ids'
	);

	$payments = new WP_Query( $args );

	if( $payments )
		$ret = $payments->post_count;
	return $ret;
}

/**
 * Processes the purchase data and uses the Cash On Delivery to record
 * the transaction in the Order History
 *
 * @since 1.0
 * @param array $purchase_data Purchase Data
 * @return void
*/
function pl8app_cash_on_delivery_payment( $purchase_data ) {
	if( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'pl8app-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

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
	$payment = pl8app_insert_payment( $payment_data);

	if ( $payment ) {
		pl8app_update_payment_status( $payment, 'processing' );
		pl8app_update_order_status( $payment, 'pending' );
		// Empty the shopping cart
		pl8app_empty_cart();
		pl8app_send_to_success_page();
	} else {
		pl8app_record_gateway_error( __( 'Payment Error', 'pl8app' ), sprintf( __( 'Payment creation failed while processing with Cash On delivery order. Payment data: %s', 'pl8app' ), json_encode( $payment_data ) ), $payment );
		// If errors are present, send the user back to the purchase page so they can be corrected
		pl8app_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['pl8app-gateway'] );
	}
}
add_action( 'pl8app_gateway_cash_on_delivery', 'pl8app_cash_on_delivery_payment' );

/**
 * Cash On Delivery Remove CC Form
 *
 * Cash On Delivery does not need a CC form, so remove it.
 *
 * @access private
 * @since 1.0
 */
add_action( 'pl8app_cash_on_delivery_cc_form', '__return_false' );

function pl8app_service_method_case_enabled_payment_gateways($gateway_list){

    $delivery_type = isset($_COOKIE['service_type']) ? $_COOKIE['service_type'] : '';
    $options = get_option('pl8app_settings');

    if($delivery_type == 'delivery'){
        $disabled_payments = !empty($options['gateways_disable_del']) && is_array($options['gateways_disable_del'])?$options['gateways_disable_del']:array();
    }
    else{
        $disabled_payments = !empty($options['gateways_disable_pic'])&& is_array($options['gateways_disable_pic'])?$options['gateways_disable_pic']:array();
    }

    $gateway_list = array_diff_key($gateway_list, $disabled_payments);

    return $gateway_list;
}

add_filter( 'pl8app_enabled_payment_gateways', 'pl8app_service_method_case_enabled_payment_gateways', 10, 1);