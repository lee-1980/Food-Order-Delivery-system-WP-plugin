<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register a view for the single customer view
 *
 * @since  1.0.0
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function pl8app_register_default_customer_views( $views ) {

	$default_views = array(
		'overview'  => 'pl8app_customers_view',
		'delete'    => 'pl8app_customers_delete_view',
		'notes'     => 'pl8app_customer_notes_view',
		'tools'      => 'pl8app_customer_tools_view',
	);

	return array_merge( $views, $default_views );

}
add_filter( 'pl8app_customer_views', 'pl8app_register_default_customer_views', 1, 1 );

/**
 * Register a tab for the single customer view
 *
 * @since  1.0.0
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function pl8app_register_default_customer_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-admin-users', 'title' => _x( 'Profile', 'Customer Details tab title', 'pl8app' ) ),
		'notes'    => array( 'dashicon' => 'dashicons-admin-comments', 'title' => _x( 'Notes', 'Customer Notes tab title', 'pl8app' ) ),
		'tools'    => array( 'dashicon' => 'dashicons-admin-tools', 'title' => _x( 'Tools', 'Customer Tools tab title', 'pl8app' ) ),
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'pl8app_customer_tabs', 'pl8app_register_default_customer_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since 1.0
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function pl8app_register_delete_customer_tab( $tabs ) {

	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => _x( 'Delete', 'Delete Customer tab title', 'pl8app' ) );

	return $tabs;
}
add_filter( 'pl8app_customer_tabs', 'pl8app_register_delete_customer_tab', PHP_INT_MAX, 1 );

/**
 * Remove the admin bar edit profile link when the user is not verified
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_maybe_remove_adminbar_profile_link() {

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( pl8app_user_pending_verification() ) {

		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('edit-profile', 'user-actions');

	}

}
add_action( 'wp_before_admin_bar_render', 'pl8app_maybe_remove_adminbar_profile_link' );

/**
 * Remove the admin menus and disable profile access for non-verified users
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_maybe_remove_menu_profile_links() {

	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( pl8app_user_pending_verification() ) {

		if( defined( 'IS_PROFILE_PAGE' ) && true === IS_PROFILE_PAGE ) {
			$url     = esc_url( pl8app_get_user_verification_request_url() );
			$message = sprintf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'pl8app' ), $url );
			$title   = __( 'Account Pending Verification', 'pl8app' );
			$args    = array(
				'response' => 403,
			);
			wp_die( $message, $title, $args );
		}

		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );

	}

}
add_action( 'admin_init', 'pl8app_maybe_remove_menu_profile_links' );
