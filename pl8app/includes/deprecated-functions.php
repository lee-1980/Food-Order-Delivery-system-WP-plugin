<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     pl8app
 * @subpackage  Deprecated
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a menuitem.
 *
 * @since       1.0
 * @deprecated  1.3.4
 *
 * @param int $menuitem_id ID number of the menuitem to retrieve a log for
 * @param bool $paginate Whether to paginate the results or not
 * @param int $number Number of results to return
 * @param int $offset Number of items to skip
 *
 * @return mixed array|bool
*/
function pl8app_get_menuitem_sales_log( $menuitem_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$sales_log = get_post_meta( $menuitem_id, '_pl8app_sales_log', true );

	if ( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;

		if ( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get File Download Log
 *
 * Returns an array of file menuitem dates and user info.
 *
 * @deprecated 1.3.4
 * @since 1.0
 *
 * @param int $menuitem_id the ID number of the menuitem to retrieve a log for
 * @param bool $paginate whether to paginate the results or not
 * @param int $number the number of results to return
 * @param int $offset the number of items to skip
 *
 * @return mixed array|bool
*/
function pl8app_get_file_menuitem_log( $menuitem_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$menuitem_log = get_post_meta( $menuitem_id, '_pl8app_file_menuitem_log', true );

	if ( $menuitem_log ) {
		$menuitem_log = array_reverse( $menuitem_log );
		$log = array();
		$log['number'] = count( $menuitem_log );
		$log['menuitems'] = $menuitem_log;

		if ( $paginate ) {
			$log['menuitems'] = array_slice( $menuitem_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get pl8app Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int  $payment_id ID number of the purchase
 * @param null $payment_meta
 * @return bool|mixed
 */
function pl8app_get_menuitems_of_purchase( $payment_id, $payment_meta = null ) {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.4', 'pl8app_get_payment_meta_menuitems', $backtrace );

	if ( is_null( $payment_meta ) ) {
		$payment_meta = pl8app_get_payment_meta( $payment_id );
	}

	$menuitems = maybe_unserialize( $payment_meta['menuitems'] );

	if ( $menuitems )
		return $menuitems;

	return false;
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access the menuitems menu. Currently not
 * changeable, but here for a future update.
 *
 * @since 1.0
 * @deprecated 1.4.4
 * @return string
*/
function pl8app_get_menu_access_level() {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.4.4', 'current_user_can(\'manage_shop_settings\')', $backtrace );

	return apply_filters( 'pl8app_menu_access_level', 'manage_options' );
}



/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the pl8app Settings.
 *
 * @since 1.0.0
 * @deprecated 1.6
 * @global $pl8app_options
 * @return bool $local_only
 */
function pl8app_local_taxes_only() {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	global $pl8app_options;

	$local_only = isset( $pl8app_options['tax_condition'] ) && $pl8app_options['tax_condition'] == 'local';

	return apply_filters( 'pl8app_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.0
 * @deprecated 1.6
 * @uses pl8app_Session::get()
 * @return bool
 */
function pl8app_local_tax_opted_in() {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	$opted_in = PL8PRESS()->session->get( 'pl8app_local_tax_opt_in' );
	return ! empty( $opted_in );
}

/**
 * Show taxes on individual prices?
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @global $pl8app_options
 * @return bool Whether or not to show taxes on prices
 */
function pl8app_taxes_on_prices() {
	global $pl8app_options;

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.9', 'no alternatives', $backtrace );

	return apply_filters( 'pl8app_taxes_on_prices', isset( $pl8app_options['taxes_on_prices'] ) );
}

/**
 * Get Cart Amount
 *
 * @since 1.0
 * @deprecated 1.9
 * @param bool $add_taxes Whether to apply taxes (if enabled) (default: true)
 * @param bool $local_override Force the local opt-in param - used for when not reading $_POST (default: false)
 * @return float Total amount
*/
function pl8app_get_cart_amount( $add_taxes = true, $local_override = false ) {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '1.9', 'pl8app_get_cart_subtotal() or pl8app_get_cart_total()', $backtrace );

	$amount = pl8app_get_cart_subtotal( );
	if ( ! empty( $_POST['pl8app-discount'] ) || pl8app_get_cart_discounts() !== false ) {
		// Retrieve the discount stored in cookies
		$discounts = pl8app_get_cart_discounts();

		// Check for a posted discount
		$posted_discount = isset( $_POST['pl8app-discount'] ) ? trim( $_POST['pl8app-discount'] ) : '';

		if ( $posted_discount && ! in_array( $posted_discount, $discounts ) ) {
			// This discount hasn't been applied, so apply it
			$amount = pl8app_get_discounted_amount( $posted_discount, $amount );
		}

		if( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= pl8app_get_cart_discounted_amount();
		}
	}

	if ( pl8app_use_taxes() && pl8app_is_cart_taxed() && $add_taxes ) {
		$tax = pl8app_get_cart_tax();
		$amount += $tax;
	}

	if( $amount < 0 )
		$amount = 0.00;

	return apply_filters( 'pl8app_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Purchase Receipt Template Tags
 *
 * Displays all available template tags for the purchase receipt.
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @author pl8app
 * @return string $tags
 */
function pl8app_get_purchase_receipt_template_tags() {
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:','pl8app' ) . '<br/>' .
			'{menuitem_list} - ' . __('A list of menuitem purchased','pl8app' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','pl8app' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','pl8app' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','pl8app' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','pl8app' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','pl8app' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','pl8app' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','pl8app' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','pl8app' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','pl8app' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','pl8app' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','pl8app' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','pl8app' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','pl8app' ) . '<br/>' .
			'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'pl8app' );

	return apply_filters( 'pl8app_purchase_receipt_template_tags_description', $tags );
}


/**
 * Get Sale Notification Template Tags
 *
 * Displays all available template tags for the sale notification email
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @author pl8app
 * @return string $tags
 */
function pl8app_get_sale_notification_template_tags() {
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'pl8app' ) . '<br/>' .
			'{menuitem_list} - ' . __('A list of menuitem purchased','pl8app' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','pl8app' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','pl8app' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','pl8app' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','pl8app' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','pl8app' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','pl8app' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','pl8app' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','pl8app' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','pl8app' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','pl8app' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','pl8app' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','pl8app' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','pl8app' );

	return apply_filters( 'pl8app_sale_notification_template_tags_description', $tags );
}

/**
 * Email Template Header
 *
 * @access private
 * @since 1.0
 * @deprecated 2.0
 * @return string Email template header
 */
function pl8app_get_email_body_header() {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	?>
	<html>
	<head>
		<style type="text/css">#outlook a { padding: 0; }</style>
	</head>
	<body dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
	<?php
	do_action( 'pl8app_email_body_header' );
	return ob_get_clean();
}

/**
 * Email Template Footer
 *
 * @since 1.0
 * @deprecated 2.0
 * @return string Email template footer
 */
function pl8app_get_email_body_footer() {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	do_action( 'pl8app_email_body_footer' );
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.0
 * @deprecated 2.1
 * @global $pl8app_options
 * @return bool Whether or not taxes are calculated after discount
 */
function pl8app_taxes_after_discounts() {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.1', 'none', $backtrace );

	global $pl8app_options;
	$ret = isset( $pl8app_options['taxes_after_discounts'] ) && pl8app_use_taxes();
	return apply_filters( 'pl8app_taxes_after_discounts', $ret );
}

/**
 * Get Success Page URL
 *
 * @param string $query_string
 * @since       1.0
 * @deprecated  2.6 Please avoid usage of this function in favor of pl8app_get_success_page_uri()
 * @return      string
*/
function pl8app_get_success_page_url( $query_string = null ) {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.6', 'pl8app_get_success_page_uri()', $backtrace );

	return apply_filters( 'pl8app_success_page_url', pl8app_get_success_page_uri( $query_string ) );
}

/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.0
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @deprecated  2.5.7 Please avoid usage of this function in favor of refund() in pl8app_Payment
 * @internal param Arguments $data passed
 */
function pl8app_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	$backtrace = debug_backtrace();
	_pl8app_deprecated_function( 'pl8app_undo_purchase_on_refund', '2.5.7', 'pl8app_Payment->refund()', $backtrace );

	$payment = new pl8app_Payment( $payment_id );
	$payment->refund();
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 * @deprecated 2.7
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $earnings Earnings
 */
function pl8app_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null, $include_taxes = true ) {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.7', 'pl8app_Payment_Stats()->get_earnings()', $backtrace );

	global $wpdb;

	$args = array(
		'post_type'      => 'pl8app_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'update_post_term_cache' => false,
		'include_taxes'  => $include_taxes,
	);

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) || $hour == 0 ) {
		$args['hour'] = $hour;
	}

	$args   = apply_filters( 'pl8app_get_earnings_by_date_args', $args );
	$cached = get_transient( 'pl8app_stats_earnings' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			$sales = implode( ',', $sales );

			$total_earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_pl8app_payment_total' AND post_id IN ({$sales})" );
			$total_tax      = 0;

			if ( ! $include_taxes ) {
				$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_pl8app_payment_tax' AND post_id IN ({$sales})" );
			}

			$earnings += ( $total_earnings - $total_tax );
		}
		// Cache the results for one hour
		$cached[ $key ] = $earnings;
		set_transient( 'pl8app_stats_earnings', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return round( $result, 2 );
}

/**
 * Get Sales By Date
 *
 * @since 1.1.4.0
 * @deprecated 2.7
 * @author pl8app
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Sales
 */
function pl8app_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {
	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.7', 'pl8app_Payment_Stats()->get_sales()', $backtrace );

	$args = array(
		'post_type'      => 'pl8app_payment',
		'nopaging'       => true,
		'year'           => $year,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	$show_free = apply_filters( 'pl8app_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key' => '_pl8app_payment_total',
				'value' => 0,
				'compare' => '>',
				'type' => 'NUMERIC',
			),
		);
	}

	if ( ! empty( $month_num ) )
		$args['monthnum'] = $month_num;

	if ( ! empty( $day ) )
		$args['day'] = $day;

	if ( ! empty( $hour ) )
		$args['hour'] = $hour;

	$args = apply_filters( 'pl8app_get_sales_by_date_args', $args  );

	$cached = get_transient( 'pl8app_stats_sales' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;

		// Cache the results for one hour
		$cached[ $key ] = $count;
		set_transient( 'pl8app_stats_sales', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return $result;
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.0
 * @deprecated 2.8
 * @return string
 */
function pl8app_get_paypal_page_style() {

	$backtrace = debug_backtrace();

	_pl8app_deprecated_function( __FUNCTION__, '2.8', 'pl8app_get_paypal_image_url', $backtrace );

	$page_style = trim( pl8app_get_option( 'paypal_page_style', 'PayPal' ) );
	return apply_filters( 'pl8app_paypal_page_style', $page_style );
}
