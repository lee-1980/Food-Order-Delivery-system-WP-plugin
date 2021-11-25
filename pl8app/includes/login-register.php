<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since 1.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Login form
*/
function pl8app_login_form( $redirect = '' ) {
	global $pl8app_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = pl8app_get_current_page_url();
	}

	$pl8app_login_redirect = $redirect;

	ob_start();

	pl8app_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'pl8app_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since  1.0.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Register form
*/
function pl8app_register_form( $redirect = '' ) {
	global $pl8app_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = pl8app_get_current_page_url();
	}

	$pl8app_register_redirect = $redirect;

	ob_start();

	pl8app_get_template_part( 'shortcode', 'register' );

	return apply_filters( 'pl8app_register_form', ob_get_clean() );
}

/**
 * Process Login Form
 *
 * @since 1.0
 * @param array $data Data sent from the login form
 * @return void
*/
function pl8app_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['pl8app_login_nonce'], 'pl8app-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['pl8app_user_login'] );
		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['pl8app_user_login'] );
		}
		if ( $user_data ) {
			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;

			if ( wp_check_password( $data['pl8app_user_pass'], $user_data->user_pass, $user_data->ID ) ) {

				if ( isset( $data['rememberme'] ) ) {
					$data['rememberme'] = true;
				} else {
					$data['rememberme'] = false;
				}

				pl8app_log_user_in( $user_data->ID, $data['pl8app_user_login'], $data['pl8app_user_pass'], $data['rememberme'] );
			} else {
				pl8app_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'pl8app' ) );
			}
		} else {
			pl8app_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'pl8app' ) );
		}
		// Check for errors and redirect if none present
		$errors = pl8app_get_errors();
		if ( ! $errors ) {
			$redirect = apply_filters( 'pl8app_login_redirect', $data['pl8app_redirect'], $user_ID );
			wp_redirect( $redirect );
			pl8app_die();
		}
	}
}
add_action( 'pl8app_user_login', 'pl8app_process_login_form' );

/**
 * Log User In
 *
 * @since 1.0
 * @param int $user_id User ID
 * @param string $user_login Username
 * @param string $user_pass Password
 * @param boolean $remember Remember me
 * @return void
*/
function pl8app_log_user_in( $user_id, $user_login, $user_pass, $remember = false ) {
	if ( $user_id < 1 )
		return;

	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'pl8app_log_user_in', $user_id, $user_login, $user_pass );
}


/**
 * Process Register Form
 *
 * @since  1.0.0
 * @param array $data Data sent from the register form
 * @return void
*/
function pl8app_process_register_form( $data ) {

	if( is_user_logged_in() ) {
		return;
	}

	if( empty( $_POST['pl8app_register_submit'] ) ) {
		return;
	}

	do_action( 'pl8app_pre_process_register_form' );

	if( empty( $data['pl8app_user_login'] ) ) {
		pl8app_set_error( 'empty_username', __( 'Invalid username', 'pl8app' ) );
	}

	if( username_exists( $data['pl8app_user_login'] ) ) {
		pl8app_set_error( 'username_unavailable', __( 'Username already taken', 'pl8app' ) );
	}

	if( ! validate_username( $data['pl8app_user_login'] ) ) {
		pl8app_set_error( 'username_invalid', __( 'Invalid username', 'pl8app' ) );
	}

	if( email_exists( $data['pl8app_user_email'] ) ) {
		pl8app_set_error( 'email_unavailable', __( 'Email address already taken', 'pl8app' ) );
	}

	if( empty( $data['pl8app_user_email'] ) || ! is_email( $data['pl8app_user_email'] ) ) {
		pl8app_set_error( 'email_invalid', __( 'Invalid email', 'pl8app' ) );
	}

	if( ! empty( $data['pl8app_payment_email'] ) && $data['pl8app_payment_email'] != $data['pl8app_user_email'] && ! is_email( $data['pl8app_payment_email'] ) ) {
		pl8app_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'pl8app' ) );
	}

	if( empty( $_POST['pl8app_user_pass'] ) ) {
		pl8app_set_error( 'empty_password', __( 'Please enter a password', 'pl8app' ) );
	}

	if( ( ! empty( $_POST['pl8app_user_pass'] ) && empty( $_POST['pl8app_user_pass2'] ) ) || ( $_POST['pl8app_user_pass'] !== $_POST['pl8app_user_pass2'] ) ) {
		pl8app_set_error( 'password_mismatch', __( 'Passwords do not match', 'pl8app' ) );
	}

	do_action( 'pl8app_process_register_form' );

	// Check for errors and redirect if none present
	$errors = pl8app_get_errors();

	if (  empty( $errors ) ) {

		$redirect = apply_filters( 'pl8app_register_redirect', $data['pl8app_redirect'] );

		pl8app_register_and_login_new_user( array(
			'user_login'      => $data['pl8app_user_login'],
			'user_pass'       => $data['pl8app_user_pass'],
			'user_email'      => $data['pl8app_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' )
		) );

		wp_redirect( $redirect );
		pl8app_die();
	}
}
add_action( 'pl8app_user_register', 'pl8app_process_register_form' );
