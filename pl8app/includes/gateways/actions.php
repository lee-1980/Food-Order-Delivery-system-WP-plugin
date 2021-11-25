<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since  1.0.0
 *
 * @param $data
 */
function pl8app_process_gateway_select( $data ) {
	if( isset( $_POST['gateway_submit'] ) ) {
		wp_redirect( add_query_arg( 'payment-mode', $_POST['payment-mode'] ) ); exit;
	}
}
add_action( 'pl8app_gateway_select', 'pl8app_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_load_ajax_gateway() {
	if ( isset( $_POST['pl8app_payment_mode'] ) ) {
		pl8app_show_cc_form();
		exit();
	}
}
add_action( 'wp_ajax_pl8app_load_gateway', 'pl8app_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_pl8app_load_gateway', 'pl8app_load_ajax_gateway' );

/**
 * Sets an error on checkout if no gateways are enabled
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_no_gateway_error() {
	$gateways = pl8app_get_enabled_payment_gateways();

	if ( empty( $gateways ) && pl8app_get_cart_total() > 0 ) {
		remove_action( 'pl8app_after_cc_fields', 'pl8app_default_cc_address_fields' );
		remove_action( 'pl8app_cc_form', 'pl8app_get_cc_form' );
		pl8app_set_error( 'no_gateways', __( 'You must enable a payment gateway to use pl8app', 'pl8app' ) );
	} else {
		pl8app_unset_error( 'no_gateways' );
	}
}
add_action( 'init', 'pl8app_no_gateway_error' );
