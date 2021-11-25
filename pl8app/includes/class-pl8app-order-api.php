<?php
/**
 * pl8app_Order_Tracking
 *
 * @package pl8app_Order_Tracking
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main pl8app_Order_Tracking Class.
 *
 * @class pl8app_Order_Tracking
 */

class pl8app_Order_Tracking {

	/**
	 * pl8app_Order_Tracking Settings.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * The single instance of the class.
	 *
	 * @var pl8app_Order_Tracking
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Holds the version number
	 *
	 * @var string
	 * @since 1.0
	 */
	public $version = '1.0.0';

	/**
	 * Main pl8app_Order_Tracking Instance.
	 *
	 * Ensures only one instance of pl8app_Order_Tracking is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @return pl8app_Order_Tracking - Main instance.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * pl8app_Order_Tracking Constructor.
	 */
	public function __construct() {

		$this->setup_globals();
		$this->init_hooks();
		self::$settings = $this->get_api_settings();
	}

	/**
	 * Sets up the constants/globals used
	 *
	 * @since 1.0
	 * @access public
	 */
	private function setup_globals() {

		// File Path and URL Information
		$this->file        	= PL8_PLUGIN_FILE;
		$this->basename    	= apply_filters( 'pl8app_tracking_addon_basenname', plugin_basename( $this->file ) );
		$this->plugin_url  	= plugin_dir_url( PL8_PLUGIN_FILE );
		$this->plugin_path 	= plugin_dir_path( PL8_PLUGIN_FILE );

		// Firebase
		$this->fb_api_key 	= 'AAAAeOF9Ads:APA91bEEDZr5IcglacSchgCtC3aNWOjc8nVZ2E6tjfOvkp6AjatmwgeSggDfPmc-O_LvWGFKSko5_8BFXuwzu1jG_b3p5oGj85O_8zqwzscIpN2_QNexMAfQAVE7VOH2HEU5sz8CFUk5';
		$this->fb_project 	= '519179141595';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {

		// Load the updator and license class once all plugins are loaded
		add_action ('plugins_loaded', array( $this, 'updater' ) );


		// Add direct Settings page link from plugins screen
  		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'pl8app_ota_settings_link' ) );

		// Settings panel under pl8app Settings
  		add_filter( 'pl8app_settings_sections_general', array( $this, 'pl8app_add_api_settings' ) );

		// Settings page for Tracking API option
  		add_filter( 'pl8app_settings_general' , array( $this, 'pl8app_general_api_settings' ), 10 );

		// Rest API Actions
  		add_action( 'rest_api_init', array( $this, 'pl8app_ota_create_endpoint' ) );

