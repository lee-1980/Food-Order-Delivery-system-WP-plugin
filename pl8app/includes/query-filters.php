<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function pl8app_unset_discount_query_arg( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$discount = $query->get( 'discount' );

	if ( ! empty( $discount ) ) {

		// unset ref var from $wp_query
		$query->set( 'discount', null );

		global $wp;

		// unset ref var from $wp
		unset( $wp->query_vars[ 'discount' ] );

		// if in home (because $wp->query_vars is empty) and 'show_on_front' is page
		if ( empty( $wp->query_vars ) && get_option( 'show_on_front' ) === 'page' ) {

		 	// reset and re-parse query vars
			$wp->query_vars['page_id'] = get_option( 'page_on_front' );
			$query->parse_query( $wp->query_vars );

		}

	}

}
add_action( 'pre_get_posts', 'pl8app_unset_discount_query_arg', 999999 );

/**
 * Filters on canonical redirects
 *
 * @since 2.4.3
 * @return string
 */
function pl8app_prevent_canonical_redirect( $redirect_url, $requested_url ) {

	if( ! is_front_page() ) {
		return $redirect_url;
	}

	$discount = get_query_var( 'discount' );

	if( ! empty( $discount ) || false !== strpos( $requested_url, 'discount' ) ) {

		$redirect_url = $requested_url;

	}

	return $redirect_url;

}
add_action( 'redirect_canonical', 'pl8app_prevent_canonical_redirect', 0, 2 );

/**
 * Auto flush permalinks wth a soft flush when a 404 error is detected on an pl8app page
 *
 * @since 2.4.3
 * @return string
 */
function pl8app_refresh_permalinks_on_bad_404() {

	global $wp;

	if( ! is_404() ) {
		return;
	}

	if( isset( $_GET['pl8app-flush'] ) ) {
		return;
	}

	if( false === get_transient( 'pl8app_refresh_404_permalinks' ) ) {

		$slug  = defined( 'pl8app_SLUG' ) ? pl8app_SLUG : 'menuitems';

		$parts = explode( '/', $wp->request );

		if( $slug !== $parts[0] ) {
			return;
		}

		flush_rewrite_rules( false );

		set_transient( 'pl8app_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12 );

		wp_redirect( home_url( add_query_arg( array( 'pl8app-flush' => 1 ), $wp->request ) ) ); exit;

	}
}
add_action( 'template_redirect', 'pl8app_refresh_permalinks_on_bad_404' );