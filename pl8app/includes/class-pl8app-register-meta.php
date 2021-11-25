<?php
/**
 *
 * This class is for registering our meta
 *
 * @package     pl8app
 * @subpackage  Classes/Register Meta
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Register_Meta Class
 *
 * @since  1.0.0
 */
class pl8app_Register_Meta {

	private static $instance;

	/**
	 * Setup the meta registration
	 *
	 * @since  1.0.0
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Get the one true instance of pl8app_Register_Meta.
	 *
	 * @since  1.0.0
	 * @return $instance
	 */
	static public function instance() {

		if ( !self::$instance ) {
			self::$instance = new pl8app_Register_Meta();
		}

		return self::$instance;

	}

	/**
	 * Register the hooks to kick off meta registration.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'register_menuitem_meta' ) );
		add_action( 'init', array( $this, 'register_payment_meta' ) );
	}

	/**
	 * Register the meta for the menuitem post type.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menuitem_meta() {
		register_meta(
			'post',
			'_pl8app_menuitem_earnings',
			array(
				'sanitize_callback' => 'pl8app_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total earnings for the specified product', 'pl8app' ),
			)
		);

		// Pre-WordPress 4.6 compatibility
		if ( ! has_filter( 'sanitize_post_meta__pl8app_menuitem_earnings' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_menuitem_earnings', 'pl8app_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_menuitem_sales',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'float',
				'description'       => __( 'The number of sales for the specified product.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_menuitem_sales' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_menuitem_sales', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'pl8app_price',
			array(
				'sanitize_callback' => array( $this, 'sanitize_price' ),
				'type'              => 'float',
				'description'       => __( 'The price of the product.', 'pl8app' ),
				'show_in_rest'      => false,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_pl8app_price' ) ) {
			add_filter( 'sanitize_post_meta_pl8app_price', array( $this, 'sanitize_price' ), 10, 4 );
		}

		register_meta(
			'post',
			'pl8app_variable_prices',
			array(
				'sanitize_callback' => array( $this, 'sanitize_variable_prices'),
				'type'              => 'array',
				'description'       => __( 'An array of variable prices for the product.', 'pl8app' ),
				'show_in_rest'      => false,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_pl8app_variable_prices' ) ) {
			add_filter( 'sanitize_post_meta_pl8app_variable_prices', array( $this, 'sanitize_variable_prices'), 10, 4 );
		}

		register_meta(
			'post',
			'pl8app_menuitem_files',
			array(
				'sanitize_callback' => array( $this, 'sanitize_files' ),
				'type'              => 'array',
				'description'       => __( 'The files associated with the product, available for menuitem.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_pl8app_menuitem_files' ) ) {
			add_filter( 'sanitize_post_meta_pl8app_menuitem_files', array( $this, 'sanitize_files' ), 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_bundled_products',
			array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'type'              => 'array',
				'description'       => __( 'An array of product IDs to associate with a bundle.', 'pl8app' ),
				'show_in_rest'      => false,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_bundled_products' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_bundled_products', array( $this, 'sanitize_array' ), 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_button_behavior',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( "Defines how this product's 'Purchase' button should behave, either add to cart or buy now", 'pl8app' ),
				'show_in_rest'      => false,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_button_behavior' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_button_behavior', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_default_price_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'When variable pricing is enabled, this value defines which option should be chosen by default.', 'pl8app' ),
				'show_in_rest'      => false,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_default_price_id' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_default_price_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

	}

	/**
	 * Register the meta for the pl8app_payment post type.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_payment_meta() {

		register_meta(
			'post',
			'_pl8app_payment_user_email',
			array(
				'sanitize_callback' => 'sanitize_email',
				'type'              => 'string',
				'description'       => __( 'The email address associated with the purchase.', 'pl8app' ),
			)
		);

		// Pre-WordPress 4.6 compatibility
		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_user_email' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_user_email', 'sanitize_email', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_customer_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The Customer ID associated with the payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_customer_id' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_customer_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_user_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The User ID associated with the payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_user_id' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_user_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_user_ip',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The IP address the payment was made from.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_user_ip' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_user_ip', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_purchase_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The unique purchase key for this payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_purchase_key' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_purchase_key', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_total',
			array(
				'sanitize_callback' => 'pl8app_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The order total for this payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_total' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_total', 'pl8app_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_mode',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'Identifies if the purchase was made in Test or Live mode.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_mode' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_mode', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_gateway',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The registered gateway that was used to process this payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_gateway' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_gateway', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_meta',
			array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'type'              => 'array',
				'description'       => __( 'Array of payment meta that contains cart details, menuitems, amounts, taxes, discounts, and subtotals, etc.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_meta' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_meta', array( $this, 'sanitize_array' ), 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_payment_tax',
			array(
				'sanitize_callback' => 'pl8app_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total amount of tax paid for this payment.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_payment_tax' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_payment_tax', 'pl8app_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_pl8app_completed_date',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The date this payment was changed to the `completed` status.', 'pl8app' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__pl8app_completed_date' ) ) {
			add_filter( 'sanitize_post_meta__pl8app_completed_date', 'sanitize_text_field', 10, 4 );
		}


	}

	/**
	 * Wrapper for intval
	 * Setting intval as the callback was stating an improper number of arguments, this avoids that.
	 *
	 * @since  1.0.0
	 * @param  int $value The value to sanitize.
	 * @return int        The value sanitiezed to be an int.
	 */
	public function intval_wrapper( $value ) {
		return intval( $value );
	}