		// New order notification once the order is placed
  		add_action( 'pl8app_payment_saved', array( $this, 'pl8app_api_new_order_notification'), 10, 2 );
  	}

  	/**
	 * Loads the Updater
	 *
	 * Instantiates the Software Licensing Plugin Updater and passes the plugin
	 * data to the class.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function updater() {
		if ( class_exists( 'pl8app_License' ) ) {
			new pl8app_License( $this->file, 'pl8app - Order Tracking API', $this->version, 'MagniGenie', 'pl8app_pl8app___order_tracking_api' );
		}
	}

  	/**
	 * Get Order Tracking settings from the pl8app Settings
	 *
	 * @return mixed
	 */
  	protected function get_api_settings() {
  		$api_options = get_option( 'pl8app_settings', array() );
  		return apply_filters( 'pl8app_order_api_options', $api_options );
  	}


	/**
	 * Add settings link for pl8app Order Tracking
	 * @since 1.0.0
	 *
	 * @param $links Array of links
	 * @return array
	 */
	public function pl8app_ota_settings_link( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=pl8app-settings&tab=general&section=order-tracking' ) . '" aria-label="' . esc_attr__( 'View Order Tracking Settings', 'order-tracking' ) . '">' . esc_html__( 'Settings', 'order-tracking' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	/**
	 * Creates section for Order Tracking Settings in the admin
	 *
	 * @since  1.0.0
	 * @param  array  $section Array of section
	 * @return array  array of links for the section
	 */
	public function pl8app_add_api_settings( $section ) {
		$section['order-tracking'] = __( 'Order Tracking', 'order-tracking' );
		return $section;
	}

	/**
	 * Add section for Order Tracking in the pl8app section
	 *
	 * @since 1.0.0
	 * @access public
	 * @param arr $general_settings
	 *
	 */
	public function pl8app_general_api_settings( $general_settings ) {

		$general_settings['order-tracking'] = array(

			'tracking_api_settings' => array(
				'id'    => 'tracking_api_settings',
				'type'  => 'header',
				'name'  => '<h3>' . __( 'Tracking API Settings', 'order-tracking' ) . '</h3>',
			),

			'enable_api' => array(
				'id' => 'enable_api',
				'name'    => __( 'Enable Tracking API', 'order-tracking' ),
				'desc'    => __( 'Check this option to enable order tracking API', 'order-tracking' ),
				'type' => 'checkbox',
			),

			'pl8app_order_api_key' => array(
				'id'   => 'pl8app_order_api_key',
				'name' => __( 'API Key', 'order-tracking' ),
				'type' => 'text',
				'allow_blank' => false,
				'readonly' => true,
				'std'  => $this->pl8app_ota_generate_api_key()
			),

			'printing_api_settings' => array(
				'id'    => 'printing_api_settings',
				'type'  => 'header',
				'name'  => '<h3>' . __( 'Printing Contents', 'order-tracking' ) . '</h3>',
			),

			'order_print_store_logo' => array(
		        'id'    => 'ot_store_logo',
		        'name'  => __('Store Logo', 'pl8app-printer'),
		        'desc'  => __('Select an image to use as the logo in the invoice. Recommended size 280x75.', 'pl8app-printer'),
		        'type'  => 'upload',
		    ),

			'ot_footer_content' => array(
				'id'   => 'ot_footer_content',
		        'name' => __( 'Footer Content', 'order-tracking' ),
		        'desc' => __( 'Enter the details you want to show on invoice below the items listing.You can add image and align the content using the editor.', 'order-tracking' ),
		        'type' => 'rich_editor',
		    ),
		);

		return $general_settings;
	}

	/**
	 * Generating the unique API key on the 1st Load of Settings page
	 *
	 * Note: It remains same for an installation. It can not be changed
	 * from addon Settings page
	 *
	 * @since 1.0.0
	 * @access private
	 * @return str $unique_key
	 */
	public function pl8app_ota_generate_api_key() {

		$unique_key = md5(microtime().rand());

		if( !get_option( 'pl8app_order_tracking_api' ) ) {
			add_option( 'pl8app_order_tracking_api', $unique_key );
		} else {
			$unique_key = get_option( 'pl8app_order_tracking_api' );
		}

		return $unique_key;
	}

	/**
	 * Generating domain key name to communicate with Firbase
	 *
	 * Note: It remains same for an installation. It can not be changed
	 * from addon Settings page.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return str $unique_key
	 */
	public static function pl8app_generate_domain_key_name() {

		$domain_name = $_SERVER['SERVER_NAME'];
		$domain_name = str_replace( '.', '', $domain_name );
		$domain_name = str_replace( '-', '', $domain_name );
		$unique_key  = $domain_name . '_' . rand();

		if( !get_option( 'pl8app_firebase_key_name' ) ) {
			add_option( 'pl8app_firebase_key_name', $unique_key );
		} else {
			$unique_key = get_option( 'pl8app_firebase_key_name' );
		}

		return $unique_key;
	}

	/**
	 * Generate Notification group to keep all Device
	 * IDs at one place.
	 *
	 * @since 1.0.0
	 */
	public function pl8app_firebase_generate_notification_group( $device_id ) {

		if( empty( $device_id ) )
			return __( 'Please provide valid device ID', 'order-tracking' );

  		// Store device ID and Validate with existing IDs
		if( get_option( 'pl8app_firebase_notification_device_ids' ) ) {

			$stored_device_ids = get_option('pl8app_firebase_notification_device_ids');
			if( in_array( $device_id, $stored_device_ids ) ) {
				return __( 'Device ID already exist.', 'order-tracking' );
			} else {
				array_push( $stored_device_ids, $device_id );
				update_option( 'pl8app_firebase_notification_device_ids', $stored_device_ids );
			}

		} else {
			update_option( 'pl8app_firebase_notification_device_ids', array($device_id) );
		}

		$key_name = self::pl8app_generate_domain_key_name();

		$url = 'https://iid.googleapis.com/iid/v1/'.$device_id.'/rel/topics/' . $key_name;

		$headers = array (
			'Content-Type: application/json',
			'Authorization: key=' . $this->fb_api_key,
		);

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$result = curl_exec ( $ch );
		$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close ( $ch );

		if( 200 == $http_status ) {
			return 'success';
		} else {
			return 'Invalid access token';
		}
	}

	/**
	 * Validating API key received via a API call with the
	 * unique key stored in the website DB
	 *
	 * @since 1.0.0
	 * @access public
	 * @param str $api_key The key received from API Call
	 * @return bool
	 *
	 */
	public function validate_api_key( $api_key ) {

		// Validate License Key and Status before Verifying API Key
		if( !get_option( 'pl8app_pl8app___order_tracking_api_status') || 'valid' !== get_option( 'pl8app_pl8app___order_tracking_api_status') ) {
			return __( 'Your license key has been expired or not updated.', 'order-tracking' );
		}

		// Validate the API Key
		if( get_option( 'pl8app_order_tracking_api' ) ) {
			$unique_key = get_option( 'pl8app_order_tracking_api' );
			if( sanitize_text_field( $api_key ) == $unique_key ) {
				return 'success';
			} else {
				return __( 'You have given invalid API key. Kindly check again.', 'order-tracking' );
			}
		} else {
			return __( 'You have given invalid API key. Kindly check again.', 'order-tracking' );
		}
	}

	/**
	 * Validating API call using WP Rest API. It validates the API Key
	 * sent from the Android App and returns status.
	 *
	 * @since 1.0.0
	 * @param arr $attr
	 * @return arr $response object
	 *
	 */
	public function pl8app_order_tracking_validate( $attr ) {

		if( !empty($attr['api_key'])) {

			if( get_option( 'pl8app_order_tracking_api' ) ) {

				$unique_key = get_option( 'pl8app_order_tracking_api' );
				if( sanitize_text_field( $attr['api_key'] ) == $unique_key ) {

					$response_array = array(
						'status'  => 'success',
						'message' => __( 'API key accepted', 'order-tracking' ),
					);

					$response = new WP_REST_Response( $response_array );
					$response->set_status(200);

				} else {

					$response_array = array(
						'status'  => 'error',
						'message' => __( 'API key is invalid.', 'order-tracking' ),
					);
					$response = new WP_REST_Response( $response_array );
					$response->set_status(403);
				}

			} else {

				$response_array = array(
					'status'  => 'error',
					'message' => __( 'Order tracking API is not enabled. Please check plugin settings on website or contact your administrator.', 'order-tracking' ),
				);
				$response = new WP_REST_Response( $response_array );
				$response->set_status(403);
			}

		} else {

			$response_array = array(
				'message' => __( 'Please provide the API key to proceed.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);
		}

		return $response;
	}

	/**
	 * Validating user login using WP Rest API. It validates the API Key
	 * as well as the username and password received by GET method
	 *
	 * @since 1.0.0
	 * @param arr $attr
	 * @return arr $response object with user ID and user meta
	 *
	*/
	public function pl8app_order_tracking_login() {

		if( empty( $_GET['api_key'] ) ) {
			$response_array = array(
				'message' => __( 'Please provide the API key to proceed.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$validate_api_key = $this->validate_api_key( $_GET['api_key']);

		if( 'success' !== $validate_api_key ) {

			$response_array = array(
				'message' => $validate_api_key
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		if( empty( $_GET['username'] ) || empty( $_GET['password'] ) ) {

			$response_array = array(
				'message' => __( 'Please provide Username and Password.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;

		} else {

			$username = isset( $_GET['username'] ) ? base64_decode( $_GET['username'] ) : '';
			$password = isset( $_GET['password'] ) ? base64_decode( $_GET['password'] ) : '';

			$creds = array();
			$creds['user_login'] 	= $username;
			$creds['user_password'] = $password;
			$creds['remember'] 		= true;

			$user = wp_signon( $creds, true );

			if( is_wp_error( $user ) ) {

				$response_array = array(
					'message' => wp_strip_all_tags( $user->get_error_message() )
				);
				$response = new WP_REST_Response( $response_array );
				$response->set_status(401);

				return $response;

			} else {

				$firebase_message = '';

		    	// Firebase - Generate Notification Group
				if( isset( $_GET['device_id'] ) && $_GET['device_id'] != '' ) {
					$firebase_message = $this->pl8app_firebase_generate_notification_group($_GET['device_id']);
				}

				$user_meta = get_user_meta($user->ID);
				$response_meta = array();
				$response_meta['nickname'] = $user_meta['nickname'];
				$response_meta['first_name'] = $user_meta['first_name'];
				$response_meta['last_name'] = $user_meta['last_name'];
				$response_meta['description'] = $user_meta['description'];
				$response_meta['capabilities'] = $user_meta['wp_capabilities'];
				$response_meta['user_level'] = $user_meta['wp_user_level'];
				$response_meta['address'] = $user_meta['_pl8app_user_address'];

				$response_array = array(
					'user_id' => $user->ID,
					'user_meta' => $response_meta,
					'firebase_status' => $firebase_message,
				);
				$response = new WP_REST_Response( $response_array );
				$response->set_status(200);

				return $response;
			}
		}
	}

	/**
	 * pl8app Order Listing API call. It will be validate using
	 * API key. Have multiple arguments to filter the results with.
	 *
	 * @since 1.0.0
	 * @return arr $response object with list of Orders
	 *
	 */
	public function pl8app_order_listings_callback() {

		if( empty( $_GET['api_key'] ) ) {

			$response_array = array(
				'message' => __( 'Please provide the API key to proceed.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$validate_api_key = $this->validate_api_key( $_GET['api_key']);

		if( 'success' !== $validate_api_key ) {

			$response_array = array(
				'message' => $validate_api_key
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$paged   	= isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$per_page   = isset( $_GET['per_page'] ) ? $_GET['per_page'] : 10;
		$orderby    = isset( $_GET['orderby'] ) ? urldecode( $_GET['orderby'] ) : 'ID';
		$order      = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$user       = isset( $_GET['user'] ) ? $_GET['user'] : null;
		$customer   = isset( $_GET['customer'] ) ? $_GET['customer'] : null;
		$meta_key   = isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;
		$year       = isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month      = isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day        = isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$payment_status = isset( $_GET['payment_status'] ) ? strtolower($_GET['payment_status']) : 'any';
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field($_GET['end-date']) : $start_date;

		$args = array(
			'number'     => $per_page,
			'page'       => $paged,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'customer'   => $customer,
			'status'     => $payment_status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
		);

		if( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {

			$args['search_in_notes'] = true;
			$args['s'] = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		$status_meta = array();
		$service_meta = array();
		$service_date_meta = array();

		// Get orders by order status
		if( isset( $_GET['status'] ) && $_GET['status'] != '' ) {
			if( strpos( $_GET['status'], ',' ) ) {
				$all_status = explode(',', $_GET['status'] );
				$compare = 'IN';
			} else {
				$all_status = $_GET['status'];
				$compare = '=';
			}
		} else {
			$all_status = pl8app_get_order_statuses();
			$compare = 'IN';
		}

		// Order status meta query
		$status_meta = array(
            'key'     => '_order_status',
            'value'   => $all_status,
            'compare' => $compare,
        );

		// Get Orders by Service Type
		if( isset( $_GET['service'] ) && $_GET['service'] != '' ) {
			$service_meta = array(
	            'key'     => '_pl8app_delivery_type',
	            'value'   => $_GET['service'],
	            'compare' => '=',
	        );
		}

		// Get orders by Service date
		if( isset( $_GET['service_date'] ) && $_GET['service_date'] != '' ) {
			$service_meta = array(
	            'key'     => '_pl8app_delivery_date',
	            'value'   => $_GET['service_date'],
	            'compare' => '=',
	        );
		}

		$args['meta_query'] = array(
			'relation' => 'AND',
			$status_meta,
			$service_meta,
			$service_date_meta,
		);

		// $args['meta_key'] = '_order_status';
		// $args['meta_value'] = $all_status;
		// $args['meta_compare'] = $compare;

		$payments_query  = new pl8app_Payments_Query( $args );
		$api_listings = [];

		// $c = ($paged - 1) * $per_page;
		$c = 0;
		foreach ( $payments_query->get_payments() as $payment_data ) {

			$updated_cart_items = array();
			foreach ( $payment_data->cart_details as $key => $single_cart_item) {

				$addon_items = $single_cart_item['addon_items'];

		        // Remove from Cart Item Array as They are Repeating
				unset($single_cart_item['addon_items']);
				unset($single_cart_item['item_number']);

		        // Removed From Addon Array as they are unnecessary
				unset($addon_items['quantity']);
				unset($addon_items['price_id']);

				$cart_item = $single_cart_item;
				foreach ( $addon_items as $addon) {
					$cart_item['addon_items'][] = $addon;
				}

				if( is_array( $cart_item ) ) {
					array_push( $updated_cart_items, $cart_item );
				}
			}

			$delivery_address = get_post_meta( $payment_data->ID, '_pl8app_delivery_address', true );
			$service_type = get_post_meta( $payment_data->ID, '_pl8app_delivery_type', true );
			$service_date = get_post_meta( $payment_data->ID, '_pl8app_delivery_date', true );
			$service_date = ! empty( $service_date ) ? date_i18n( get_option( 'date_format' ), strtotime($service_date) ) : '';
			$service_time = get_post_meta( $payment_data->ID, '_pl8app_delivery_time', true );
			$dinein_table = isset( $payment_data->payment_meta['pl8app_dinein_table_id'] ) ? $payment_data->payment_meta['pl8app_dinein_table_id'] : '';
			$dinein_time  = get_post_meta( $payment_data->ID, 'dinein_order_take_time', true );
			$dinein_time  = ! empty( $dinein_time ) ? date_i18n( get_option( 'time_format' ), $dinein_time ) : '';

			$user_info 					= array();
			$user_info['first_name'] 	= $payment_data->user_info['first_name'];
			$user_info['last_name'] 	= $payment_data->user_info['last_name'];
			$user_info['discount'] 		= $payment_data->user_info['discount'];
			$user_info['id'] 			= $payment_data->user_info['id'];
			$user_info['email'] 		= isset( $payment_data->payment_meta['email'] ) ? $payment_data->payment_meta['email'] : '';
			$user_info['phone'] 		= isset( $payment_data->payment_meta['phone'] ) ? $payment_data->payment_meta['phone'] : '';

			$api_listings[$c]['id'] 				= $payment_data->ID;
			$api_listings[$c]['user_info'] 			= $user_info;
			$api_listings[$c]['cart_details'] 		= $updated_cart_items;
			$api_listings[$c]['order_date_time'] 	= $payment_data->date;
			$api_listings[$c]['gateway'] 			= pl8app_get_gateway_checkout_label($payment_data->gateway);
			$api_listings[$c]['currency'] 			= $payment_data->currency;
			$api_listings[$c]['currency_symbol'] 	= pl8app_currency_symbol($payment_data->currency);
			$api_listings[$c]['subtotal'] 			= $payment_data->subtotal;
			$api_listings[$c]['order_total'] 		= $payment_data->total;
			$api_listings[$c]['fees'] 				= $payment_data->fees;
			$api_listings[$c]['transaction_id'] 	= $payment_data->transaction_id;
			$api_listings[$c]['payment_status'] 	= pl8app_get_payment_status_label($payment_data->status);
			$api_listings[$c]['order_status'] 		= pl8app_get_order_status( $payment_data->ID );
			$api_listings[$c]['order_note'] 		= get_post_meta( $payment_data->ID, '_pl8app_order_note', true );

			$api_listings[$c]['service_type'] 		= pl8app_service_label($service_type);
			$api_listings[$c]['delivery_address'] 	= $delivery_address;
			$api_listings[$c]['service_date'] 		= $service_date;
			$api_listings[$c]['service_time'] 		= $service_time;
			$api_listings[$c]['dinein_table'] 		= ( $service_type == 'dinein' ) ? $dinein_table : '';
			$api_listings[$c]['dinein_time'] 		= $dinein_time;

			$c++;
		}

		$response_array = array(
			'listings' => $api_listings
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * pl8app Order Detail API call. It needs the order ID as
	 * cumpolsory get parameter using which it will prepare the order
	 * to return.
	 *
	 * @since 1.0.0
	 * @return arr $response object with Order Detail
	 *
	 */
	public function pl8app_order_details_callback( $attr ) {

		if( empty( $_GET['api_key'] ) ) {

			$response_array = array(
				'message' => __( 'Please provide the API key to proceed.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$validate_api_key = $this->validate_api_key( $_GET['api_key']);

		if( 'success' !== $validate_api_key ) {

			$response_array = array(
				'message' => $validate_api_key
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$args = array(
			'post__in' => array( $attr['order_id'] ),
		);

		$payments_query  = new pl8app_Payments_Query( $args );

		$api_listings = [];

		$c = 1;

		foreach ( $payments_query->get_payments() as $payment_data ) {

			$updated_cart_items = array();
			foreach ( $payment_data->cart_details as $key => $single_cart_item) {

				$addon_items = $single_cart_item['addon_items'];

		        // Remove from Cart Item Array as They are Repeating
				unset($single_cart_item['addon_items']);
				unset($single_cart_item['item_number']);

		        // Removed From Addon Array as they are unnecessary
				unset($addon_items['quantity']);
				unset($addon_items['price_id']);

				$cart_item = $single_cart_item;
				foreach ( $addon_items as $addon) {
					$cart_item['addon_items'][] = $addon;
				}
				array_push( $updated_cart_items, $cart_item );
			}

			$delivery_address = get_post_meta( $payment_data->ID, '_pl8app_delivery_address', true );
			$service_type = get_post_meta( $payment_data->ID, '_pl8app_delivery_type', true );
			$service_date = get_post_meta( $payment_data->ID, '_pl8app_delivery_date', true );
			$service_date = ! empty( $service_date ) ? date_i18n( get_option( 'date_format' ), strtotime($service_date) ) : '';
			$service_time = get_post_meta( $payment_data->ID, '_pl8app_delivery_time', true );
			$dinein_table = isset( $payment_data->payment_meta['pl8app_dinein_table_id'] ) ? $payment_data->payment_meta['pl8app_dinein_table_id'] : '';
			$dinein_time  = get_post_meta( $payment_data->ID, 'dinein_order_take_time', true );
			$dinein_time  = ! empty( $dinein_time ) ? date_i18n( get_option( 'time_format' ), $dinein_time ) : '';

			$user_info = array();
			$user_info['first_name'] 	= $payment_data->user_info['first_name'];
			$user_info['last_name'] 	= $payment_data->user_info['last_name'];
			$user_info['discount'] 		= $payment_data->user_info['discount'];
			$user_info['id'] 			= $payment_data->user_info['id'];
			$user_info['email'] 		= isset( $payment_data->payment_meta['email'] ) ? $payment_data->payment_meta['email'] : '';
			$user_info['phone'] 		= isset( $payment_data->payment_meta['phone'] ) ? $payment_data->payment_meta['phone'] : '';

			$api_listings[$c]['id'] 				= $payment_data->ID;
			$api_listings[$c]['user_info'] 			= $user_info;
			$api_listings[$c]['cart_details'] 		= $updated_cart_items;
			$api_listings[$c]['order_date_time'] 	= $payment_data->date;
			$api_listings[$c]['gateway'] 			= pl8app_get_gateway_checkout_label($payment_data->gateway);
			$api_listings[$c]['currency'] 			= $payment_data->currency;
			$api_listings[$c]['currency_symbol'] 	= pl8app_currency_symbol($payment_data->currency);
			$api_listings[$c]['subtotal'] 			= $payment_data->subtotal;
			$api_listings[$c]['order_total'] 		= $payment_data->total;
			$api_listings[$c]['fees'] 				= $payment_data->fees;
			$api_listings[$c]['transaction_id'] 	= $payment_data->transaction_id;
			$api_listings[$c]['payment_status'] 	= pl8app_get_payment_status_label($payment_data->status);
			$api_listings[$c]['order_status'] 		= pl8app_get_order_status( $payment_data->ID );
			$api_listings[$c]['order_note'] 		= get_post_meta( $payment_data->ID, '_pl8app_order_note', true );

			$api_listings[$c]['service_type'] 		= pl8app_service_label($service_type);
			$api_listings[$c]['delivery_address'] 	= $delivery_address;
			$api_listings[$c]['service_date'] 		= $service_date;
			$api_listings[$c]['service_time'] 		= $service_time;
			$api_listings[$c]['dinein_table'] 		= ( $service_type == 'dinein' ) ? $dinein_table : '';
			$api_listings[$c]['dinein_time'] 		= $dinein_time;

			$c++;
		}

		$response_array = array(
			'listings' => $api_listings
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * pl8app Order Status update API call. It needs the Order
	 * ID and status as cumpolsory values. Upon validating it will
	 * then update the order status and returns the status.
	 *
	 * @since 1.0.0
	 * @return arr $response object with success message
	 *
	 */
	public function pl8app_order_update_callback( $attr ) {

		if( empty( $_GET['api_key'] ) ) {

			$response_array = array(
				'message' => __( 'Please provide the API key to proceed.', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$validate_api_key = $this->validate_api_key( $_GET['api_key']);

		if( 'success' !== $validate_api_key ) {

			$response_array = array(
				'message' => $validate_api_key
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		if( !empty( $attr['order_id'] ) && !empty( $attr['order_status'] ) ) {
			pl8app_update_order_status( $attr['order_id'], $attr['order_status'] );
		}

		if( isset( $_GET['current_time'] ) && !empty( $_GET['current_time'] ) ) {
			update_post_meta( $attr['order_id'], 'dinein_order_current_time', $_GET['current_time'] );
		}

		if( isset( $_GET['serve_time'] ) && !empty( $_GET['serve_time'] ) ) {
			update_post_meta( $attr['order_id'], 'dinein_order_take_time', $_GET['serve_time'] );
		}

		$response_array = array(
			'message' => 'Order status successfully updated.'
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * pl8app Order Status list API call.
	 *
	 * @since 1.0.0
	 * @return arr $response object with success message
	 *
	 */
	public function pl8app_order_status_callback( $attr ) {

		if( ! empty( $_GET['type'] ) && $_GET['type'] == 'payment' ) {

			$statuses = pl8app_get_payment_statuses();

			if( function_exists( 'pl8app_get_payment_status_colors' ) ) {
				$color_codes = pl8app_get_payment_status_colors();
			} else {
				$color_codes = array(
					'pending' 			=> '#fcbdbd',
					'pending_text' 		=> '#333333',
					'publish' 			=> '#e0f0d7',
					'publish_text' 		=> '#3a773a',
					'refunded' 			=> '#e5e5e5',
					'refunded_text' 	=> '#777777',
					'failed' 			=> '#e76450',
					'failed_text' 		=> '#ffffff',
			    	'processing'		=> '#f7ae18',
			    	'processing_text'	=> '#ffffff',
				);
			}

		} else {

			$statuses = pl8app_get_order_statuses();

			if( function_exists( 'pl8app_get_order_status_colors' ) ) {
				$color_codes = pl8app_get_order_status_colors();
			} else {
				$color_codes = array(
					'pending' => '#800000',
					'accepted' => '#008000',
					'processing' => '#808000',
					'ready' => '#00FF00',
					'transit' => '#800080',
					'cancelled' => '#FF0000',
					'completed' => '#FFFF00',
				);
			}
		}

		$response_array = array(
			'statuses' => $statuses,
			'color_codes' => $color_codes,
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * pl8app Services list API call.
	 *
	 * @since 1.0.0
	 * @return arr $response object with success message
	 *
	 */
	public function pl8app_service_list_callback( $attr ) {

		$statuses = pl8app_get_service_types();

		$response_array = array(
			'services' => $statuses,
		);

		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * pl8app API Callback to Count Orders
	 *
	 * @since 1.0.0
	 * @return arr $response object with success message
	 *
	 */
	public function pl8app_order_count_callback( $attr ) {

		$purchases = pl8app_count_payments();

		$response_array = array(
			'orders' => $purchases,
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * Send the content for print page footer area
	 * This function will be modified later to make it more dynamic
	 *
	 * @since 1.0.0
	 * @author pl8app
	 */
	public function pl8app_print_content_callback () {

		$text_array = array();

		$get_settings = pl8app_Order_Tracking::get_api_settings();

	    if( isset( $get_settings['ot_footer_content'] ) ) {
	    	$html = $get_settings['ot_footer_content'];
	    	$texts = preg_split("/\r\n|\n|\r/", $html);
	    }

	    if( isset( $get_settings['ot_store_logo'] ) ) {
	    	$logo = $get_settings['ot_store_logo'];
	    } else {
	    	$logo = '';
	    }

	    if( !empty( $texts ) ) {
	    	foreach ( $texts as $key => $line ) {
		    	$text = wp_strip_all_tags( $line );
		    	array_push($text_array, $text );
		    }
	    }

	    $response_array = array(
	    	'logo'		=> $logo,
			'contents' 	=> $text_array,
		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status(200);

		return $response;
	}

	/**
	 * Remove device ID from FCM once user loggedout from App
	 *
	 * @since 1.0.0
	 * @author pl8app
	 */
	public function pl8app_user_logout_callback() {

		// Return if no device ID received
		if( empty( $_GET['device_id'] ) ) {

			$response_array = array(
				'message' => __( 'Device ID is empty', 'order-tracking' )
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}

		$device_id = $_GET['device_id'];

  		// Store device ID and Validate with existing IDs
		if( get_option( 'pl8app_firebase_notification_device_ids' ) ) {

			$stored_device_ids = get_option('pl8app_firebase_notification_device_ids');
			if (($key = array_search($device_id, $stored_device_ids)) !== false) {
				unset($stored_device_ids[$key]);
				update_option('pl8app_firebase_notification_device_ids', $stored_device_ids);
			}
		}

		$key_name = self::pl8app_generate_domain_key_name();
		$url = 'https://iid.googleapis.com/iid/v1/'.$device_id.'/rel/topics/' . $key_name;

		$headers = array (
			'Content-Type: application/json',
			'Authorization: key=' . $this->fb_api_key,
		);

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

		$result = curl_exec ( $ch );
		$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
		curl_close ( $ch );

		if( 200 == $http_status ) {

			$response_array = array(
				'message' => 'success',
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(200);

			return $response;

		} else {

			$response_array = array(
				'message' => 'Device ID not found.',
			);
			$response = new WP_REST_Response( $response_array );
			$response->set_status(403);

			return $response;
		}
	}

	/**
	 *
	 * pl8app Order Tracking EndPoints.
	 *
	 * @since 1.0.0
	 */
	public function pl8app_ota_create_endpoint() {

		register_rest_route( 'pl8app-api/v1', 'validate-api/(?P<api_key>[a-zA-Z0-9-]+)',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_tracking_validate' ),
		));

		register_rest_route( 'pl8app-api/v1', 'validate-login',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_tracking_login' ),
		));

		register_rest_route( 'pl8app-api/v1', 'statuses',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_status_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'services',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_service_list_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'order_counts',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_count_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'orders',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_listings_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'order_detail/(?P<order_id>\d+)',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_details_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'order_update/(?P<order_id>\d+)/(?P<order_status>[a-z-]+)',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_order_update_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'print_content',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_print_content_callback' ),
		));

		register_rest_route( 'pl8app-api/v1', 'logged_out',	array(
			'methods'  => 'GET',
			'callback' => array( $this, 'pl8app_user_logout_callback' ),
		));
	}

	/**
	 * New order notification generation once a order is placed
	 * It does not check whether the payment is successful or not
	 *
	 * @since 1.0.0
	 * @param int $payment_id Payment ID
	 * @param object $payment Complete Payment Object
	 */
	public function pl8app_api_new_order_notification ( $payment_id, $payment ) {

		$do_notify = false;

		if( $payment->status == 'pending' ) {
			$do_notify = true;
		}

  		// Notification to be sent only if the payment status is pending
		if( ! is_admin() && ! empty( $payment->menuitems ) && $do_notify ) :

			// If transient already created for auto print then exit
      		if( get_transient( 'pl8app_fb_notify_' . $payment_id ) )
        		return '';

      		// Create transient to avoid multiple print
      		set_transient( 'pl8app_fb_notify_' . $payment_id, 'on_progress', 300 );

			$order_total = number_format( (float) $payment->total, 2, '.', '' );
			$currency = $payment->currency;
			$customer = $payment->first_name . ' ' . $payment->last_name;

			$title = __('You have received a new order !!', 'order-tracking' );
			$body = sprintf( __( '%s has placed a new order. Order total is %s %s', 'order-tracking' ), $customer, $currency, $order_total );

			$key_name = self::pl8app_generate_domain_key_name();

			$headers = array (
				'Content-Type: application/json',
				'Authorization: key=' . $this->fb_api_key
			);

			$fields = array (
				'condition' => "'".$key_name."' in topics",
				'data' => array(
					'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
					'order_id' => $payment_id
				),
				'notification' => array(
					'title' => $title,
					'body' => $body
				),
				'aps' => array(
					'alert' => array(
						'title' => $title,
						'body' => $body
					)
				)
			);

			$fields = json_encode ( $fields );

			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
			curl_setopt ( $ch, CURLOPT_POST, true );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

			$result = curl_exec ( $ch );
			curl_close ( $ch );

		endif;
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'order-tracking', false, dirname( plugin_basename( PL8_PLUGIN_FILE ) ). '/languages/' );
	}
}