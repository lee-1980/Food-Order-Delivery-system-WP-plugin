<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve a menuitem by a given field
 *
 * @since       2.0
 * @param       string $field The field to retrieve the discount with
 * @param       mixed $value The value for field
 * @return      mixed
 */
function pl8app_get_menuitem_by( $field = '', $value = '' ) {

	if( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch( strtolower( $field ) ) {

		case 'id':
			$menuitem = get_post( $value );

			if( get_post_type( $menuitem ) != 'menuitem' ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$menuitem = get_posts( array(
				'post_type'      => 'menuitem',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $menuitem ) {
				$menuitem = $menuitem[0];
			}

			break;

		case 'sku':
			$menuitem = get_posts( array(
				'post_type'      => 'menuitem',
				'meta_key'       => 'pl8app_sku',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $menuitem ) {
				$menuitem = $menuitem[0];
			}

			break;

		default:
			return false;
	}

	if( $menuitem ) {
		return $menuitem;
	}

	return false;
}

/**
 * Retrieves a menuitem post object by ID or slug.
 *
 * @since 1.0
 * @since 2.9 - Return an pl8app_Menuitem object.
 *
 * @param int $menuitem_id Item ID.
 *
 * @return pl8app_Menuitem $menuitem Entire menuitem data.
 */
function pl8app_get_menuitem( $menuitem_id = 0 ) {
	$menuitem = null;

	if ( is_numeric( $menuitem_id ) ) {

		$found_menuitem = new pl8app_Menuitem( $menuitem_id );

		if ( ! empty( $found_menuitem->ID ) ) {
			$menuitem = $found_menuitem;
		}

	} else { // Support getting a menuitem by name.
		$args = array(
			'post_type'     => 'menuitem',
			'name'          => $menuitem_id,
			'post_per_page' => 1,
			'fields'        => 'ids',
		);

		$menuitems = new WP_Query( $args );
		if ( is_array( $menuitems->posts ) && ! empty( $menuitems->posts ) ) {

			$menuitem_id = $menuitems->posts[0];

			$menuitem = new pl8app_Menuitem( $menuitem_id );

		}
	}

	return $menuitem;
}

/**
 * Checks whether or not a menuitem is free
 *
 * @since  1.0.0
 * @author pl8app
 * @param int $menuitem_id ID number of the menuitem to check
 * @param int $price_id (Optional) ID number of a variably priced item to check
 * @return bool $is_free True if the product is free, false if the product is not free or the check fails
 */
function pl8app_is_free_menuitem( $menuitem_id = 0, $price_id = false ) {

	if( empty( $menuitem_id ) ) {
		return false;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->is_free( $price_id );
}

/**
 * Returns the price of a menuitem, but only for non-variable priced menuitems.
 *
 * @since 1.0
 * @param int $menuitem_id ID number of the menuitem to retrieve a price for
 * @return mixed|string|int Price of the menuitem
 */
function pl8app_get_menuitem_price( $menuitem_id = 0 ) {

	if( empty( $menuitem_id ) ) {
		return false;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->get_price();
}

function pl8app_menuitem_add_bundled_price($price, $menuitem_id, $price_id){

    $menuitem = new pl8app_Menuitem($menuitem_id);

    $discount = $menuitem->get_bundle_discount();

    $bundle_price = 0;
    if($menuitem->is_bundled_menuitem()){
        $bundled_products = $menuitem->get_bundled_menuitems();
        foreach ($bundled_products as $bundled_id){
            if(check_availability_menu_item_timing($bundled_id)){
                $sub_bundled_price = pl8app_get_menuitem_price( $bundled_id );
                $bundle_price +=  pl8app_sanitize_amount( $sub_bundled_price );
            }
        }
        return $bundle_price - (float)$discount;
    }
    return $price;
}


/**
 * Displays a formatted price for a menuitem
 *
 * @since 1.0
 * @param int $menuitem_id ID of the menuitem price to show
 * @param bool $echo Whether to echo or return the results
 * @param int $price_id Optional price id for variable pricing
 * @return void
 */
function pl8app_price( $menuitem_id = 0, $echo = true, $price_id = false ) {

	if( empty( $menuitem_id ) ) {
		$menuitem_id = get_the_ID();
	}

	$price = pl8app_get_menuitem_price( $menuitem_id );

	$price           = apply_filters( 'pl8app_menuitem_price', pl8app_sanitize_amount( $price ), $menuitem_id, $price_id );
	$formatted_price = '<span class="pl8app_price" id="pl8app_price_' . $menuitem_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'pl8app_menuitem_price_after_html', $formatted_price, $menuitem_id, $price, $price_id );

	if ( $echo ) {
		echo $formatted_price;
	} else {
		return $formatted_price;
	}
}
add_filter( 'pl8app_menuitem_price', 'pl8app_menuitem_add_bundled_price', 10, 3);
add_filter( 'pl8app_menuitem_price', 'pl8app_format_amount', 10 );
add_filter( 'pl8app_menuitem_price', 'pl8app_currency_filter', 20 );


/**
 * Retrieves the final price of a menuitemable product after purchase
 * this price includes any necessary discounts that were applied
 *
 * @since 1.0
 * @param int $menuitem_id ID of the menuitem
 * @param array $user_purchase_info - an array of all information for the payment
 * @param string $amount_override a custom amount that over rides the 'pl8app_price' meta, used for variable prices
 * @return string - the price of the menuitem
 */
function pl8app_get_menuitem_final_price( $menuitem_id = 0, $user_purchase_info, $amount_override = null ) {
	if ( is_null( $amount_override ) ) {
		$original_price = get_post_meta( $menuitem_id, 'pl8app_price', true );
	} else {
		$original_price = $amount_override;
	}
	if ( isset( $user_purchase_info['discount'] ) && $user_purchase_info['discount'] != 'none' ) {
		// if the discount was a %, we modify the amount. Flat rate discounts are ignored
		if ( pl8app_get_discount_type( pl8app_get_discount_id_by_code( $user_purchase_info['discount'] ) ) != 'flat' )
			$price = pl8app_get_discounted_amount( $user_purchase_info['discount'], $original_price );
		else
			$price = $original_price;
	} else {
		$price = $original_price;
	}
	return apply_filters( 'pl8app_final_price', $price, $menuitem_id, $user_purchase_info );
}

/**
 * Retrieves the variable prices for a menuitem
 *
 * @since 1.0.0
 * @param int $menuitem_id ID of the menuitem
 * @return array Variable prices
 */
function pl8app_get_variable_prices( $menuitem_id = 0 ) {

	if( empty( $menuitem_id ) ) {
		return false;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->get_prices();
}

/**
 * Checks to see if a menuitem has variable prices enabled.
 *
 * @since 1.0.0
 * @param int $menuitem_id ID number of the menuitem to check
 * @return bool true if has variable prices, false otherwise
 */
function pl8app_has_variable_prices( $menuitem_id = 0 ) {

	if( empty( $menuitem_id ) ) {
		return false;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->has_variable_prices();
}

/**
 * Returns the default price ID for variable pricing, or the first
 * price if none is set
 *
 * @since 1.0
 * @param  int $menuitem_id ID number of the menuitem to check
 * @return int              The Price ID to select by default
 */
function pl8app_get_default_variable_price( $menuitem_id = 0 ) {

	if ( ! pl8app_has_variable_prices( $menuitem_id ) ) {
		return false;
	}

	$prices = pl8app_get_variable_prices( $menuitem_id );
	$default_price_id = get_post_meta( $menuitem_id, '_pl8app_default_price_id', true );

	if ( $default_price_id === '' ||  ! isset( $prices[$default_price_id] ) ) {
		if( is_array( $prices) ) {
			$default_price_id = current( array_keys( $prices ) );
		}

	}

	return apply_filters( 'pl8app_variable_default_price_id', absint( $default_price_id ), $menuitem_id );

}

/**
 * Retrieves the name of a variable price option
 *
 * @since 1.0.9
 * @param int $menuitem_id ID of the menuitem
 * @param int $price_id ID of the price option
 * @param int $payment_id optional payment ID for use in filters
 * @return string $price_name Name of the price option
 */
function pl8app_get_price_option_name( $menuitem_id = 0, $price_id = 0, $payment_id = 0 ) {
	$prices = pl8app_get_variable_prices( $menuitem_id );
	$price_name = '';

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$price_name = $prices[ $price_id ]['name'];
	}

	return apply_filters( 'pl8app_get_price_option_name', $price_name, $menuitem_id, $payment_id, $price_id );
}

/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.0
 * @param int $menuitem_id ID of the menuitem
 * @param int $price_id ID of the price option
 * @param int $payment_id ID of the payment
 * @return float $amount Amount of the price option
 */
function pl8app_get_price_option_amount( $menuitem_id = 0, $price_id = 0 ) {

	$prices = pl8app_get_variable_prices( $menuitem_id );
	$amount = 0.00;

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$amount = $prices[ $price_id ]['amount'];
	}

	return apply_filters( 'pl8app_get_price_option_amount', pl8app_sanitize_amount( $amount ), $menuitem_id, $price_id );
}

/**
 * Retrieves cheapest price option of a variable priced menuitem
 *
 * @since  1.0.0
 * @param int $menuitem_id ID of the menuitem
 * @return float Amount of the lowest price
 */
function pl8app_get_lowest_price_option( $menuitem_id = 0 ) {

	if ( empty( $menuitem_id ) )
		$menuitem_id = get_the_ID();

	if ( ! pl8app_has_variable_prices( $menuitem_id ) ) {
		return pl8app_get_menuitem_price( $menuitem_id );
	}

	$prices = pl8app_get_variable_prices( $menuitem_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}

		$low = $prices[ $min_id ]['amount'];

	}

	return pl8app_sanitize_amount( $low );
}

/**
 * Retrieves the ID for the cheapest price option of a variable priced menuitem
 *
 * @since  1.0.0
 * @param int $menuitem_id ID of the menuitem
 * @return int ID of the lowest price
 */
function pl8app_get_lowest_price_id( $menuitem_id = 0 ) {
	if ( empty( $menuitem_id ) )
		$menuitem_id = get_the_ID();

	if ( ! pl8app_has_variable_prices( $menuitem_id ) ) {
		return pl8app_get_menuitem_price( $menuitem_id );
	}

	$prices = pl8app_get_variable_prices( $menuitem_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}
	}

	return (int) $min_id;
}

/**
 * Retrieves most expensive price option of a variable priced menuitem
 *
 * @since  1.0.0
 * @param int $menuitem_id ID of the menuitem
 * @return float Amount of the highest price
 */
function pl8app_get_highest_price_option( $menuitem_id = 0 ) {

	if ( empty( $menuitem_id ) ) {
		$menuitem_id = get_the_ID();
	}

	if ( ! pl8app_has_variable_prices( $menuitem_id ) ) {
		return pl8app_get_menuitem_price( $menuitem_id );
	}

	$prices = pl8app_get_variable_prices( $menuitem_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {

		$max = 0;

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			$max = max( $max, $price['amount'] );

			if ( $price['amount'] == $max ) {
				$max_id = $key;
			}
		}

		$high = $prices[ $max_id ]['amount'];
	}

	return pl8app_sanitize_amount( $high );
}

/**
 * Retrieves a price from from low to high of a variable priced menuitem
 *
 * @since  1.0.0
 * @param int $menuitem_id ID of the menuitem
 * @return string $range A fully formatted price range
 */
function pl8app_price_range( $menuitem_id = 0 ) {
	$low   = pl8app_get_lowest_price_option( $menuitem_id );
	$high  = pl8app_get_highest_price_option( $menuitem_id );
	$range = '<span class="pl8app_price pl8app_price_range_low" id="pl8app_price_low_' . $menuitem_id . '">' . pl8app_currency_filter( pl8app_format_amount( $low ) ) . '</span>';
	if( $low < $high ){
		$range .= '<span class="pl8app_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
		$range .= '<span class="pl8app_price pl8app_price_range_high" id="pl8app_price_high_' . $menuitem_id . '">' . pl8app_currency_filter( pl8app_format_amount( $high ) ) . '</span>';
	}
	return apply_filters( 'pl8app_price_range', $range, $menuitem_id, $low, $high );
}

/**
 * Checks to see if multiple price options can be purchased at once
 *
 * @since 1.0.0
 * @param int $menuitem_id Item ID
 * @return bool
 */
function pl8app_single_price_option_mode( $menuitem_id = 0 ) {

	if ( empty( $menuitem_id ) ) {
		$menuitem = get_post();

		$menuitem_id = isset( $menuitem->ID ) ? $menuitem->ID : 0;
	}

	if ( empty( $menuitem_id ) ) {
		return false;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->is_single_price_mode();

}

/**
 * Get product types
 *
 * @since 1.0
 * @return array $types Item types
 */
function pl8app_get_menuitem_types() {

	$types = array(
		'default'       => __( 'Default', 'pl8app' ),
		'bundle'  => __( 'Bundle', 'pl8app' )
	);

	return apply_filters( 'pl8app_menuitem_types', $types );
}

/**
 * Gets the Item type
 *
 * @since  1.0.0
 * @param int $menuitem_id Item ID
 * @return string $type Item type
 */
function pl8app_get_menuitem_type( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->type;
}

/**
 * Determines if a product is a bundle
 *
 * @since  1.0.0
 * @param int $menuitem_id Item ID
 * @return bool
 */
function pl8app_is_bundled_product( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->is_bundled_menuitem();
}


/**
 * Retrieves the product IDs of bundled products
 *
 * @since  1.0.0
 * @param int $menuitem_id Item ID
 * @return array $products Products in the bundle
 *
 * @since 1.0
 * @param int $price_id Variable price ID
 */
function pl8app_get_bundled_products( $menuitem_id = 0, $price_id = null ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	if ( null !== $price_id ) {
		return $menuitem->get_variable_priced_bundled_menuitems( $price_id );
	} else {
		return $menuitem->bundled_menuitems;
	}
}

/**
 * Returns the total earnings for a menuitem.
 *
 * @since 1.0
 * @param int $menuitem_id Item ID
 * @return int $earnings Earnings for a certain menuitem
 */
function pl8app_get_menuitem_earnings_stats( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->earnings;
}

/**
 * Return the sales number for a menuitem.
 *
 * @since 1.0
 * @param int $menuitem_id Item ID
 * @return int $sales Amount of sales for a certain menuitem
 */
function pl8app_get_menuitem_sales_stats( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->sales;
}

/**
 * Record Sale In Log
 *
 * Stores log information for a menuitem sale.
 *
 * @since 1.0
 * @global $pl8app_logs
 * @param int $menuitem_id Item ID
 * @param int $payment_id Payment ID
 * @param bool|int $price_id Price ID, if any
 * @param string|null $sale_date The date of the sale
 * @return void
*/
function pl8app_record_sale_in_log( $menuitem_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	global $pl8app_logs;

	$log_data = array(
		'post_parent'   => $menuitem_id,
		'log_type'      => 'sale',
		'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null
	);

	$log_meta = array(
		'payment_id'    => $payment_id,
		'price_id'      => (int) $price_id
	);

	$pl8app_logs->insert_log( $log_data, $log_meta );
}

/**
 * Delete log entries when deleting menuitem product
 *
 * Removes all related log entries when a menuitem is completely deleted.
 * (Does not run when a menuitem is trashed)
 *
 * @since  1.0.0
 * @param int $menuitem_id ID
 * @return void
 */
function pl8app_remove_menuitem_logs_on_delete( $menuitem_id = 0 ) {
	if ( 'menuitem' !== get_post_type( $menuitem_id ) )
		return;

	global $pl8app_logs;

	// Remove all log entries related to this menuitem
	$pl8app_logs->delete_logs( $menuitem_id );
}
add_action( 'delete_post', 'pl8app_remove_menuitem_logs_on_delete' );

/**
 *
 * Increases the sale count of a menuitem.
 *
 * @since 1.0
 * @param int $menuitem_id ID
 * @param int $quantity Quantity to increase purchase count by
 * @return bool|int
 */
function pl8app_increase_purchase_count( $menuitem_id = 0, $quantity = 1 ) {
	$quantity = (int) $quantity;
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->increase_sales( $quantity );
}

/**
 * Decreases the sale count of a menuitem. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.0.0.1
 * @param int $menuitem_id ID
 * @return bool|int
 */
function pl8app_decrease_purchase_count( $menuitem_id = 0, $quantity = 1 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->decrease_sales( $quantity );
}

/**
 * Increases the total earnings of a menuitem.
 *
 * @since 1.0
 * @param int $menuitem_id ID
 * @param int $amount Earnings
 * @return bool|int
 */
function pl8app_increase_earnings( $menuitem_id = 0, $amount ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->increase_earnings( $amount );
}

/**
 * Decreases the total earnings of a menuitem. Primarily for when a purchase is refunded.
 *
 * @since 1.0.0.1
 * @param int $menuitem_id ID
 * @param int $amount Earnings
 * @return bool|int
 */
function pl8app_decrease_earnings( $menuitem_id = 0, $amount ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->decrease_earnings( $amount );
}

/**
 * Retrieves the average monthly earnings for a specific menuitem
 *
 * @since 1.0
 * @param int $menuitem_id ID
 * @return float $earnings Average monthly earnings
 */
function pl8app_get_average_monthly_menuitem_earnings( $menuitem_id = 0 ) {
	$earnings 	  = pl8app_get_menuitem_earnings_stats( $menuitem_id );
	$release_date = get_post_field( 'post_date', $menuitem_id );

	$diff 	= abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 ) {
		$earnings = ( $earnings / $months );
	}

	return $earnings < 0 ? 0 : $earnings;
}

/**
 * Retrieves the average monthly sales for a specific menuitem
 *
 * @since 1.0
 * @param int $menuitem_id ID
 * @return float $sales Average monthly sales
 */
function pl8app_get_average_monthly_menuitem_sales( $menuitem_id = 0 ) {
	$sales          = pl8app_get_menuitem_sales_stats( $menuitem_id );
	$release_date   = get_post_field( 'post_date', $menuitem_id );

	$diff   = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 )
		$sales = ( $sales / $months );

	return $sales;
}

/**
 * Get product notes
 *
 * @since  1.0.0
 * @param int $menuitem_id ID
 * @return string $notes Product notes
 */
function pl8app_get_product_notes( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->notes;
}

/**
 * Retrieves a menuitem SKU by ID.
 *
 * @since  1.0.0
 *
 * @author pl8app
 * @param int $menuitem_id
 *
 * @return mixed|void SKU
 */
function pl8app_get_menuitem_sku( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->sku;
}

/**
 * get the button behavior, either add to cart or direct
 *
 * @since  1.0.0
 *
 * @param int $menuitem_id
 * @return mixed|void Add to Cart or Direct
 */
function pl8app_get_menuitem_button_behavior( $menuitem_id = 0 ) {
	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->button_behavior;
}

/**
 * Is quantity input disabled on this product?
 *
 * @since 1.0
 * @return bool
 */
function pl8app_menuitem_quantities_disabled( $menuitem_id = 0 ) {

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->quantities_disabled();
}

/**
 * Get the  method
 *
 * @since  1.0.0
 * @return string The method to use for file menuitems
 */
function pl8app_get_file_menuitem_method() {
	$method = pl8app_get_option( 'menuitem_method', 'direct' );
	return apply_filters( 'pl8app_file_menuitem_method', $method );
}

/**
 * Given a value from the product dropdown array, parse it's parts
 *
 * @since  1.0.0.9
 * @param  string $values A value saved in a product dropdown array
 * @return array          A parsed set of values for menuitem_id and price_id
 */
function pl8app_parse_product_dropdown_value( $value ) {
	$parts       = explode( '_', $value );
	$menuitem_id = $parts[0];
	$price_id    = isset( $parts[1] ) ? $parts[1] : false;

	return array( 'menuitem_id' => $menuitem_id, 'price_id' => $price_id );
}

/**
 * Get bundle pricing variations
 *
 * @since 1.0
 * @param  int $menuitem_id
 * @return array|void
 */
function pl8app_get_bundle_pricing_variations( $menuitem_id = 0 ) {
	if ( $menuitem_id == 0 ) {
		return;
	}

	$menuitem = new pl8app_Menuitem( $menuitem_id );
	return $menuitem->get_bundle_pricing_variations();
}

/**
 * Returns the addon categories
 * @param  integer $parent id of the parent category
 * @return array   array of cateories
 * @since 3.0
 */
function pl8app_get_addons( $parent = 0 ) {

  $addons_args = apply_filters(
    'pl8app_get_addons_args',
    array(
      'taxonomy'  	=> 'addon_category',
      'orderby'   	=> 'name',
      'parent'    	=> $parent,
      'hide_empty'  => false
    )
  );

  $addons = get_terms( $addons_args );

  return apply_filters( 'pl8app_get_addons', $addons );
}


/**
 * Returns the addon meta data
 * @param  integer $term id of the addon
 * @param  string $field of the addon
 * @return array   array of cateories
 * @since 3.0
 */
function pl8app_get_addon_data( $term_id, $field ) {
  if ( ! $term_id )
    return;

  $data = get_option( 'taxonomy_term_' . $term_id, array() );
  $value = '';

  if ( !empty( $data ) ) {
    $value = isset( $data[ $field ] ) ? $data[ $field ] : '';
  } else {
    $value = get_term_meta( $term_id, '_' . $field, true );
  }
  return $value;
}

/**
 * Get addon type
 */
function pl8app_get_addon_types(){

  $addon_types = apply_filters(
    'pl8app_addon_types',
    array(
      'single'  => 'Single',
      'multiple'  => 'Multiple'
    )
  );

  return $addon_types;
}

/**
 * Get dynamic price of addon
 */
function pl8app_dynamic_addon_price( $post_id, $child_addon, $parent_addon = null, $price_id = null ) {

	if( is_null( $price_id ) )
		$price_id = 0;

	if( is_null( $parent_addon ) ) {
		$term = get_term( $child_addon, 'addon_category' );
		$parent_addon = $term->parent;
	}

	$addon_price 	= pl8app_get_addon_data( $child_addon, 'price' );
	$item_addons 	= get_post_meta( $post_id, '_addon_items', true );

	if( empty( $item_addons ) )
		return $addon_price;

	if( ! isset( $item_addons[$parent_addon]['prices'] ) )
		return $addon_price;

	if( pl8app_has_variable_prices( $post_id ) ) {
		$prices = pl8app_get_variable_prices( $post_id );
		$addon_price = $item_addons[$parent_addon]['prices'][$child_addon][$prices[$price_id]['name']];
	} else {
		$addon_price = $item_addons[$parent_addon]['prices'][$child_addon];
	}

	return $addon_price;
}