	/**
	 * Sanitize values that come in as arrays
	 *
	 * @since  1.0.0
	 * @param  array  $value The value passed into the meta.
	 * @return array         The sanitized value.
	 */
	public function sanitize_array( $value = array() ) {

		if ( ! is_array( $value ) ) {

			if ( is_object( $value ) ) {
				$value = (array) $value;
			}

			if ( is_serialized( $value ) ) {

				preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $value, $matches );
				if ( ! empty( $matches ) ) {
					return false;
				}

				$value = (array) maybe_unserialize( $value );

			}

		}

		return $value;
	}

	/**
	 * Perform some sanitization on the amount field including not allowing negative values by default
	 *
	 * @since  1.0.0.5
	 * @param  float $price The price to sanitize
	 * @return float        A sanitized price
	 */
	public function sanitize_price( $price ) {

		$allow_negative_prices = apply_filters( 'pl8app_allow_negative_prices', false );

		if ( ! $allow_negative_prices && $price < 0 ) {
			$price = 0;
		}

		return pl8app_sanitize_amount( $price );
	}

	/**
	 * Sanitize the variable prices
	 *
	 * Ensures prices are correctly mapped to an array starting with an index of 0
	 *
	 * @since  1.0.0
	 * @param array $prices Variable prices
	 * @return array $prices Array of the remapped variable prices
	 */
	public function sanitize_variable_prices( $prices = array() ) {
		$prices = $this->remove_blank_rows( $prices );

		if ( ! is_array( $prices ) ) {
			return array();
		}

		foreach ( $prices as $id => $price ) {

			if ( empty( $price['amount'] ) && empty( $price['name'] ) ) {

				unset( $prices[ $id ] );
				continue;

			} elseif ( empty( $price['amount'] ) ) {

				$price['amount'] = 0;

			}

			$prices[ $id ]['amount'] = $this->sanitize_price( $price['amount'] );

		}

		return $prices;
	}

	/**
	 * Sanitize the file menuitems
	 *
	 * Ensures files are correctly mapped to an array starting with an index of 0
	 *
	 * @since  1.0.0
	 * @param array $files Array of all the file menuitems
	 * @return array $files Array of the remapped file menuitems
	 */
	function sanitize_files( $files = array() ) {
		$files = $this->remove_blank_rows( $files );

		// Files should always be in array format, even when there are none.
		if ( ! is_array( $files ) ) {
			$files = array();
		}

		// Clean up filenames to ensure whitespaces are stripped
		foreach( $files as $id => $file ) {

			if( ! empty( $files[ $id ]['file'] ) ) {
				$files[ $id ]['file'] = trim( $file['file'] );
			}

			if( ! empty( $files[ $id ]['name'] ) ) {
				$files[ $id ]['name'] = trim( $file['name'] );
			}
		}

		// Make sure all files are rekeyed starting at 0
		return $files;
	}

	/**
	 * Don't save blank rows.
	 *
	 * When saving, check the price and file table for blank rows.
	 * If the name of the price or file is empty, that row should not
	 * be saved.
	 *
	 * @since  1.0.0
	 * @param array $new Array of all the meta values
	 * @return array $new New meta value with empty keys removed
	 */
	private function remove_blank_rows( $new ) {

		if ( is_array( $new ) ) {
			foreach ( $new as $key => $value ) {
				if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) ) {
					unset( $new[ $key ] );
				}
			}
		}

		return $new;
	}

}
pl8app_Register_Meta::instance();
