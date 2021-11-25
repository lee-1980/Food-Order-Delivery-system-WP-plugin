<?php


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if taxes are enabled by using the option set from the pl8app Settings.
 * The value returned can be filtered.
 *
 * @since 1.0.0
 * @return bool Whether or not taxes are enabled
 */
function pl8app_use_taxes() {
	$ret = pl8app_get_option( 'enable_taxes', false );
	$ret = $ret == 1? true: false;
	return (bool) apply_filters( 'pl8app_use_taxes', $ret );
}

/**
 * Retrieve tax rates
 *
 * @since  1.0.0
 * @return array Defined tax rates
 */
function pl8app_get_tax_rates($tax_key) {
	$options = get_option( 'pl8app_settings');
	$tax_rates = isset($options['tax'])?$options['tax']:array();
    if(!empty($tax_key)){
        $tax_object =  !empty($tax_rates[$tax_key])? $tax_rates[$tax_key]: array();
        return apply_filters( 'pl8app_get_tax_rates', $tax_object);
    }
	else{
        return apply_filters( 'pl8app_get_tax_rates', $tax_rates);
    }
}

/**
 * Get taxation rate
 *
 * @since 1.0.0
 * @param bool $country
 * @param bool $state
 * @return mixed|void
 */
function pl8app_get_tax_rate( $country = false, $state = false ) {

    $rate = (float) pl8app_get_option( 'tax_rate', 0 );
    // Convert to a number we can use
    $rate = $rate / 100;
    return apply_filters( 'pl8app_tax_rate', $rate, $country, $state );
}

/**
 * Retrieve a fully formatted tax rate
 *
 * @since  1.0.0
 * @param string $country The country to retrieve a rate for
 * @param string $state The state to retrieve a rate for
 * @return string Formatted rate
 */
function pl8app_get_formatted_tax_rate( $country = false, $state = false ) {
	$rate = pl8app_get_tax_rate( $country, $state );
	$rate = round( $rate * 100, 4 );
	$formatted = $rate .= '%';
	return apply_filters( 'pl8app_formatted_tax_rate', $formatted, $rate, $country, $state );
}

/**
 * Calculate the taxed amount
 *
 * @since 1.0.0
 * @param $amount float The original amount to calculate a tax cost
 * @param $country string The country to calculate tax for. Will use default if not passed
 * @param $state string The state to calculate tax for. Will use default if not passed
 * @return float $tax Taxed amount
 */
function pl8app_calculate_tax( $amount = 0, $options, $menuitem_id = 0) {

	$tax  = 0.00;
    $rate = 0.00;

    $tax_key = isset($options['tax_key'])? $options['tax_key']: '';
    $tax_object = pl8app_get_tax_rates($tax_key);

    if(isset($tax_object['rate'])){
        $rate = $tax_object['rate'];
        $rate = round( $rate / 100, 4 );
        if ( pl8app_use_taxes() && $amount > 0 ) {

            if ( pl8app_prices_include_tax() ) {
                $pre_tax = ( $amount / ( 1 + $rate ) );
                $tax     = $amount - $pre_tax;
            } else {
                $tax = $amount * $rate;
            }

        }
    }

	return apply_filters( 'pl8app_taxed_amount', $tax, $rate);
}

/**
 * Returns the formatted tax amount for the given year
 *
 * @since 1.0.0
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses pl8app_get_sales_tax_for_year()
 * @return void
*/
function pl8app_sales_tax_for_year( $year = null ) {
	echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_sales_tax_for_year( $year ) ) );
}

/**
 * Gets the sales tax for the given year
 *
 * @since 1.0.0
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses pl8app_get_payment_tax()
 * @return float $tax Sales tax
 */
function pl8app_get_sales_tax_for_year( $year = null ) {
	global $wpdb;

	// Start at zero
	$tax = 0;

	if ( ! empty( $year ) ) {


		$args = array(
			'post_type'      => 'pl8app_payment',
			'post_status'    => array( 'publish', 'revoked' ),
			'posts_per_page' => -1,
			'year'           => $year,
			'fields'         => 'ids'
		);

		$payments    = get_posts( $args );
		$payment_ids = implode( ',', $payments );

		if ( count( $payments ) > 0 ) {
			$sql = "SELECT SUM( meta_value ) FROM $wpdb->postmeta WHERE meta_key = '_pl8app_payment_tax' AND post_id IN( $payment_ids )";
			$tax = $wpdb->get_var( $sql );
		}

	}

	return apply_filters( 'pl8app_get_sales_tax_for_year', $tax, $year );
}

/**
 * Is the cart taxed?
 *
 * This used to include a check for local tax opt-in, but that was ripped out in v1.6, so this is just a wrapper now
 *
 * @since 1.0
 * @return bool
 */
function pl8app_is_cart_taxed() {
	return pl8app_use_taxes();
}

/**
 * Check if the individual product prices include tax
 *
 * @since 1.0
 * @return bool $include_tax
*/
function pl8app_prices_include_tax() {
	$ret = ( pl8app_get_option( 'prices_include_tax', false ) == 'yes' && pl8app_use_taxes() );

	return apply_filters( 'pl8app_prices_include_tax', $ret );
}

/**
 * Checks whether the user has enabled display of taxes on the checkout
 *
 * @since 1.0
 * @return bool $include_tax
 */
function pl8app_prices_show_tax_on_checkout() {
	$ret = ( pl8app_get_option( 'checkout_include_tax', false ) == 'yes' && pl8app_use_taxes() );

	return apply_filters( 'pl8app_taxes_on_prices_on_checkout', $ret );
}

/**
 * Should we show address fields for taxation purposes?
 *
 * @since 1.y
 * @return bool
 */
function pl8app_cart_needs_tax_address_fields() {

	if( ! pl8app_is_cart_taxed() )
		return false;

	return ! did_action( 'pl8app_after_cc_fields', 'pl8app_default_cc_address_fields' );
}

/**
 * Is this Item excluded from tax?
 *
 * @since  1.0.0
 * @return bool
 */
function pl8app_menuitem_is_tax_exclusive( $menuitem_id = 0 ) {
	$ret = (bool) get_post_meta( $menuitem_id, '_pl8app_menuitem_tax_exclusive', true );
	return apply_filters( 'pl8app_menuitem_is_tax_exclusive', $ret, $menuitem_id );
}

/**
 * Get tax name
 *
 * @since  2.6
 * @return string
 */
function pl8app_get_tax_name() {

    $tax_name = pl8app_get_option( 'tax_name', '' );

    if ( empty( $tax_name ) ) {
        $tax_name = __( 'Estimated Tax', 'pl8app' );
    }

    $tax_name = apply_filters( 'pl8app_tax_name', $tax_name );

    return $tax_name;
}

/**
 * Checks whether it needs to show the billing details or not.
 *
 * @since 2.5.5
 * @return bool Whether or the fields needs to be shown
 */
function pl8app_show_billing_fields() {
    $enable_fields = pl8app_get_option( 'enable_billing_fields', false );
    return (bool) apply_filters( 'pl8app_show_billing_fields', $enable_fields );
}