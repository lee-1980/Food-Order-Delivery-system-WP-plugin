<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Endpoints for the Cart
 *
 * These endpoints are used for adding/removing items from the cart
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_add_rewrite_endpoints( $rewrite_rules ) {
	add_rewrite_endpoint( 'pl8app-add', EP_ALL );
	add_rewrite_endpoint( 'pl8app-remove', EP_ALL );
}
add_action( 'init', 'pl8app_add_rewrite_endpoints' );

/**
 * Process Cart Endpoints
 *
 * Listens for add/remove requests sent from the cart
 *
 * @since  1.0.0
 * @global $wp_query Used to access the current query that is being requested
 * @return void
*/
function pl8app_process_cart_endpoints() {
	global $wp_query;

	// Adds an item to the cart with a /pl8app-add/# URL
	if ( isset( $wp_query->query_vars['pl8app-add'] ) ) {
		$menuitem_id = absint( $wp_query->query_vars['pl8app-add'] );
		$cart        = pl8app_add_to_cart( $menuitem_id, array() );

		wp_redirect( pl8app_get_checkout_uri() ); pl8app_die();
	}

	// Removes an item from the cart with a /pl8app-remove/# URL
	if ( isset( $wp_query->query_vars['pl8app-remove'] ) ) {
		$cart_key = absint( $wp_query->query_vars['pl8app-remove'] );
		$cart     = pl8app_remove_from_cart( $cart_key );

		wp_redirect( pl8app_get_checkout_uri() ); pl8app_die();
	}
}
add_action( 'template_redirect', 'pl8app_process_cart_endpoints', 100 );

/**
 * Process the Add to Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function pl8app_process_add_to_cart( $data ) {
	$menuitem_id = absint( $data['menuitem_id'] );
	$options     = isset( $data['pl8app_options'] ) ? $data['pl8app_options'] : array();

	if ( ! empty( $data['menuitem_qty'] ) ) {
		$options['quantity'] = absint( $data['menuitem_qty'] );
	}

	if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
		foreach ( $options['price_id'] as  $key => $price_id ) {
			$options['quantity'][ $key ] = isset( $data[ 'pl8app_menuitem_quantity_' . $price_id ] ) ? absint( $data[ 'pl8app_menuitem_quantity_' . $price_id ] ) : 1;
		}
	}

	$cart        = pl8app_add_to_cart( $menuitem_id, $options );

	if ( pl8app_straight_to_checkout() && ! pl8app_is_checkout() ) {
		$query_args 	= remove_query_arg( array( 'pl8app_action', 'menuitem_id', 'pl8app_options' ) );
		$query_part 	= strpos( $query_args, "?" );
		$url_parameters = '';

		if ( false !== $query_part ) { 
			$url_parameters = substr( $query_args, $query_part ); 
		}

		wp_redirect( pl8app_get_checkout_uri() . $url_parameters, 303 );
		pl8app_die();
	} else {
		wp_redirect( remove_query_arg( array( 'pl8app_action', 'menuitem_id', 'pl8app_options' ) ) ); pl8app_die();
	}
}
add_action( 'pl8app_add_to_cart', 'pl8app_process_add_to_cart' );

/**
 * Process the Remove from Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function pl8app_process_remove_from_cart( $data ) {
	$cart_key = absint( $_GET['cart_item'] );
	pl8app_remove_from_cart( $cart_key );
	wp_redirect( remove_query_arg( array( 'pl8app_action', 'cart_item', 'nocache' ) ) ); pl8app_die();
}
add_action( 'pl8app_remove', 'pl8app_process_remove_from_cart' );

/**
 * Process the Remove fee from Cart request
 *
 * @since  1.0.0
 *
 * @param $data
 */
function pl8app_process_remove_fee_from_cart( $data ) {
	$fee = sanitize_text_field( $data['fee'] );
	PL8PRESS()->fees->remove_fee( $fee );
	wp_redirect( remove_query_arg( array( 'pl8app_action', 'fee', 'nocache' ) ) ); pl8app_die();
}
add_action( 'pl8app_remove_fee', 'pl8app_process_remove_fee_from_cart' );

/**
 * Process the Collection Purchase request
 *
 * @since 1.0
 *
 * @param $data
 */
function pl8app_process_collection_purchase( $data ) {
	$taxonomy   = urldecode( $data['taxonomy'] );
	$terms      = urldecode( $data['terms'] );
	$cart_items = pl8app_add_collection_to_cart( $taxonomy, $terms );
	wp_redirect( add_query_arg( 'added', '1', remove_query_arg( array( 'pl8app_action', 'taxonomy', 'terms' ) ) ) );
	pl8app_die();
}
add_action( 'pl8app_purchase_collection', 'pl8app_process_collection_purchase' );


/**
 * Process cart updates, primarily for quantities
 *
 * @since  1.0.0
 */
function pl8app_process_cart_update( $data ) {

	foreach( $data['pl8app-cart-menuitems'] as $key => $cart_menuitem_id ) {
		$options  = json_decode( stripslashes( $data['pl8app-cart-menuitem-' . $key . '-options'] ), true );
		$quantity = absint( $data['pl8app-cart-menuitem-' . $key . '-quantity'] );
		pl8app_set_cart_item_quantity( $cart_menuitem_id, $quantity, $options );
	}

}
add_action( 'pl8app_update_cart', 'pl8app_process_cart_update' );

/**
 * Process cart save
 *
 * @since 1.0
 * @return void
 */
function pl8app_process_cart_save( $data ) {

	$cart = pl8app_save_cart();
	if( ! $cart ) {
		wp_redirect( pl8app_get_checkout_uri() ); exit;
	}

}
add_action( 'pl8app_save_cart', 'pl8app_process_cart_save' );

/**
 * Process cart save
 *
 * @since 1.0
 * @return void
 */
function pl8app_process_cart_restore( $data ) {

	$cart = pl8app_restore_cart();
	if( ! is_wp_error( $cart ) ) {
		wp_redirect( pl8app_get_checkout_uri() ); exit;
	}

}
add_action( 'pl8app_restore_cart', 'pl8app_process_cart_restore' );
