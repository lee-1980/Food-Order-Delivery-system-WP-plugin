<?php
/**
 * Menu Item Object
 *
 * @package     pl8app
 * @subpackage  Classes/pl8app
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Menuitem Class
 *
 * @since  1.0.0
 */
class pl8app_Menuitem {

	/**
	 * The menuitem ID
	 *
	 * @since  1.0.0
	 */
	public $ID = 0;

	/**
	 * The menuitem price
	 *
	 * @since  1.0.0
	 */
	private $price;

	/**
	 * The menuitem prices, if Variable Prices are enabled
	 *
	 * @since  1.0.0
	 */
	private $prices;

	/**
	 * The menuitem  vegan type, VEG or NON-VEG
	 *
	 * @since  1.0.0
	 */
	private $menu_type;

	/**
	 * The menuitem type, default or bundle
	 *
	 * @since  1.0.0
	 */
	private $type;

	/**
	 * The bundled menuitems, if this is a bundle type
	 *
	 * @since  1.0.0
	 */
	private $bundled_menuitems;

	/**
	 * The menuitem's sale count
	 *
	 * @since  1.0.0
	 */
	private $sales;

	/**
	 * The menuitem's total earnings
	 *
	 * @since  1.0.0
	 */
	private $earnings;

	/**
	 * The menuitem's notes
	 *
	 * @since  1.0.0
	 */
	private $notes;

	/**
	 * The menuitem sku
	 *
	 * @since  1.0.0
	 */
	private $sku;

	/**
	 * The menuitem's purchase button behavior
	 *
	 * @since  1.0.0
	 */
	private $button_behavior;

    /**
     * THe menuitem's purchase VAT
     *
     */
    private $vat;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 */
	public function __construct( $_id = false, $_args = array() ) {

		$menuitem = WP_Post::get_instance( $_id );

		return $this->setup_menuitem( $menuitem );

	}

	/**
	 * Given the menuitem data, let's set the variables
	 *
	 * @since  1.0.0.6
	 * @param  WP_Post $menuitem The WP_Post object for menuitem.
	 * @return bool             If the setup was successful or not
	 */
	private function setup_menuitem( $menuitem ) {

		if( ! is_object( $menuitem ) ) {
			return false;
		}

		if( ! $menuitem instanceof WP_Post ) {
			return false;
		}

		if( 'menuitem' !== $menuitem->post_type ) {
			return false;
		}

		foreach ( $menuitem as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}

		}

