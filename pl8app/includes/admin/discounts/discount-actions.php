<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up and stores a new discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses pl8app_store_discount()
 * @return void
 */
function pl8app_add_discount( $data ) {
	
	if ( ! isset( $data['pl8app-discount-nonce'] ) || ! wp_verify_nonce( $data['pl8app-discount-nonce'], 'pl8app_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'restr-press' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	// Setup the discount code details
	$posted = array();

	if ( empty( $data['name'] ) || empty( $data['code'] ) || empty( $data['type'] ) || empty( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'pl8app-message', 'discount_validation_failed' ) );
		pl8app_die();
	}

	// Verify only accepted characters
	$sanitized = preg_replace('/[^a-zA-Z0-9-_]+/', '', $data['code'] );
	if ( strtoupper( $data['code'] ) !== strtoupper( $sanitized ) ) {
		wp_redirect( add_query_arg( 'pl8app-message', 'discount_invalid_code' ) );
		pl8app_die();
	}

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$posted[ $key ] = $value;

		} else if ( $key != 'pl8app-discount-nonce' && $key != 'pl8app-action' && $key != 'pl8app-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$posted[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$posted[ $key ] = array_map( 'absint', $value );

			}
		}

	}

	// Ensure this discount doesn't already exist
	if ( ! pl8app_get_discount_by_code( $posted['code'] ) ) {

		// Set the discount code's default status to active
		$posted['status'] = 'active';

		if ( pl8app_store_discount( $posted ) ) {

			wp_redirect( add_query_arg( 'pl8app_discount_added', '1', $data['pl8app-redirect'] ) ); pl8app_die();

		} else {

			wp_redirect( add_query_arg( 'pl8app-message', 'discount_add_failed', $data['pl8app-redirect'] ) ); pl8app_die();

		}

	} else {

		wp_redirect( add_query_arg( 'pl8app-message', 'discount_exists', $data['pl8app-redirect'] ) ); pl8app_die();

	}

}
add_action( 'pl8app_add_discount', 'pl8app_add_discount' );

/**
 * Saves an edited discount
 *
 * @since 1.0.6
 * @param array $data Discount code data
 * @return void
 */
function pl8app_edit_discount( $data ) {

	if ( ! isset( $data['pl8app-discount-nonce'] ) || ! wp_verify_nonce( $data['pl8app-discount-nonce'], 'pl8app_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	// Setup the discount code details
	$discount = array();

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$discount[ $key ] = $value;

		} else if ( $key != 'pl8app-discount-nonce' && $key != 'pl8app-action' && $key != 'discount-id' && $key != 'pl8app-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$discount[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$discount[ $key ] = array_map( 'absint', $value );

			}

		}

	}

	$old_discount     = new pl8app_Discount( (int) $data['discount-id'] );
	$discount['uses'] = pl8app_get_discount_uses( $old_discount->ID );

	if ( pl8app_store_discount( $discount, $data['discount-id'] ) ) {

		wp_redirect( add_query_arg( 'pl8app_discount_updated', '1', $data['pl8app-redirect'] ) ); pl8app_die();

	} else {

		wp_redirect( add_query_arg( 'pl8app-message', 'discount_update_failed', $data['pl8app-redirect'] ) ); pl8app_die();

	}

}
add_action( 'pl8app_edit_discount', 'pl8app_edit_discount' );

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses pl8app_remove_discount()
 * @return void
 */
function pl8app_delete_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'pl8app_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$discount_id = $data['discount'];
	pl8app_remove_discount( $discount_id );
}
add_action( 'pl8app_delete_discount', 'pl8app_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount code's status to active
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses pl8app_update_discount_status()
 * @return void
 */
function pl8app_activate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'pl8app_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	pl8app_update_discount_status( $id, 'active' );
}
add_action( 'pl8app_activate_discount', 'pl8app_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount code's status to deactivate
 *
 * @since 1.0.6
 * @param array $data Discount code data
 * @uses pl8app_update_discount_status()
 * @return void
*/
function pl8app_deactivate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'pl8app_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'pl8app' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	pl8app_update_discount_status( $id, 'inactive' );
}
add_action( 'pl8app_deactivate_discount', 'pl8app_deactivate_discount' );
