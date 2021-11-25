<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses pl8app_get_errors()
 * @uses pl8app_clear_errors()
 * @return void
 */
function pl8app_print_errors() {
	$errors = pl8app_get_errors();
	if ( $errors ) {

		$classes = apply_filters( 'pl8app_error_class', array(
			'pl8app_errors', 'pl8app-alert', 'pl8app-alert-error'
		) );

		if ( ! empty( $errors ) ) {
			echo '<div class="' . implode( ' ', $classes ) . '">';
				// Loop error codes and display errors
				foreach ( $errors as $error_id => $error ) {

					echo '<p class="pl8app_error" id="pl8app_error_' . $error_id . '"><strong>' . __( 'Error', 'pl8app' ) . '</strong>: ' . $error . '</p>';

				}

			echo '</div>';
		}

		pl8app_clear_errors();

	}
}
add_action( 'pl8app_purchase_form_before_submit', 'pl8app_print_errors' );
add_action( 'pl8app_ajax_checkout_errors', 'pl8app_print_errors' );
add_action( 'pl8app_print_errors', 'pl8app_print_errors' );

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses pl8app_Session::get()
 * @return mixed array if errors are present, false if none found
 */
function pl8app_get_errors() {
	$errors = PL8PRESS()->session->get( 'pl8app_errors' );
	$errors = apply_filters( 'pl8app_errors', $errors );
	return $errors;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses pl8app_Session::get()
 * @param int $error_id ID of the error being set
 * @param string $error_message Message to store with the error
 * @return void
 */
function pl8app_set_error( $error_id, $error_message ) {
	$errors = pl8app_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	PL8PRESS()->session->set( 'pl8app_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses pl8app_Session::set()
 * @return void
 */
function pl8app_clear_errors() {
	PL8PRESS()->session->set( 'pl8app_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since  1.0.0
 * @uses pl8app_Session::set()
 * @param int $error_id ID of the error being set
 * @return string
 */
function pl8app_unset_error( $error_id ) {
	$errors = pl8app_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		PL8PRESS()->session->set( 'pl8app_errors', $errors );
	}
}

/**
 * Register die handler for pl8app_die()
 *
 * @author pl8app
 * @since  1.0.0
 * @return void
 */
function _pl8app_die_handler() {
	if ( defined( 'pl8app_UNIT_TESTS' ) )
		return '_pl8app_die_handler';
	else
		die();
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using pl8app_die() in the unit tests.
 *
 * @author pl8app
 * @since  1.0.0
 * @return void
 */
function pl8app_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_pl8app_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_pl8app_die_handler', 10, 3 );
	wp_die( $message, $title, array( 'response' => $status ));
}