		return true;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since  1.0.0
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'pl8app-menuitem-invalid-property', sprintf( __( 'Can\'t get property %s', 'pl8app' ), $key ) );

		}

	}

	/**
	 * Creates a menuitem
	 *
	 * @since  1.0.0.6
	 * @param  array  $data Array of attributes for a menuitem
	 * @return mixed  false if data isn't passed and class not instantiated for creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'menuitem',
			'post_status' => 'draft',
			'post_title'  => __( 'New Product', 'pl8app' )
		);

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a menuitem is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'pl8app_menuitem_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$menuitem = WP_Post::get_instance( $id );

		/**
		 * Fired after a menuitem is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'pl8app_menuitem_post_create', $id, $args );

		return $this->setup_menuitem( $menuitem );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since  1.0.0
	 * @return int ID of the menuitem
	 */
	public function get_ID() {

		return $this->ID;

	}

	/**
	 * Retrieve the menuitem name
	 *
	 * @since 1.0
	 * @return string Name of the menuitem
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	public function get_menu_type() {

		if( ! isset( $this->menu_type ) ) {

			$this->menu_type = get_post_meta( $this->ID, 'pl8app_menu_type', true );
			return apply_filters( 'pl8app_get_item_menu_type', $this->menu_type, $this->ID );
		}
	}

	/**
	 * Retrieve the price
	 *
	 * @since  1.0.0
	 * @return float Price of the menuitem
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = get_post_meta( $this->ID, 'pl8app_price', true );

			if ( $this->price ) {
				$this->price = pl8app_sanitize_amount( $this->price );
			} else {
				$this->price = 0;
			}
		}

		/**
		 * Override the menuitem price.
		 *
		 * @since  1.0.0
		 *
		 * @param string $price The menuitem price(s).
		 * @param string|int $id The menuitems ID.
		 */
		return apply_filters( 'pl8app_get_menuitem_price', $this->price, $this->ID );

	}


	public function get_vat(){

	    if(!isset($this->vat)){
            $this->vat = get_post_meta( $this->ID, 'pl8app_menuitem_vat', true );
        }

        /**
         * Override the menuitem vat.
         *
         * @since  1.0.0
         *
         * @param string $price The menuitem VAT.
         * @param string|int $id The menuitems ID.
         */
        return apply_filters( 'pl8app_get_menuitem_vat', $this->vat, $this->ID );
    }

	public function get_bundle_discount(){

	    $bundle_discount = get_post_meta( $this->ID, '_pl8app_bundled_discount', true );
        if(empty($bundle_discount)) $bundle_discount = 0;

        return apply_filters( 'pl8app_get_bundle_discount', $bundle_discount, $this->ID );
    }
	/**
	 * Retrieve the variable prices
	 *
	 * @since  1.0.0
	 * @return array List of the variable prices
	 */
	public function get_prices() {

		$this->prices = array();

		if( true === $this->has_variable_prices() ) {

			if ( empty( $this->prices ) ) {
				$this->prices = get_post_meta( $this->ID, 'pl8app_variable_prices', true );
			}
		}

		/**
		 * Override variable prices
		 *
		 * @since  1.0.0
		 *
		 * @param array $prices The array of variables prices.
		 * @param int|string The ID of the menuitem.
		 */
		return apply_filters( 'pl8app_get_variable_prices', $this->prices, $this->ID );
	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since  1.0.0
	 * @return bool True if menuitem is in single price mode, false otherwise
	 */
	public function is_single_price_mode() {

		$ret = get_post_meta( $this->ID, '_pl8app_price_options_mode', true );

		/**
		 * Override the price mode for a menuitem when checking if is in single price mode.
		 *
		 * @since 1.0
		 *
		 * @param bool $ret Is menuitem in single price mode?
		 * @param int|string The ID of the menuitem.
		 */
		return (bool) apply_filters( 'pl8app_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if the menuitem has variable prices enabled
	 *
	 * @since  1.0.0
	 * @return bool True when the menuitem has variable pricing enabled, false otherwise
	 */
	public function has_variable_prices() {

		$ret = get_post_meta( $this->ID, '_variable_pricing', true );

		/**
		 * Override whether the menuitem has variables prices.
		 *
		 * @since 1.0
		 *
		 * @param bool $ret Does menuitem have variable prices?
		 * @param int|string The ID of the menuitem.
		 */
		return (bool) apply_filters( 'pl8app_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Get menu Categories
	 *
	 * @since 2.4.2
	 * @param arr str $fields
	 * @access public
	 */
	public function get_menu_categories( $fields = 'ids' ){

		$menu_categories = wp_get_post_terms(
			$this->ID,
			'menu-category',
			array(
				'fields' => $fields
			)
		);
		return $menu_categories;
	}

	/**
	 * Retrieve the menuitem type, default or bundle
	 *
	 * @since  1.0.0
	 * @return string Type of menuitem, either 'default' or 'bundle'
	 */
	public function get_type() {

		if( ! isset( $this->type ) ) {

			$this->type = get_post_meta( $this->ID, '_pl8app_product_type', true );

			if( empty( $this->type ) ) {
				$this->type = 'default';
			}

		}

		return apply_filters( 'pl8app_get_menuitem_type', $this->type, $this->ID );
	}

	/**
	 * Determine if this is a bundled menuitem
	 *
	 * @since  1.0.0
	 * @return bool True when menuitem is a bundle, false otherwise
	 */
	public function is_bundled_menuitem() {
		return 'bundle' === $this->get_type();
	}

	/**
	 * Retrieves the Menu Item IDs that are bundled with this
	 *
	 * @since  1.0.0
	 * @return array List of bundled menuitems
	 */
	public function get_bundled_menuitems() {

		if( ! isset( $this->bundled_menuitems ) ) {

			$this->bundled_menuitems = (array) get_post_meta( $this->ID, '_pl8app_bundled_products', true );

		}

		return (array) apply_filters( 'pl8app_get_bundled_products', array_filter( $this->bundled_menuitems ), $this->ID );

	}

	/**
	 * Retrieve the Product IDs that are bundled with this based on the variable pricing ID passed
	 *
	 * @since 1.0
	 * @param int $price_id Variable pricing ID
	 * @return array List of bundled menuitems
	 */
	public function get_variable_priced_bundled_menuitems( $price_id = null ) {
		if ( null == $price_id ) {
			return $this->get_bundled_menuitems();
		}

		$menuitems         = array();
		$bundled_menuitems = $this->get_bundled_menuitems();
		$price_assignments = $this->get_bundle_pricing_variations();

		if ( ! $price_assignments ) {
			return $bundled_menuitems;
		}

		$price_assignments = $price_assignments[0];
		$price_assignments = array_values( $price_assignments );

		foreach ( $price_assignments as $key => $value ) {
			if ( $value == $price_id || $value == 'all' ) {
				$menuitems[] = $bundled_menuitems[ $key ];
			}
		}

		return $menuitems;
	}

	/**
	 * Retrieve the menuitem notes
	 *
	 * @since  1.0.0
	 * @return string Note related to the menuitem
	 */
	public function get_notes() {

		if( ! isset( $this->notes ) ) {

			$this->notes = get_post_meta( $this->ID, 'pl8app_product_notes', true );

		}

		return (string) apply_filters( 'pl8app_product_notes', $this->notes, $this->ID );

	}

	/**
	 * Retrieve the menuitem sku
	 *
	 * @since  1.0.0
	 * @return string SKU of the menuitem
	 */
	public function get_sku() {

		if( ! isset( $this->sku ) ) {

			$this->sku = get_post_meta( $this->ID, 'pl8app_sku', true );

			if ( empty( $this->sku ) ) {
				$this->sku = '-';
			}

		}

		return apply_filters( 'pl8app_get_menuitem_sku', $this->sku, $this->ID );

	}

	/**
	 * Retrieve the purchase button behavior
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_button_behavior() {

		if( ! isset( $this->button_behavior ) ) {

			$this->button_behavior = get_post_meta( $this->ID, '_pl8app_button_behavior', true );

			if( empty( $this->button_behavior ) || ! pl8app_shop_supports_buy_now() ) {

				$this->button_behavior = 'add_to_cart';

			}

		}

		return apply_filters( 'pl8app_get_menuitem_button_behavior', $this->button_behavior, $this->ID );

	}

	/**
	 * Retrieve the sale count for the menuitem
	 *
	 * @since  1.0.0
	 * @return int Number of times this has been purchased
	 */
	public function get_sales() {

		if( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_pl8app_menuitem_sales', true ) ) {
				add_post_meta( $this->ID, '_pl8app_menuitem_sales', 0 );
			}

			$this->sales = get_post_meta( $this->ID, '_pl8app_menuitem_sales', true );

			// Never let sales be less than zero
			$this->sales = max( $this->sales, 0 );

		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since  1.0.0
	 * @param int $quantity The quantity to increase the sales by
	 * @return int New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		$quantity    = absint( $quantity );
		$total_sales = $this->get_sales() + $quantity;

		if ( $this->update_meta( '_pl8app_menuitem_sales', $total_sales ) ) {

			$this->sales = $total_sales;

			do_action( 'pl8app_menuitem_increase_sales', $this->ID, $this->sales, $this );

			return $this->sales;

		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since  1.0.0
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		// Only decrease if not already zero
		if ( $this->get_sales() > 0 ) {

			$quantity    = absint( $quantity );
			$total_sales = $this->get_sales() - $quantity;

			if ( $this->update_meta( '_pl8app_menuitem_sales', $total_sales ) ) {

				$this->sales = $total_sales;

				do_action( 'pl8app_menuitem_decrease_sales', $this->ID, $this->sales, $this );

				return $this->sales;

			}

		}

		return false;

	}

	/**
	 * Retrieve the total earnings for the menuitem
	 *
	 * @since  1.0.0
	 * @return float Total menuitem earnings
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_pl8app_menuitem_earnings', true ) ) {
				add_post_meta( $this->ID, '_pl8app_menuitem_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_pl8app_menuitem_earnings', true );

			// Never let earnings be less than zero
			$this->earnings = max( $this->earnings, 0 );

		}

		return $this->earnings;

	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since  1.0.0
	 * @param int|float $amount Amount to increase the earnings by
	 * @return float New number of total earnings
	 */
	public function increase_earnings( $amount = 0 ) {

		$current_earnings = $this->get_earnings();
		$new_amount = apply_filters( 'pl8app_menuitem_increase_earnings_amount', $current_earnings + (float) $amount, $current_earnings, $amount, $this );

		if ( $this->update_meta( '_pl8app_menuitem_earnings', $new_amount ) ) {

			$this->earnings = $new_amount;

			do_action( 'pl8app_menuitem_increase_earnings', $this->ID, $this->earnings, $this );

			return $this->earnings;

		}

		return false;

	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since  1.0.0
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		// Only decrease if greater than zero
		if ( $this->get_earnings() > 0 ) {

			$current_earnings = $this->get_earnings();
			$new_amount = apply_filters( 'pl8app_menuitem_decrease_earnings_amount', $current_earnings - (float) $amount, $current_earnings, $amount, $this );

			if ( $this->update_meta( '_pl8app_menuitem_earnings', $new_amount ) ) {

				$this->earnings = $new_amount;

				do_action( 'pl8app_menuitem_decrease_earnings', $this->ID, $this->earnings, $this );

				return $this->earnings;

			}

		}

		return false;

	}

	/**
	 * Determine if the menuitem is free or if the given price ID is free
	 *
	 * @since  1.0.0
	 * @param bool $price_id ID of variation if needed
	 * @return bool True when the menuitem is free, false otherwise
	 */
	public function is_free( $price_id = false ) {

		$is_free = false;
		$variable_pricing = pl8app_has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {

			$price = pl8app_get_price_option_amount( $this->ID, $price_id );

		} elseif ( $variable_pricing && $price_id === false ) {

			$lowest_price  = (float) pl8app_get_lowest_price_option( $this->ID );
			$highest_price = (float) pl8app_get_highest_price_option( $this->ID );

			if ( $lowest_price === 0.00 && $highest_price === 0.00 ) {
				$price = 0;
			}

		} elseif( ! $variable_pricing ) {

			$price = get_post_meta( $this->ID, 'pl8app_price', true );

		}

		if( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'pl8app_is_free_menuitem', $is_free, $this->ID, $price_id );

	}

	/**
	 * Is quantity input disabled on this product?
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function quantities_disabled() {

		$ret = (bool) get_post_meta( $this->ID, '_pl8app_quantities_disabled', true );
		return apply_filters( 'pl8app_menuitem_quantity_disabled', $ret, $this->ID );

	}

	/**
	 * Updates a single meta entry for the menuitem
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $meta_key   The meta_key to update
	 * @param  string|array|object $meta_value The value to put into the meta
	 * @return bool             The result of the update query
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		global $wpdb;

		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );
			return true;

		}

		return false;
	}

	/**
	 * Checks if the menuitem can be purchased
	 *
	 * NOTE: Currently only checks on pl8app_get_cart_contents() and pl8app_add_to_cart()
	 *
	 * @since  1.0.0.4
	 * @return bool If the current menuitem ID can be purchased
	 */
	public function can_purchase() {
		$can_purchase = true;

		if ( $this->post_status != 'publish' ) {
			$can_purchase = false;
		}

		return (bool) apply_filters( 'pl8app_can_purchase_menuitem', $can_purchase, $this );
	}

	/**
	 * Get pricing variations for bundled items
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_bundle_pricing_variations() {
		return get_post_meta( $this->ID, '_pl8app_bundled_products_conditions' );
	}

}
